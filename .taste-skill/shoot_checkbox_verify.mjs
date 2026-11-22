import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-checkbox-verify-${Date.now()}`);
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
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.id && pend.has(m.id)) {
      pend.get(m.id)(m);
      pend.delete(m.id);
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

  const evalVal = async expr => {
    const res = await S('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true });
    return res?.result?.value;
  };

  const snap = async filename => {
    const shot = await S('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(outDir, filename), Buffer.from(shot.data, 'base64'));
    console.log('Saved', filename);
  };

  // 1. Desktop Login (1440x900)
  console.log('Testing Desktop /login...');
  await S('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await sleep(1800);

  // Close helper modal if visible
  await evalVal(`(() => {
    const m = document.getElementById('modal-info-login');
    if (m) m.style.display = 'none';
  })()`);
  await sleep(300);

  // Checkbox unchecked
  await snap('final-login-checkbox-unchecked.png');

  // Check the checkbox
  console.log('Clicking Remember Me checkbox...');
  await evalVal(`(() => {
    const cb = document.getElementById('ingat_saya');
    if (cb) {
      cb.click();
    }
  })()`);
  await sleep(400);

  // Checkbox checked
  await snap('final-login-checkbox-checked.png');

  // 2. Mobile Login (390x844)
  console.log('Testing Mobile /login (390x844)...');
  await S('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await sleep(1800);

  await evalVal(`(() => {
    const m = document.getElementById('modal-info-login');
    if (m) m.style.display = 'none';
    const cb = document.getElementById('ingat_saya');
    if (cb) cb.click();
  })()`);
  await sleep(400);
  await snap('final-login-mobile-checkbox-checked.png');

  ws.close();
  child.kill();
  console.log('ALL CHECKBOX SCREENSHOTS CAPTURED!');
}

main().catch(e => {
  console.error(e);
  child.kill();
  process.exit(1);
});
