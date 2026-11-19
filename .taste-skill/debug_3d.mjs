import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profileDir = path.join('/tmp', `chrome-debug-${Date.now()}`);
fs.mkdirSync(profileDir, { recursive: true });

const portFile = path.join(profileDir, 'DevToolsActivePort');

const child = spawn(chromePath, [
  '--headless=new',
  `--user-data-dir=${profileDir}`,
  '--remote-debugging-port=0',
  '--no-first-run',
  '--no-default-browser-check',
  '--disable-extensions',
  '--disable-gpu',
  '--window-size=1280,900',
  'about:blank'
], { stdio: 'ignore' });

let port = null;
for (let i = 0; i < 150 && !port; i++) {
  await new Promise(r => setTimeout(r, 100));
  try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
}

const ver = await (await fetch(`http://127.0.0.1:${port}/json/version`)).json();
const ws = new WebSocket(ver.webSocketDebuggerUrl);
await new Promise((r, j) => { ws.addEventListener('open', r, { once: true }); ws.addEventListener('error', j, { once: true }); });

let id = 0;
const pend = new Map();
const consoleLogs = [];

ws.addEventListener('message', e => {
  const m = JSON.parse(e.data);
  if (m.method === 'Runtime.consoleAPICalled') {
    consoleLogs.push({ type: m.params.type, args: m.params.args.map(a => a.value || a.description) });
  } else if (m.method === 'Runtime.exceptionThrown') {
    consoleLogs.push({ type: 'exception', text: m.params.exceptionDetails.text, exception: m.params.exceptionDetails.exception?.description });
  }
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
await S('Console.enable');

await S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
await new Promise(r => setTimeout(r, 3000));

const domInfo = await ev(`(() => {
  const wrapper = document.querySelector('#desktop-hero-3d-wrapper');
  const canvas = document.querySelector('#desktop-hero-3d-canvas');
  return {
    hasWrapper: !!wrapper,
    wrapperSize: wrapper ? { w: wrapper.clientWidth, h: wrapper.clientHeight } : null,
    hasCanvas: !!canvas,
    canvasSize: canvas ? { w: canvas.clientWidth, h: canvas.clientHeight, width: canvas.width, height: canvas.height } : null,
    scripts: Array.from(document.querySelectorAll('script')).map(s => s.src)
  };
})()`);

console.log('DOM Info:', JSON.stringify(domInfo, null, 2));
console.log('Console logs:', JSON.stringify(consoleLogs, null, 2));

ws.close();
child.kill();
fs.rmSync(profileDir, { recursive: true, force: true });
