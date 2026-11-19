import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

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

  const fontRequests = [];
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.method === 'Network.responseReceived') {
      const resp = m.params.response;
      if (resp.mimeType?.includes('font') || resp.url?.includes('fonts.gstatic.com') || resp.url?.endsWith('.woff2')) {
        fontRequests.push({
          url: resp.url,
          mimeType: resp.mimeType,
          status: resp.status,
          encodedDataLength: resp.encodedDataLength
        });
      }
    }
  });

  return { child, ws, S, ev, fontRequests };
}

async function run() {
  console.log('=== 1. Checking Desktop Member (1280px) ===');
  const dClient = await launchChrome('.taste-skill/chrome-test-desktop');
  let desktopFonts = [];
  try {
    await dClient.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await dClient.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 3500));
    desktopFonts = [...dClient.fontRequests];
    console.log(`Desktop (1280px) font requests: ${desktopFonts.length}`);
    desktopFonts.forEach(f => console.log(`  - ${f.url} (${f.encodedDataLength || 0} bytes)`));

    // Verify computed font on H1
    const h1Font = await dClient.ev(`(() => {
      const h1 = document.querySelector('h1');
      if (!h1) return null;
      const s = window.getComputedStyle(h1);
      return {
        fontFamily: s.fontFamily,
        fontWeight: s.fontWeight,
        fontSize: s.fontSize,
        lineHeight: s.lineHeight,
        letterSpacing: s.letterSpacing,
        fontVariationSettings: s.fontVariationSettings
      };
    })()`);
    console.log('Desktop H1 computed font:', JSON.stringify(h1Font, null, 2));

    const scriptAccentFont = await dClient.ev(`(() => {
      const el = document.querySelector('.font-accent');
      if (!el) return null;
      const s = window.getComputedStyle(el);
      return {
        fontFamily: s.fontFamily,
        fontSize: s.fontSize,
        color: s.color
      };
    })()`);
    console.log('Desktop .font-accent computed font:', JSON.stringify(scriptAccentFont, null, 2));
  } finally {
    dClient.ws.close();
    dClient.child.kill();
  }

  console.log('\n=== 2. Checking Mobile Member (375px) ===');
  const mClient = await launchChrome('.taste-skill/chrome-test-mobile');
  let mobileFonts = [];
  try {
    await mClient.S('Emulation.setDeviceMetricsOverride', { width: 375, height: 667, deviceScaleFactor: 2, mobile: true });
    await mClient.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 3000));
    mobileFonts = [...mClient.fontRequests];
    console.log(`Mobile (375px) font requests: ${mobileFonts.length}`);
    mobileFonts.forEach(f => console.log(`  - ${f.url}`));
    const loadedFrauncesInMobile = mobileFonts.some(f => f.url.toLowerCase().includes('fraunces') || f.url.toLowerCase().includes('caveat'));
    console.log('Fraunces / Caveat loaded in Mobile?', loadedFrauncesInMobile ? 'YES (FAIL)' : 'NO (PASS - Isolated)');
  } finally {
    mClient.ws.close();
    mClient.child.kill();
  }

  console.log('\n=== 3. Checking Admin (1280px) ===');
  const aClient = await launchChrome('.taste-skill/chrome-test-admin');
  let adminFonts = [];
  try {
    await aClient.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await aClient.S('Page.navigate', { url: 'http://127.0.0.1:8000/admin/login' });
    await new Promise(r => setTimeout(r, 3000));
    adminFonts = [...aClient.fontRequests];
    console.log(`Admin (1280px) font requests: ${adminFonts.length}`);
    adminFonts.forEach(f => console.log(`  - ${f.url}`));
    const loadedFrauncesInAdmin = adminFonts.some(f => f.url.toLowerCase().includes('fraunces') || f.url.toLowerCase().includes('caveat'));
    console.log('Fraunces / Caveat loaded in Admin?', loadedFrauncesInAdmin ? 'YES (FAIL)' : 'NO (PASS - Isolated)');
  } finally {
    aClient.ws.close();
    aClient.child.kill();
  }
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
