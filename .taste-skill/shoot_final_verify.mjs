import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-welcome-verify-${Date.now()}`);
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

  // 1. Welcome Desktop (1440x900)
  console.log('1. Capturing Welcome Desktop...');
  await S('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/welcome' });
  await sleep(2200);
  const welcomeDesktopShot = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(outDir, 'final-welcome-desktop.png'), Buffer.from(welcomeDesktopShot.data, 'base64'));

  // 2. Welcome Mobile (430x932)
  console.log('2. Capturing Welcome Mobile...');
  await S('Emulation.setDeviceMetricsOverride', { width: 430, height: 932, deviceScaleFactor: 2, mobile: true });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/welcome' });
  await sleep(2000);
  const welcomeMobileShot = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(outDir, 'final-welcome-mobile.png'), Buffer.from(welcomeMobileShot.data, 'base64'));

  ws.close();
  child.kill();
  console.log('ALL WELCOME SCREENSHOTS CAPTURED SUCCESSFULLY!');
}

main().catch(e => {
  console.error(e);
  child.kill();
  process.exit(1);
});
