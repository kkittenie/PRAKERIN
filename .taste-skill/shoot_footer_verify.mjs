import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const outDir = 'shots/phase3';
const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/phase3';
const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function launchChrome(profileDir) {
  fs.mkdirSync(profileDir, { recursive: true });
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  try { fs.rmSync(portFile, { force: true }); } catch {}
  try { fs.rmSync(path.join(profileDir, 'SingletonLock'), { force: true }); } catch {}
  try { fs.rmSync(path.join(profileDir, 'SingletonSocket'), { force: true }); } catch {}
  try { fs.rmSync(path.join(profileDir, 'SingletonCookie'), { force: true }); } catch {}

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

  return { child, ws, S, ev };
}

async function captureFooter(S, ev, width, outName) {
  // Set full document height first so all elements exist in page surface
  const fullHeight = await ev(`(() => {
    const footer = document.querySelector('footer.hidden.lg\\\\:block');
    if (footer) {
      const r = footer.getBoundingClientRect();
      return Math.ceil(r.bottom + window.scrollY);
    }
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
  })()`);

  await S('Emulation.setDeviceMetricsOverride', { width, height: fullHeight, deviceScaleFactor: 1, mobile: false });
  await new Promise(r => setTimeout(r, 600));

  const footerRect = await ev(`(() => {
    const footer = document.querySelector('footer.hidden.lg\\\\:block');
    if (!footer) return null;
    const r = footer.getBoundingClientRect();
    return { x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height };
  })()`);

  if (footerRect) {
    const { data } = await S('Page.captureScreenshot', {
      format: 'png',
      captureBeyondViewport: true,
      clip: {
        x: Math.max(0, footerRect.x),
        y: Math.max(0, footerRect.y),
        width: footerRect.width,
        height: footerRect.height,
        scale: 1
      }
    });
    const savePath = path.join(outDir, `${outName}-footer.png`);
    fs.writeFileSync(savePath, Buffer.from(data, 'base64'));
    fs.copyFileSync(savePath, path.join(artifactDir, `${outName}-footer.png`));
    console.log(`Saved footer: ${savePath}`);
  }
}

async function main() {
  const c = await launchChrome('.taste-skill/chrome-quick-footer');
  try {
    await c.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await c.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 2000));
    await captureFooter(c.S, c.ev, 1280, 'beranda-guest-1280');
    await captureFooter(c.S, c.ev, 1920, 'beranda-guest-1920');
  } finally {
    c.ws.close();
    c.child.kill();
  }
}

main().catch(err => { console.error(err); process.exit(1); });
