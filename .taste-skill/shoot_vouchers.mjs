import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/vouchers';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/vouchers';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', 'chrome-vouchers-' + Date.now());
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
  const params = { format: 'png' };
  if (fullPage) {
    params.captureBeyondViewport = true;
  }
  const { result: { data } } = await session.S('Page.captureScreenshot', params);
  const buf = Buffer.from(data, 'base64');
  fs.writeFileSync(outPath, buf);
  const artPath = path.join(artifactDir, path.basename(outPath));
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

    // 1. Vouchers Catalog (Desktop 1280px)
    console.log('Navigating to vouchers catalog...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/vouchers/01-vouchers-catalog-desktop-1280.png');
    await captureShot(session, 'shots/vouchers/01-vouchers-catalog-fullpage-1280.png', true);

    // 2. Switch to Stamp tab
    console.log('Switching to Stamp Rewards tab...');
    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const btn = document.querySelector('[data-desktop-tab="stamp"]');
        if (btn) btn.click();
        else if (typeof window.switchDesktopTab === 'function') window.switchDesktopTab('stamp');
      })()`
    });
    await new Promise(r => setTimeout(r, 1500));
    await captureShot(session, 'shots/vouchers/02-vouchers-stamp-tab-desktop-1280.png');
    await captureShot(session, 'shots/vouchers/02-vouchers-stamp-tab-fullpage-1280.png', true);

    // 3. Switch to How It Works / Guide tab
    console.log('Switching to How It Works guide tab...');
    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const btn = document.querySelector('[data-desktop-tab="guide"]');
        if (btn) btn.click();
        else if (typeof window.switchDesktopTab === 'function') window.switchDesktopTab('guide');
      })()`
    });
    await new Promise(r => setTimeout(r, 1500));
    await captureShot(session, 'shots/vouchers/03-vouchers-guide-tab-fullpage-1280.png', true);

    // 4. Navigate to first voucher detail
    console.log('Finding first voucher link...');
    const voucherHref = await session.S('Runtime.evaluate', {
      expression: `(() => {
        const link = document.querySelector('#desktop-pane-voucher a[href*="/mobile/reward/"]');
        return link ? link.href : null;
      })()`,
      returnByValue: true
    });

    const targetUrl = voucherHref.result.value || 'http://127.0.0.1:8000/mobile/reward/1';
    console.log('Navigating to voucher detail:', targetUrl);
    await session.S('Page.navigate', { url: targetUrl });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/vouchers/04-voucher-detail-desktop-1280.png');
    await captureShot(session, 'shots/vouchers/04-voucher-detail-fullpage-1280.png', true);

  } finally {
    try { session.child.kill(); } catch {}
  }
}

main().catch(err => {
  console.error('Error in shoot_vouchers:', err);
  process.exit(1);
});
