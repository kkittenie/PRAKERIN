import fs from 'node:fs';
import path from 'node:path';
import { spawn, execSync } from 'node:child_process';

const outDir = 'shots/phase3';
fs.mkdirSync(outDir, { recursive: true });

const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/phase3';
fs.mkdirSync(artifactDir, { recursive: true });

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

// 1. SHOOT GUEST (clean temp profile)
async function shootGuest() {
  console.log('--- SHOOTING GUEST VIEW ---');
  const profileGuest = path.resolve('.taste-skill/chrome-guest');
  fs.mkdirSync(profileGuest, { recursive: true });

  for (const w of [1280, 1440]) {
    const outFile = `${outDir}/beranda-guest-${w}.png`;
    console.log(`Shooting Guest @ ${w}px -> ${outFile}`);
    execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "http://localhost:8000/mobile/beranda" --w ${w} --out "${outFile}" --profile "${profileGuest}" --no-tiles --wait 800`, {
      stdio: 'inherit',
      env: { ...process.env, no_proxy: '*', NO_PROXY: '*' }
    });
  }
}

// 2. LOG IN MEMBER & SHOOT MEMBER VIEW
async function loginAndShootMember() {
  console.log('\n--- LOGGING IN MEMBER & SHOOTING MEMBER VIEW ---');
  const profileMember = path.resolve('.taste-skill/chrome-member');
  fs.mkdirSync(profileMember, { recursive: true });
  const portFile = path.join(profileMember, 'DevToolsActivePort');
  try { fs.rmSync(portFile); } catch {}

  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileMember}`,
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
  if (!port) throw new Error('Chrome did not start for member login');

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

  console.log('Navigating to http://localhost:8000/login ...');
  await S('Page.navigate', { url: 'http://localhost:8000/login' });
  await new Promise(r => setTimeout(r, 2500));

  console.log('Filling member login credentials...');
  const loginResult = await ev(`
    (() => {
      const idInput = document.querySelector('input[name="identifier"]');
      const passInput = document.querySelector('input[name="password"]');
      const form = document.querySelector('form');
      if (!idInput || !passInput || !form) return 'Form elements not found';
      idInput.value = 'sofi';
      passInput.value = 'password123';
      form.action = '/login';
      form.submit();
      return 'Submitted';
    })()
  `);
  console.log('Login form submitted:', loginResult);

  // Wait for login redirect
  await new Promise(r => setTimeout(r, 3500));
  const currentUrl = await ev('window.location.href');
  console.log('Current URL after login:', currentUrl);

  // Close this CDP instance so profile is ready for shoot.mjs
  ws.close();
  child.kill();
  await new Promise(r => setTimeout(r, 1000));

  // Now shoot with member profile
  for (const w of [1280, 1440]) {
    const outFile = `${outDir}/beranda-member-${w}.png`;
    console.log(`Shooting Member @ ${w}px -> ${outFile}`);
    execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "http://localhost:8000/mobile/beranda" --w ${w} --out "${outFile}" --profile "${profileMember}" --no-tiles --wait 800`, {
      stdio: 'inherit',
      env: { ...process.env, no_proxy: '*', NO_PROXY: '*' }
    });
  }
}

async function main() {
  await shootGuest();
  await loginAndShootMember();

  // Copy to artifact directory
  console.log('\n--- COPYING TO ARTIFACT DIRECTORY ---');
  for (const f of fs.readdirSync(outDir)) {
    if (f.endsWith('.png')) {
      fs.copyFileSync(path.join(outDir, f), path.join(artifactDir, f));
    }
  }
  console.log('All screenshots saved in:', artifactDir);
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
