import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/phase3';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/phase3';
fs.mkdirSync(artifactDir, { recursive: true });

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

  const kill = () => { try { child.kill(); } catch {} };
  process.on('exit', kill);

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
  const ev = x => S('Runtime.evaluate', { expression: x, awaitPromise: true, returnByValue: true }).then(r => r.result.value);

  await S('Page.enable');
  await S('Runtime.enable');
  await S('Network.enable');

  return { child, ws, S, ev };
}

async function captureFullPage(S, ev, width, outPath) {
  console.log(`Setting device metrics width=${width}...`);
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: 900,
    deviceScaleFactor: 1,
    mobile: false
  });

  await new Promise(r => setTimeout(r, 1000));
  const height = await ev('Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)');
  console.log(`Page full height: ${height}px for width ${width}px`);

  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height,
    deviceScaleFactor: 1,
    mobile: false
  });
  await new Promise(r => setTimeout(r, 500));

  const { data } = await S('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
  fs.writeFileSync(outPath, Buffer.from(data, 'base64'));
  console.log(`Saved screenshot: ${outPath}`);
}

async function main() {
  // 1. Guest Session
  console.log('\n=== CAPTURING GUEST (1280 & 1440) ===');
  const guestProf = path.resolve('/tmp/chrome-guest-' + Date.now());
  const guest = await launchChrome(guestProf);
  await guest.S('Page.navigate', { url: 'http://localhost:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 3000));

  await captureFullPage(guest.S, guest.ev, 1280, `${outDir}/beranda-guest-1280.png`);
  await captureFullPage(guest.S, guest.ev, 1440, `${outDir}/beranda-guest-1440.png`);
  guest.ws.close();
  guest.child.kill();

  // 2. Member Session
  console.log('\n=== CAPTURING MEMBER (1280 & 1440) ===');
  const memberProf = path.resolve('/tmp/chrome-member-' + Date.now());
  const member = await launchChrome(memberProf);
  await member.S('Page.navigate', { url: 'http://localhost:8000/login' });
  await new Promise(r => setTimeout(r, 2500));

  await member.ev(`
    (() => {
      const idInput = document.querySelector('input[name="identifier"]');
      const passInput = document.querySelector('input[name="password"]');
      const form = document.querySelector('form');
      if (!idInput || !passInput || !form) return 'not found';
      idInput.value = 'sofi';
      passInput.value = 'password123';
      form.action = '/login';
      form.submit();
      return 'submitted';
    })()
  `);
  console.log('Submitted member login form, waiting redirect...');
  await new Promise(r => setTimeout(r, 3500));

  console.log('Navigating to http://localhost:8000/mobile/beranda ...');
  await member.S('Page.navigate', { url: 'http://localhost:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 3000));

  await captureFullPage(member.S, member.ev, 1280, `${outDir}/beranda-member-1280.png`);
  await captureFullPage(member.S, member.ev, 1440, `${outDir}/beranda-member-1440.png`);
  member.ws.close();
  member.child.kill();

  // 3. Copy to Artifacts
  console.log('\n=== COPYING TO ARTIFACT DIR ===');
  for (const f of fs.readdirSync(outDir)) {
    if (f.endsWith('.png')) {
      fs.copyFileSync(path.join(outDir, f), path.join(artifactDir, f));
    }
  }
  console.log('Done! All screenshots updated in artifact dir.');
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
