// Browser checks for the real Blade page, rendered with isolated SQLite data.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { readFileSync, writeFileSync, mkdtempSync, existsSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import path from 'node:path';

const render = count => {
    const result = spawnSync(process.env.PHP_BINARY || 'C:/php82/php.exe', [], {
        windowsHide: true, encoding: 'utf8',
        env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:',
            DB_URL: '', SESSION_DRIVER: 'array', CACHE_STORE: 'array', APPROVAL_PENDING_COUNT: String(count) },
        input: `<?php
require 'vendor/autoload.php';
$test = new Tests\\Feature\\BsCashFlowTest('test_atasan_batch_approves_only_selected_requests_and_keeps_the_period');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
if (Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Isolated database required');
$make = new ReflectionMethod($test, 'bs');
for ($i = 1; $i <= (int) getenv('APPROVAL_PENDING_COUNT'); $i++) {
    $make->invoke($test, ['document_no' => 'BS-TEST-'.$i, 'status' => 'PENDING_ATASAN', 'nominal_pengajuan' => $i === 1 ? '100000.25' : '200000.50'], false);
}
foreach (['APPROVED_ATASAN', 'REJECTED_ATASAN'] as $status) $make->invoke($test, ['status' => $status], false);
$test->actingAs(App\\Models\\User::findOrFail(2));
echo $test->get(route('approval-atasan.index', ['periode' => '2026-09']))->assertOk()->getContent();
` });
    assert.equal(result.status, 0, result.stderr || result.stdout);
    return result.stdout
        .replace(/<link[^>]*(?:fonts\.googleapis|bootstrap-icons)[^>]*>/g, '')
        // The isolated fixture has no notification API; preserve the page's form scripts.
        .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("getElementById('notifBell')") ? '' : script)
        .replace(/(src|href)="https?:\/\/[^/"\s]+\/((?:css|js)\/[^"\s]*)"/g, '$1="/$2"')
        .replace('</body>', `<script>
document.addEventListener('submit', event => {
    if (event.defaultPrevented) return;
    event.preventDefault();
    window.submitted = {action: new URL(event.target.action).pathname, fields: [...new FormData(event.target)]};
});
</script></body>`);
};
const pages = { '/default': render(2), '/none': render(0), '/many': render(101) };
const publicRoot = path.resolve('public');
const server = createServer((req, res) => {
    const pathname = new URL(req.url, 'http://localhost').pathname;
    const asset = path.resolve(publicRoot, '.' + pathname);
    if (asset.startsWith(publicRoot + path.sep) && /^\/(js|css)\//.test(pathname) && existsSync(asset)) {
        res.writeHead(200, { 'Content-Type': pathname.endsWith('.js') ? 'text/javascript' : 'text/css' });
        res.end(readFileSync(asset));
    } else {
        res.writeHead(pages[pathname] ? 200 : 404, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(pages[pathname] || 'Not found');
    }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const run = mkdtempSync(path.join(tmpdir(), 'approval-atasan-test-'));
const browser = spawn(process.env.CHROME_BINARY || 'C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=0', '--user-data-dir=' + path.join(run, 'profile'),
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
            debug = 'http://127.0.0.1:' + readFileSync(path.join(run, 'profile/DevToolsActivePort'), 'utf8').split('\n')[0].trim();
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
    const js = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true, userGesture: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
        return result.result.value;
    };
    const check = async (label, expression) => { assert.equal(await js(expression), true, label); checks++; };
    const waitFor = async expression => {
        for (let i = 0; i < 200; i++) { if (await js(expression)) return; await sleep(50); }
        throw new Error('Timed out: ' + expression);
    };
    const navigate = async route => {
        await call('Page.navigate', { url: base + route });
        await waitFor(`document.readyState === 'complete' && location.pathname === ${JSON.stringify(route)} && typeof bootstrap !== 'undefined'`);
    };
    const open = async selector => {
        await js(`document.querySelector(${JSON.stringify(selector)}).click()`);
        await waitFor("document.getElementById('decisionModal').classList.contains('show')");
        await sleep(350);
    };
    const close = async () => {
        await js("document.querySelector('#decisionModal [data-bs-dismiss]').click()");
        await waitFor("!document.getElementById('decisionModal').classList.contains('show')");
        await sleep(350);
    };
    for (const width of [390, 1280]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        await navigate('/default');
        await check('only pending requests can be selected', "document.querySelectorAll('[data-approval-select]').length === 2 && [...document.querySelectorAll('[data-batch-action]')].every(b=>b.disabled)");
        await js("document.querySelector('[data-approval-select][value=\"1\"]').focus()");
        await call('Input.dispatchKeyEvent', { type: 'keyDown', key: ' ', code: 'Space', windowsVirtualKeyCode: 32 });
        await call('Input.dispatchKeyEvent', { type: 'keyUp', key: ' ', code: 'Space', windowsVirtualKeyCode: 32 });
        await check('keyboard selection updates count total and partial select-all', "document.getElementById('batchCount').textContent==='1' && document.getElementById('batchTotal').textContent==='Rp 100.000,25' && document.getElementById('selectAllPending').indeterminate");
        await js("document.getElementById('selectAllPending').click()");
        await check('select-all selects both pending rows', "document.getElementById('batchCount').textContent==='2' && document.getElementById('batchTotal').textContent==='Rp 300.000,75' && document.querySelectorAll('tr.is-selected').length===2 && !document.getElementById('selectAllPending').indeterminate");
        const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
        writeFileSync(path.join(run, 'selection-' + width + '.png'), Buffer.from(shot.data, 'base64'));
        await open('[data-batch-action][data-mode="approve"]');
        await check('batch modal contains only selected ids and shared optional message', "new URL(document.getElementById('decisionForm').action).pathname==='/approval-atasan/batch-approve' && document.querySelectorAll('#batchHiddenInputs input').length===2 && document.getElementById('decisionTotal').textContent==='Rp 300.000,75' && document.getElementById('decisionMessage').name==='approval_message' && !document.getElementById('decisionMessage').required");
        await close();
        await check('cancel preserves selection without a submission', "!window.submitted && document.getElementById('batchCount').textContent==='2'");
        await open('[data-batch-action][data-mode="reject"]');
        await js("document.getElementById('decisionForm').requestSubmit()");
        await check('bulk rejection requires a reason', "!window.submitted && document.getElementById('decisionMessage').required");
        await js("document.getElementById('decisionMessage').value='Alasan bersama';document.getElementById('decisionMessage').dispatchEvent(new Event('input'));document.getElementById('decisionForm').requestSubmit()");
        const rejection = await js('window.submitted');
        assert.equal(rejection.action, '/approval-atasan/batch-reject');
        assert.deepEqual(rejection.fields.filter(([key]) => key === 'ids[]').map(([, value]) => value).sort(), ['1', '2']);
        assert.ok(rejection.fields.some(([key, value]) => key === 'rejection_reason' && value === 'Alasan bersama'));
        assert.ok(rejection.fields.some(([key, value]) => key === 'periode' && value === '2026-09'));
        await check('submit is protected from a double click', "document.getElementById('decisionSubmitBtn').disabled && document.getElementById('decisionForm').dataset.submitting==='true'");

        await navigate('/default');
        await js("document.getElementById('selectAllPending').click();document.getElementById('clearSelection').click()");
        await check('clear resets select-all totals and actions', "document.getElementById('batchCount').textContent==='0' && !document.getElementById('selectAllPending').checked && document.getElementById('clearSelection').disabled");
        await js("document.querySelector('[data-selectable] td:nth-child(2)').click()");
        await check('clicking a row selects it', "document.getElementById('batchCount').textContent==='1'");
        await open('[data-batch-action][data-mode="approve"]');
        await close();
        await open('[data-selectable] [data-mode="approve"]');
        await check('single approval clears batch ids and keeps selection unchanged', "document.querySelectorAll('#batchHiddenInputs input').length===0 && document.getElementById('decisionBatchSummary').hidden && document.getElementById('batchCount').textContent==='1' && !document.getElementById('decisionForm').action.includes('batch-')");
        await close();
        await open('[data-batch-action][data-mode="approve"]');
        await js("document.getElementById('decisionMessage').value='Pesan bersama';document.getElementById('decisionForm').requestSubmit()");
        const approval = await js('window.submitted');
        assert.equal(approval.action, '/approval-atasan/batch-approve');
        assert.equal(approval.fields.filter(([key]) => key === 'ids[]').length, 1);
        assert.ok(approval.fields.some(([key, value]) => key === 'approval_message' && value === 'Pesan bersama'));
        await navigate('/none');
        await check('processed-only page disables selection', "document.getElementById('selectAllPending').disabled && document.querySelectorAll('[data-approval-select]').length===0");
        await navigate('/many');
        await js("document.getElementById('selectAllPending').click()");
        await check('oversize selection explains limit and disables batch actions', "document.getElementById('batchCount').textContent==='101' && !document.getElementById('batchLimit').hidden && [...document.querySelectorAll('[data-batch-action]')].every(b=>b.disabled)");
        await js("document.querySelector('[data-approval-select]').click()");
        await check('100 selections can be processed', "document.getElementById('batchCount').textContent==='100' && document.getElementById('batchLimit').hidden && [...document.querySelectorAll('[data-batch-action]')].every(b=>!b.disabled)");
    }
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} browser checks at 390 and 1280 px; real Bootstrap modals, selected IDs and keyboard selection verified. Screenshots: ${run}`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    if (socket) socket.close();
    browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
