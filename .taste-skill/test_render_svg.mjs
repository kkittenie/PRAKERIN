import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function run() {
  const profileDir = path.join('/tmp', `chrome-test-${Date.now()}`);
  fs.mkdirSync(profileDir, { recursive: true });

  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileDir}`,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--disable-extensions',
    '--window-size=1440,600',
    'about:blank'
  ], { stdio: 'ignore' });

  let port = null;
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  for (let i = 0; i < 150 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }

  const ver = await (await fetch(`http://127.0.0.1:${port}/json/version`)).json();
  const ws = new WebSocket(ver.webSocketDebuggerUrl);
  let id = 1;
  const send = (method, params = {}) => new Promise((resolve) => {
    const curId = id++;
    const handler = (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id === curId) {
        ws.removeEventListener('message', handler);
        resolve(msg.result);
      }
    };
    ws.addEventListener('message', handler);
    ws.send(JSON.stringify({ id: curId, method, params }));
  });

  await new Promise(r => ws.addEventListener('open', r));
  await send('Page.enable');

  const fileUrl = `file://${process.cwd()}/shots/menu/melt_svg_test.svg`;
  await send('Page.navigate', { url: fileUrl });
  await new Promise(r => setTimeout(r, 800));

  const res = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('shots/menu/melt_svg_test.png', Buffer.from(res.data, 'base64'));

  try { child.kill(); } catch {}
  console.log('Saved melt_svg_test.png');
}
run();
