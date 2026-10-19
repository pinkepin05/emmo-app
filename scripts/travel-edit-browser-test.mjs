// Real Blade pages with in-memory fixtures; no business database or form submissions.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { readFileSync, writeFileSync, mkdtempSync, existsSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import path from 'node:path';

const render = spawnSync(process.env.PHP_BINARY || 'C:/php82/php.exe', [], {
    windowsHide: true, encoding: 'utf8', maxBuffer: 8 * 1024 * 1024,
    env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:', DB_URL: '', SESSION_DRIVER: 'array', CACHE_STORE: 'array' },
    input: `<?php
require 'vendor/autoload.php';
$test = new Tests\\Feature\\BsCashFlowTest('test_detail_photo_edit_preserves_replaces_adds_and_removes');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
if (Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Isolated database required');
Illuminate\\Support\\Facades\\DB::table('wilayah')->insert(['id' => 2, 'nama' => 'Jawa Tengah', 'teritory' => 'Jawa']);
Illuminate\\Support\\Facades\\DB::table('wilayah')->where('id', 1)->update(['parent_id' => 2]);
$pages = [];
foreach (['pd' => true, 'opr' => false] as $key => $travel) {
    $source = (new ReflectionMethod($test, 'photoSource'))->invoke($test, $travel);
    $source->details()->first()->update(['photo_name' => 'Bukti bensin.png']);
    $source->details()->create(['cost_category_id' => 1, 'remark' => 'Parkir', 'nominal' => '70000.00']);
    $module = $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
    $id = $travel ? $source->transaksi_id : $source->id;
    $pages['/'.$key] = $test->get(route($module.'.show', $id).'?edit=1')->assertOk()->getContent();
    $pages['/'.$key.'-create'] = $test->get(route($module.'.create'))->assertOk()->getContent();
}
$receipt = imagecreatetruecolor(1600, 1000);
imagefill($receipt, 0, 0, imagecolorallocate($receipt, 246, 248, 252));
$ink = imagecolorallocate($receipt, 40, 55, 75);
imagestring($receipt, 5, 100, 100, 'BUKTI PEMBAYARAN - DATA PRATINJAU', $ink);
imagestring($receipt, 5, 100, 180, 'Bensin kendaraan dinas', $ink);
imagestring($receipt, 5, 100, 260, 'Total: Rp 150.000,25', $ink);
imageline($receipt, 100, 320, 1500, 320, $ink);
imagestring($receipt, 5, 100, 390, 'Gambar 1600 x 1000 untuk pemeriksaan ukuran thumbnail.', $ink);
ob_start(); imagepng($receipt); $photo = base64_encode(ob_get_clean());
echo json_encode(['pages' => $pages, 'photo' => $photo], JSON_THROW_ON_ERROR);
` });
assert.equal(render.status, 0, render.stderr || render.stdout);
const fixture = JSON.parse(render.stdout);
const pages = Object.fromEntries(Object.entries(fixture.pages).map(([key, html]) => [key, html
    .replace(/(src|href)="https?:\/\/[^/"\s]+\/((?:css|js)\/[^"\s]*)"/g, '$1="/$2"')
    .replace(/https?:\/\/[^/"\s]+(\/rincian-biaya\/[^"\s]+)/g, '$1')
    .replace(/(data-[\w-]+-url)="https?:\/\/[^/"\s]+(\/[^"\s]*)"/g, '$1="$2"')
    .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("getElementById('notifBell')") ? '' : script)
]));
const publicRoot = path.resolve('public');
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    const file = path.resolve(publicRoot, '.' + url.pathname);
    if (pages[url.pathname]) { res.setHeader('Content-Type', 'text/html; charset=utf-8'); res.end(pages[url.pathname]); }
    else if (url.pathname.startsWith('/rincian-biaya/') || url.pathname === '/fixture.png') { res.setHeader('Content-Type', 'image/png'); res.end(Buffer.from(fixture.photo, 'base64')); }
    else if (file.startsWith(publicRoot + path.sep) && /^\/(css|js)\//.test(url.pathname) && existsSync(file)) { res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css'); res.end(readFileSync(file)); }
    else { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify(url.pathname.includes('periode') ? { open: true } : { owners: [{ id: 1, balance: '250000', opening_final: false }] })); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const run = mkdtempSync(path.join(tmpdir(), 'travel-edit-preview-'));
const browser = spawn(process.env.CHROME_BINARY || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--remote-debugging-port=0', '--user-data-dir=' + path.join(run, 'profile'), '--no-first-run', '--no-default-browser-check', 'about:blank'], { windowsHide: true, stdio: 'ignore' });
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
    await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
    const pending = new Map();
    let id = 0;
    call = (method, params = {}) => new Promise((resolve, reject) => {
        const key = ++id;
        const timer = setTimeout(() => { pending.delete(key); reject(new Error('Timeout: ' + method)); }, 10000);
        pending.set(key, { resolve: value => { clearTimeout(timer); resolve(value); }, reject });
        socket.send(JSON.stringify({ id: key, method, params }));
    });
    socket.addEventListener('message', event => {
        const msg = JSON.parse(event.data);
        if (msg.id && pending.has(msg.id)) { const handler = pending.get(msg.id); pending.delete(msg.id); msg.error ? handler.reject(msg.error) : handler.resolve(msg.result); }
        if (msg.method === 'Runtime.exceptionThrown') errors.push(msg.params.exceptionDetails.exception?.description || msg.params.exceptionDetails.text);
    });
    await call('Page.enable'); await call('Runtime.enable');
    const js = async expression => { const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true }); if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text); return result.result.value; };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const wait = async expression => { for (let i = 0; i < 200; i++) { if (await js(expression)) return; await sleep(50); } throw new Error('Timeout: ' + expression); };
    const navigate = async route => { await call('Page.navigate', { url: base + route }); await wait(`location.pathname === ${JSON.stringify(route)} && document.readyState === 'complete'`); };
    const screenshot = async name => { const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false }); writeFileSync(path.join(run, name + '.png'), Buffer.from(shot.data, 'base64')); };
    const selectPhoto = async (selector = '[data-expense-photo]', type = 'valid') => {
        await js(`(async () => { const input = document.querySelector(${JSON.stringify(selector)}); const blob = await (await fetch('/fixture.png')).blob(); const data = new DataTransfer(); data.items.add(${type === 'oversize' ? "new File([new Uint8Array(2097153)], 'besar.png', {type:'image/png'})" : type === 'invalid' ? "new File(['text'], 'dokumen.txt', {type:'text/plain'})" : "new File([blob], 'Foto pengganti dengan nama panjang untuk bukti bensin.png', {type:'image/png'})"}); input.files = data.files; input.dispatchEvent(new Event('change', {bubbles:true})); })()`);
    };
    for (const width of [320, 390, 844, 1366]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: width < 600 });
        await navigate('/pd');
        await js("document.querySelector('[data-existing-photo] img').scrollIntoView({block:'center'})");
        await wait("document.querySelector('[data-existing-photo] img').naturalWidth === 1600");
        await check('page and thumbnail fit at ' + width, "document.documentElement.scrollWidth <= innerWidth + 1 && document.querySelector('[data-existing-photo] img').getBoundingClientRect().width <= 96 && document.querySelector('[data-existing-photo] img').getBoundingClientRect().height <= 72");
        await check('edit fields remain inside the form', "['tanggal','jarak_km','durasi_jam','teritory','provinsi_id','tujuan_dinas_id','description','bs_owner_id'].every(name=>document.querySelector('#form-edit-pd').elements.namedItem(name))");
        await screenshot('details-' + width);
        await js('window.scrollTo(0,0)'); await screenshot('form-' + width);
        await js("document.querySelector('[data-existing-photo] [data-photo-open]').click()");
        await wait("document.querySelector('dialog[open] img')?.naturalWidth === 1600");
        await check('enlarged photo fits viewport', "(()=>{const r=document.querySelector('dialog[open]').getBoundingClientRect();return r.left>=0 && r.right<=innerWidth+1 && r.top>=0 && r.bottom<=innerHeight+1})()");
        await screenshot('photo-' + width);
        await call('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
        await call('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
        await wait("!document.querySelector('dialog[open]')");
        await check('Escape restores thumbnail focus', "document.activeElement.matches('[data-photo-open]') && !document.documentElement.classList.contains('photo-lightbox-open')");
        await js("document.querySelector('[data-remove-photo]').click()");
        await selectPhoto();
        await check('replacement clears removal and shows filename', "!document.querySelector('[data-remove-photo]').checked && !document.querySelector('[data-photo-preview]').hidden && document.querySelector('[data-photo-filename]').textContent.includes('Foto pengganti') && document.documentElement.scrollWidth<=innerWidth+1");
        await js("document.querySelector('[data-photo-preview] [data-photo-open]').click()");
        await wait("document.querySelector('dialog[open] img')?.naturalWidth === 1600");
        await js("document.querySelector('.photo-lightbox-close').click(); document.querySelector('[data-clear-photo]').click()");
        await check('cancel preserves saved image and amount', "document.querySelector('[data-photo-preview]').hidden && !document.querySelector('[data-expense-photo]').files.length && !!document.querySelector('[data-existing-photo] img') && document.querySelector('[data-expense-total]').textContent.includes('220.000,25')");
        await selectPhoto(undefined, 'invalid');
        await check('invalid type shows error', "!document.querySelector('[data-photo-error]').hidden && document.querySelector('[data-photo-error]').getBoundingClientRect().height > 0 && !document.querySelector('[data-expense-photo]').files.length");
        await selectPhoto(undefined, 'oversize');
        await check('oversize shows error', "document.querySelector('[data-photo-error]').textContent.includes('2 MB') && !document.querySelector('[data-photo-error]').hidden");
        await selectPhoto();
        await check('valid replacement clears error', "document.querySelector('[data-photo-error]').hidden && !document.querySelector('[data-photo-preview]').hidden");
        await js("document.querySelector('[data-remove-expense]').click()");
        await check('row deletion disables photo fields', "document.querySelector('[data-expense-photo]').disabled && document.querySelector('[data-delete-expense]').checked");
        await js("document.querySelector('[data-remove-expense]').click(); document.querySelector('[data-add-expense]').click()");
        await selectPhoto('[data-expense-row]:last-child [data-expense-photo]');
        await check('added row supports photo and keeps field naming', "!document.querySelector('[data-expense-row]:last-child [data-photo-preview]').hidden && document.querySelector('[data-expense-row]:last-child [data-expense-photo]').name === 'new_details[0][photo]'");
        await js("document.querySelector('[data-expense-row]:last-child [data-remove-expense]').click()");
        await check('remove added row preserves existing rows', "document.querySelectorAll('[data-expense-row]').length === 2 && !document.querySelector('[data-expense-photo]').disabled");
    }
    for (const route of ['/opr', '/pd-create', '/opr-create']) {
        await navigate(route);
        await selectPhoto();
        await check('shared preview works on ' + route, "!document.querySelector('[data-photo-preview]').hidden && document.querySelector('[data-photo-preview] [data-photo-open]').href === document.querySelector('[data-photo-preview] img').src");
        await selectPhoto(undefined, 'invalid');
        await check('shared error visible on ' + route, "!document.querySelector('[data-photo-error]').hidden && getComputedStyle(document.querySelector('[data-photo-error]')).display !== 'none'");
    }
    assert.deepEqual(errors, [], 'browser errors');
    console.log(`PASS: ${checks} browser checks, four viewport sizes. Screenshots: ${run}`);
} finally {
    if (call) { try { await call('Browser.close'); } catch {} }
    socket?.close(); browser.kill(); server.close();
}
