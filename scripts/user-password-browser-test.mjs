// Real Blade forms, isolated SQLite users, and a synthetic reveal response.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { readFileSync, writeFileSync, mkdtempSync, mkdirSync, existsSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import path from 'node:path';

const password = 'Password-demo-2026';
const render = spawnSync('C:/php82/php.exe', [], {
    windowsHide: true, encoding: 'utf8', maxBuffer: 4 * 1024 * 1024,
    env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:', DB_URL: '', SESSION_DRIVER: 'array', CACHE_STORE: 'array' },
    input: `<?php
require 'vendor/autoload.php';
$test = new Tests\\Feature\\MasterDataPagesTest('test_master_password_update_and_blank_password_keep_login_and_reveal_consistent');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
$user = (new ReflectionMethod($test, 'passwordUserFixture'))->invoke($test);
$pages['/legacy'] = $test->get(route('users.edit', $user))->assertOk()->getContent();
$data = (new ReflectionMethod($test, 'passwordUserData'))->invoke($test, ['password' => '${password}']);
$test->put(route('users.update', $user), $data)->assertSessionHasNoErrors();
$pages['/edit'] = $test->get(route('users.edit', $user))->assertOk()->getContent();
$pages['/create'] = $test->get(route('users.create'))->assertOk()->getContent();
echo json_encode($pages, JSON_THROW_ON_ERROR);
` });
assert.equal(render.status, 0, render.stderr || render.stdout);
const bootstrap = readFileSync('storage/app/private/transaction-recap-verification/bootstrap.min.css', 'utf8');
const pages = Object.fromEntries(Object.entries(JSON.parse(render.stdout)).map(([key, html]) => [key, html
    .replaceAll('http://localhost', '')
    .replace(/<link\b[^>]*href="https:\/\/cdn.jsdelivr.net\/npm\/bootstrap@5.3.0\/dist\/css\/bootstrap.min.css"[^>]*>/g, '<style>' + bootstrap + '</style>')
    // These external widgets are unrelated to password controls and unavailable offline.
    .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("$('#wilayah-select')") || body.includes("getElementById('notifBell')") ? '' : script)
]));
assert.ok(!pages['/edit'].includes(password), 'Saved password must not be embedded in the HTML');
let failNext = false;
const root = path.resolve('public');
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    const file = path.resolve(root, '.' + url.pathname);
    if (req.method === 'POST' && url.pathname === '/users/1/password/reveal') {
        assert.ok(req.headers['x-csrf-token']);
        res.setHeader('Content-Type', 'application/json');
        res.setHeader('Cache-Control', 'no-store');
        res.statusCode = failNext ? 409 : 200;
        res.end(JSON.stringify(failNext ? { message: 'Password belum tersedia untuk ditampilkan.' } : { password }));
        failNext = false;
    } else if (pages[url.pathname]) {
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.end(pages[url.pathname]);
    } else if (file.startsWith(root + path.sep) && /^\/(css|js)\//.test(url.pathname) && existsSync(file)) {
        res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css');
        res.end(readFileSync(file));
    } else {
        res.setHeader('Content-Type', 'application/json');
        res.end('{}');
    }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const run = mkdtempSync(path.join(tmpdir(), 'user-password-preview-'));
const output = 'storage/app/private/user-password-preview';
mkdirSync(output, { recursive: true });
const browser = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--remote-debugging-port=0', '--user-data-dir=' + path.join(run, 'profile'), '--no-first-run', '--no-default-browser-check', 'about:blank'], { windowsHide: true, stdio: 'ignore' });
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
let socket, call;
const errors = [];
let checks = 0;
try {
    let debug;
    for (let i = 0; i < 100; i++) {
        try { debug = 'http://127.0.0.1:' + readFileSync(path.join(run, 'profile/DevToolsActivePort'), 'utf8').split('\n')[0].trim(); await fetch(debug + '/json/version'); break; }
        catch { await sleep(100); }
    }
    const target = await (await fetch(debug + '/json/new?about:blank', { method: 'PUT' })).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let id = 0;
    const pending = new Map();
    call = (method, params = {}) => new Promise((resolve, reject) => {
        const key = ++id;
        const timer = setTimeout(() => { pending.delete(key); reject(new Error('Timeout: ' + method)); }, 10000);
        pending.set(key, { resolve: value => { clearTimeout(timer); resolve(value); }, reject });
        socket.send(JSON.stringify({ id: key, method, params }));
    });
    socket.addEventListener('message', event => {
        const msg = JSON.parse(event.data);
        if (msg.id && pending.has(msg.id)) { const handler = pending.get(msg.id); pending.delete(msg.id); msg.error ? handler.reject(msg.error) : handler.resolve(msg.result); }
        if (msg.method === 'Runtime.exceptionThrown') errors.push(msg.params.exceptionDetails.text);
    });
    await call('Page.enable'); await call('Runtime.enable'); await call('Network.enable');
    await call('Network.setBlockedURLs', { urls: ['https://*'] });
    const js = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.text);
        return result.result.value;
    };
    const wait = async expression => { for (let i = 0; i < 100; i++) { if (await js(expression)) return; await sleep(50); } throw new Error('Timeout: ' + expression); };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const navigate = async route => {
        await js('window.__pendingNavigation = true');
        await call('Page.navigate', { url: base + route });
        await wait(`!window.__pendingNavigation && location.pathname === ${JSON.stringify(route)} && document.readyState === 'complete'`);
    };
    for (const width of [390, 1366]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
        await navigate('/edit');
        await check('stored and new passwords start empty', "document.getElementById('master-user-stored-password').value==='' && document.getElementById('master-user-password').value===''");
        await js("document.querySelector('[data-stored-password-toggle]').click()");
        await wait("document.getElementById('master-user-stored-password').type==='text'");
        await check('reveal shows only the stored password', `document.getElementById('master-user-stored-password').value===${JSON.stringify(password)} && document.getElementById('master-user-password').value==='' && document.querySelector('[data-stored-password-toggle]').getAttribute('aria-pressed')==='true'`);
        await check('the saved password will not be submitted as a replacement', "!document.getElementById('master-user-stored-password').name && new FormData(document.getElementById('master-user-password').form).get('password')===''");
        await js("document.querySelector('[data-stored-password]').scrollIntoView({block:'center'})");
        const shot = await call('Page.captureScreenshot', { format: 'png' });
        writeFileSync(path.join(output, 'master-user-password-' + width + '.png'), Buffer.from(shot.data, 'base64'));
        await check('password controls fit the viewport', "[...document.querySelectorAll('[data-stored-password] input,[data-stored-password] button,#master-user-password,[data-password-toggle]')].every(el=>{const r=el.getBoundingClientRect();return r.left>=0 && r.right<=innerWidth+1})");
        await js("document.querySelector('[data-stored-password-toggle]').click()");
        await check('hiding discards the revealed value', "document.getElementById('master-user-stored-password').value==='' && document.getElementById('master-user-stored-password').type==='password'");
        await js("document.getElementById('master-user-password').value='New-password-demo';document.querySelector('[data-password-toggle]').click()");
        await check('new password toggle still works independently', "document.getElementById('master-user-password').type==='text' && document.getElementById('master-user-password').value==='New-password-demo' && document.getElementById('master-user-stored-password').value===''");
        await js("document.getElementById('master-user-password').form.reset()");
        await check('reset hides both controls', "document.getElementById('master-user-password').type==='password' && document.getElementById('master-user-password').value===''");
        failNext = true;
        await js("document.querySelector('[data-stored-password-toggle]').click()");
        await wait("document.getElementById('stored-password-feedback').classList.contains('text-danger')");
        await check('failed reveal stays empty and allows retry', "document.getElementById('master-user-stored-password').value==='' && !document.querySelector('[data-stored-password-toggle]').disabled");
        await navigate('/legacy');
        await check('legacy users get a clear one-time setup message', "!document.querySelector('[data-stored-password-toggle]') && document.querySelector('[data-stored-password]').textContent.includes('simpan sekali')");
        await navigate('/create');
        await js("document.getElementById('master-user-password').value='Create-password-demo';document.querySelector('[data-password-toggle]').click()");
        await check('new user form can show the typed password', "document.getElementById('master-user-password').type==='text' && !document.querySelector('[data-stored-password]')");
    }
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} password browser checks at 390 and 1366 px. Synthetic previews: ${output}`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    socket?.close(); browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
