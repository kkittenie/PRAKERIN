import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/product';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/product';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', 'chrome-alert-shoot-' + Date.now());
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
    '--window-size=1280,900',
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
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);
  const ev = x => S('Runtime.evaluate', { expression: x, awaitPromise: true, returnByValue: true }).then(r => r?.result?.value);

  await S('Page.enable');
  await S('Runtime.enable');
  await S('Network.enable');

  return { child, ws, S, ev, profileDir };
}

async function captureViewport(session, filename, width = 1280, height = 900) {
  const { S } = session;
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height,
    deviceScaleFactor: 1,
    mobile: false
  });

  const shot = await S('Page.captureScreenshot', {
    format: 'png',
    clip: { x: 0, y: 0, width, height, scale: 1 }
  });

  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync(path.join(outDir, filename), buf);
  fs.writeFileSync(path.join(artifactDir, filename), buf);
  console.log(`Saved ${filename} (${width}x${height})`);
}

async function main() {
  console.log('=== Capturing Product Detail Alert/Modal ===');
  const session = await launchChrome();
  try {
    // 1. Login
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await new Promise(r => setTimeout(r, 1200));

    await session.ev(`(() => {
      const idInput = document.querySelector('input[name="identifier"]');
      const passInput = document.querySelector('input[name="password"]');
      const form = document.querySelector('form');
      if (idInput) idInput.value = 'sofi';
      if (passInput) passInput.value = 'password123';
      if (form) {
        form.action = '/login';
        form.submit();
      }
    })()`);
    await new Promise(r => setTimeout(r, 2000));

    // 2. Navigate directly to Cinnamon Roll (id 7)
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/produk/7' });
    await new Promise(r => setTimeout(r, 2500));

    // Check button text
    const info = await session.ev(`(() => {
      const btns = Array.from(document.querySelectorAll('button[type="submit"]'));
      const visibleBtn = btns.find(b => b.offsetParent !== null);
      return {
        totalButtons: btns.length,
        visibleText: visibleBtn ? visibleBtn.innerText.trim() : null,
      };
    })()`);
    console.log('Button search info:', JSON.stringify(info));

    // Save page before click
    await captureViewport(session, '01-product-detail-add-to-cart-1280.png', 1280, 900);

    // 3. Click Add to Cart
    console.log('Clicking visible submit button...');
    const clickRes = await session.ev(`(() => {
      const btns = Array.from(document.querySelectorAll('button[type="submit"]'));
      const visibleBtn = btns.find(b => b.offsetParent !== null);
      if (visibleBtn) {
        visibleBtn.click();
        return 'CLICKED: ' + visibleBtn.innerText.trim();
      }
      return 'NO_VISIBLE_BTN';
    })()`);
    console.log('Click result:', clickRes);

    // Wait for network response and modal animation
    await new Promise(r => setTimeout(r, 2000));

    // Check if dialog is open
    const dialogOpen = await session.ev(`(() => {
      const dlg = document.getElementById('sheet-tambah-keranjang');
      return dlg ? { open: dlg.open, dataOpen: dlg.getAttribute('data-open'), innerText: dlg.innerText } : null;
    })()`);
    console.log('Dialog status:', dialogOpen);

    // Capture modal screenshot
    await captureViewport(session, '02-product-cart-alert-modal-1280.png', 1280, 900);

  } finally {
    session.ws.close();
    try { session.child.kill(); } catch {}
    try { fs.rmSync(session.profileDir, { recursive: true, force: true }); } catch {}
  }
}

main().catch(err => {
  console.error('Error:', err);
  process.exit(1);
});
