import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/menu';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/menu';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', `chrome-search-test-${Date.now()}`);
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

async function captureViewport(session, filename, width = 1280, height = 900) {
  const { S } = session;
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height,
    deviceScaleFactor: 1,
    mobile: false
  });
  const shot = await S('Page.captureScreenshot', { format: 'png' });
  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync(path.join(outDir, filename), buf);
  fs.writeFileSync(path.join(artifactDir, filename), buf);
  console.log(`Saved screenshot -> ${filename}`);
}

async function main() {
  console.log('=== Testing Desktop Menu Search & Empty State ===');
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

    // 2. Navigate to /mobile/menu
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/menu' });
    await new Promise(r => setTimeout(r, 2500));

    // 3. Scroll to search bar
    await session.ev(`(() => {
      const el = document.getElementById('menu-desktop-search');
      if (el) el.scrollIntoView({ behavior: 'instant', block: 'start' });
    })()`);
    await new Promise(r => setTimeout(r, 500));

    // 4. Test Search with "as" (User scenario)
    console.log('--- Testing search: "as" ---');
    await session.ev(`(() => {
      const input = document.querySelector('#menu-desktop-search input');
      if (input) {
        input.value = 'as';
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    })()`);
    await new Promise(r => setTimeout(r, 600));

    // Check visible categories and empty state
    const statusAs = await session.ev(`(() => {
      const sections = Array.from(document.querySelectorAll('#menu-showcase-content .menu-category-section')).map(s => {
        const title = s.querySelector('h2')?.textContent?.trim();
        const display = window.getComputedStyle(s).display;
        return { title, display };
      });
      const emptyState = document.querySelector('#menu-showcase-content [x-show="!hasMatchingProducts()"]');
      const emptyStateDisplay = emptyState ? window.getComputedStyle(emptyState).display : 'none';
      return { sections, emptyStateDisplay };
    })()`);
    console.log('Search "as" result:', JSON.stringify(statusAs, null, 2));

    await captureViewport(session, '04-menu-search-as-empty-1280.png');

    // 5. Test Search with "croissant"
    console.log('--- Testing search: "croissant" ---');
    await session.ev(`(() => {
      const input = document.querySelector('#menu-desktop-search input');
      if (input) {
        input.value = 'croissant';
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    })()`);
    await new Promise(r => setTimeout(r, 600));

    const statusCroissant = await session.ev(`(() => {
      const sections = Array.from(document.querySelectorAll('#menu-showcase-content .menu-category-section')).map(s => {
        const title = s.querySelector('h2')?.textContent?.trim();
        const display = window.getComputedStyle(s).display;
        return { title, display };
      });
      const emptyState = document.querySelector('#menu-showcase-content [x-show="!hasMatchingProducts()"]');
      const emptyStateDisplay = emptyState ? window.getComputedStyle(emptyState).display : 'none';
      return { sections, emptyStateDisplay };
    })()`);
    console.log('Search "croissant" result:', JSON.stringify(statusCroissant, null, 2));

    await captureViewport(session, '05-menu-search-croissant-1280.png');

  } finally {
    session.ws.close();
    try { session.child.kill(); } catch {}
    try { fs.rmSync(session.profileDir, { recursive: true, force: true }); } catch {}
  }
  console.log('Search test completed!');
}

main().catch(err => {
  console.error('Error:', err);
  process.exit(1);
});
