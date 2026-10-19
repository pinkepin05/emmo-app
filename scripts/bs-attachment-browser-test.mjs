// Functional upload checks against rendered Blade and a temporary local server.
// No application database or business submission is used.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { readFileSync, mkdtempSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import path from 'node:path';

const render = type => {
    const result = spawnSync(process.env.PHP_BINARY || 'C:/php82/php.exe', [], {
        windowsHide: true, encoding: 'utf8',
        env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:',
            DB_URL: '', SESSION_DRIVER: 'array', CACHE_STORE: 'array', BS_ATTACHMENT_OLD_TYPE: type },
        input: `<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
    'session.driver' => 'array', 'cache.default' => 'array']);
$request = Illuminate\\Http\\Request::create('http://localhost/pengajuan-bs/create');
$app->instance('request', $request);
$session = app('session')->driver();
$session->start();
if (getenv('BS_ATTACHMENT_OLD_TYPE')) $session->put('_old_input', ['tipe_pengajuan' => getenv('BS_ATTACHMENT_OLD_TYPE')]);
$request->setLaravelSession($session);
view()->share('errors', new Illuminate\\Support\\ViewErrorBag());
echo view('pengajuan-bs.create', ['members' => collect(), 'periode' => '2026-10', 'sisaSaldo' => 0])->render();
` });
    assert.equal(result.status, 0, result.stderr || result.stdout);
    // External CSS/fonts are unnecessary for functional checks; keep the form's own CSS/JS.
    return result.stdout.replace(/<link[^>]*>/g, '').replace(/<script\b[^>]*\bsrc=[^>]*><\/script>/g, '')
        .replace('</head>', '<style>.d-none{display:none!important}</style></head>')
        .replace('</body>', `<script>window.testSubmits=0;document.querySelector('#form-pengajuan').addEventListener('submit',e=>{e.preventDefault();window.testSubmits++});</script></body>`);
};

const pages = { '/default': render(''), '/travel': render('perjalanan_dinas') };
const server = createServer((req, res) => {
    res.writeHead(pages[req.url] ? 200 : 404, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(pages[req.url] || 'Not found');
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const profile = mkdtempSync(path.join(tmpdir(), 'bs-attachment-test-'));
const browser = spawn(process.env.CHROME_BINARY || 'C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=0', '--user-data-dir=' + profile,
    '--no-first-run', '--no-default-browser-check', '--disable-extensions', 'about:blank',
], { windowsHide: true, stdio: 'ignore' });
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
let socket, call;
const errors = [];
let checks = 0;

try {
    let debug;
    for (let i = 0; i < 100; i++) {
        try {
            debug = 'http://127.0.0.1:' + readFileSync(path.join(profile, 'DevToolsActivePort'), 'utf8').split('\n')[0].trim();
            await fetch(debug + '/json/version');
            break;
        } catch { await sleep(100); }
    }
    const target = await (await fetch(debug + '/json/new?about:blank', { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => {
        socket.addEventListener('open', resolve, { once: true });
        socket.addEventListener('error', reject, { once: true });
    });
    const pending = new Map();
    let nextId = 0;
    call = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++nextId;
        const timer = setTimeout(() => { pending.delete(id); reject(new Error('Timeout: ' + method)); }, 10000);
        pending.set(id, { resolve: result => { clearTimeout(timer); resolve(result); }, reject: error => { clearTimeout(timer); reject(error); } });
        socket.send(JSON.stringify({ id, method, params }));
    });
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.id) {
            const handler = pending.get(message.id);
            if (handler) { pending.delete(message.id); message.error ? handler.reject(message.error) : handler.resolve(message.result); }
        }
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
    });
    await call('Page.enable');
    await call('Runtime.enable');
    await call('Page.setInterceptFileChooserDialog', { enabled: true });
    const js = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
        return result.result.value;
    };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const navigate = async route => {
        await call('Page.navigate', { url: base + route });
        for (let i = 0; i < 100; i++) {
            if (await js(`document.readyState === 'complete' && location.pathname === ${JSON.stringify(route)}`)) return;
            await sleep(50);
        }
        throw new Error('Navigation timed out: ' + route);
    };
    const select = (name, type, size = 200, drop = false) => js(`(() => {
        const dt=new DataTransfer();dt.items.add(new File([new Uint8Array(${size})],${JSON.stringify(name)},{type:${JSON.stringify(type)}}));
        const input=document.getElementById('attachment');
        ${drop ? "document.getElementById('attachment-dropzone').dispatchEvent(new DragEvent('drop',{bubbles:true,cancelable:true,dataTransfer:dt}));" : "input.files=dt.files;input.dispatchEvent(new Event('change',{bubbles:true}));"}
    })()`);
    const preview = "document.getElementById('attachment-preview').classList.contains('show')";
    for (const width of [390, 1280]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 844, deviceScaleFactor: 1, mobile: width < 600 });
        await navigate('/default');
        await check('operational starts without a required upload', "document.getElementById('attachment').disabled && !document.getElementById('attachment').required");
        await js("document.getElementById('tipe_dinas').click();document.getElementById('nominal-display').value='1000'");
        await check('travel upload is required and focusable', "document.getElementById('attachment').required && !document.getElementById('attachment').disabled && document.getElementById('attachment').getClientRects().length > 0");
        await js("document.querySelector('#form-pengajuan').requestSubmit()");
        await check('empty upload shows a visible error and receives focus', "window.testSubmits===0 && document.activeElement.id==='attachment' && document.getElementById('attachment-error').textContent.includes('wajib')");
        await select('izin.pdf', 'application/pdf');
        await check('PDF preview clears the error', preview + " && document.getElementById('attachment-error').textContent==='' && getComputedStyle(document.getElementById('attachment-placeholder')).display==='none'");
        await select('invalid.txt', 'text/plain');
        await check('invalid replacement clears the old preview and file', '!' + preview + " && document.getElementById('attachment').files.length===0 && document.getElementById('attachment-error').textContent.includes('Format')");
        await select('izin.jpg', 'image/jpeg');
        await check('JPG preview works', preview);
        await select('besar.pdf', 'application/pdf', 2097153);
        await check('oversize replacement clears the old preview', '!' + preview + " && document.getElementById('attachment-error').textContent.includes('2MB')");
        await select('batas.pdf', 'application/pdf', 2097152);
        await check('exactly 2 MB is accepted', preview);
        await js("document.getElementById('attachment-remove').click()");
        await check('remove clears and focuses the input', '!' + preview + " && document.getElementById('attachment').files.length===0 && document.activeElement.id==='attachment'");
        await select('dropped.pdf', 'application/pdf', 200, true);
        await check('drag and drop selects the file', preview + " && document.getElementById('attachment').files[0].name==='dropped.pdf'");
        await select('unknown-mime.pdf', '');
        await check('valid extension with unavailable client MIME can reach server validation', preview);
        await js("document.querySelector('#form-pengajuan').requestSubmit()");
        await check('valid file permits submission', 'window.testSubmits===1');
        await js("document.getElementById('tipe_operasional').click();document.querySelector('#form-pengajuan').requestSubmit()");
        await check('switching back clears and excludes the attachment', "window.testSubmits===2 && document.getElementById('attachment').disabled && document.getElementById('attachment').files.length===0");
        await js("sessionStorage.setItem('prefillTipeBs','operasional_sales')");
        await navigate('/travel');
        await check('validation return retains travel despite stale prefill', "document.getElementById('tipe_dinas').checked && document.getElementById('attachment').required");
        await select('ulang.pdf', 'application/pdf');
        await check('upload works after validation return', preview);
    }
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} browser checks at 390 and 1280 px; no JavaScript errors.`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    if (socket) socket.close();
    browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
