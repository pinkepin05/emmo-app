import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import path from 'node:path';

const directory = path.resolve('storage/app/private/transaction-ux-preview');
const output = path.join(directory, 'browser-' + Date.now());
mkdirSync(output, { recursive: true });
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=9285', '--no-first-run', '--no-default-browser-check',
    '--user-data-dir=' + path.join(output, 'profile'), 'about:blank',
], { windowsHide: true, stdio: 'ignore' });
let socket;
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
try {
    for (let i = 0; i < 60; i++) {
        try { await fetch('http://127.0.0.1:9285/json/version'); break; } catch { await sleep(100); }
    }
    const target = await (await fetch('http://127.0.0.1:9285/json/new?about:blank', { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
    const pending = new Map(); let nextId = 0;
    socket.onmessage = event => {
        const message = JSON.parse(event.data), task = pending.get(message.id);
        if (!task) return;
        pending.delete(message.id); clearTimeout(task.timer);
        message.error ? task.reject(new Error(message.error.message)) : task.resolve(message.result);
    };
    const call = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++nextId;
        const timer = setTimeout(() => { pending.delete(id); reject(new Error('Timeout: ' + method)); }, 15000);
        pending.set(id, { resolve, reject, timer }); socket.send(JSON.stringify({ id, method, params }));
    });
    const js = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description ?? result.exceptionDetails.text);
        return result.result.value;
    };
    await call('Page.enable'); await call('Runtime.enable');
    const results = [];
    const selected = process.argv.slice(2);
    const pages = JSON.parse(readFileSync(path.join(directory, 'pages.json'), 'utf8')).filter(page => !selected.length || selected.includes(page));
    for (const page of pages) {
        let html = readFileSync(path.join(directory, page + '.html'), 'utf8').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '');
        html = html.replace(/<link\b[^>]*href="([^"]+)"[^>]*>/gi, (tag, href) => {
            if (href === 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css') {
                return '<style>' + readFileSync('storage/app/private/transaction-recap-verification/bootstrap.min.css', 'utf8') + '</style>';
            }
            const local = path.resolve('public', new URL(href, 'http://localhost').pathname.replace(/^\/+/, ''));
            return local.startsWith(path.resolve('public') + path.sep) && local.endsWith('.css') && existsSync(local)
                ? '<style>' + readFileSync(local, 'utf8') + '</style>' : tag;
        });
        const { frameTree } = await call('Page.getFrameTree');
        await call('Page.setDocumentContent', { frameId: frameTree.frame.id, html });
        await js(`new Promise(resolve => { if(document.readyState === 'complete') return resolve(); window.addEventListener('load', resolve, { once: true }); setTimeout(resolve, 4000); })`);
        await js('Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 2500))])');
        if (page === 'check-form' || page === 'draft-form') {
            await js(readFileSync('public/js/bs-usage-checks.js', 'utf8'));
            const interaction = await js(`(() => {
                const select = document.getElementById('keputusan'), note = document.getElementById('catatan_keputusan');
                const amounts = [...document.querySelectorAll('[data-approved]')];
                const original = amounts.map(el => el.value);
                select.value = 'REJECTED'; select.dispatchEvent(new Event('change', { bubbles: true }));
                const rejected = amounts.every(el => el.value === '0' && el.readOnly) && note.required && document.getElementById('check-live-approved').textContent === 'Rp 0,00';
                select.value = 'APPROVED'; select.dispatchEvent(new Event('change', { bubbles: true }));
                const approved = !note.required && amounts.every((el,i) => !el.readOnly && el.value === original[i]);
                const twoDecisions = !select.querySelector('option[value="REVISION"]');
                amounts[0].value = 'invalid'; amounts[0].dispatchEvent(new Event('input', { bubbles: true }));
                const invalid = document.getElementById('check-live-approved').textContent === 'Periksa nominal';
                amounts[0].value = original[0]; amounts[0].dispatchEvent(new Event('input', { bubbles: true }));
                return { rejected, twoDecisions, approved, invalid };
            })()`);
            if (Object.values(interaction).some(value => !value)) throw new Error('Decision interaction failed: ' + JSON.stringify(interaction));
            results.push({ page, interaction });
        }
        if (page === 'detail') {
            const back = await js(`(() => { const u = new URL(document.querySelector('.recap-back').href); return u.pathname === '/laporan-transaksi/modul/OPR' && u.searchParams.has('pemakaian_periode') && u.searchParams.get('pemakaian_periode') === '' && u.searchParams.get('pemakaian_page') === '2'; })()`);
            if (!back) throw new Error('Detail back link lost report filters.');
        }
        if (page === 'lookup' || page.startsWith('finance-')) {
            const navigation = await js(`(() => {
                const panel = document.getElementById('finance-workspace');
                const nav = [...panel.querySelectorAll('.recap-nav a')];
                const form = panel.querySelector('form');
                return {
                    singleMenu: [...document.querySelectorAll('.sidebar-link')].filter(a => a.textContent.trim() === 'Pengecekan Pemakaian BS').length === 1
                        && !document.querySelector('.sidebar').textContent.includes('Laporan Pengecekan BS'),
                    sameWorkspace: nav.length === 3 && nav.every(a => new URL(a.href).pathname === '/pengecekan-pemakaian-bs')
                        && new URL(form.action).pathname === '/pengecekan-pemakaian-bs',
                    oneActiveView: panel.querySelectorAll('.recap-nav [aria-current="page"]').length === 1,
                    historySelection: !document.getElementById('mode') || ['terakhir', 'semua'].includes(document.getElementById('mode').value)
                };
            })()`);
            if (Object.values(navigation).some(value => !value)) throw new Error('Finance navigation: ' + JSON.stringify(navigation));
            results.push({ page, navigation });
        }
        for (const width of [1366, 768, 390, 320]) {
            await call('Emulation.setDeviceMetricsOverride', { width, height: 1100, deviceScaleFactor: 1, mobile: false });
            await js('new Promise(resolve => requestAnimationFrame(() => { window.scrollTo(0,0); requestAnimationFrame(resolve); }))');
            const result = await js(`(() => {
                const panel = document.querySelector('.recap-workspace');
                const cards = [...panel.querySelectorAll('.usage-module')];
                return { width: innerWidth, pageFits: document.documentElement.scrollWidth <= innerWidth + 1,
                    cardsFit: cards.every(el => el.scrollWidth <= el.clientWidth + 1), modules: cards.length,
                    activeTab: panel.querySelectorAll('.recap-nav [aria-current="page"]').length,
                    mobileRows: innerWidth >= 768 || [...panel.querySelectorAll('.usage-table tbody tr')].every(el => getComputedStyle(el).display === 'grid') };
            })()`);
            const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
            writeFileSync(path.join(output, page + '-' + width + '.png'), Buffer.from(shot.data, 'base64'));
            const report = ['pengajuan','finance','empty','event-dalam','event-luar'].includes(page);
            if (!result.pageFits || !result.cardsFit || !result.mobileRows || (page.startsWith('dashboard') && (result.modules !== 4 || result.activeTab !== 0)) || (report && (result.modules !== 0 || result.activeTab !== 0))) {
                throw new Error(JSON.stringify({ page, ...result, output }));
            }
            results.push({ page, ...result });
        }
        if (page === 'check-form') {
            const saved = await js(`(() => {
                const form = document.getElementById('bs-check-form');
                let submitted;
                form.addEventListener('submit', e => { e.preventDefault(); submitted = new FormData(form); });
                form.querySelector('button[value="confirm"]').click();
                return submitted?.getAll('save_action').join(',') === 'confirm'
                    && [...form.querySelectorAll('button[name="save_action"]')].every(b => b.disabled && b.getAttribute('aria-busy') === 'true');
            })()`);
            if (!saved) throw new Error('Confirmation action or feedback was lost.');
        }
        if (page === 'draft-form') {
            const saved = await js(`(() => {
                const form = document.getElementById('bs-check-form'), decision = document.getElementById('keputusan');
                let submitted, attempts = 0;
                form.addEventListener('submit', e => { e.preventDefault(); attempts++; submitted = new FormData(form); });
                decision.value = 'REJECTED'; decision.dispatchEvent(new Event('change'));
                document.getElementById('catatan_keputusan').value = '';
                form.querySelector('[data-approved]').value = '';
                form.querySelector('button[value="confirm"]').click();
                const confirmationBlocked = attempts === 0 && !form.checkValidity();
                form.querySelector('button[value="draft"]').click();
                const draftSaved = attempts === 1 && submitted?.getAll('save_action').join(',') === 'draft';
                form.dispatchEvent(new SubmitEvent('submit', { cancelable: true }));
                return { confirmationBlocked, draftSaved, singleAction: form.querySelectorAll('input[name="save_action"]').length === 1 };
            })()`);
            if (Object.values(saved).some(value => !value)) throw new Error('Draft action: ' + JSON.stringify(saved));
            results.push({ page, saveActions: saved });
        }
        console.log('PASS ' + page + ': desktop, tablet, 390px, 320px');
    }
    writeFileSync(path.join(output, 'result.json'), JSON.stringify(results, null, 2));
    console.log('Screenshots: ' + output);
} finally { socket?.close(); chrome.kill(); }
