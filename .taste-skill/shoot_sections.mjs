import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/sections';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/sections';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', `chrome-shoot-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`);
  fs.mkdirSync(profileDir, { recursive: true });

  const portFile = path.join(profileDir, 'DevToolsActivePort');

  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileDir}`,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    '--window-size=1280,1000',
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

async function captureFullPage(session, filename, width = 1280) {
  const { S, ev } = session;
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: 1000,
    deviceScaleFactor: 1,
    mobile: false
  });

  // Scroll through entire page to activate all GSAP ScrollTrigger animations
  await ev(`(async () => {
    const totalHeight = document.body.scrollHeight;
    for (let y = 0; y < totalHeight; y += 500) {
      window.scrollTo(0, y);
      await new Promise(r => setTimeout(r, 60));
    }
    window.scrollTo(0, 0);
  })()`);
  await new Promise(r => setTimeout(r, 900));

  // Get total content height
  const scrollHeight = await ev(`Math.max(
    document.body.scrollHeight, document.documentElement.scrollHeight,
    document.body.offsetHeight, document.documentElement.offsetHeight,
    document.body.clientHeight, document.documentElement.clientHeight
  )`);

  const shot = await S('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: true,
    clip: { x: 0, y: 0, width, height: scrollHeight, scale: 1 }
  });

  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync(path.join(outDir, filename), buf);
  fs.writeFileSync(path.join(artifactDir, filename), buf);
  console.log(`Saved full page: ${filename} (${width}x${scrollHeight})`);
}

async function captureElement(session, selector, filename, width = 1280, padding = 20) {
  const { S, ev } = session;
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: 1000,
    deviceScaleFactor: 1,
    mobile: false
  });

  // Scroll element into view to trigger ScrollTrigger animations
  await ev(`(() => {
    const el = document.querySelector('${selector}');
    if (el) {
      el.scrollIntoView({ block: 'center' });
      if (window.ScrollTrigger) ScrollTrigger.refresh();
    }
  })()`);
  await new Promise(r => setTimeout(r, 700));

  const rect = await ev(`(() => {
    const el = document.querySelector('${selector}');
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.left + window.scrollX, y: r.top + window.scrollY, width: r.width, height: r.height };
  })()`);

  if (!rect) {
    console.error(`Element not found for selector: ${selector}`);
    return;
  }

  const clip = {
    x: Math.max(0, rect.x - padding),
    y: Math.max(0, rect.y - padding),
    width: Math.min(width, rect.width + padding * 2),
    height: rect.height + padding * 2,
    scale: 1
  };

  const shot = await S('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: true,
    clip
  });

  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync(path.join(outDir, filename), buf);
  fs.writeFileSync(path.join(artifactDir, filename), buf);
  console.log(`Saved element ${selector} -> ${filename} (${Math.round(clip.width)}x${Math.round(clip.height)})`);
}

async function main() {
  console.log('=== Capturing Member Desktop Sections ===');
  const session = await launchChrome();
  try {
    // Login
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

    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 3000));

    // Capture sections at 1280
    console.log('--- Capturing 1280px ---');
    await captureElement(session, 'header', '01-navbar-1280.png', 1280, 0);
    await captureElement(session, '#taste-desktop-hero', '02-hero-1280.png', 1280, 0);
    await captureElement(session, '#taste-desktop-taste-showcase', '03-section-taste-showcase-1280.png', 1280, 10);
    await captureElement(session, '#taste-desktop-brand-story', '04-section-brand-story-1280.png', 1280, 10);
    await captureElement(session, '#taste-desktop-seasonal-promo', '05-section-seasonal-promo-1280.png', 1280, 10);
    await captureElement(session, '#taste-desktop-ticker-cake', '05a-section-ticker-cake-1280.png', 1280, 10);
    await captureElement(session, '#taste-desktop-store-receipt', '05b-section-store-receipt-1280.png', 1280, 10);
    await captureElement(session, '#taste-desktop-faq', '06-section-faq-1280.png', 1280, 10);
    await captureFullPage(session, '00-fullpage-member-1280.png', 1280);

    // Capture at 1920
    console.log('--- Capturing 1920px ---');
    await captureElement(session, 'header', '01-navbar-1920.png', 1920, 0);
    await captureElement(session, '#taste-desktop-taste-showcase', '03-section-taste-showcase-1920.png', 1920, 10);
    await captureElement(session, '#taste-desktop-brand-story', '04-section-brand-story-1920.png', 1920, 10);
    await captureElement(session, '#taste-desktop-seasonal-promo', '05-section-seasonal-promo-1920.png', 1920, 10);
    await captureElement(session, '#taste-desktop-ticker-cake', '05a-section-ticker-cake-1920.png', 1920, 10);
    await captureElement(session, '#taste-desktop-store-receipt', '05b-section-store-receipt-1920.png', 1920, 10);
    await captureElement(session, '#taste-desktop-faq', '06-section-faq-1920.png', 1920, 10);
    await captureFullPage(session, '00-fullpage-member-1920.png', 1920);

  } finally {
    session.ws.close();
    try { session.child.kill(); } catch {}
    try { fs.rmSync(session.profileDir, { recursive: true, force: true }); } catch {}
  }

  console.log('All section screenshots captured successfully!');
}

main().catch(err => {
  console.error('Error:', err);
  process.exit(1);
});
