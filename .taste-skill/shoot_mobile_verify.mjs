import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/mobile_theme';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/mobile_theme';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome(width = 390, height = 844) {
  const profileDir = path.join('/tmp', `chrome-mobile-shoot-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`);
  fs.mkdirSync(profileDir, { recursive: true });

  const portFile = path.join(profileDir, 'DevToolsActivePort');

  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileDir}`,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    `--window-size=${width},${height}`,
    'about:blank'
  ], { stdio: 'ignore' });

  let port = null;
  for (let i = 0; i < 150 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }
  if (!port) {
    try { child.kill(); } catch {}
    throw new Error('Chrome did not start');
  }

  const ver = await (await fetch(`http://127.0.0.1:${port}/json/version`)).json();
  const ws = new WebSocket(ver.webSocketDebuggerUrl);
  await new Promise((r, j) => { ws.addEventListener('open', r, { once: true }); ws.addEventListener('error', j, { once: true }); });

  let id = 0;
  const pend = new Map();
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.id && pend.has(m.id)) { pend.get(m.id)(m); pend.delete(m.id); }
  });
  const send = (method, params = {}, sessionId) => new Promise(r => {
    const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId }));
  });

  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);
  const ev = x => S('Runtime.evaluate', { expression: x, awaitPromise: true, returnByValue: true }).then(r => r?.result?.value);

  await S('Page.enable');
  await S('Runtime.enable');
  await S('Network.enable');

  return { child, ws, S, ev, profileDir };
}

async function captureMobile(session, url, filename, scrollY = 0) {
  console.log(`Navigating to ${url}...`);
  await session.S('Emulation.setDeviceMetricsOverride', {
    width: 390,
    height: 844,
    deviceScaleFactor: 2,
    mobile: true
  });
  await session.S('Page.navigate', { url });
  await new Promise(r => setTimeout(r, 1800));

  if (scrollY > 0) {
    await session.ev(`window.scrollTo(0, ${scrollY})`);
    await new Promise(r => setTimeout(r, 500));
  }

  const shot = await session.S('Page.captureScreenshot', { format: 'png' });
  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync(path.join(outDir, filename), buf);
  fs.writeFileSync(path.join(artifactDir, filename), buf);
  console.log(`Saved ${filename}`);
}

async function main() {
  const session = await launchChrome(390, 844);
  try {
    // 1. Mobile Beranda - Scrolled to Spesial Katalog Reward (Voucher Cards)
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/beranda', '01-mobile-vouchers.png', 780);

    // 2. Mobile Beranda Top
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/beranda', '01-mobile-beranda.png', 0);

    // 3. Mobile Menu
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/menu', '02-mobile-menu.png', 0);

    // 4. Mobile Akun
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/akun', '03-mobile-akun.png', 0);

    // 5. Mobile Akun Scrolled (Social & CS)
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/akun', '03-mobile-akun-cs.png', 300);

    // 6. Mobile Riwayat
    await captureMobile(session, 'http://127.0.0.1:8000/mobile/riwayat', '06-mobile-riwayat.png', 0);

    console.log('All screenshots captured successfully!');
  } finally {
    session.ws.close();
    try { session.child.kill(); } catch {}
    try { fs.rmSync(session.profileDir, { recursive: true, force: true }); } catch {}
  }
}

main().catch(console.error);
