import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const profile = path.resolve('.taste-skill/chrome-admin');
fs.mkdirSync(profile, { recursive: true });
const portFile = path.join(profile, 'DevToolsActivePort');
try { fs.rmSync(portFile); } catch {}

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const child = spawn(chromePath, [
  '--headless=new',
  `--user-data-dir=${profile}`,
  '--remote-debugging-port=0',
  '--no-first-run',
  '--no-default-browser-check',
  '--disable-extensions',
  '--window-size=1280,800',
  'about:blank'
], { stdio: 'ignore' });

const kill = () => { try { child.kill(); } catch {} };
process.on('exit', kill);

async function run() {
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

  console.log('Navigating to http://localhost:8000/admin/login...');
  await S('Page.navigate', { url: 'http://localhost:8000/admin/login' });
  await new Promise(r => setTimeout(r, 3000));

  const cookies = await S('Network.getCookies');
  console.log('Cookies:', cookies.cookies.map(c => c.name));

  const csrfToken = await ev(`document.querySelector('input[name="_token"]')?.value`);
  console.log('CSRF token:', csrfToken);

  console.log('Filling form...');
  await ev(`
    (() => {
      const u = document.querySelector('input[name="identifier"]');
      const p = document.querySelector('input[name="password"]');
      if (u) { u.value = 'admin'; u.dispatchEvent(new Event('input', { bubbles: true })); }
      if (p) { p.value = 'admin'; p.dispatchEvent(new Event('input', { bubbles: true })); }
      const f = document.querySelector('form');
      if (f) {
        f.action = '/admin/login';
      }
      const btn = document.querySelector('button[type="submit"]');
      if (btn) btn.click();
      return true;
    })()
  `);

  await new Promise(r => setTimeout(r, 5000));
  const currentUrl = await ev('window.location.href');
  console.log('Current URL after login:', currentUrl);
  const text = await ev('document.body.innerText');
  console.log('Body text snippet:', text.slice(0, 300));
  kill();
}

run().catch(e => { console.error(e); kill(); process.exit(1); });
