import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/activity';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/activity';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', 'chrome-activity-' + Date.now());
  fs.mkdirSync(profileDir, { recursive: true });
  const portFile = path.join(profileDir, 'DevToolsActivePort');

  const child = spawn(chromePath, [
    '--headless=new',
    '--user-data-dir=' + profileDir,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    '--disable-gpu',
    '--use-mock-keychain',
    '--password-store=basic',
    '--window-size=1280,950',
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

  const ver = await (await fetch('http://127.0.0.1:' + port + '/json/version')).json();
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
  const S = (method, params = {}) => send(method, params, sessionId);

  await S('Page.enable');
  await S('DOM.enable');
  await S('Runtime.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 950, deviceScaleFactor: 1, mobile: false });

  return { child, S };
}

async function captureShot(session, outPath, fullPage = false) {
  const dir = path.dirname(outPath);
  fs.mkdirSync(dir, { recursive: true });
  const artDir = path.join('/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818', dir);
  fs.mkdirSync(artDir, { recursive: true });
  const artPath = path.join(artDir, path.basename(outPath));

  const params = { format: 'png' };
  if (fullPage) {
    params.captureBeyondViewport = true;
  }
  const { result: { data } } = await session.S('Page.captureScreenshot', params);
  const buf = Buffer.from(data, 'base64');
  fs.writeFileSync(outPath, buf);
  fs.writeFileSync(artPath, buf);
  console.log('Saved:', outPath, 'and', artPath);
}

async function main() {
  const session = await launchChrome();
  try {
    console.log('Logging in as sofi...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await new Promise(r => setTimeout(r, 1500));

    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const u = document.querySelector('input[name="identifier"]');
        const p = document.querySelector('input[name="password"]');
        const f = document.querySelector('form');
        if (u) u.value = 'sofi';
        if (p) p.value = 'password123';
        if (f) {
          f.action = '/login';
          f.submit();
        }
      })()`
    });
    await new Promise(r => setTimeout(r, 2500));

    // 1. Activity / Riwayat (Desktop 1280px)
    console.log('Navigating to activity / riwayat...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/riwayat' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/activity/01-activity-desktop-1280.png');
    await captureShot(session, 'shots/activity/01-activity-fullpage-1280.png', true);

    // Switch to stamp tab
    console.log('Switching to STAMP tab...');
    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const btns = Array.from(document.querySelectorAll('#activity-scallop-tabs button'));
        const stampBtn = btns.find(b => b.textContent.includes('STAMP'));
        if (stampBtn) stampBtn.click();
      })()`
    });
    await new Promise(r => setTimeout(r, 800));
    await captureShot(session, 'shots/activity/02-activity-stamp-tab-1280.png', true);

    // 2. Pesanan Status (Desktop 1280px)
    console.log('Navigating to pesanan / status...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/pesanan/2' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/pesanan/10-pesanan-desktop-after-1280.png');
    await captureShot(session, 'shots/pesanan/10-pesanan-desktop-fullpage-after-1280.png', true);

    // 3. Product Detail (Desktop 1280px)
    console.log('Navigating to product detail 7...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/produk/7' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/product/05-product-detail-desktop-1280.png');

    // Trigger Add to Cart sheet
    console.log('Clicking ADD TO CART...');
    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const btn = document.querySelector('button[type="submit"]');
        if (btn) btn.click();
      })()`
    });
    await new Promise(r => setTimeout(r, 1200));
    await captureShot(session, 'shots/product/06-product-cart-modal-1280.png');

  } finally {
    try { session.child.kill(); } catch {}
  }
}

main().catch(err => {
  console.error('Error in shoot_activity:', err);
  process.exit(1);
});
