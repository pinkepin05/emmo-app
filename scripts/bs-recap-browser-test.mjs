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
$test = new Tests\\Feature\\BsCashFlowTest('test_bs_dashboard_combines_usage_and_finance_with_real_values');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
if (Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Isolated database required');
$bs = (new ReflectionMethod($test, 'bs'))->invoke($test, ['nominal_pengajuan' => '500000', 'nominal_approved' => '300000']);
$source = (new ReflectionMethod($test, 'expense'))->invoke($test, $bs, 'VALIDATED', '200000', true);
(new ReflectionMethod($test, 'recapDecision'))->invoke($test, $source, 'PD', 'APPROVED', '170000');
(new ReflectionMethod($test, 'expense'))->invoke($test, $bs, 'VALIDATED', '250000');
App\\Models\\Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
$old = (new ReflectionMethod($test, 'bs'))->invoke($test, ['periode' => '2026-08', 'tanggal' => '2026-08-01', 'nominal_pengajuan' => '250000', 'nominal_approved' => '250000']);
$oldSource = (new ReflectionMethod($test, 'expense'))->invoke($test, $old, 'VALIDATED', '100000', true);
(new ReflectionMethod($test, 'recapDecision'))->invoke($test, $oldSource, 'PD', 'APPROVED', '90000');
$future = (new ReflectionMethod($test, 'bs'))->invoke($test, ['periode' => '2026-10', 'tanggal' => '2026-10-01', 'nominal_pengajuan' => '700000', 'nominal_approved' => '700000']);
(new ReflectionMethod($test, 'expense'))->invoke($test, $future, 'VALIDATED', '500000');
$test->actingAs(App\\Models\\User::findOrFail(1));
$pages = [
    '/dashboard/transaksi' => $test->get(route('dashboard.transaksi'))->assertOk()->getContent(),
    '/laporan-transaksi/modul/PD' => $test->get(route('transaction-recap.module', ['jenis' => 'PD']))->assertOk()->getContent(),
];
foreach (['CHECKED', 'PENDING'] as $status) {
    $pages['/laporan-transaksi/modul/PD?pemakaian_status='.$status] = $test->get(route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_status' => $status]))->assertOk()->getContent();
}
foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) {
    $pages['/laporan-transaksi/modul/'.$type] = $test->get(route('transaction-recap.module', ['jenis' => $type]))->assertOk()->getContent();
}
foreach (['diajukan', 'dicairkan', 'pending', 'ditolak'] as $stage) {
    $pages['/rekap-bs?tahap='.$stage] = $test->get(route('transaction-recap.funding', ['tahap' => $stage]))->assertOk()->getContent();
}
foreach (['2026-08', '', '2026-07'] as $period) {
    $pages['/dashboard/transaksi?pemakaian_periode='.$period] = $test->get(route('dashboard.transaksi', ['pemakaian_periode' => $period]))->assertOk()->getContent();
}
$sources = (new ReflectionMethod($test, 'transactionActionSources'))->invoke($test);
$finance = (new ReflectionMethod($test, 'transactionActionUser'))->invoke($test);
foreach ($sources as $route => $transaction) {
    $path = parse_url(route($route.'.index'), PHP_URL_PATH);
    $test->actingAs(App\\Models\\User::findOrFail(1));
    $pages[$path] = $test->get(route($route.'.index', ['periode' => '2026-09']))->assertOk()->getContent();
    $pages[$path.'?dilaporkan=1'] = $test->get(route($route.'.index', ['periode' => '2026-09', 'dilaporkan' => 1]))->assertOk()->getContent();
    $test->actingAs($finance);
    $pages['/fixture-finance'.$path] = $test->get(route($route.'.index', ['periode' => '2026-09']))->assertOk()->getContent();
}
$shared = (new ReflectionMethod($test, 'expense'))->invoke($test, $bs, 'VALIDATED', '75000', true, ['user_id' => 3, 'bs_owner_id' => 1, 'bs_id' => null]);
(new ReflectionMethod($test, 'recapDecision'))->invoke($test, $shared, 'PD', 'APPROVED', '50000');
(new ReflectionMethod($test, 'expense'))->invoke($test, $bs, 'VALIDATED', '25000', true, ['user_id' => 3]);
foreach ([1 => 'owner', 3 => 'member'] as $viewer => $label) {
    $test->actingAs(App\\Models\\User::findOrFail($viewer));
    $pages['/fixture-cash-'.$label] = $test->get(route('cash-flow.show', '202609'))->assertOk()->getContent();
}
echo json_encode(['pages' => $pages], JSON_THROW_ON_ERROR);
` });
assert.equal(render.status, 0, render.stderr || render.stdout);
const fixture = JSON.parse(render.stdout);
const bootstrapPath = 'storage/app/private/transaction-recap-verification/bootstrap.min.css';
const pages = Object.fromEntries(Object.entries(fixture.pages).map(([key, html]) => [key, html
    .replaceAll('http://localhost', '')
    .replace(/<link\b[^>]*href="https:\/\/cdn.jsdelivr.net\/npm\/bootstrap@5.3.0\/dist\/css\/bootstrap.min.css"[^>]*>/g,
        tag => existsSync(bootstrapPath) ? '<style>' + readFileSync(bootstrapPath, 'utf8') + '</style>' : tag)
    .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("getElementById('notifBell')") ? '' : script)
]));
const publicRoot = path.resolve('public');
const server = createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    const file = path.resolve(publicRoot, '.' + url.pathname);
    let pageKey = url.pathname === '/rekap-bs' ? url.pathname + '?tahap=' + (url.searchParams.get('tahap') || 'diajukan') : url.pathname;
    if (url.searchParams.get('dilaporkan') === '1') pageKey += '?dilaporkan=1';
    if (url.pathname === '/dashboard/transaksi' && url.searchParams.has('pemakaian_periode') && url.searchParams.get('pemakaian_periode') !== '2026-09') {
        pageKey += '?pemakaian_periode=' + url.searchParams.get('pemakaian_periode');
    }
    if (url.pathname === '/laporan-transaksi/modul/PD' && ['CHECKED', 'PENDING'].includes(url.searchParams.get('pemakaian_status'))) {
        pageKey += '?pemakaian_status=' + url.searchParams.get('pemakaian_status');
    }
    if (pages[pageKey]) { res.setHeader('Content-Type', 'text/html; charset=utf-8'); res.end(pages[pageKey]); }
    else if (/^\/(rincian-biaya|rincian-pemeriksaan)\//.test(url.pathname) || url.pathname === '/fixture.png') { res.setHeader('Content-Type', 'image/png'); res.end(Buffer.from(fixture.photo, 'base64')); }
    else if (file.startsWith(publicRoot + path.sep) && /^\/(css|js)\//.test(url.pathname) && existsSync(file)) { res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css'); res.end(readFileSync(file)); }
    else { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify(url.pathname.includes('periode') ? { open: true } : { owners: [{ id: 1, balance: '250000', opening_final: false }] })); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const run = mkdtempSync(path.join(tmpdir(), 'bs-recap-preview-'));
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
    // Bootstrap CSS is embedded above; external fonts/CDN scripts must not stall these local fixtures.
    await call('Network.enable');
    await call('Network.setBlockedURLs', { urls: ['https://*'] });
    await call('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    const js = async expression => { const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true }); if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text); return result.result.value; };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const wait = async expression => { for (let i = 0; i < 200; i++) { if (await js(expression)) return; await sleep(50); } throw new Error('Timeout: ' + expression); };
    const navigate = async route => {
        // Same-path navigation must wait for the new document, not the previous query's page.
        await js('window.__recapNavigationPending = true');
        await call('Page.navigate', { url: base + route });
        await wait(`!window.__recapNavigationPending && location.pathname + location.search === ${JSON.stringify(route)} && document.readyState === 'complete'`);
    };
    const screenshot = async name => { const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false }); writeFileSync(path.join(run, name + '.png'), Buffer.from(shot.data, 'base64')); };
    for (const width of [320, 390, 844, 1366, 1600]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
        await navigate('/dashboard/transaksi');
        await check('dashboard fits at ' + width, "document.documentElement.scrollWidth <= innerWidth + 1");
        await check('one global period filter', "document.querySelectorAll('[name=pemakaian_periode]').length === 1 && document.querySelector('.dashboard-period-filter input[type=month]').value === '2026-09' && document.querySelector('.recap-filter') === null && document.querySelector('.dashboard-period-filter').scrollWidth <= document.querySelector('.dashboard-period-filter').clientWidth + 1");
        await check('two ordered sections with unified usage', "document.getElementById('funding-recap-title').getBoundingClientRect().top < document.getElementById('usage-recap-title').getBoundingClientRect().top && !document.getElementById('finance-recap-title') && document.querySelectorAll('[data-usage-stage]').length === 4 && !document.querySelector('[data-usage-stage=DIFFERENCE]')");
        await check('real values in unified summary', "document.querySelector('[data-funding-stage=diajukan] .recap-module-total').textContent.trim() === 'Rp 500.000,00' && document.querySelector('[data-funding-stage=dicairkan] .recap-module-total').textContent.trim() === 'Rp 300.000,00' && document.querySelector('[data-funding-stage=pending] .recap-module-total').textContent.trim() === 'Rp 0,00' && document.querySelector('[data-funding-stage=ditolak] .recap-module-total').textContent.trim() === 'Rp 200.000,00' && document.querySelector('[data-usage-stage=REPORTED] .recap-module-total').textContent.trim() === 'Rp 450.000,00' && document.querySelector('[data-usage-stage=APPROVED] .recap-module-total').textContent.trim() === 'Rp 170.000,00' && document.querySelector('[data-usage-stage=PENDING] .recap-module-total').textContent.trim() === 'Rp 250.000,00' && document.querySelector('[data-usage-stage=REJECTED] .recap-module-total').textContent.trim() === 'Rp 30.000,00'");
        await check('four cards with difference inside rejected card', "document.querySelector('.recap-funding-grid').children.length === 4 && document.querySelector('[data-funding-stage=ditolak]').textContent.includes('Termasuk selisih Finance:') && getComputedStyle(document.querySelector('.recap-funding-grid')).gridTemplateColumns.split(' ').length === (innerWidth >= 1400 ? 4 : innerWidth > 767 ? 2 : 1)");
        await check('funding panel preserves pending and rejected label colors', "getComputedStyle(document.querySelector('[data-funding-stage=pending] .recap-funding-label')).color === 'rgb(180, 83, 9)' && getComputedStyle(document.querySelector('[data-funding-stage=ditolak] .recap-funding-label')).color === 'rgb(220, 38, 38)'");
        await check('all cards fit', "[...document.querySelectorAll('[data-funding-stage],[data-usage-stage]')].every(el=>el.scrollWidth<=el.clientWidth+1)");
        await check('usage panel adapts to viewport', "getComputedStyle(document.querySelector('.recap-usage-totals')).gridTemplateColumns.split(' ').length === (innerWidth >= 1200 ? 4 : innerWidth > 575 ? 2 : 1) && getComputedStyle(document.querySelector('.recap-usage-finance')).gridTemplateColumns.split(' ').length === (innerWidth > 575 ? 3 : 1)");
        await js("document.querySelector('.dashboard-period-filter').scrollIntoView({block:'start'})");
        await screenshot('filter-' + width);
        await js("document.querySelector('[data-funding-recap]').scrollIntoView({block:'start'})");
        await screenshot('funding-' + width);
        await js("document.querySelector('[data-usage-recap]').scrollIntoView({block:'start'})");
        await screenshot('usage-' + width);
        if (width >= 1366) {
            const clip = await js("(()=>{const r=document.querySelector('[data-usage-recap]').getBoundingClientRect();return {x:r.x+scrollX,y:r.y+scrollY,width:r.width,height:r.height,scale:1}})()");
            const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip });
            writeFileSync(path.join(run, 'unified-usage-' + width + '.png'), Buffer.from(shot.data, 'base64'));
        }
        await check('each module has two distinct links and all three Finance amounts', "!document.querySelector('[data-usage-recap] details, [data-usage-recap] summary') && !document.querySelector('[data-usage-recap]').textContent.includes('Lihat Riwayat') && document.querySelectorAll('[data-usage-history]').length === 8 && [...document.querySelectorAll('[data-usage-history]')].every(a=>a.checkVisibility() && a.scrollWidth<=a.clientWidth+1) && [...document.querySelectorAll('[data-usage-finance]')].every(a=>!new URL(a.href).searchParams.has('pemakaian_status') && a.querySelectorAll('[data-finance-status]').length===3 && !a.querySelector('a,button')) && document.documentElement.scrollWidth <= innerWidth + 1");
        await check('all status amounts remain visible including zero', "[...document.querySelectorAll('[data-usage-row=PD] [data-finance-status] strong')].map(el=>el.textContent.trim()).join('|')==='Rp 170.000,00|Rp 0,00|Rp 30.000,00' && document.querySelector('[data-usage-row=OPR] [data-finance-status=PENDING] strong').textContent.trim()==='Rp 250.000,00' && [...document.querySelectorAll('[data-finance-status]')].every(el=>el.checkVisibility() && el.scrollWidth<=el.clientWidth+1)");
        await screenshot('usage-history-' + width);
        await js("document.querySelector('[data-usage-row=PD] [data-finance-status=REJECTED]').click()");
        await wait("location.pathname === '/laporan-transaksi/modul/PD' && document.readyState === 'complete'");
        await check('clicking a status inside the grouped card opens all statuses', "!new URLSearchParams(location.search).has('pemakaian_status') && document.querySelector('[name=pemakaian_status]').value==='' && !document.querySelector('.recap-status-tabs [aria-current=true]') && document.querySelector('h1').textContent.includes('Daftar Pengecekan Finance')");
        await check('Finance list has only the requested filters and no summary cards', "!document.querySelector('.recap-amount-summary,.recap-submission-total,.recap-period-shortcuts,[data-amount-status]') && document.querySelectorAll('.recap-filter input:not([type=hidden])').length===2 && document.querySelector('.recap-filter input[type=search]') && document.querySelector('.recap-filter input[type=month]') && document.querySelectorAll('.recap-status-tabs a').length===2 && document.querySelector('[data-check-status=CHECKED]').textContent.includes('Sudah Dicek') && document.querySelector('[data-check-status=PENDING]').textContent.includes('Belum Dicek') && document.documentElement.scrollWidth<=innerWidth+1");
        await screenshot('finance-list-' + width);
        await js("document.querySelector('[data-check-status=CHECKED]').click()");
        await wait("new URLSearchParams(location.search).get('pemakaian_status')==='CHECKED' && document.readyState==='complete'");
        await check('checked filter preserves its counts and displays the approved transaction', "document.querySelector('[name=pemakaian_status]').value==='CHECKED' && document.querySelector('[data-check-status=CHECKED][aria-current=true]') && document.querySelector('[data-label=\"Disetujui Finance\"]').textContent.trim()==='Rp 170.000,00' && [...document.querySelectorAll('.recap-status-tabs .tab-count')].map(el=>el.textContent.trim()).join('|')==='1|0' && new URLSearchParams(location.search).get('pemakaian_periode')==='2026-09'");
        await js("document.querySelector('[data-check-status=PENDING]').click()");
        await wait("new URLSearchParams(location.search).get('pemakaian_status')==='PENDING' && document.readyState==='complete'");
        await check('pending empty state keeps both counts and no period shortcuts', "document.querySelector('[data-check-status=PENDING][aria-current=true]') && document.querySelector('.recap-empty-enhanced') && [...document.querySelectorAll('.recap-status-tabs .tab-count')].map(el=>el.textContent.trim()).join('|')==='1|0' && !document.querySelector('.recap-empty-enhanced').textContent.includes('Semua Periode') && document.documentElement.scrollWidth<=innerWidth+1");
        await js("document.querySelector('[data-check-status=PENDING]').click()");
        await wait("document.readyState==='complete' && document.querySelector('[name=pemakaian_status]')?.value==='' && document.querySelector('.usage-table')!==null");
        await check('selected status can be cleared without losing period', "!document.querySelector('.recap-status-tabs [aria-current=true]') && new URLSearchParams(location.search).get('pemakaian_periode')==='2026-09'");
        await js("document.querySelector('.recap-back').click()");
        await wait("location.pathname === '/dashboard/transaksi' && document.readyState === 'complete'");
        await js("document.querySelector('[data-funding-stage=dicairkan]').click()");
        await wait("location.pathname === '/rekap-bs' && document.readyState === 'complete'");
        await check('funding history works', "document.querySelector('h1').textContent.includes('Pengajuan BS yang Dicairkan') && document.querySelector('table thead').textContent.includes('Nominal Dicairkan') && document.querySelector('table tbody td.text-end').textContent.trim() === 'Rp 300.000,00' && document.querySelector('.recap-filter input[type=search]') !== null && document.documentElement.scrollWidth <= innerWidth + 1");
        await screenshot('history-' + width);
        for (const [stage, label, amount] of [['diajukan', 'Diajukan', 'Rp 500.000,00'], ['pending', 'Pending', null], ['ditolak', 'Ditolak / Selisih', 'Rp 200.000,00']]) {
            await js("document.querySelector('.recap-back').click()");
            await wait("location.pathname === '/dashboard/transaksi' && document.readyState === 'complete'");
            await js(`document.querySelector('[data-funding-stage=${stage}]').click()`);
            await wait(`location.pathname === '/rekap-bs' && new URLSearchParams(location.search).get('tahap') === ${JSON.stringify(stage)} && document.readyState === 'complete'`);
            await check(stage + ' history uses its own amount', `document.querySelector('table thead').textContent.includes(${JSON.stringify('Nominal ' + label)}) && (document.querySelector('table tbody td.text-end')?.textContent.trim() ?? null) === ${JSON.stringify(amount)} && document.documentElement.scrollWidth <= innerWidth + 1`);
        }
        await screenshot('rejected-history-' + width);
        await js("document.querySelector('.recap-back').click()");
        await wait("location.pathname === '/dashboard/transaksi' && document.readyState === 'complete'");
        for (const [type, pathname, title] of [['OPR', '/transaksi-operasional', 'Daftar Transaksi Operasional Area Sales'], ['PD', '/transaksi-perjalanan-dinas', 'Daftar Transaksi Perjalanan Dinas'], ['EDK', '/event-dalam-kota', 'Daftar Transaksi Event Dalam Kota'], ['ELK', '/event-luar-kota', 'Daftar Event Luar Kota']]) {
            await navigate('/dashboard/transaksi');
            await js(`document.querySelector('[data-usage-finance][data-usage-history=${type}]').click()`);
            await wait(`location.pathname === '/laporan-transaksi/modul/${type}' && document.readyState === 'complete'`);
            await check('Finance link opens all statuses for ' + type, "document.querySelector('h1').textContent.includes('Daftar Pengecekan Finance') && document.querySelector('[name=pemakaian_status]').value==='' && new URLSearchParams(location.search).get('pemakaian_periode')==='2026-09'");
            await navigate('/dashboard/transaksi');
            await js(`document.querySelector('[data-usage-reported][data-usage-history=${type}]').click()`);
            await wait(`location.pathname === ${JSON.stringify(pathname)} && document.readyState === 'complete'`);
            await check('reported usage opens native ' + type + ' list', `document.body.textContent.includes(${JSON.stringify(title)}) && new URLSearchParams(location.search).get('periode')==='2026-09' && document.querySelector('select[name=periode]').value==='2026-09' && document.documentElement.scrollWidth <= innerWidth + 1`);
            await check('reported list matches the dashboard for ' + type, `new URLSearchParams(location.search).get('dilaporkan')==='1' && document.querySelector('input[name=dilaporkan]').value==='1' && document.querySelectorAll('.transaction-total').length===${['OPR', 'PD'].includes(type) ? 1 : 0} && [...document.querySelectorAll('.transaction-total')].every(cell=>cell.textContent.trim()===${JSON.stringify(type === 'OPR' ? 'Rp 250.000,00' : 'Rp 200.000,00')}) && !document.querySelector('tbody').textContent.includes('Draft')`);
            await screenshot('reported-user-' + type + '-' + width);
            await navigate(pathname);
            await check('user has direct Detail only for ' + type, "document.querySelectorAll('.transaction-action-detail').length > 0 && !document.querySelector('.transaction-action-delete,[data-action-toggle],[data-action-menu]') && [...document.querySelectorAll('.transaction-action-detail')].every(a=>a.getBoundingClientRect().height >= 44)");
            await check('each transaction shows its total for ' + type, `document.querySelector('thead').textContent.includes('Total Nominal') && document.querySelectorAll('.transaction-total').length === document.querySelectorAll('.transaction-action-detail').length && [...document.querySelectorAll('.transaction-total')].some(cell=>cell.textContent.trim()===${JSON.stringify(['OPR', 'PD'].includes(type) ? 'Rp 600.000,00' : 'Rp 300,00')})`);
            await screenshot('actions-user-' + type + '-' + width);
            await navigate('/fixture-finance' + pathname);
            await check('finance has direct Detail and Hapus for ' + type, "document.querySelector('.transaction-action-detail') !== null && document.querySelector('.transaction-action-delete') !== null && !document.querySelector('[data-action-toggle],[data-action-menu]') && document.documentElement.scrollWidth <= innerWidth + 1");
            await screenshot('actions-finance-' + type + '-' + width);
        }

        for (const viewer of ['owner', 'member']) {
            await navigate('/fixture-cash-' + viewer);
            await check('shared BS usage is visible to ' + viewer + ' at ' + width, "document.querySelectorAll('[data-shared-bs-reference]').length===2 && document.querySelector('#sec-shared-bs').textContent.includes('Pemakai: Anggota') && document.querySelector('#sec-shared-bs').textContent.includes('Pemilik BS: Pemilik') && document.querySelector('#sec-shared-bs').textContent.includes('Belum Dicek Finance') && document.querySelector('#sec-shared-bs').textContent.includes('Rp 50.000,00') && document.documentElement.scrollWidth<=innerWidth+1");
            await check('shared BS is only charged to owner at ' + width, `document.querySelector('#sec-keluar .cf-badge').textContent.trim()===${JSON.stringify(viewer === 'owner' ? '2 transaksi' : '0 transaksi')}`);
            await js("document.querySelector('#sec-shared-bs').scrollIntoView({block:'start'})");
            await screenshot('shared-bs-' + viewer + '-' + width);
        }
        await navigate('/dashboard/transaksi');
        await js("document.getElementById('dashboard-period').value='2026-08';document.querySelector('.dashboard-period-filter button').click()");
        await wait("new URLSearchParams(location.search).get('pemakaian_periode')==='2026-08' && document.readyState==='complete' && document.querySelector('.dashboard-saldo-section__period')?.textContent.includes('Agustus')");
        await check('chosen month changes both recaps', "document.querySelector('[data-funding-stage=diajukan] .recap-module-total').textContent.trim()==='Rp 250.000,00' && document.querySelector('[data-usage-stage=REPORTED] .recap-module-total').textContent.trim()==='Rp 100.000,00' && document.querySelector('[data-usage-stage=APPROVED] .recap-module-total').textContent.trim()==='Rp 90.000,00'");
        await check('history links preserve selected period', "[...document.querySelectorAll('[data-funding-stage],[data-usage-history]')].every(a=>{const p=new URL(a.href).searchParams;return (p.get('pemakaian_periode')??p.get('periode'))==='2026-08'})");
        await js("document.getElementById('dashboard-period').value='2026-07';document.querySelector('.dashboard-period-filter button').click()");
        await wait("new URLSearchParams(location.search).get('pemakaian_periode')==='2026-07' && document.readyState==='complete' && document.querySelector('.dashboard-saldo-section__period')?.textContent.includes('Juli')");
        await check('empty month shows zeros', "[...document.querySelectorAll('[data-usage-stage] .recap-module-total')].every(el=>el.textContent.trim()==='Rp 0,00') && document.querySelector('[data-funding-stage=diajukan] .recap-module-total').textContent.trim()==='Rp 0,00'");
    }
    // Export the actual rendered panel without the surrounding dashboard chrome.
    await navigate('/dashboard/transaksi');
    await js("document.body.innerHTML = document.querySelector('[data-usage-recap]').outerHTML; document.body.style.cssText = 'margin:0;padding:20px;background:#f8fafc;'; scrollTo(0,0)");
    const preview = await js("(()=>{const r=document.querySelector('[data-usage-recap]').getBoundingClientRect();return {x:0,y:0,width:innerWidth,height:r.bottom+20,scale:1}})()");
    const previewShot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: preview });
    writeFileSync(path.join(run, 'rekap-pemakaian-konfirmasi-bs.png'), Buffer.from(previewShot.data, 'base64'));
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} BS recap browser checks at 320, 390, 844, 1366 and 1600 px; no JavaScript errors. Screenshots: ${run}`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    if (socket) socket.close();
    browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
