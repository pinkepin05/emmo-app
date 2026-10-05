import { spawn } from 'node:child_process';
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import path from 'node:path';

const directory = path.resolve('storage/app/private/transaction-recap-verification');
const output = path.join(directory, 'browser-' + Date.now());
mkdirSync(output, { recursive: true });
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--remote-debugging-port=9284', '--no-first-run', '--no-default-browser-check',
    '--user-data-dir=' + path.join(output, 'profile'), 'about:blank',
], { windowsHide: true, stdio: 'ignore' });
let socket;
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
try {
    for (let i = 0; i < 60; i++) {
        try { await fetch('http://127.0.0.1:9284/json/version'); break; } catch { await sleep(100); }
    }
    const target = await (await fetch('http://127.0.0.1:9284/json/new?about:blank', { method: 'PUT' })).json();
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
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.text);
        return result.result.value;
    };
    await call('Page.enable'); await call('Runtime.enable');
    const results = [];
    for (const mode of ['pengajuan', 'finance']) {
        let html = readFileSync(path.join(directory, mode + '.html'), 'utf8').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '');
        html = html.replace(/<link\b[^>]*href="([^"]+)"[^>]*>/gi, (tag, href) => {
            if (href === 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css') {
                return '<style>' + readFileSync(path.join(directory, 'bootstrap.min.css'), 'utf8') + '</style>';
            }
            const local = path.resolve('public', new URL(href, 'http://localhost').pathname.replace(/^\/+/, ''));
            return local.startsWith(path.resolve('public') + path.sep) && local.endsWith('.css') && existsSync(local)
                ? '<style>' + readFileSync(local, 'utf8') + '</style>' : tag;
        });
        const { frameTree } = await call('Page.getFrameTree');
        await call('Page.setDocumentContent', { frameId: frameTree.frame.id, html });
        await js(`new Promise(resolve => {
            if (document.readyState === 'complete') return resolve();
            window.addEventListener('load', resolve, { once: true });
            setTimeout(resolve, 6000);
        })`);
        await js('Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 3000))])');
        for (const width of [1366, 768, 390, 320]) {
            await call('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
            await js(`new Promise(resolve => requestAnimationFrame(() => { document.getElementById('pemakaian-bs').scrollIntoView(); requestAnimationFrame(resolve); }))`);
            const result = await js(`(() => {
                const panel = document.querySelector('.usage-panel');
                const modules = [...panel.querySelectorAll('.usage-module')];
                const fits = modules.every(el => el.scrollWidth <= el.clientWidth + 1)
                    && [...panel.querySelectorAll('.usage-metric')].every(el => el.scrollWidth <= el.clientWidth + 1);
                return { width: innerWidth, modules: modules.length, fits, pageFits: document.documentElement.scrollWidth <= innerWidth + 1 };
            })()`);
            const shot = await call('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
            writeFileSync(path.join(output, mode + '-' + width + '.png'), Buffer.from(shot.data, 'base64'));
            if (!result.fits || !result.pageFits || result.modules !== 4) {
                console.log(await js(`JSON.stringify([...document.querySelectorAll('body *')].filter(el => el.getBoundingClientRect().right > innerWidth + 1 && !el.closest('.table-responsive')).slice(0,12).map(el => ({ tag: el.tagName, class: el.className, right: el.getBoundingClientRect().right })))`));
                throw new Error(JSON.stringify({ mode, ...result, output }));
            }
            results.push({ mode, ...result });
            console.log('PASS ' + mode + ' at ' + width + 'px');
        }
    }
    writeFileSync(path.join(output, 'result.json'), JSON.stringify(results, null, 2));
    console.log('Screenshots: ' + output);
} finally { socket?.close(); chrome.kill(); }
