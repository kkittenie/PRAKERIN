import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/account_and_content';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/account_and_content';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome() {
  const profileDir = path.join('/tmp', 'chrome-content-' + Date.now());
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
  const shot = await session.S('Page.captureScreenshot', params);
  const buf = Buffer.from(shot.result.data, 'base64');
  fs.writeFileSync(outPath, buf);
  fs.writeFileSync(artPath, buf);
  console.log(`Saved screenshot: ${outPath} (${buf.length} bytes)`);
}

async function run() {
  console.log('--- Launching Headless Chrome for Content & Account Verification ---');
  const session = await launchChrome();

  try {
    // 1. Login as Member 'sofi'
    console.log('Logging in as sofi...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await new Promise(r => setTimeout(r, 1200));

    await session.S('Runtime.evaluate', {
      expression: `(() => {
        const idInput = document.querySelector('input[name="identifier"]');
        const passInput = document.querySelector('input[name="password"]');
        const form = document.querySelector('form');
        if (idInput) idInput.value = 'sofi';
        if (passInput) passInput.value = 'password123';
        if (form) {
          form.action = '/login';
          form.submit();
        }
      })()`
    });
    await new Promise(r => setTimeout(r, 2500));

    // 2. Visit /mobile/akun
    console.log('Navigating to /mobile/akun (checking stamp count & links)...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/akun' });
    await new Promise(r => setTimeout(r, 2000));
    const stampText = await session.S('Runtime.evaluate', {
      expression: `(() => {
        const cards = Array.from(document.querySelectorAll('#account-showcase-content p'));
        return cards.map(c => c.innerText.trim()).filter(t => t.includes('STAMPS'));
      })()`
    });
    console.log('Stamp Text on Account page:', JSON.stringify(stampText.result.value));
    await captureShot(session, 'shots/account_and_content/01-account-member-1280.png', false);
    await captureShot(session, 'shots/account_and_content/01-account-member-fullpage-1280.png', true);

    // 3. Visit /mobile/stamps directly (verify desktop redirect to /mobile/reward?tab=stamp)
    console.log('Navigating to /mobile/stamps directly...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/stamps' });
    await new Promise(r => setTimeout(r, 2500));
    const currentUrl = await session.S('Runtime.evaluate', { expression: `window.location.href` });
    console.log('URL after visiting /mobile/stamps:', currentUrl.result.value);
    await captureShot(session, 'shots/account_and_content/02-stamps-redirect-1280.png', false);

    // 4. Visit /mobile/info/help-center
    console.log('Navigating to /mobile/info/help-center...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/info/help-center' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/account_and_content/03-help-center-1280.png', false);
    await captureShot(session, 'shots/account_and_content/03-help-center-fullpage-1280.png', true);

    // 5. Visit /mobile/info/terms-conditions
    console.log('Navigating to /mobile/info/terms-conditions...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/info/terms-conditions' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/account_and_content/04-terms-conditions-1280.png', false);
    await captureShot(session, 'shots/account_and_content/04-terms-conditions-fullpage-1280.png', true);

    // 6. Visit /mobile/info/privacy-policy
    console.log('Navigating to /mobile/info/privacy-policy...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/info/privacy-policy' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/account_and_content/05-privacy-policy-1280.png', false);
    await captureShot(session, 'shots/account_and_content/05-privacy-policy-fullpage-1280.png', true);

    // 7. Visit /mobile/profil
    console.log('Navigating to /mobile/profil...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/profil' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/account_and_content/06-profil-1280.png', false);

    // 8. Visit /mobile/settings/ubah-password
    console.log('Navigating to /mobile/settings/ubah-password...');
    await session.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/settings/ubah-password' });
    await new Promise(r => setTimeout(r, 2000));
    await captureShot(session, 'shots/account_and_content/07-ubah-password-1280.png', false);

    console.log('Verification shots completed successfully!');
  } finally {
    try { session.child.kill(); } catch {}
  }
}

run().catch(err => {
  console.error('Error running shoot_account_and_content:', err);
  process.exit(1);
});
