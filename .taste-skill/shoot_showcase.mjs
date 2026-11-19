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
  await S('Network.enable');

  return { child, ws, S, ev };
}

async function captureView(S, ev, width, outName) {
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: 900,
    deviceScaleFactor: 1,
    mobile: false
  });
  // Wait for canvas to be ready
  for (let i = 0; i < 30; i++) {
    const ready = await ev('!!window.__heroShowcase');
    if (ready) break;
    await new Promise(r => setTimeout(r, 100));
  }
  await new Promise(r => setTimeout(r, 800)); // wait for layout and webgl render

  // Hero showcase closeup (captured at standard viewport)
  const showcaseRect = await ev(`(() => {
    const el = document.getElementById('desktop-hero-brand-emblem') || document.getElementById('desktop-hero-showcase');
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height };
  })()`);

  if (showcaseRect && showcaseRect.width > 0) {
    const { data: cropData } = await S('Page.captureScreenshot', {
      format: 'png',
      clip: {
        x: Math.max(0, showcaseRect.x - 16),
        y: Math.max(0, showcaseRect.y - 16),
        width: showcaseRect.width + 32,
        height: showcaseRect.height + 32,
        scale: 1
      }
    });
    const cropPath = path.join(outDir, `${outName}-showcase.png`);
    fs.writeFileSync(cropPath, Buffer.from(cropData, 'base64'));
    fs.copyFileSync(cropPath, path.join(artifactDir, `${outName}-showcase.png`));
    console.log(`Saved showcase closeup: ${cropPath}`);
  }

  // Navbar closeup
  const navbarRect = await ev(`(() => {
    const el = document.querySelector('header.hidden.lg\\\\:flex');
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height };
  })()`);
  if (navbarRect && navbarRect.width > 0) {
    const { data: navData } = await S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: navbarRect.x, y: navbarRect.y, width: navbarRect.width, height: navbarRect.height, scale: 1 }
    });
    const navPath = path.join(outDir, `${outName}-navbar.png`);
    fs.writeFileSync(navPath, Buffer.from(navData, 'base64'));
    fs.copyFileSync(navPath, path.join(artifactDir, `${outName}-navbar.png`));
    console.log(`Saved navbar closeup: ${navPath}`);
  }

  // Trigger all scroll animations and expand viewport for full-page screenshot
  await ev(`(() => {
    document.querySelectorAll('.scroll-fade-up, .desktop-fade-up').forEach(el => {
      el.style.opacity = '1';
      el.style.transform = 'none';
      el.classList.add('visible', 'is-visible');
    });
  })()`);

  const fullHeight = await ev(`(() => {
    const footer = document.querySelector('footer.hidden.lg\\\\:block') || document.querySelector('footer');
    if (footer) {
      const r = footer.getBoundingClientRect();
      return Math.ceil(r.bottom + window.scrollY);
    }
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
  })()`);
  await S('Emulation.setDeviceMetricsOverride', {
    width,
    height: fullHeight,
    deviceScaleFactor: 1,
    mobile: false
  });
  await new Promise(r => setTimeout(r, 600));

  // Footer closeup (captured after full height is active)
  const footerRect = await ev(`(() => {
    const el = document.querySelector('footer.hidden.lg\\\\:block');
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return { x: r.left, y: r.top, width: r.width, height: r.height };
  })()`);
  if (footerRect && footerRect.width > 0) {
    const { data: footData } = await S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: footerRect.x, y: footerRect.y, width: footerRect.width, height: footerRect.height, scale: 1 }
    });
    const footPath = path.join(outDir, `${outName}-footer.png`);
    fs.writeFileSync(footPath, Buffer.from(footData, 'base64'));
    fs.copyFileSync(footPath, path.join(artifactDir, `${outName}-footer.png`));
    console.log(`Saved footer closeup: ${footPath}`);
  }

  try {
    const res = await S('Page.captureScreenshot', {
      format: 'png',
      captureBeyondViewport: true
    });
    if (res && res.data) {
      const savePath = path.join(outDir, `${outName}.png`);
      fs.writeFileSync(savePath, Buffer.from(res.data, 'base64'));
      fs.copyFileSync(savePath, path.join(artifactDir, `${outName}.png`));
      console.log(`Saved screenshot: ${savePath}`);
    }
  } catch (err) {
    console.warn(`Could not capture full screenshot for ${outName}:`, err.message);
  }

  // Restore 900 height for subsequent operations
  await S('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });

  // Measure hero section height
  const heroHeight = await ev(`(() => {
    const hero = document.querySelector('div.hidden.lg\\\\:block section');
    return hero ? hero.getBoundingClientRect().height : null;
  })()`);
  console.log(`[${outName} ${width}px] Hero section height: ${heroHeight}px`);
}

async function run() {
  console.log('=== Capturing Guest 1280 & 1920 ===');
  const guest = await launchChrome('.taste-skill/chrome-guest-showcase');
  try {
    // Set 1280px BEFORE navigation so matchMedia(min-width: 1024px) is true on DOMContentLoaded
    await guest.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await guest.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 2500));

    await captureView(guest.S, guest.ev, 1280, 'beranda-guest-1280');
    await captureView(guest.S, guest.ev, 1920, 'beranda-guest-1920');

    // Capture transition frames (dissolve liquid noise progress t=0.25, 0.50, 0.75)
    console.log('=== Capturing Transition Dissolve Frames ===');
    await guest.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    
    // Wait for __heroShowcase to be ready
    let showcaseReady = false;
    for (let i = 0; i < 40; i++) {
      showcaseReady = await guest.ev('!!window.__heroShowcase');
      if (showcaseReady) break;
      await new Promise(r => setTimeout(r, 200));
    }
    console.log('Showcase hook ready:', showcaseReady);

    if (showcaseReady) {
      // Setup transition next slide
      await guest.ev('window.__heroShowcase.triggerNextSlide()');
      
      for (const t of [0.25, 0.50, 0.75]) {
        await guest.ev(`window.__heroShowcase.setTransitionProgress(${t})`);
        await new Promise(r => setTimeout(r, 150));

        const showcaseRect = await guest.ev(`(() => {
          const el = document.getElementById('desktop-hero-showcase');
          if (!el) return null;
          const r = el.getBoundingClientRect();
          return { x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height };
        })()`);

        if (showcaseRect) {
          const { data: frameData } = await guest.S('Page.captureScreenshot', {
            format: 'png',
            clip: {
              x: Math.max(0, showcaseRect.x - 16),
              y: Math.max(0, showcaseRect.y - 16),
              width: showcaseRect.width + 32,
              height: showcaseRect.height + 32,
              scale: 1
            }
          });
          const frameName = `hero-transition-frame-t${Math.round(t * 100)}`;
          fs.writeFileSync(path.join(outDir, `${frameName}.png`), Buffer.from(frameData, 'base64'));
          fs.copyFileSync(path.join(outDir, `${frameName}.png`), path.join(artifactDir, `${frameName}.png`));
          console.log(`Saved transition frame: ${frameName}.png`);
        }
      }
      await guest.ev('window.__heroShowcase.completeTransition()');
    }

    // Capture Fallback (prefers-reduced-motion)
    console.log('=== Capturing Fallback (prefers-reduced-motion) ===');
    await guest.S('Emulation.setEmulatedMedia', {
      features: [{ name: 'prefers-reduced-motion', value: 'reduce' }]
    });
    await guest.S('Page.reload');
    await new Promise(r => setTimeout(r, 2000));
    const { data: fallbackData } = await guest.S('Page.captureScreenshot', {
      format: 'png',
      clip: { x: 0, y: 0, width: 1280, height: 900, scale: 1 }
    });
    fs.writeFileSync(path.join(outDir, 'hero-fallback-reduced-motion-1280.png'), Buffer.from(fallbackData, 'base64'));
    fs.copyFileSync(path.join(outDir, 'hero-fallback-reduced-motion-1280.png'), path.join(artifactDir, 'hero-fallback-reduced-motion-1280.png'));
    console.log('Saved fallback screenshot: hero-fallback-reduced-motion-1280.png');

  } finally {
    guest.ws.close();
    guest.child.kill();
  }

  console.log('=== Capturing Member 1280 & 1920 ===');
  const member = await launchChrome('.taste-skill/chrome-member-showcase');
  try {
    await member.S('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    // Login as member
    await member.S('Page.navigate', { url: 'http://127.0.0.1:8000/login' });
    await new Promise(r => setTimeout(r, 1500));
    await member.ev(`(() => {
      const idInput = document.querySelector('input[name="identifier"]');
      const passInput = document.querySelector('input[name="password"]');
      const form = document.querySelector('form');
      if (idInput) idInput.value = 'sofi';
      if (passInput) passInput.value = 'password123';
      if (form) {
        form.action = '/login';
        form.submit();
      }
    })()`);
    await new Promise(r => setTimeout(r, 2500));

    await member.S('Page.navigate', { url: 'http://127.0.0.1:8000/mobile/beranda' });
    await new Promise(r => setTimeout(r, 2500));

    await captureView(member.S, member.ev, 1280, 'beranda-member-1280');
    await captureView(member.S, member.ev, 1920, 'beranda-member-1920');
  } finally {
    member.ws.close();
    member.child.kill();
  }

  console.log('All screenshots completed successfully!');
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
