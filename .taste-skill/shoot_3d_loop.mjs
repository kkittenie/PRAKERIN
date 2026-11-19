import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/phase3';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/phase3';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome(profileDir) {
  fs.mkdirSync(profileDir, { recursive: true });
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  try { fs.rmSync(portFile); } catch {}

  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileDir}`,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    '--window-size=1280,900',
    'about:blank'
  ], { stdio: 'ignore' });

  let port = null;
  for (let i = 0; i < 150 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }
  if (!port) throw new Error('Chrome did not start');

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

  return { child, ws, S, ev };
}

async function captureHero(S, ev, width, outName) {
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: 900,
    deviceScaleFactor: 1,
    mobile: false
  });
  await new Promise(r => setTimeout(r, 1200)); // wait for 3D render frame

  // Capture hero crop or full page
  const fullHeight = await ev('Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)');
  const { data: fullData } = await S('Page.captureScreenshot', {
    format: 'png',
    clip: { x: 0, y: 0, width, height: Math.min(fullHeight, 4000), scale: 1 }
  });
  const savePath = path.join(outDir, `${outName}.png`);
  fs.writeFileSync(savePath, Buffer.from(fullData, 'base64'));
  fs.copyFileSync(savePath, path.join(artifactDir, `${outName}.png`));
  console.log(`Saved screenshot: ${savePath}`);

  // Also capture close-up of Hero
  const heroRect = await ev(`(() => {
    const el = document.getElementById('desktop-hero-3d');
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height };
  })()`);

  if (heroRect && heroRect.width > 0) {
    const { data: cropData } = await S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: Math.max(0, heroRect.x - 20), y: Math.max(0, heroRect.y - 20), width: heroRect.width + 40, height: heroRect.height + 40, scale: 1 }
    });
    const cropPath = path.join(outDir, `${outName}-closeup.png`);
    fs.writeFileSync(cropPath, Buffer.from(cropData, 'base64'));
    fs.copyFileSync(cropPath, path.join(artifactDir, `${outName}-closeup.png`));
    console.log(`Saved closeup: ${cropPath}`);
  }
}

async function run() {
  console.log('=== CAPTURING GUEST WITH 3D DESSERT ===');
  const guest = await launchChrome(`/tmp/chrome-guest-3d-${Date.now()}`);
  await guest.S('Page.navigate', { url: 'http://localhost:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 2000));

  await captureHero(guest.S, guest.ev, 1280, 'beranda-guest-1280-3d');
  await captureHero(guest.S, guest.ev, 1440, 'beranda-guest-1440-3d');
  await captureHero(guest.S, guest.ev, 1920, 'beranda-guest-1920-3d');

  try { guest.child.kill(); } catch {}

  console.log('=== CAPTURING MEMBER WITH 3D DESSERT ===');
  const member = await launchChrome(`/tmp/chrome-member-3d-${Date.now()}`);
  await member.S('Page.navigate', { url: 'http://localhost:8000/login' });
  await new Promise(r => setTimeout(r, 2000));

  await member.ev(`(() => {
    const idInput = document.querySelector('input[name="identifier"]');
    const passInput = document.querySelector('input[name="password"]');
    const form = document.querySelector('form');
    if (!idInput || !passInput || !form) return 'not found';
    idInput.value = 'sofi';
    passInput.value = 'password123';
    form.action = '/login';
    form.submit();
    return 'submitted';
  })()`);
  await new Promise(r => setTimeout(r, 3000));

  await member.S('Page.navigate', { url: 'http://localhost:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 2500));

  await captureHero(member.S, member.ev, 1280, 'beranda-member-1280-3d');
  await captureHero(member.S, member.ev, 1440, 'beranda-member-1440-3d');
  await captureHero(member.S, member.ev, 1920, 'beranda-member-1920-3d');

  try { member.child.kill(); } catch {}

  console.log('Done capturing all 3D screenshots!');
}

run().catch(console.error);
