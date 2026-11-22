import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-desktop-note-${Date.now()}`);
fs.mkdirSync(profileDir, { recursive: true });

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
  const send = (method, params = {}, sessionId) => new Promise(r => {
    const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId }));
  });

  const { result: { targetId } } = await send('Target.createTarget', { url: 'about:blank' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);

  await S('Page.enable');
  await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
  await new Promise(r => setTimeout(r, 3500));

  // Scroll to section 5b
  await S('Runtime.evaluate', {
    expression: `(() => {
      const sec = document.getElementById('taste-desktop-store-receipt');
      if (sec) sec.scrollIntoView({ behavior: 'instant', block: 'center' });
    })()`
  });
  await new Promise(r => setTimeout(r, 1200));

  const shot = await S('Page.captureScreenshot', { format: 'png' });
  const buf = Buffer.from(shot.data, 'base64');
  fs.writeFileSync('shots/mobile_theme/desktop-address-verified.png', buf);
  console.log('Saved shots/mobile_theme/desktop-address-verified.png');

  ws.close();
  try { child.kill(); } catch {}
  try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch {}
}

main().catch(e => {
  console.error(e);
  try { child.kill(); } catch {}
  process.exit(1);
});
