import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-cake-verify-${Date.now()}`);
const outDir = path.join(process.cwd(), 'shots', 'v2_verify');
fs.mkdirSync(profileDir, { recursive: true });
fs.mkdirSync(outDir, { recursive: true });

const child = spawn(chromePath, [
  '--headless=new',
  `--user-data-dir=${profileDir}`,
  '--remote-debugging-port=0',
  '--no-first-run',
  '--no-default-browser-check',
  '--disable-extensions',
  '--window-size=1440,900',
  'about:blank'
], { stdio: 'ignore' });

const sleep = ms => new Promise(r => setTimeout(r, ms));

async function main() {
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  let port = null;
  for (let i = 0; i < 120 && !port; i++) {
    await sleep(100);
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }
  if (!port) throw new Error('Chrome did not start');

  const ver = await (await fetch(`http://127.0.0.1:${port}/json/version`)).json();
  const ws = new WebSocket(ver.webSocketDebuggerUrl);
  await new Promise(r => ws.addEventListener('open', r, { once: true }));

  let id = 0;
  const pend = new Map();
  const errs = [];
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.id && pend.has(m.id)) {
      pend.get(m.id)(m);
      pend.delete(m.id);
    } else if (m.method === 'Runtime.exceptionThrown') {
      const txt = m.params?.exceptionDetails?.exception?.description || m.params?.exceptionDetails?.text;
      errs.push(txt);
      console.error('[EXCEPTION]', txt);
    } else if (m.method === 'Runtime.consoleAPICalled' && m.params?.type === 'error') {
      const txt = (m.params.args || []).map(a => a.value || a.description).join(' ');
      errs.push(txt);
      console.error('[CONSOLE ERROR]', txt);
    }
  });
  const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
    const i = ++id;
    const timeout = setTimeout(() => { pend.delete(i); reject(new Error(`Timeout on ${method}`)); }, 30000);
    pend.set(i, res => { clearTimeout(timeout); resolve(res); });
    ws.send(JSON.stringify({ id: i, method, params, sessionId }));
  });

  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);

  await S('Page.enable');
  await S('Runtime.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });

  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
  await sleep(3200);

  const rect = await S('Runtime.evaluate', {
    expression: `(() => {
      const c = document.getElementById('desktop-hero-3d-canvas');
      if (!c) return null;
      const r = c.getBoundingClientRect();
      return { x: r.x, y: r.y, width: r.width, height: r.height };
    })()`,
    returnByValue: true
  }).then(r => r.result.value);

  console.log('Canvas rect:', rect);

  const shotFull = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(outDir, '06-3d-cake-hero-desktop.png'), Buffer.from(shotFull.data, 'base64'));
  console.log('Saved 06-3d-cake-hero-desktop.png');

  if (rect && rect.width > 100) {
    const shotClose = await S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: rect.x, y: rect.y, width: rect.width, height: rect.height, scale: 2 }
    });
    fs.writeFileSync(path.join(outDir, '07-3d-cake-closeup.png'), Buffer.from(shotClose.data, 'base64'));
    console.log('Saved 07-3d-cake-closeup.png');

    // Drag-spin slightly to inspect the bow & top from a second angle!
    await S('Input.dispatchMouseEvent', { type: 'mousePressed', x: rect.x + rect.width / 2, y: rect.y + rect.height / 2, button: 'left', clickCount: 1 });
    await S('Input.dispatchMouseEvent', { type: 'mouseMoved', x: rect.x + rect.width / 2 + 95, y: rect.y + rect.height / 2 + 20, buttons: 1 });
    await S('Input.dispatchMouseEvent', { type: 'mouseReleased', x: rect.x + rect.width / 2 + 95, y: rect.y + rect.height / 2 + 20, button: 'left', clickCount: 1 });
    await sleep(500);
    const shotAngle2 = await S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: rect.x, y: rect.y, width: rect.width, height: rect.height, scale: 2 }
    });
    fs.writeFileSync(path.join(outDir, '07b-3d-cake-angle2.png'), Buffer.from(shotAngle2.data, 'base64'));
    console.log('Saved 07b-3d-cake-angle2.png');
  }

  console.log('Errors:', errs.length);
  ws.close();
  try { child.kill(); } catch {}
  try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch {}
}

main().catch(e => { console.error(e); try { child.kill(); } catch {} process.exit(1); });
