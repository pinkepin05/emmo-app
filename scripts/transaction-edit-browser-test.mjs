// Detail/edit/save browser checks; uses only dedicated drafts in the verified isolated database.
// Run from the project root: node scripts/transaction-edit-browser-test.mjs
import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, mkdirSync, openSync } from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const fixture = JSON.parse(readFileSync(process.env.MOBILE_FIXTURE || 'storage/app/private/transaction-edit/fixture.json'));
const run = path.join(root, 'storage/app/private/transaction-edit', String(Date.now()));
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
    const fill = async (selector, value) => js(`(() => {const e=document.querySelector(${JSON.stringify(selector)});if(!e)throw Error('Missing '+${JSON.stringify(selector)});e.value=${JSON.stringify(String(value))};e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('change',{bubbles:true}));})()`);
    const click = async selector => { await js(`document.querySelector(${JSON.stringify(selector)}).click()`); await sleep(150); };
    await viewport(390, 844, true);
    await navigate('/login');
    await fill('[name=username]', fixture.accounts.finance.username);
    await fill('[name=password]', fixture.accounts.finance.password);
    await js("document.querySelector('form').requestSubmit()");
    await wait("location.pathname !== '/login' && document.readyState !== 'loading'");
    for (const [kind, document] of Object.entries(fixture.documents)) {
        const url = document.path;
        await navigate(url);
        await assert(kind + ' detail has no expense editing controls', "!document.querySelector('[data-transaction-editor]') && !document.querySelector('[data-add-expense]') && !document.querySelector('[data-remove-expense]') && document.body.innerText.includes('Biaya awal')");
        await inspect('390-' + kind + '-detail');
        await screenshot('390-' + kind + '-detail');
        await click('a[href$="?edit=1"]');
        await wait("!!document.querySelector('[data-transaction-editor]') && document.readyState !== 'loading'");
        await fill('[name=description]', 'Batal perubahan');
        await click('[data-add-expense]');
        await click('[data-transaction-editor] a.btn-outline-secondary, [data-transaction-editor] a[href]:not([href*="?edit"])');
        await wait("!location.search && !document.querySelector('[data-transaction-editor]') && document.readyState !== 'loading'");
        await assert(kind + ' cancel discards unsaved changes', "!document.body.innerText.includes('Batal perubahan') && document.body.innerText.includes('Biaya awal')");
        await click('a[href$="?edit=1"]');
        await wait("!!document.querySelector('[data-transaction-editor]') && document.readyState !== 'loading'");
        for (const width of [320, 390, 844, 1366]) {
            await viewport(width, width === 844 ? 390 : 844, width < 992);
            await inspect(width + '-' + kind + '-edit');
        }
        await viewport(390, 844, true);
        await fill('[name=description]', 'Transaksi diperbarui dari mode edit ' + kind);
        if (kind === 'pd') {
            await fill('[name=jarak_km]', '350.25');
            await fill('[name=durasi_jam]', '10.5');
            const province = await js("[...document.querySelector('#edit-provinsi').options].find(o=>o.value && !o.selected)?.value");
            if (province) {
                await fill('#edit-provinsi', province);
                await wait("document.querySelector('#edit-kota').options.length > 1");
                await fill('#edit-kota', await js("document.querySelector('#edit-kota').options[1].value"));
                await assert('travel destination cascades', "document.querySelector('#edit-kota').value !== ''");
            }
        }
        await fill('[data-expense-row]:first-child input[name$="[remark]"]', 'Biaya lama diperbarui');
        await fill('[data-expense-row]:first-child [data-expense-amount]', '125000.25');
        await click('[data-expense-row]:nth-child(2) [data-remove-expense]');
        await assert(kind + ' delete only marks existing expense', "document.querySelectorAll('[data-expense-rows] [data-expense-row]').length === 2 && document.querySelector('[data-expense-row]:nth-child(2) [data-delete-expense]').checked");
        await click('[data-expense-row]:nth-child(2) [data-remove-expense]');
        await assert(kind + ' expense deletion can be undone', "!document.querySelector('[data-expense-row]:nth-child(2) [data-expense-amount]').disabled");
        await click('[data-expense-row]:nth-child(2) [data-remove-expense]');
        for (let index=0;index<3;index++) await click('[data-add-expense]');
        await click('[data-expense-rows] [data-expense-row]:nth-child(4) [data-remove-expense]');
        await click('[data-add-expense]');
        await assert(kind + ' add after removing middle row keeps unique field names', "(() => {const names=[...document.querySelectorAll('[data-expense-rows] [name^=new_details]')].map(e=>e.name);return names.length===9 && new Set(names).size===9;})()");
        for (const [index, value] of [25000.5,10000.75,5000.25].entries()) {
            const selector = '[data-expense-rows] [data-expense-row]:not([data-existing-id]):nth-last-child(' + (3-index) + ')';
            await fill(selector + ' select', fixture.category_id);
            await fill(selector + ' input[name$="[remark]"]', 'Biaya baru ' + index);
            await fill(selector + ' [data-expense-amount]', value);
        }
        await assert(kind + ' total includes active rows with decimals', "document.querySelector('[data-expense-total]').textContent.includes('165.001,75')");
        await js("document.querySelector('[data-expense-rows]').scrollIntoView({block:'start',behavior:'instant'})");
        await sleep(200);
        await screenshot('390-' + kind + '-edit-costs');
        await assert(kind + ' form valid before saving', "document.querySelector('[data-transaction-editor]').checkValidity()");
        await js("document.querySelector('[data-transaction-editor]').requestSubmit()");
        await wait("!location.search && !document.querySelector('[data-transaction-editor]') && document.readyState !== 'loading'");
        await assert(kind + ' one save persists header and all expense changes', `document.body.innerText.includes(${JSON.stringify('Transaksi diperbarui dari mode edit ' + kind)}) && document.body.innerText.includes('Biaya lama diperbarui') && document.body.innerText.includes('Biaya baru 0') && document.body.innerText.includes('Biaya baru 1') && document.body.innerText.includes('Biaya baru 2') && !document.body.innerText.includes('Biaya akan dihapus')`);
        await inspect('390-' + kind + '-saved-detail');
        await screenshot('390-' + kind + '-saved-detail');
        console.log('Passed detail/edit/save flow: ' + kind);
    }
    const failures = results.filter(r => r.overflow || r.serverError || (r.width < 992 && r.smallInputs.length));
    if (failures.length || errors.length) process.exitCode = 1;
    console.log(JSON.stringify({pages:results.length,interactions:interactions.length,failures,scriptErrors:errors,artifacts:run}));

} catch (error) {
    console.error(String(error));
    errors.push({ page: currentPage, error: String(error) });
    process.exitCode = 1;
} finally {
    writeFileSync(path.join(run, 'report.json'), JSON.stringify({ results, interactions, errors }, null, 2));
    if (socket?.readyState === WebSocket.OPEN) { try { await call('Browser.close'); } catch {} }
    socket?.close(); browser.kill(); server.kill();
}
