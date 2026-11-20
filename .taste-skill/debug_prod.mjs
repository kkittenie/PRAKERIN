import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function main() {
  const profileDir = path.join('/tmp', 'chrome-debug-' + Date.now());
  fs.mkdirSync(profileDir, { recursive: true });
  const portFile = path.join(profileDir, 'DevToolsActivePort');

  const child = spawn(chromePath, [
    '--headless=new',
    '--user-data-dir=' + profileDir,
    '--remote-debugging-port=0',
    'about:blank'
  ], { stdio: 'ignore' });

  let port = null;
  for (let i = 0; i < 150 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }

  const ver = await (await fetch('http://127.0.0.1:' + port + '/json/version')).json();
  const ws = new WebSocket(ver.webSocketDebuggerUrl);
  await new Promise((r, j) => { ws.addEventListener('open', r, { once: true }); ws.addEventListener('error', j, { once: true }); });

  let id = 0;
  const pend = new Map();
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.method === 'Runtime.consoleAPICalled') {
      console.log('CONSOLE:', m.params.type, m.params.args.map(a => a.value || a.description));
    }
    if (m.method === 'Runtime.exceptionThrown') {
      console.log('EXCEPTION:', JSON.stringify(m.params.exceptionDetails));
    }
    if (m.id && pend.has(m.id)) { pend.get(m.id)(m); pend.delete(m.id); }
  });
  const send = (method, params = {}, sessionId) => new Promise(r => {
    const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId }));
  });

  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);

  await S('Page.enable');
  await S('Runtime.enable');
  await S('Network.enable');

  // Login
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await new Promise(r => setTimeout(r, 1200));

  await S('Runtime.evaluate', { expression: '(() => { const i = document.querySelector("input[name=identifier]"); const p = document.querySelector("input[name=password]"); if (i) i.value = "sofi"; if (p) p.value = "password123"; document.querySelector("form")?.submit(); })()' });
  await new Promise(r => setTimeout(r, 2000));

  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/produk/7' });
  await new Promise(r => setTimeout(r, 2500));

  const debugInfo = await S('Runtime.evaluate', { expression: `(() => {
    const root = document.querySelector('[x-data]');
    return {
      title: document.title,
      prodPhotoAttr: root?.getAttribute('data-foto'),
      buttonHTML: document.querySelector('.hidden.lg\\\\:block form button[type=submit]')?.outerHTML,
      buttonClasses: document.querySelector('.hidden.lg\\\\:block form button[type=submit]')?.className,
      alpineData: {
        hargaSatuan: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.hargaSatuan : 'no alpine',
        stokTersedia: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.stokTersedia : 'no alpine',
        isHabis: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.isHabis : 'no alpine',
        tidakBisaPesan: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.tidakBisaPesan : 'no alpine',
        labelTidakBisa: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.labelTidakBisa : 'no alpine',
        jumlah: typeof Alpine !== 'undefined' ? Alpine.$data(root)?.jumlah : 'no alpine',
      }
    };
  })()`, returnByValue: true });

  console.log('DEBUG INFO:', JSON.stringify(debugInfo?.result?.value, null, 2));

  ws.close();
  try { child.kill(); } catch {}
  try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch {}
}
main().catch(console.error);
