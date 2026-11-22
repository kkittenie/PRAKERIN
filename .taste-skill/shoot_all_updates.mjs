import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-v2-verify-${Date.now()}`);
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
  '--window-size=1440,900',
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
  const consoleErrors = [];
  ws.addEventListener('message', e => {
    const m = JSON.parse(e.data);
    if (m.id && pend.has(m.id)) {
      pend.get(m.id)(m);
      pend.delete(m.id);
    } else if (m.method === 'Runtime.exceptionThrown') {
      const txt = m.params?.exceptionDetails?.exception?.description || m.params?.exceptionDetails?.text || JSON.stringify(m.params);
      consoleErrors.push(txt);
      console.error('[PAGE EXCEPTION]', txt);
    } else if (m.method === 'Runtime.consoleAPICalled' && m.params?.type === 'error') {
      const txt = (m.params.args || []).map(a => a.value || a.description).join(' ');
      consoleErrors.push(txt);
      console.error('[CONSOLE ERROR]', txt);
    }
  });
  const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
    const i = ++id;
    const timeout = setTimeout(() => {
      pend.delete(i);
      reject(new Error(`Timeout on ${method}`));
    }, 35000);
    pend.set(i, res => {
      clearTimeout(timeout);
      resolve(res);
    });
    ws.send(JSON.stringify({ id: i, method, params, sessionId }));
  });

  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);

  await S('Page.enable');
  await S('Runtime.enable');

  async function setViewport(width, height, mobile = false, scale = 1) {
    await S('Emulation.setDeviceMetricsOverride', {
      width,
      height,
      deviceScaleFactor: scale,
      mobile
    });
  }

  async function evalVal(expression) {
    const res = await S('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    return res?.result?.value;
  }

  async function snap(filename, opts = {}) {
    const shot = await S('Page.captureScreenshot', { format: 'png', ...opts });
    fs.writeFileSync(path.join(outDir, filename), Buffer.from(shot.data, 'base64'));
    console.log('Saved', filename);
  }

  async function waitWaveDone(maxMs = 3500) {
    const start = Date.now();
    while (Date.now() - start < maxMs) {
      const done = await evalVal(`(() => {
        const el = document.getElementById('daisy-wave-loader');
        return !el || el.classList.contains('is-done') || getComputedStyle(el).display === 'none';
      })()`);
      if (done) return true;
      await sleep(150);
    }
    return false;
  }

  // 1. Wave Loader mid-drain screenshot + Auth Login Desktop
  await setViewport(1440, 900, false, 1);
  console.log('Testing wave loader & /login desktop...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await sleep(1600);
  await waitWaveDone();

  // Pause wave curtain mid-descent so we visually inspect the exact 3-layer SVG wave crest!
  await evalVal(`(() => {
    const m = document.getElementById('modal-info-login');
    if (m) m.style.display = 'none';
    const el = document.getElementById('daisy-wave-loader');
    const curtain = el?.querySelector('.daisy-wave-curtain');
    if (el && curtain) {
      el.classList.remove('is-done', 'is-draining');
      el.style.display = 'block';
      curtain.style.transition = 'none';
      curtain.style.transform = 'translate3d(0, 46vh, 0)';
    }
  })()`);
  await sleep(150);
  await snap('01-wave-loader-mid-drain.png');

  await evalVal(`(() => {
    const el = document.getElementById('daisy-wave-loader');
    if (el) el.classList.add('is-done');
  })()`);
  await sleep(150);
  await snap('02-auth-login-desktop.png');

  // 2. Auth Login Mobile
  await setViewport(390, 844, true, 2);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await sleep(1800);
  await waitWaveDone();
  await evalVal(`(() => {
    const m = document.getElementById('modal-info-login');
    if (m) m.style.display = 'none';
  })()`);
  await sleep(150);
  await snap('03-auth-login-mobile.png');

  // 3. Auth Register Desktop & Mobile
  await setViewport(1440, 900, false, 1);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/daftar' });
  await sleep(1600);
  await waitWaveDone();
  await snap('04-auth-register-desktop.png');

  await setViewport(390, 844, true, 2);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/daftar' });
  await sleep(1600);
  await waitWaveDone();
  await snap('05-auth-register-mobile.png');

  // 4. Member Login (sofi / password123)
  console.log('Logging in as member sofi...');
  await setViewport(1440, 900, false, 1);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await sleep(1500);
  await waitWaveDone();
  await evalVal(`(() => {
    const m = document.getElementById('modal-info-login');
    if (m) m.style.display = 'none';
    const idInput = document.querySelector('input[name="identifier"]');
    const passInput = document.querySelector('input[name="password"]');
    const btn = document.querySelector('button[type="submit"]');
    if (idInput) {
      idInput.value = 'sofi';
      idInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (passInput) {
      passInput.value = 'password123';
      passInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (btn) btn.click();
  })()`);
  await sleep(3000);

  // 5. 3D Cake Hero on Member Desktop Beranda
  console.log('Capturing 3D Cake Hero...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
  await sleep(2500);
  await waitWaveDone();
  await sleep(800);
  const canvasInfo = await evalVal(`(() => {
    const c = document.getElementById('desktop-hero-3d-canvas') || document.querySelector('canvas');
    if (!c) return null;
    const r = c.getBoundingClientRect();
    const gl = c.getContext('webgl2') || c.getContext('webgl');
    return { x: r.x, y: r.y, width: r.width, height: r.height, attrW: c.width, attrH: c.height, hasGL: !!gl };
  })()`);
  console.log('3D canvas info:', canvasInfo);
  await snap('06-3d-cake-hero-desktop.png');
  if (canvasInfo && canvasInfo.width > 100) {
    await snap('07-3d-cake-closeup.png', {
      clip: {
        x: Math.max(0, canvasInfo.x - 10),
        y: Math.max(0, canvasInfo.y - 10),
        width: canvasInfo.width + 20,
        height: canvasInfo.height + 20,
        scale: 2
      }
    });
  }

  // 6. Menu Hero Doodles (Cake & Croissant)
  console.log('Capturing Menu Hero Doodles...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/menu' });
  await sleep(1600);
  await waitWaveDone();
  await sleep(300);
  await snap('08-menu-hero-doodles-desktop.png');

  // 7. Voucher Hero Doodles
  console.log('Capturing Voucher Hero Doodles...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward' });
  await sleep(1600);
  await waitWaveDone();
  await sleep(300);
  await snap('09-voucher-hero-doodles-desktop.png');

  // 8. Voucher Details Slider (Desktop & Mobile)
  console.log('Capturing Voucher Details Slider...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward/1' });
  await sleep(1600);
  await waitWaveDone();
  await evalVal(`(() => {
    const track = document.querySelector('[x-ref="sliderTrack"]');
    if (track) track.scrollIntoView({ behavior: 'instant', block: 'center' });
  })()`);
  await sleep(400);
  const slideCheck = await evalVal(`(async () => {
    const track = document.querySelector('[x-ref="sliderTrack"]');
    if (!track) return null;
    const before = track.scrollLeft;
    const cards = track.children.length;
    const nextBtn = document.querySelector('button[aria-label="Slide next items"]');
    if (nextBtn) nextBtn.click();
    await new Promise(r => setTimeout(r, 700));
    return { cards, before, after: track.scrollLeft, maxScroll: track.scrollWidth - track.clientWidth };
  })()`);
  console.log('Desktop slider test (clicked Next):', slideCheck);
  await snap('10-voucher-detail-slider-desktop.png');

  await setViewport(390, 844, true, 2);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward/1' });
  await sleep(1600);
  await waitWaveDone();
  await evalVal(`(() => {
    const track = document.querySelector('[x-ref="mobileSliderTrack"]');
    if (track) track.scrollIntoView({ behavior: 'instant', block: 'center' });
    else window.scrollTo(0, 360);
  })()`);
  await sleep(400);
  await snap('11-voucher-detail-slider-mobile.png');

  // 9. Admin Login + Admin Dashboard + Footer + Custom Dropdown + Profile + Mobile
  console.log('Capturing Admin Login & Panel...');
  await setViewport(1440, 900, false, 1);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/admin/login' });
  await sleep(1600);
  await waitWaveDone();
  await snap('12-admin-login-desktop.png');

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
  await sleep(2800);
  await waitWaveDone();
  const adminUrl = await evalVal(`location.href`);
  console.log('After admin login URL:', adminUrl);
  await snap('13-admin-dashboard-desktop.png');

  // Scroll to bottom to verify Admin Footer
  await evalVal(`window.scrollTo(0, document.body.scrollHeight)`);
  await sleep(400);
  await snap('14-admin-footer-desktop.png');

  // Admin Produk page + open custom dropdown
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/produk' });
  await sleep(2000);
  await waitWaveDone();
  const ddInfo = await evalVal(`(() => {
    const triggers = document.querySelectorAll('.daisy-select-trigger');
    if (triggers[0]) triggers[0].click();
    return { triggerCount: triggers.length, openPortal: !!document.querySelector('.daisy-select-portal.is-open, .daisy-select-portal') };
  })()`);
  console.log('Admin Produk custom dropdown check:', ddInfo);
  await sleep(350);
  await snap('15-admin-produk-dropdown-desktop.png');

  // Admin Profile page
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/profile' });
  await sleep(1800);
  await waitWaveDone();
  await snap('16-admin-profile-desktop.png');

  // Admin Dashboard Mobile
  await setViewport(390, 844, true, 2);
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/admin' });
  await sleep(1800);
  await waitWaveDone();
  await snap('17-admin-dashboard-mobile.png');

  if (consoleErrors.length) {
    console.log('TOTAL CONSOLE ERRORS:', consoleErrors.length);
  } else {
    console.log('ZERO CONSOLE ERRORS!');
  }

  ws.close();
  try { child.kill(); } catch {}
  try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch {}
}

main().catch(e => {
  console.error(e);
  try { child.kill(); } catch {}
  process.exit(1);
});
