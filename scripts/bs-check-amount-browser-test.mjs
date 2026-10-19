// Render the real form and persist submitted amounts using an isolated SQLite fixture.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { readFileSync, mkdtempSync } from 'node:fs';
import { createServer } from 'node:http';
import { tmpdir } from 'node:os';
import path from 'node:path';

const workspaceMode = process.argv.includes('--workspace');
const render = (submitted = null) => {
    const result = spawnSync(process.env.PHP_BINARY || 'C:/php82/php.exe', [], {
        windowsHide: true, encoding: 'utf8',
        env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:',
            DB_URL: '', SESSION_DRIVER: 'array', CACHE_STORE: 'array', CHECK_SUBMITTED: JSON.stringify(submitted), CHECK_WORKSPACE: workspaceMode ? '1' : '0' },
        input: `<?php
require 'vendor/autoload.php';
$test = new Tests\\Feature\\BsUsageCheckTest('test_finance_form_preserves_saved_amounts_and_notes_through_draft_and_confirmation');
(new ReflectionMethod($test, 'setUp'))->invoke($test);
if (Illuminate\\Support\\Facades\\DB::connection()->getDatabaseName() !== ':memory:') throw new RuntimeException('Isolated database required');
$source = (new ReflectionMethod($test, 'source'))->invoke($test, 'PD', []);
foreach (['Bensin Mobil', 'Cek Oli'] as $remark) $source->details()->create(['cost_category_id' => 1, 'nominal' => '100000', 'remark' => $remark]);
$workspace = getenv('CHECK_WORKSPACE') === '1';
$url = $workspace ? route('transaction-recap.show', ['jenis' => 'PD', 'source' => $source->id, 'pemeriksaan' => 1]) : route('bs-usage-checks.create', ['PD', $source->id]);
$form = $test->get($url)->assertOk();
$formData = $workspace ? $form->viewData('formData') : ['sourceToken' => $form->viewData('sourceToken'), 'reviewToken' => $form->viewData('reviewToken')];
$submitted = json_decode(getenv('CHECK_SUBMITTED'), true);
$saved = null;
if ($submitted !== null) {
    $details = [];
    foreach ($submitted as $key => $value) {
        if (preg_match('/^details\\[(\\d+)\\]\\[(\\w+)\\]$/', $key, $parts)) $details[$parts[1]][$parts[2]] = $value;
    }
    $test->post(route('bs-usage-checks.store', ['PD', $source->id]), [
        'source_token' => $formData['sourceToken'], 'review_token' => $formData['reviewToken'], '_workspace' => $workspace ? 1 : 0,
        'tanggal_pengecekan' => '2026-09-25', 'keputusan' => 'APPROVED',
        'save_action' => $submitted['save_action'], 'details' => $details,
    ])->assertSessionHasNoErrors();
    $saved = $submitted['save_action'] === 'draft'
        ? App\\Models\\BsUsageCheckDraft::firstOrFail()->details
        : App\\Models\\BsUsageCheck::firstOrFail()->details->map(fn ($detail) => $detail->toArray() + ['selisih' => $detail->selisih])->all();
    $form = $test->get($url)->assertOk();
}
echo json_encode(['html' => $form->getContent(), 'saved' => $saved], JSON_THROW_ON_ERROR);
` });
    assert.equal(result.status, 0, result.stderr || result.stdout);
    const fixture = JSON.parse(result.stdout);
    fixture.html = fixture.html.replace(/<link[^>]*>/g, '').replace(/<script\b[^>]*\bsrc=[^>]*><\/script>/g, '')
        // Notifications are outside this isolated form fixture; retain the form's inline scripts.
        .replace(/<script>([\s\S]*?)<\/script>/g, (script, body) => body.includes("getElementById('notifBell')") ? '' : script)
        .replace('</body>', `<script src="/checks.js" defer></script><script>
document.addEventListener('submit', event => {
    if (event.defaultPrevented) return;
    event.preventDefault();
    window.submitted = Object.fromEntries(new FormData(event.target));
});
</script></body>`);
    return fixture;
};

const pages = { '/new': render().html };
const server = createServer((req, res) => {
    if (req.url === '/checks.js') {
        res.writeHead(200, { 'Content-Type': 'text/javascript' });
        res.end(readFileSync('public/js/bs-usage-checks.js'));
    } else {
        res.writeHead(pages[req.url] ? 200 : 404, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(pages[req.url] || 'Not found');
    }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const base = 'http://127.0.0.1:' + server.address().port;
const profile = mkdtempSync(path.join(tmpdir(), 'bs-check-amount-test-'));
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
        pending.set(id, { resolve: value => { clearTimeout(timer); resolve(value); }, reject: error => { clearTimeout(timer); reject(error); } });
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
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
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
    const fill = (index, value) => js(`(() => {
        const input = document.querySelectorAll('[data-approved]')[${index}];
        input.focus(); input.value = ${JSON.stringify(value)}; input.dispatchEvent(new Event('input', {bubbles:true}));
    })()`);
    const submit = action => js(`document.querySelector('#bs-check-form').requestSubmit(document.querySelector('button[value="${action}"]'))`);
    const total = "document.getElementById('check-total-approved').textContent";
    const difference = "document.getElementById('check-total-difference').textContent";
    for (const width of [390, 1280]) {
        await call('Emulation.setDeviceMetricsOverride', { width, height: 844, deviceScaleFactor: 1, mobile: width < 600 });
        await navigate('/new');
        await check('initial totals use rupiah', `${total} === '200.000,00' && ${difference} === '0,00'`);
        await fill(1, '70.000');
        await check('typed amounts update all totals and the row', `${total} === '170.000,00' && ${difference} === '30.000,00' && document.querySelectorAll('[data-difference]')[1].textContent === '30.000,00' && document.getElementById('check-live-approved').textContent === 'Rp 170.000,00'`);
        await js("document.querySelectorAll('textarea[name$=\"[catatan]\"]')[1].value = 'Oli disetujui sebagian'");
        await submit('draft');
        const submitted = await js('window.submitted');
        assert.equal(submitted['details[2][nominal_disetujui]'], '70000.00');
        assert.equal(submitted.save_action, 'draft');
        const draft = render(submitted);
        assert.equal(draft.saved[1].nominal_disetujui, '70000.00');
        pages['/saved-draft'] = draft.html;
        await navigate('/saved-draft');
        await check('reopened draft restores values and notes', `document.querySelectorAll('[data-approved]')[1].value === '70.000' && ${total} === '170.000,00' && document.querySelectorAll('textarea[name$="[catatan]"]')[1].value === 'Oli disetujui sebagian'`);
        await submit('confirm');
        const confirmed = await js('window.submitted');
        assert.equal(confirmed.save_action, 'confirm');
        const result = render(confirmed);
        assert.equal(result.saved[1].nominal_disetujui, '70000.00');
        assert.equal(result.saved[1].selisih, '30000.00');
        pages['/confirmed'] = result.html;
        await navigate('/confirmed');
        await check('confirmed form retains the amounts and totals', `document.querySelectorAll('[data-approved]')[1].value === '70.000' && ${total} === '170.000,00' && ${difference} === '30.000,00'`);

        await navigate('/new');
        await fill(1, '70.000,50');
        await check('cents are exact', `${total} === '170.000,50' && ${difference} === '29.999,50'`);
        await submit('draft');
        assert.equal(render(await js('window.submitted')).saved[1].nominal_disetujui, '70000.50');
        await navigate('/new');
        await fill(1, '0');
        await js('document.activeElement.blur()');
        await check('zero stays visible and contributes zero', `document.querySelectorAll('[data-approved]')[1].value === '0' && ${total} === '100.000,00' && ${difference} === '100.000,00'`);
        await submit('confirm');
        assert.equal(render(await js('window.submitted')).saved[1].nominal_disetujui, '0.00');

        await navigate('/new');
        await fill(1, '');
        await submit('confirm');
        await check('blank amount blocks confirmation', "!window.submitted && document.querySelector('#bs-check-form').dataset.submitting !== 'true'");
        await submit('draft');
        const incomplete = render(await js('window.submitted'));
        assert.equal(incomplete.saved[1].nominal_disetujui, null);
        pages['/incomplete'] = incomplete.html;
        await navigate('/incomplete');
        await check('blank draft stays blank', `document.querySelectorAll('[data-approved]')[1].value === '' && ${total} === '—'`);
        for (const value of ['1.23', '-1', '70.000,123', '10000000000000', 'abc']) {
            await fill(1, value);
            await submit('draft');
            await check('invalid amount is not silently changed: ' + value, `!window.submitted && ${total} === '—'`);
        }
        await fill(1, '9.999.999.999.999,99');
        await check('maximum supported amount remains exact', `${total} === '10.000.000.099.999,99'`);
        await navigate('/new');
        await js("document.querySelectorAll('[data-approved]')[1].value = '70.000'");
        await submit('draft');
        await check('submit synchronizes even without an input event', "window.submitted['details[2][nominal_disetujui]'] === '70000.00'");
    }
    assert.deepEqual(errors, []);
    console.log(`PASS: ${checks} browser checks (${workspaceMode ? 'Detail Transaksi' : 'legacy form'}) at 390 and 1280 px; draft/confirmation amounts persisted in SQLite; no JavaScript errors.`);
} finally {
    if (call) await call('Browser.close').catch(() => {});
    if (socket) socket.close();
    browser.kill();
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
}
