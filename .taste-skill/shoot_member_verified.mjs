import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-member-verify-${Date.now()}`);
fs.mkdirSync(profileDir, { recursive: true });

const child = spawn(chromePath, [
  '--headless=new',
  `--user-data-dir=${profileDir}`,
  '--remote-debugging-port=0',
  '--no-first-run',
  '--no-default-browser-check',
  '--disable-extensions',
  '--window-size=390,844',
  'about:blank'
], { stdio: 'ignore' });

async function main() {
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  let port = null;
  for (let i = 0; i < 100 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
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
    if (m.id && pend.has(m.id)) { pend.get(m.id)(m); pend.delete(m.id); }
  });
  const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
    const i = ++id;
    const timeout = setTimeout(() => {
      pend.delete(i);
      reject(new Error(`Timeout on ${method}`));
    }, 15000);
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
  await S('Emulation.setDeviceMetricsOverride', {
    width: 390,
    height: 844,
    deviceScaleFactor: 2,
    mobile: true
  });

  // Login
  console.log('Logging in...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
  await new Promise(r => setTimeout(r, 2000));

  await S('Runtime.evaluate', {
    expression: `(() => {
      const idInput = document.querySelector('input[name="identifier"]');
      const passInput = document.querySelector('input[name="password"]');
      const btn = document.querySelector('button[type="submit"]');
      if (idInput) {
        idInput.value = 'sofi';
        idInput.dispatchEvent(new Event('input', { bubbles: true }));
        idInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
      if (passInput) {
        passInput.value = 'password123';
        passInput.dispatchEvent(new Event('input', { bubbles: true }));
        passInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
      setTimeout(() => {
        if (btn) btn.click();
      }, 300);
    })()`
  });
  await new Promise(r => setTimeout(r, 3500));

  // 1. Beranda page (scrolled down to Special Reward Catalog)
  console.log('Navigating to beranda...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 2000));
  await S('Runtime.evaluate', { expression: `window.scrollTo(0, 520);` });
  await new Promise(r => setTimeout(r, 1000));
  const shotBeranda = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/01-member-beranda-vouchers.png', Buffer.from(shotBeranda.data, 'base64'));
  console.log('Saved shots/mobile_theme/01-member-beranda-vouchers.png');

  // 2. Reward / Vouchers page
  console.log('Navigating to reward...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward' });
  await new Promise(r => setTimeout(r, 2000));
  const shotReward = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/02-mobile-reward-verified.png', Buffer.from(shotReward.data, 'base64'));
  console.log('Saved shots/mobile_theme/02-mobile-reward-verified.png');

  // 3. Voucher details page
  console.log('Navigating to voucher details...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/reward/1' });
  await new Promise(r => setTimeout(r, 2000));
  const shotDetails = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/03-mobile-voucher-details.png', Buffer.from(shotDetails.data, 'base64'));
  console.log('Saved shots/mobile_theme/03-mobile-voucher-details.png');

  // 4. Profil / Edit Account page
  console.log('Navigating to profil...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/profil' });
  await new Promise(r => setTimeout(r, 2000));
  const shotProfil = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/04-mobile-profil.png', Buffer.from(shotProfil.data, 'base64'));
  console.log('Saved shots/mobile_theme/04-mobile-profil.png');

  // 5. Akun page
  console.log('Navigating to akun...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/akun' });
  await new Promise(r => setTimeout(r, 2000));
  const shotAkun = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/05-mobile-akun.png', Buffer.from(shotAkun.data, 'base64'));
  console.log('Saved shots/mobile_theme/05-mobile-akun.png');

  // 6. Checkout page
  console.log('Navigating to product 1 to add to cart...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/produk/1' });
  await new Promise(r => setTimeout(r, 2000));
  await S('Runtime.evaluate', {
    expression: `(() => {
      const btn = document.querySelector('form button[type="submit"]');
      if (btn) btn.click();
    })()`
  });
  await new Promise(r => setTimeout(r, 2000));
  console.log('Navigating to checkout...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/checkout' });
  await new Promise(r => setTimeout(r, 2000));
  const shotCheckout = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/06-mobile-checkout.png', Buffer.from(shotCheckout.data, 'base64'));
  console.log('Saved shots/mobile_theme/06-mobile-checkout.png');

  // 7. Notifications page
  console.log('Navigating to notifikasi...');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/notifikasi' });
  await new Promise(r => setTimeout(r, 2000));
  const shotNotif = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/mobile_theme/07-mobile-notifikasi.png', Buffer.from(shotNotif.data, 'base64'));
  console.log('Saved shots/mobile_theme/07-mobile-notifikasi.png');

  ws.close();
  try { child.kill(); } catch {}
  try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch {}
}

main().catch(e => {
  console.error(e);
  try { child.kill(); } catch {}
  process.exit(1);
});
