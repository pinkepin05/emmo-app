import {spawn} from 'node:child_process';
import {readFileSync, writeFileSync, mkdirSync, openSync} from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const dir = path.join(root, 'storage/app/private/bs-readiness');
const fixture = JSON.parse(readFileSync(path.join(dir, 'browser-fixture.json')));
const base = 'http://127.0.0.1:8099';
const run = path.join(dir, 'browser-'+Date.now());
mkdirSync(run);
const serverLog = openSync(path.join(run, 'server.log'), 'w');
const server = spawn('C:/php82/php.exe', ['-S','127.0.0.1:8099','-t','public','scripts/bs-uat-router.php'], {cwd:root, windowsHide:true, stdio:['ignore',serverLog,serverLog]});
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new','--remote-debugging-port=9277','--user-data-dir='+path.join(run,'profile'),'--no-first-run','--no-default-browser-check','about:blank'], {windowsHide:true, stdio:'ignore'});
const sleep = ms => new Promise(r=>setTimeout(r,ms));
let socket;
const results = []; const errors = []; let lastPage = '';
try {
    for(let i=0;i<100;i++) { try { await fetch(base+'/login'); await fetch('http://127.0.0.1:9277/json/version'); break; } catch { await sleep(150); } }
    const target = await (await fetch('http://127.0.0.1:9277/json/new?about:blank',{method:'PUT'})).json();
    socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve,reject)=>{socket.addEventListener('open',resolve,{once:true});socket.addEventListener('error',reject,{once:true});});
    const pending = new Map(); let nextId=0;
    const call=(method,params={})=>new Promise((resolve,reject)=>{
        const id=++nextId;
        const timeout=setTimeout(()=>{pending.delete(id); reject(new Error('CDP timeout: '+method));},25000);
        pending.set(id,{resolve:v=>{clearTimeout(timeout);resolve(v);},reject:e=>{clearTimeout(timeout);reject(e);}});
        socket.send(JSON.stringify({id,method,params}));
    });
    socket.addEventListener('message',event=>{
        const msg=JSON.parse(event.data);
        if(msg.id) { const p=pending.get(msg.id); if(!p)return; pending.delete(msg.id); msg.error?p.reject(new Error(msg.error.message)):p.resolve(msg.result); }
        if(msg.method==='Runtime.exceptionThrown') errors.push({page:lastPage, text:msg.params.exceptionDetails.exception?.description ?? msg.params.exceptionDetails.text});
        if(msg.method==='Page.javascriptDialogOpening') call('Page.handleJavaScriptDialog',{accept:true}).catch(()=>{});
    });
    await call('Page.enable'); await call('Runtime.enable');
    const js=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.exception?.description??r.exceptionDetails.text);return r.result.value;};
    const wait=async(condition, label='condition')=>{for(let i=0;i<100;i++){try{if(await js(condition))return;}catch{}await sleep(150);}throw new Error('Timed out: '+label+' at '+await js('location.pathname'));};
    const goto=async url=>{lastPage=url;await call('Page.navigate',{url:base+url});await wait(`location.pathname === ${JSON.stringify(url.split('?')[0])} && document.readyState === 'complete'`,'navigate '+url);await sleep(200);};
    const fill=async(selector,value)=>js(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});if(!e)throw Error('Missing input '+${JSON.stringify(selector)});e.value=${JSON.stringify(String(value))};e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('change',{bubbles:true}));return e.value;})()`);
    const click=async selector=>js(`(()=>{const e=document.querySelector(${JSON.stringify(selector)});if(!e||e.disabled)throw Error('Missing/disabled button '+${JSON.stringify(selector)});e.click();})()`);
    const submit=async(selector,destination)=>{await js(`document.querySelector(${JSON.stringify(selector)}).requestSubmit()`);await wait(`location.pathname === ${JSON.stringify(destination)} && document.readyState==='complete'`,'submit '+selector);await sleep(200);};
    const shot=async name=>{const r=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});writeFileSync(path.join(run,name+'.png'),Buffer.from(r.data,'base64'));};
    const layout=async name=>{const r=await js(`({width:innerWidth,documentWidth:document.documentElement.scrollWidth,overflow:document.documentElement.scrollWidth>innerWidth+2,body:document.body.innerText.slice(0,300)})`);if(r.body.includes('Server Error'))throw Error('Server error on '+name);results.push({case:name,...r});await shot(name.replace(/[^a-z0-9-]/gi,'-'));return r;};
    const viewport=async(width,height,mobile)=>{await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile});await call('Emulation.setTouchEmulationEnabled',{enabled:mobile});};
    const login=async tag=>{
        await call('Network.clearBrowserCookies');await goto('/login');
        await fill('[name=username]',fixture.accounts[tag].username);await fill('[name=password]',fixture.accounts[tag].password);
        await submit('form','/dashboard');
    };
    await viewport(1366,900,false);
    await login('owner');
    const bsIds={};
    for(const [type,label] of [['operasional_sales','oas'],['perjalanan_dinas','travel']]) {
        await goto('/pengajuan-bs/create');
        await click(`[name=tipe_pengajuan][value=${type}]`);
        const nominalSelector=await js(`'#'+document.querySelector('[name=nominal_pengajuan]').previousElementSibling.id`);
        // The visible nominal field populates the hidden value through its real input handler.
        const visible=await js(`Array.from(document.querySelectorAll('input[type=text]')).find(e=>e.id.includes('nominal'))?.id`);
        if(!visible)throw Error('Nominal input not found');
        await fill('#'+visible,'1000000');
        await fill('[name=description]','UAT browser '+label);
        if(type==='perjalanan_dinas')await click(`[name="member_ids[]"][value="${fixture.accounts.member.id}"]`);
        await layout('desktop-pengajuan-'+label);
        await submit('#form-pengajuan','/pengajuan-bs');
        const ids=await js(`Array.from(document.querySelectorAll('a[href]')).map(e=>e.getAttribute('href')).filter(h=>/\/pengajuan-bs\/\d+$/.test(h)).map(h=>Number(h.split('/').pop()))`);
        if(!ids.length)throw Error('Submitted BS not visible');
        bsIds[label]=Math.max(...ids);
    }
    await login('finance');
    for(const [label,id] of Object.entries(bsIds)) {
        await goto('/approval-atasan');
        await click(`[data-action$="/approval-atasan/${id}/approve"]`);
        await wait(`document.querySelector('#decisionForm').action.endsWith('/${id}/approve')`,'approval modal');
        await submit('#decisionForm','/approval-atasan');
        await goto('/approval-finance');
        const form=`form[action$="/approval-finance/${id}/disburse"]`;
        await click(form+' .btn-full');
        await js(`document.querySelector(${JSON.stringify(form+' .btn-cairkan')}).click()`);
        await sleep(700);await wait(`document.readyState==='complete' && !document.querySelector(${JSON.stringify(form)})`,'disbursement '+label);
    }
    await viewport(390,844,true);
    await login('owner');
    await goto('/transaksi-operasional/create');
    await fill('[name=tanggal]',fixture.today);await fill('[name=description]','UAT OAS browser');await fill('[name=bs_id]',bsIds.oas);
    await fill('[name="details[0][cost_category_id]"]',fixture.category_id);await fill('[name="details[0][remark]"]','Biaya uji desktop dan HP');
    await fill('.nominal-display','600000');
    await layout('mobile-oas-draft');
    await submit('#form-operasional','/transaksi-operasional');
    const validate=await js(`Array.from(document.querySelectorAll('form')).find(e=>/\/transaksi-operasional\/\d+\/validate$/.test(e.action))?.getAttribute('action')`);
    if(!validate)throw Error('OAS draft validation action missing');
    await js(`document.querySelector('form[action="'+${JSON.stringify(validate)}+'"]').requestSubmit()`);
    await sleep(700);await wait(`document.readyState==='complete' && document.body.innerText.includes('Validated')`,'OAS final');
    await goto('/cash-flow/'+fixture.period);await layout('mobile-cash-after-oas');
    if(!await js(`document.body.innerText.includes('1.400.000') && document.body.innerText.includes('600.000')`))throw Error('Cash did not match two disbursements minus OAS');

    await login('member');
    await goto('/transaksi-perjalanan-dinas/create');
    await fill('[name=tanggal]',fixture.today);await fill('[name=description]','UAT PD anggota browser');await fill('[name=bs_id]',bsIds.travel);
    await fill('#teritory-select',fixture.city.territory);
    await wait(`Array.from(document.querySelector('#provinsi-select').options).some(o=>o.value===${JSON.stringify(String(fixture.city.province_id))})`,'provinces');
    await fill('#provinsi-select',fixture.city.province_id);
    await wait(`Array.from(document.querySelector('#kota-select').options).some(o=>o.value===${JSON.stringify(String(fixture.city.id))})`,'cities');
    await fill('#kota-select',fixture.city.id);await fill('#jarak_km','300');await fill('#durasi_jam','10');
    await fill('[name="details[0][cost_category_id]"]',fixture.category_id);await fill('[name="details[0][remark]"]','Biaya PD anggota');await sleep(500);
    await fill('.nominal-formatted','250000');await layout('mobile-travel-draft');
    const pdForm=await js(`'#'+document.querySelector('[name=bs_id]').form.id`);
    await submit(pdForm,'/transaksi-perjalanan-dinas');
    const travelPath=await js(`new URL(Array.from(document.querySelectorAll('a')).find(a=>/\/transaksi-perjalanan-dinas\/PD[A-Z0-9]+$/.test(a.href)).href).pathname`);
    await goto(travelPath);await layout('mobile-travel-before-final');
    await js(`document.querySelector('form[action$="/validate"]').requestSubmit()`);await sleep(700);
    await wait(`document.readyState==='complete' && document.body.innerText.includes('VALIDATED')`,'PD final');
    await layout('mobile-travel-final');
    await goto('/cash-flow/'+fixture.period);
    if(!await js(`!document.body.innerText.includes('250.000')`))throw Error('Member cash incorrectly charged');
    await login('owner');await goto('/cash-flow/'+fixture.period);await layout('mobile-owner-cash-final');
    if(!await js(`document.body.innerText.includes('1.150.000') && document.body.innerText.includes('850.000')`))throw Error('Owner cash did not include member expense');
    for(const width of [360,768,1366]) {
        await viewport(width,width===1366?900:844,width<768);
        for(const url of ['/pengajuan-bs','/cash-flow','/cash-flow/'+fixture.period,'/transaksi-operasional',travelPath]) {await goto(url);await layout('layout-'+width+'-'+url.replaceAll('/','-'));}
    }
    await login('finance');await viewport(390,844,true);
    for(const url of ['/approval-atasan','/approval-finance','/periode']) {await goto(url);await layout('mobile-'+url.slice(1));}
    writeFileSync(path.join(dir,'browser-report.json'),JSON.stringify({passed:true,fixtureDatabase:fixture.database,run,bsIds,results,errors},null,2));
    console.log(JSON.stringify({passed:true,pages:results.length,overflow:results.filter(r=>r.overflow).map(r=>r.case),scriptErrors:errors,artifacts:run}));
} catch(error) {
    writeFileSync(path.join(dir,'browser-report.json'),JSON.stringify({passed:false,error:String(error),lastPage,run,results,errors},null,2));
    console.error(String(error));process.exitCode=1;
} finally {
    socket?.close();chrome.kill();server.kill();
}
