// Layout checks against the isolated BS verification database; no business forms are submitted.
// Run from the project root: node scripts/mobile-browser-test.mjs
import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, mkdirSync, openSync } from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const fixture = JSON.parse(readFileSync(process.env.MOBILE_FIXTURE || 'storage/app/private/mobile-preview/fixture.json'));
const run = path.join(root, 'storage/app/private/mobile-preview', String(Date.now()));
mkdirSync(run, { recursive: true });
const base = 'http://127.0.0.1:8098';
const log = openSync(path.join(run, 'server.log'), 'w');
const server = spawn(process.env.PHP_BINARY || 'C:/php82/php.exe', ['-d', 'memory_limit=512M', '-S', '127.0.0.1:8098', '-t', 'public', 'scripts/bs-uat-router.php'], { windowsHide: true, stdio: ['ignore', log, log] });
const browser = spawn(process.env.CHROME_BINARY || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--remote-debugging-port=0', '--user-data-dir=' + path.join(run, 'profile'), '--no-first-run', '--no-default-browser-check', '--disable-extensions', 'about:blank'], { windowsHide: true, stdio: 'ignore' });
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
let socket;
let call;
let currentPage;
let activeWidth = 390;
const results = [];
const errors = [];
const interactions = [];
const detailPages = new Map();
try {
    let debugBase;
    for (let attempt = 0; attempt < 100; attempt++) {
        try {
            debugBase = 'http://127.0.0.1:' + readFileSync(path.join(run, 'profile/DevToolsActivePort'), 'utf8').split('\n')[0].trim();
            await fetch(base + '/login'); await fetch(debugBase + '/json/version'); break;
        } catch { await sleep(150); }
    }
    const target = await (await fetch(debugBase + '/json/new?about:blank', { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
    const pending = new Map();
    let nextId = 0;
    call = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++nextId;
        const timer = setTimeout(() => { pending.delete(id); reject(new Error('Timeout: ' + method)); }, 20000);
        pending.set(id, { resolve: result => { clearTimeout(timer); resolve(result); }, reject: error => { clearTimeout(timer); reject(error); } });
        socket.send(JSON.stringify({ id, method, params }));
    });
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.id) {
            const handler = pending.get(message.id);
            if (handler) { pending.delete(message.id); message.error ? handler.reject(new Error(message.error.message)) : handler.resolve(message.result); }
        }
        if (message.method === 'Runtime.exceptionThrown') errors.push({ page: currentPage, error: message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text });
    });
    await call('Page.enable');
    await call('Runtime.enable');
    await call('Network.enable');
    const js = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
        return result.result.value;
    };
    const wait = async expression => {
        for (let attempt = 0; attempt < 300; attempt++) { try { if (await js(expression)) return; } catch {} await sleep(100); }
        console.log(await js(`JSON.stringify({url:location.href,state:document.readyState,styles:[...document.querySelectorAll('link[rel=stylesheet]')].map(e=>({href:e.href,loaded:!!e.sheet})),scripts:[...document.scripts].filter(e=>e.src).map(e=>e.src)})`));
        throw new Error('Timed out waiting at ' + currentPage + ': ' + await js('location.href'));
    };
    const navigate = async url => {
        currentPage = url;
        await call('Page.navigate', { url: base + url });
        await wait(`(location.pathname === ${JSON.stringify(url.split('?')[0])} || (${JSON.stringify(url)} === '/dashboard' && location.pathname === '/dashboard/transaksi')) && document.readyState !== 'loading'`);
        await js('Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 3000))]).then(() => true)');
        await sleep(100);
    };
    const viewport = async (width, height, mobile) => {
        activeWidth = width;
        await call('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile });
        await call('Emulation.setTouchEmulationEnabled', { enabled: mobile });
    };
    const screenshot = async label => {
        const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
        writeFileSync(path.join(run, label + '.png'), Buffer.from(shot.data, 'base64'));
    };
    const assert = async (label, expression) => {
        if (!await js(expression)) throw new Error('Interaction failed: ' + label);
        interactions.push(label);
    };
    const tap = async selector => {
        await js(`(() => {const e=document.querySelector(${JSON.stringify(selector)});const r=e.getBoundingClientRect();if(r.top<0 || r.bottom>innerHeight)e.scrollIntoView({block:'center',behavior:'instant'});})()`);
        await sleep(100);
        const point = await js(`(() => {const r=document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2};})()`);
        await call('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [point] });
        await call('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
        await sleep(350);
    };
    const inspect = async label => {
        const result = await js(`(() => {
            const width = ${activeWidth};
            const overflowing = [...document.querySelectorAll('body *')].filter(e => {
                const r = e.getBoundingClientRect();
                if (!r.width || getComputedStyle(e).position === 'fixed' || e.closest('.sidebar')) return false;
                if (e.parentElement?.closest('.table-responsive, .table-container, .profile-subordinates__scroll')) return false;
                return r.right > width + 2 || r.left < -2;
            }).slice(0, 15).map(e => ({ tag: e.tagName, class: e.className, text: e.innerText?.slice(0, 55), width: Math.round(e.getBoundingClientRect().width) }));
            const inputs = [...document.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea')].filter(e => e.getClientRects().length).map(e => ({ name: e.name, size: parseFloat(getComputedStyle(e).fontSize) }));
            return { width, documentWidth: document.documentElement.scrollWidth, overflow: document.documentElement.scrollWidth > width + 2, overflowing, smallInputs: inputs.filter(e => e.size < 16), serverError: /Server Error|403\\s+Forbidden/.test(document.body.innerText), title: document.title };
        })()`);
        results.push({ page: currentPage, label, ...result });
        if (result.overflow || result.serverError) console.log(JSON.stringify({ label, ...result }));
        if (process.env.MOBILE_SCREENSHOTS === 'all' || result.overflow || ['login', 'dashboard-transaksi', 'profile', 'transaksi-operasional-create', 'roles'].some(name => label === '390-' + name)) await screenshot(label);
    };
    for (const [width, height] of [[320, 568], [390, 844], [844, 390]]) {
        await viewport(width, height, true);
        await navigate('/login');
        await inspect(width + '-login');
        await assert('login inputs fit at ' + width, `document.documentElement.scrollWidth <= innerWidth + 1 && parseFloat(getComputedStyle(document.querySelector('#password')).fontSize) >= 16`);
    }
    await js(`document.querySelector('[name=username]').value = ${JSON.stringify(fixture.accounts.finance.username)}; document.querySelector('[name=password]').value = ${JSON.stringify(fixture.accounts.finance.password)}; document.querySelector('form').requestSubmit();`);
    await wait(`location.pathname !== '/login' && document.readyState === 'complete'`);
    const pages = ['/dashboard', '/dashboard/transaksi', '/profile', '/pengajuan-bs', '/pengajuan-bs/create', '/transaksi-operasional', '/transaksi-operasional/create', '/transaksi-perjalanan-dinas', '/transaksi-perjalanan-dinas/create', '/event-dalam-kota', '/event-dalam-kota/create', '/event-luar-kota', '/event-luar-kota/create', '/approval-atasan', '/approval-finance', '/cash-flow', '/cash-flow/' + fixture.period, '/laporan-bs', '/saldo-bs', '/persetujuan-pemakaian-bs', '/pengecekan-pemakaian-bs', '/pengecekan-pemakaian-bs/laporan', '/dashboard/master-data', '/roles', '/levels', '/positions', '/companies', '/cost_categories', '/weekday_groups', '/event_hour_groups', '/users', '/wilayah', '/setting_plafon_perjalanan_dinas', '/plafon-event-dalam-kota', '/plafon-event-luar-kota', '/periode', '/access-management'];
    const widths = (process.env.MOBILE_WIDTHS || '320,390,844,1366').split(',').map(Number);
    for (const width of widths) {
        await viewport(width, width === 844 ? 390 : 844, width < 992);
        for (const url of (process.env.MOBILE_PAGES ? process.env.MOBILE_PAGES.split(',') : pages)) {
            await navigate(url);
            await inspect(width + '-' + url.slice(1).replaceAll('/', '-'));
            if (process.env.MOBILE_DETAILS === '1') {
                const links = await js(`Array.from(document.querySelectorAll('a[href]')).map(a => new URL(a.href)).filter(u=>u.origin===location.origin).map(u=>u.pathname)`);
                for (const link of links) {
                    const detail = /^\/(pengajuan-bs|transaksi-operasional|transaksi-perjalanan-dinas|event-dalam-kota|event-luar-kota)\/[^/]+$/.test(link) && !/\/(create|export)$/.test(link);
                    const edit = /^\/(roles|users|companies|levels|positions|cost_categories|wilayah|event_hour_groups|weekday_groups|setting_plafon_perjalanan_dinas|plafon-event-dalam-kota|plafon-event-luar-kota)\/[^/]+\/edit$/.test(link);
                    const approval = /^\/approval-(atasan|finance)\/\d+\/detail$/.test(link);
                    if (detail || edit || approval) {
                        const key = link.split('/')[1] + (edit ? '-edit' : '-detail');
                        if (!detailPages.has(key)) detailPages.set(key, link);
                    }
                }
            }
        }
        console.log('Completed width ' + width);
    }
    for (const width of detailPages.size ? [320, 390, 844, 1366] : []) {
        await viewport(width, width === 844 ? 390 : 844, width < 992);
        for (const [name, url] of detailPages) {
            await navigate(url);
            await inspect(width + '-' + name);
        }
        console.log('Completed detail pages at ' + width);
    }
    await viewport(390, 844, true);
    await navigate('/dashboard/transaksi');
    await js('window.scrollTo(0, 350)');
    await sleep(300);
    const scrollBefore = await js('scrollY');
    await tap('#menuToggle');
    await assert('drawer opens, traps background and focuses close button', `document.querySelector('#appSidebar').classList.contains('is-open') && document.querySelector('.main-content').inert && document.activeElement.id === 'sidebarClose' && getComputedStyle(document.body).position === 'fixed'`);
    await screenshot('390-sidebar-open');
    await call('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape' });
    await sleep(350);
    await assert('drawer closes and restores scroll and focus', `!document.querySelector('.main-content').inert && !document.body.classList.contains('sidebar-open') && Math.abs(scrollY - ${scrollBefore}) <= 1 && document.activeElement.id === 'menuToggle'`);
    await tap('#menuToggle');
    await call('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ x: 380, y: 200 }] });
    await call('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
    await assert('tapping backdrop closes drawer', `!document.body.classList.contains('sidebar-open')`);
    await tap('#menuToggle');
    await viewport(844, 390, true);
    await assert('drawer fits landscape height', `document.querySelector('#appSidebar').getBoundingClientRect().bottom <= innerHeight + 1`);
    await viewport(1366, 900, false);
    await assert('desktop resize releases mobile dialog', `!document.querySelector('#appSidebar').hasAttribute('aria-modal') && !document.querySelector('.main-content').inert && !document.body.classList.contains('sidebar-open')`);
    await viewport(390, 844, true);
    await tap('#notifBell');
    await assert('notification panel fits phone viewport', `(() => {const r=document.querySelector('#notifPanel').getBoundingClientRect();return r.width>0 && r.left>=0 && r.right<=innerWidth+1 && r.bottom<=innerHeight+1 && document.querySelector('#notifBell').getAttribute('aria-expanded')==='true';})()`);
    await screenshot('390-notifications');
    await viewport(844, 390, true);
    await assert('notification panel fits landscape height', `document.querySelector('#notifPanel').getBoundingClientRect().bottom <= innerHeight + 1`);
    await call('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape' });

    for (const url of ['/transaksi-operasional/create', '/transaksi-perjalanan-dinas/create']) {
        await viewport(390, 844, true);
        await navigate(url);
        await assert('expense form has mobile labels: ' + url, `document.querySelectorAll('#tabel-detail td[data-label]').length >= 4 && getComputedStyle(document.querySelector('#tabel-detail td')).display === 'block'`);
        const initialRows = await js(`document.querySelector('#tabel-detail tbody').rows.length`);
        await tap('#btn-tambah-baris');
        await assert('new expense row retains mobile labels: ' + url, `document.querySelector('#tabel-detail tbody').rows.length === ${initialRows + 1} && [...document.querySelector('#tabel-detail tbody').rows].every(r=>[...r.cells].every(c=>c.hasAttribute('data-label')))`);
        await js(`(() => {const input=document.querySelector('#tabel-detail tbody tr:last-child input.nominal-display, #tabel-detail tbody tr:last-child input.nominal-formatted');input.value='125000';input.dispatchEvent(new Event('input',{bubbles:true}));})()`);
        await assert('nominal formatting keeps numeric submission value: ' + url, `document.querySelector('#tabel-detail tbody tr:last-child input[type=hidden]').value === '125000'`);
        await js(`document.querySelector('#tabel-detail tbody tr:last-child').scrollIntoView({block:'center'})`);
        await sleep(200);
        await screenshot('390-' + url.split('/')[1] + '-expense-rows');
        await tap('#tabel-detail tbody tr:last-child .btn-hapus');
        await assert('expense row can be removed: ' + url, `document.querySelector('#tabel-detail tbody').rows.length === ${initialRows}`);
        await inspect('390-' + url.split('/')[1] + '-after-edit');
    }
    const failed = results.filter(r => r.overflow || r.serverError || (r.width < 992 && r.smallInputs.length));
    if (failed.length || errors.length) process.exitCode = 1;
    console.log(JSON.stringify({ pages: results.length, overflow: results.filter(r => r.overflow).map(r => r.label), smallInputs: results.filter(r => r.width < 992 && r.smallInputs.length).map(r => ({label:r.label, inputs:r.smallInputs})), serverErrors: results.filter(r => r.serverError).map(r => r.label), interactions: interactions.length, scriptErrors: errors, artifacts: run }));
} catch (error) {
    console.error(String(error));
    errors.push({ page: currentPage, error: String(error) });
    process.exitCode = 1;
} finally {
    writeFileSync(path.join(run, 'report.json'), JSON.stringify({ results, interactions, errors }, null, 2));
    if (socket?.readyState === WebSocket.OPEN) { try { await call('Browser.close'); } catch {} }
    socket?.close(); browser.kill(); server.kill();
}
