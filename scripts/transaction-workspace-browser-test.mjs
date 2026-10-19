// Browser navigation against real Blade pages and isolated SQLite fixtures.
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
$test = new Tests\\Feature\\BsCashFlowTest('test_transaction_lists_have_one_detail_action_per_source');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
if (Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Isolated database required');
$bs = (new ReflectionMethod($test, 'bs'))->invoke($test);
$source = (new ReflectionMethod($test, 'expense'))->invoke($test, $bs, 'VALIDATED', '100000', true, ['user_id' => 3]);
Illuminate\\Support\\Facades\\DB::table('wilayah')->insert(['id' => 2, 'nama' => 'Jawa Barat', 'teritory' => 'Jawa']);
Illuminate\\Support\\Facades\\DB::table('wilayah')->where('id', 1)->update(['nama' => 'Kota Bandung', 'parent_id' => 2]);
$source->forceFill(['description' => 'Kunjungan ke Bandung', 'jarak_km' => '250.00', 'durasi_jam' => '10.00',
    'plafon_value' => '250000', 'validated_at' => '2026-09-16 01:30:28'])->save();
$source->details()->first()->update(['remark' => 'Bensin Mobil', 'photo_path' => 'receipts/preview.png']);
$source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek Oli', 'nominal' => '100000']);
(new ReflectionMethod($test, 'recapDecision'))->invoke($test, $source, 'PD', 'APPROVED', '100000', 'Pemeriksaan awal untuk pemohon');
(new ReflectionMethod($test, 'recapDecision'))->invoke($test, $source, 'PD', 'APPROVED', '85000', 'Hasil cek ulang untuk pemohon');
$detailUrl = route('transaction-recap.show', ['PD', $source->id]);
$reviewUrl = route('transaction-recap.show', ['jenis' => 'PD', 'source' => $source->id, 'pemeriksaan' => 1]);
$pages = [
    '/detail' => $test->get($detailUrl)->assertOk()->getContent(),
    '/review' => $test->get($reviewUrl)->assertOk()->getContent(),
];
$test->actingAs(App\\Models\\User::findOrFail(3));
$pages['/owner'] = $test->get($detailUrl)->assertOk()->getContent();
echo json_encode(['pages' => $pages, 'detailUrl' => $detailUrl, 'reviewUrl' => $reviewUrl, 'photo' => base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='))], JSON_THROW_ON_ERROR);
` });
assert.equal(render.status, 0, render.stderr || render.stdout);
const fixture = JSON.parse(render.stdout);
const bootstrapPath = 'storage/app/private/transaction-recap-verification/bootstrap.min.css';
const pages = Object.fromEntries(Object.entries(fixture.pages).map(([key, html]) => [key, html
    .replace(/<link\b[^>]*href="https:\/\/cdn.jsdelivr.net\/npm\/bootstrap@5.3.0\/dist\/css\/bootstrap.min.css"[^>]*>/g,
        tag => existsSync(bootstrapPath) ? '<style>' + readFileSync(bootstrapPath, 'utf8') + '</style>' : tag)
    .replaceAll(fixture.reviewUrl.replaceAll('&', '&amp;'), '/review').replaceAll(fixture.detailUrl, '/detail')
    .replace(/(src|href)="https?:\/\/[^/"\s]+\/((?:css|js)\/[^"\s]*)"/g, '$1="/$2"')
    .replace(/https?:\/\/[^/"\s]+(\/(?:rincian-biaya|rincian-pemeriksaan)\/[^"\s]+)/g, '$1')
    .replace(/(data-[\w-]+-url)="https?:\/\/[^/"\s]+(\/[^"\s]*)"/g, '$1="$2"')
    .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("getElementById('notifBell')") ? '' : script)
]));
const publicRoot = path.resolve('public');
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    const file = path.resolve(publicRoot, '.' + url.pathname);
    if (pages[url.pathname]) { res.setHeader('Content-Type', 'text/html; charset=utf-8'); res.end(pages[url.pathname]); }
    else if (/^\/(rincian-biaya|rincian-pemeriksaan)\//.test(url.pathname) || url.pathname === '/fixture.png') { res.setHeader('Content-Type', 'image/png'); res.end(Buffer.from(fixture.photo, 'base64')); }
    else if (file.startsWith(publicRoot + path.sep) && /^\/(css|js)\//.test(url.pathname) && existsSync(file)) { res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css'); res.end(readFileSync(file)); }
    else { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify(url.pathname.includes('periode') ? { open: true } : { owners: [{ id: 1, balance: '250000', opening_final: false }] })); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const run = mkdtempSync(path.join(tmpdir(), 'transaction-workspace-preview-'));
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
    await call('Network.enable');
    await call('Network.setBlockedURLs', { urls: ['https://*'] });
    const js = async expression => { const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true }); if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text); return result.result.value; };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const wait = async expression => { for (let i = 0; i < 200; i++) { if (await js(expression)) return; await sleep(50); } throw new Error('Timeout: ' + expression); };
    const navigate = async route => { await call('Page.navigate', { url: base + route }); await wait(`location.pathname === ${JSON.stringify(route)} && document.readyState === 'complete'`); };
    const screenshot = async name => { const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false }); writeFileSync(path.join(run, name + '.png'), Buffer.from(shot.data, 'base64')); };
    for (const width of [320, 390, 844, 1366]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: width < 600 });
        await navigate('/detail');
        await check('detail page fits at ' + width, "document.documentElement.scrollWidth <= innerWidth + 1");
        await check('details and result share the same page without the removed summary', "document.querySelector('#rincian-pengajuan') && document.querySelector('#hasil-finance') && document.querySelector('#decision-history-title') && !document.querySelector('#bs-check-form,#finance-result-title')");
        await check('one cost table shows six columns and three totals', "(()=>{const tables=document.querySelectorAll('#rincian-pengajuan table');const t=tables[0];return tables.length===1 && [...t.querySelectorAll('thead th')].map(e=>e.textContent.trim()).join('|')==='Item|Keterangan|Foto|Nominal Asal|Nominal Disetujui|Selisih' && t.querySelectorAll('tbody tr').length===2 && [...t.querySelectorAll('tfoot td')].map(e=>e.textContent.trim()).join('|')==='Rp 200.000,00|Rp 170.000,00|Rp 30.000,00';})()");
        await check('submission header uses original transaction data and distinguishes BS owner', "(()=>{const fields=Object.fromEntries([...document.querySelectorAll('#informasi-pengajuan .recap-info-field')].map(el=>[el.querySelector('dt').textContent.trim(),el.querySelector('dd').textContent.trim()]));return fields['User (Karyawan)']==='Anggota' && fields['Pemilik Saldo BS']==='Pemilik' && fields['Tujuan Dinas']==='Kota Bandung, Jawa Barat' && fields['Territory']==='Jawa' && fields['Jarak']==='250,00 KM' && fields['Durasi']==='10,00 Jam' && fields['Total Pengeluaran']==='Rp 200.000,00' && fields['Status Plafon']==='Dalam Plafon (Sisa: Rp 50.000,00)' && fields['Waktu konfirmasi']==='16 Sep 2026, 08:30:28 WIB' && fields['Deskripsi Kegiatan']==='Kunjungan ke Bandung';})()");
        await check('submission header fits its columns', "[...document.querySelectorAll('#informasi-pengajuan dd')].every(el=>el.scrollWidth<=el.clientWidth+1) && getComputedStyle(document.querySelector('.recap-info-grid')).gridTemplateColumns.split(' ').length===(innerWidth<=575?1:2)");
        await screenshot('detail-' + width);
        if (width >= 1366) {
            const clip = await js("(()=>{const r=document.querySelector('#informasi-pengajuan').getBoundingClientRect();return {x:r.x+scrollX,y:r.y+scrollY,width:r.width,height:r.height,scale:1}})()");
            const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip });
            writeFileSync(path.join(run, 'informasi-pengajuan.png'), Buffer.from(shot.data, 'base64'));
        }
        await js("document.querySelector('.finance-audit').scrollIntoView({block:'start'})");
        await check('audit groups two checks in a timeline', "document.querySelectorAll('[data-audit-check]').length === 2 && document.querySelector('.finance-audit').textContent.includes('Jejak Audit (Audit Trail)')");
        await screenshot('audit-' + width);
        await js("document.querySelector('.finance-audit-details > summary').click()");
        await check('audit details expand without overflowing page', "document.querySelector('.finance-audit-details').open && document.querySelectorAll('.finance-audit-details tbody tr').length === 4 && document.documentElement.scrollWidth <= innerWidth + 1");
        await screenshot('audit-expanded-' + width);
        await js("document.querySelector('#hasil-finance').scrollIntoView({block:'start'})");
        await wait("document.querySelector('#hasil-finance img').naturalWidth > 0");
        await screenshot('result-' + width);
        if (width >= 1366) {
            const clip = await js("(()=>{const r=document.querySelector('#rincian-pengajuan').getBoundingClientRect();return {x:r.x+scrollX,y:r.y+scrollY,width:r.width,height:r.height,scale:1}})()");
            const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip });
            writeFileSync(path.join(run, 'rincian-gabungan.png'), Buffer.from(shot.data, 'base64'));
        }
        await js("document.querySelector('#hasil-finance [data-photo-open]').click()");
        await wait("document.querySelector('dialog[open] img')?.naturalWidth > 0");
        await check('result photo opens inside the page', "(()=>{const r=document.querySelector('dialog[open]').getBoundingClientRect();return r.left>=0 && r.right<=innerWidth+1 && r.top>=0 && r.bottom<=innerHeight+1})()");
        await js("document.querySelector('.photo-lightbox-close').click()");
        await js("document.querySelector('[data-review-action][href=\"/review\"]').click()");
        await wait("location.pathname === '/review' && document.readyState === 'complete'");
        await check('review page fits at ' + width, "document.documentElement.scrollWidth <= innerWidth + 1");
        await check('review has one working form and correct totals', "document.querySelectorAll('#bs-check-form').length === 1 && document.querySelector('[name=_workspace]').value === '1' && document.querySelector('#check-total-approved').textContent === '170.000,00' && document.querySelector('#check-total-difference').textContent === '30.000,00'");
        await js("document.querySelector('[data-check-item]').scrollIntoView({block:'center'})");
        await screenshot('review-' + width);
        await check('receipt stays a thumbnail', "document.querySelector('.photo-thumbnail-check').getBoundingClientRect().width <= 60");
        await js("document.querySelector('.recap-nav a[href=\"/detail\"]').click()");
        await wait("location.pathname === '/detail' && document.readyState === 'complete'");
        await check('return from review shows result', "document.querySelector('#hasil-finance') && document.querySelector('.recap-nav [aria-current=page]').textContent.includes('Rincian')");
        await navigate('/owner');
        await check('owner cannot see Finance controls or internal notes', "document.querySelectorAll('.recap-nav a').length === 1 && !document.body.textContent.includes('Catatan internal Finance') && !document.querySelector('#bs-check-form')");
        await check('owner sees receipt in its own column', "document.querySelector('#rincian-pengajuan tbody tr td:nth-child(3) [data-photo-open]') !== null");
    }
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} workspace browser checks at 320, 390, 844 and 1366 px; no JavaScript errors. Screenshots: ${run}`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    if (socket) socket.close();
    browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
