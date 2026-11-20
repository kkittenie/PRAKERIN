import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/pesanan';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/pesanan';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', 'chrome-confirm-' + Date.now());
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

async function captureFullPage(session, filename, width = 1280) {
  const { S } = session;
  const { contentSize } = await S('Page.getLayoutMetrics');
  const height = Math.ceil(contentSize.height);

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
  console.log('=== Testing Receipt Confirmation Alert in English ===');
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

    // 2. Navigate to Order 4
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/pesanan/4' });
    await new Promise(r => setTimeout(r, 2000));

    // Create a 1x1 dummy PNG in /tmp
    const dummyPath = '/tmp/dummy-receipt.png';
    const pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    fs.writeFileSync(dummyPath, Buffer.from(pngBase64, 'base64'));

    // Upload receipt to form
    console.log('Setting file on file input...');
    const { root: { nodeId } } = await session.S('DOM.getDocument');
    const { nodeId: inputNodeId } = await session.S('DOM.querySelector', {
      nodeId,
      selector: '#bukti_pembayaran_desktop'
    });

    if (inputNodeId) {
      await session.S('DOM.setFileInputFiles', {
        nodeId: inputNodeId,
        files: [dummyPath]
      });
      console.log('File set, waiting for upload and reload...');
      await new Promise(r => setTimeout(r, 3500));
    }

    // Now click CONFIRM PAYMENT
    console.log('Clicking Confirm Payment...');
    await session.ev(`(() => {
      const btns = Array.from(document.querySelectorAll('button[type="submit"]'));
      const confirmBtn = btns.find(b => b.textContent.includes('CONFIRM PAYMENT') || b.textContent.includes('Konfirmasi Pembayaran'));
      if (confirmBtn) confirmBtn.click();
    })()`);
    await new Promise(r => setTimeout(r, 3500));

    await captureFullPage(session, '05-order-status-confirmed-alert-1280.png', 1280);

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
