import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-dropdowns-verify-${Date.now()}`);
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

  const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
    const i = ++id;
    const timeout = setTimeout(() => { pend.delete(i); reject(new Error(`Timeout on ${method}`)); }, 30000);
    pend.set(i, res => { clearTimeout(timeout); resolve(res); });
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

  // Login as admin
  console.log('Logging in as admin...');
  await S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 850, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/admin/login' });
  await sleep(1800);

  await evalVal(`(() => {
    const idInput = document.querySelector('input[name="identifier"], input[name="username"], input[name="email"]');
    const passInput = document.querySelector('input[name="password"]');
    const btn = document.querySelector('button[type="submit"]');
    if (idInput) {
      idInput.value = 'admin';
      idInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (passInput) {
      passInput.value = 'admin';
      passInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (btn) btn.click();
  })()`);
  await sleep(2500);

  // Navigate to /rekap-produk
  console.log('Navigating to /rekap-produk (1280x850)...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/rekap-produk' });
  await sleep(2200);

  // 1. Open Notification Dropdown
  console.log('Opening Notification Dropdown...');
  const notifOpened = await evalVal(`(() => {
    const btn = document.querySelector('[x-ref="tombolNotifikasi"]');
    if (btn) { btn.click(); return true; }
    return false;
  })()`);
  console.log('Notification button clicked:', notifOpened);
  await sleep(500);

  const notifMetrics = await evalVal(`(() => {
    const panel = document.querySelector('[x-ref="panelNotifikasi"]');
    if (!panel) return null;
    const r = panel.getBoundingClientRect();
    return {
      left: Math.round(r.left),
      right: Math.round(r.right),
      top: Math.round(r.top),
      bottom: Math.round(r.bottom),
      width: Math.round(r.width),
      height: Math.round(r.height),
      windowWidth: window.innerWidth,
      isCutOffRight: r.right > window.innerWidth,
      marginRight: Math.round(window.innerWidth - r.right)
    };
  })()`);
  console.log('Notification Dropdown Metrics (Desktop):', notifMetrics);
  await snap('final-admin-notif-desktop.png');

  // Close Notification Dropdown, Open Settings Dropdown
  console.log('Closing Notif and Opening Settings Dropdown...');
  await evalVal(`(() => {
    const notifBtn = document.querySelector('[x-ref="tombolNotifikasi"]');
    if (notifBtn) notifBtn.click();
  })()`);
  await sleep(600);

  console.log('Clicking Settings Button...');
  const settingsClicked = await evalVal(`(() => {
    const settingsBtn = document.querySelector('[x-ref="tombolSetelan"]');
    if (settingsBtn) { settingsBtn.click(); return true; }
    return false;
  })()`);
  console.log('Settings button clicked:', settingsClicked);
  await sleep(600);

  const settingsMetrics = await evalVal(`(() => {
    const panel = document.querySelector('[x-ref="panelSetelan"]');
    if (!panel) return null;
    const r = panel.getBoundingClientRect();
    return {
      left: Math.round(r.left),
      right: Math.round(r.right),
      top: Math.round(r.top),
      bottom: Math.round(r.bottom),
      width: Math.round(r.width),
      height: Math.round(r.height),
      windowWidth: window.innerWidth,
      isCutOffRight: r.right > window.innerWidth,
      marginRight: Math.round(window.innerWidth - r.right)
    };
  })()`);
  console.log('Settings Dropdown Metrics (Desktop):', settingsMetrics);
  await snap('final-admin-settings-desktop.png');

  // Close Settings Dropdown, Open User Dropdown
  console.log('Testing User Dropdown as well...');
  await evalVal(`(() => {
    const settingsBtn = document.querySelector('[x-ref="tombolSetelan"]');
    if (settingsBtn) settingsBtn.click();
    const userBtn = document.querySelector('[x-data*="dropdownOpen"] button[type="button"]:has(img)');
    if (userBtn) userBtn.click();
  })()`);
  await sleep(500);
  await snap('final-admin-user-desktop.png');

  // 2. Mobile Viewport (390x844)
  console.log('Testing Mobile Viewport (390x844)...');
  await S('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/rekap-produk' });
  await sleep(2000);

  // Open mobile app menu
  await evalVal(`(() => {
    const menuBtn = document.querySelector('button[aria-label="Menu Aksi"]');
    if (menuBtn) menuBtn.click();
  })()`);
  await sleep(400);

  // Click notif in mobile menu
  await evalVal(`(() => {
    const btn = document.querySelector('[x-ref="tombolNotifikasi"]');
    if (btn) btn.click();
  })()`);
  await sleep(400);

  const mobileNotifMetrics = await evalVal(`(() => {
    const panel = document.querySelector('[x-ref="panelNotifikasi"]');
    if (!panel) return null;
    const r = panel.getBoundingClientRect();
    return {
      left: Math.round(r.left),
      right: Math.round(r.right),
      windowWidth: window.innerWidth,
      isCutOffRight: r.right > window.innerWidth,
      marginLeft: Math.round(r.left),
      marginRight: Math.round(window.innerWidth - r.right)
    };
  })()`);
  console.log('Notification Dropdown Metrics (Mobile):', mobileNotifMetrics);
  await snap('final-admin-notif-mobile.png');

  // Click settings in mobile menu
  await evalVal(`(() => {
    const notifBtn = document.querySelector('[x-ref="tombolNotifikasi"]');
    if (notifBtn) notifBtn.click();
    const settingsBtn = document.querySelector('[x-ref="tombolSetelan"]');
    if (settingsBtn) settingsBtn.click();
  })()`);
  await sleep(400);

  const mobileSettingsMetrics = await evalVal(`(() => {
    const panel = document.querySelector('[x-ref="panelSetelan"]');
    if (!panel) return null;
    const r = panel.getBoundingClientRect();
    return {
      left: Math.round(r.left),
      right: Math.round(r.right),
      windowWidth: window.innerWidth,
      isCutOffRight: r.right > window.innerWidth,
      marginLeft: Math.round(r.left),
      marginRight: Math.round(window.innerWidth - r.right)
    };
  })()`);
  console.log('Settings Dropdown Metrics (Mobile):', mobileSettingsMetrics);
  await snap('final-admin-settings-mobile.png');

  ws.close();
  child.kill();
  console.log('ALL DROPDOWN VERIFICATION COMPLETED!');
}

main().catch(e => {
  console.error(e);
  child.kill();
  process.exit(1);
});
