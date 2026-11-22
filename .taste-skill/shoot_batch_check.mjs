import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-batch-check-${Date.now()}`);
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
  '--window-size=1280,850',
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

  const send = (method, params = {}, sessionId) => new Promise((resolve) => {
    const i = ++id;
    pend.set(i, res => resolve(res));
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

  // 1. Verify Member Navbar has "Rewards" instead of "Vouchers"
  console.log('1. Checking Member Navbar (Desktop)...');
  await S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 850, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
  await sleep(1800);
  await evalVal(`(() => { const w = document.getElementById("daisy-wave-loader"); if (w) w.style.display = "none"; })()`);
  await sleep(300);
  await snap('verify-navbar-rewards-desktop.png');

  // 2. Verify Menu page store card (Screenshot 2: logo replaced by text)
  console.log('2. Checking Menu store card on Mobile (390x844)...');
  await S('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/menu' });
  await sleep(1800);
  await evalVal(`(() => { const w = document.getElementById("daisy-wave-loader"); if (w) w.style.display = "none"; })()`);
  await sleep(300);
  await snap('verify-menu-store-card-mobile.png');

  // 3. Verify Akun page Social Media section (Screenshot 1: logo enlarged & readable)
  console.log('3. Checking Akun Social Media on Mobile...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/akun' });
  await sleep(1800);
  await evalVal(`(() => {
    const w = document.getElementById("daisy-wave-loader");
    if (w) w.style.display = "none";
    const sm = Array.from(document.querySelectorAll('h3')).find(h => h.textContent.includes('Social Media'));
    if (sm) sm.scrollIntoView({ block: 'center' });
  })()`);
  await sleep(400);
  await snap('verify-akun-social-media.png');

  // 4. Verify Admin panel does NOT have wave-loader
  console.log('4. Checking Admin panel (no loading animation)...');
  await S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 850, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/admin/login' });
  await sleep(1200);
  const adminHasWaveLoader = await evalVal(`!!document.getElementById("daisy-wave-loader")`);
  console.log('Admin login has wave-loader:', adminHasWaveLoader);

  ws.close();
  child.kill();
  console.log('ALL VERIFICATIONS COMPLETED!');
}

main().catch(e => {
  console.error(e);
  child.kill();
  process.exit(1);
});
