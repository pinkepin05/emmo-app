import { createServer } from 'node:http';
import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

// Serve real Blade fixtures rendered by render-transaction-ux-demo.php.
// All data is synthetic. This server never accesses the application database.
const directory = path.resolve('storage/app/private/transaction-ux-preview');
const output = path.join(directory, 'modules-browser-' + Date.now());
mkdirSync(output, { recursive: true });
let origin, documents = 0;
const server = createServer((request, response) => {
    const url = new URL(request.url, origin);
    if (['/js/transaction-recap-modules.js', '/js/transaction-recap-ux.js'].includes(url.pathname)) {
        response.setHeader('Content-Type', 'text/javascript; charset=utf-8');
        response.end(readFileSync('public' + url.pathname));
        return;
    }
    let name;
    if (url.pathname === '/dashboard/transaksi') {
        name = url.searchParams.get('pemakaian_periode') === '2099-01' ? 'dashboard-empty' : 'dashboard';
    } else if (/^\/laporan-transaksi\/modul\/(OPR|PD|EDK|ELK)$/.test(url.pathname)) {
        const suffix = url.searchParams.get('pemakaian_periode') === '2099-01' ? 'empty'
            : ({ PENDING: 'pending', APPROVED: 'approved', DIFFERENCE: 'difference', REVISION: 'revision', REJECTED: 'rejected' }[url.searchParams.get('pemakaian_status')] ?? '');
        name = 'module-' + url.pathname.split('/').at(-1) + (suffix ? '-' + suffix : '');
    } else if (url.pathname.startsWith('/laporan-transaksi/OPR/')) name = 'detail';
    else { response.writeHead(404); response.end(); return; }
    documents++;
    let html = readFileSync(path.join(directory, name + '.html'), 'utf8');
    html = html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, tag => /\/js\/transaction-recap-(modules|ux)\.js/.test(tag) ? tag : '');
    html = html.replace(/<link\b[^>]*href="([^"]+)"[^>]*>/gi, (tag, href) => {
        if (href.includes('/bootstrap@5.3.0/dist/css/bootstrap.min.css')) return '<style>' + readFileSync('storage/app/private/transaction-recap-verification/bootstrap.min.css', 'utf8') + '</style>';
        const url = new URL(href, 'http://localhost');
        if (url.origin !== 'http://localhost' || !url.pathname.endsWith('.css')) return '';
        const filename = path.resolve('public', url.pathname.replace(/^\/+/, ''));
        assert.ok(filename.startsWith(path.resolve('public') + path.sep));
        return '<style>' + readFileSync(filename, 'utf8') + '</style>';
    });
    response.setHeader('Content-Type', 'text/html; charset=utf-8');
    response.end(html.replaceAll('http://localhost', origin));
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
origin = 'http://127.0.0.1:' + server.address().port;
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=9287', '--no-first-run', '--no-default-browser-check',
    '--user-data-dir=' + path.join(output, 'profile'), 'about:blank',
], { windowsHide: true, stdio: 'ignore' });
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
let socket;
try {
    for (let i = 0; i < 60; i++) {
        try { await fetch('http://127.0.0.1:9287/json/version'); break; } catch { await sleep(100); }
    }
    const target = await (await fetch('http://127.0.0.1:9287/json/new?about:blank', { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
    const pending = new Map(), errors = []; let nextId = 0;
    socket.onmessage = event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails);
        const task = pending.get(message.id);
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
    const waitFor = async expression => {
        for (let i = 0; i < 100; i++) {
            try { if (await js(expression)) return; } catch {}
            await sleep(100);
        }
        throw new Error('Condition timed out: ' + expression);
    };
    await call('Page.enable'); await call('Runtime.enable');
    const navigate = async url => {
        await call('Page.navigate', { url });
        await waitFor(`location.href === ${JSON.stringify(url)} && document.readyState === 'complete'`);
    };
    const initial = origin + '/dashboard/transaksi?pemakaian_periode=';
    const results = [];
    const click = async selector => {
        await js(`window.beforeNavigation = true; document.querySelector(${JSON.stringify(selector)}).click()`);
        await waitFor(`!window.beforeNavigation && document.readyState === 'complete'`);
    };
    for (const width of [1536, 768, 390, 320]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 1100, deviceScaleFactor: 1, mobile: false });
        await navigate(initial);
        assert.equal(await js(`document.querySelectorAll('[data-module-card]').length`), 4);
        assert.equal(await js(`document.querySelectorAll('.recap-filter').length`), 1);
        assert.equal(await js(`document.querySelectorAll('[data-amount-status="RECHECK"]').length`), 0);
        assert.equal(await js(`document.querySelectorAll('[data-recap-tab], [data-recap-report]').length`), 0);
        assert.equal(await js(`document.documentElement.scrollWidth <= innerWidth + 1`), true);
        await js(`document.querySelector('.usage-modules').scrollIntoView({block:'start'})`);
        const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
        writeFileSync(path.join(output, 'cards-' + width + '.png'), Buffer.from(shot.data, 'base64'));
        for (const [index, type] of ['OPR', 'PD', 'EDK', 'ELK'].entries()) {
            await navigate(initial);
            // Clicking the amount exercises the whole card, not just its title.
            await click(`[data-module-card]:nth-child(${index + 1}) .recap-module-total`);
            const result = await js(`(() => {
                const p = document.querySelector('[data-recap-module]');
                const url = new URL(location.href);
                return { module: p?.dataset.recapModule, path: url.pathname,
                    period: url.searchParams.has('pemakaian_periode') && url.searchParams.get('pemakaian_periode') === '',
                    tabs: p.querySelectorAll('[data-recap-tab]').length,
                    scopedForm: new URL(p.querySelector('form').action).pathname === location.pathname,
                    scopedFilters: [...p.querySelectorAll('.recap-filter a')].every(a => new URL(a.href).pathname === location.pathname),
                    pageFits: document.documentElement.scrollWidth <= innerWidth + 1 };
            })()`);
            assert.equal(result.module, type); assert.equal(result.path, '/laporan-transaksi/modul/' + type);
            assert.equal(result.tabs, 0);
            for (const key of ['period', 'scopedForm', 'scopedFilters', 'pageFits']) assert.equal(result[key], true, type + ': ' + key);
            await click('.recap-back');
            assert.equal(await js('location.pathname'), '/dashboard/transaksi');
            assert.equal(await js(`document.querySelectorAll('[data-module-card]').length`), 4);
            results.push({ width, ...result });
        }
    }
    // Child status links must keep their status and must not trigger the card title.
    for (const [index, type] of ['OPR', 'PD', 'EDK', 'ELK'].entries()) {
        for (const [selector, status] of [['.approved', 'APPROVED'], ['.pending', 'PENDING'], ['.difference', 'DIFFERENCE'], ['.revision', 'REVISION'], ['.rejected', 'REJECTED']]) {
            await navigate(initial);
            if (status === 'REVISION' && !await js(`Boolean(document.querySelector('[data-module-card]:nth-child(${index + 1}) ${selector}'))`)) continue;
            await click(`[data-module-card]:nth-child(${index + 1}) ${selector}`);
            const url = new URL(await js('location.href'));
            assert.equal(url.pathname, '/laporan-transaksi/modul/' + type);
            assert.equal(url.searchParams.get('pemakaian_status'), status);
            assert.equal(await js(`document.querySelector('[data-recap-module]').dataset.recapModule`), type);
            assert.equal(await js(`document.querySelector('[name="pemakaian_status"]').value`), status);
        }
    }
    await navigate(origin + '/laporan-transaksi/modul/OPR?pemakaian_periode=');
    await click('.recap-reference');
    assert.match(await js('location.pathname'), /^\/laporan-transaksi\/OPR\/\d+$/);
    await click('.recap-back');
    assert.equal(await js('location.pathname'), '/laporan-transaksi/modul/OPR');
    assert.equal(await js(`new URL(location.href).searchParams.get('pemakaian_page')`), '2');
    // Filter submission and reset must remain on the selected module.
    await js(`window.beforeNavigation = true; document.querySelector('.recap-filter').requestSubmit()`);
    await waitFor(`!window.beforeNavigation && document.readyState === 'complete'`);
    assert.equal(await js('location.pathname'), '/laporan-transaksi/modul/OPR');
    await click('.filter-reset-btn');
    assert.equal(await js('location.pathname'), '/laporan-transaksi/modul/OPR');
    await navigate(origin + '/dashboard/transaksi?pemakaian_periode=2099-01');
    assert.equal(await js(`document.querySelectorAll('.recap-module-empty').length`), 4);
    await click('[data-module-card]:nth-child(3) .recap-module-link');
    assert.equal(await js(`!!document.querySelector('.recap-empty-enhanced')`), true);
    await click('.empty-actions .btn-primary');
    assert.equal(await js('location.pathname'), '/laporan-transaksi/modul/EDK');
    assert.equal(await js(`new URL(location.href).searchParams.get('pemakaian_periode')`), '');
    // Native title/status links remain usable without JavaScript.
    await call('Emulation.setScriptExecutionDisabled', { value: true });
    await navigate(initial);
    const nativeLink = await js(`document.querySelector('[data-module-card]:nth-child(2) .recap-module-link').href`);
    await navigate(nativeLink);
    assert.equal(await js(`document.querySelector('[data-recap-module]').dataset.recapModule`), 'PD');
    await call('Emulation.setScriptExecutionDisabled', { value: false });
    assert.deepEqual(errors, []);
    writeFileSync(path.join(output, 'results.json'), JSON.stringify(results, null, 2));
    console.log(`PASS ${results.length} module layouts; card/status navigation, filters, detail return, empty states and no-JavaScript links. ${output}`);
} finally {
    if (socket) socket.close();
    chrome.kill();
    await new Promise(resolve => server.close(resolve));
}
