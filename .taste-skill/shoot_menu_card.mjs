import fs from "node:fs";
import path from "node:path";
import { spawn } from "node:child_process";

const chromePath =
  "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
const profileDir = path.join("/tmp", `chrome-menu-card-test-${Date.now()}`);
const outDir = path.join(process.cwd(), "shots", "v2_verify");
fs.mkdirSync(profileDir, { recursive: true });
fs.mkdirSync(outDir, { recursive: true });

const child = spawn(
  chromePath,
  [
    "--headless=new",
    `--user-data-dir=${profileDir}`,
    "--remote-debugging-port=0",
    "--no-first-run",
    "--no-default-browser-check",
    "--disable-extensions",
    "--window-size=1440,1000",
    "about:blank",
  ],
  { stdio: "ignore" },
);

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function main() {
  try {
    const portFile = path.join(profileDir, "DevToolsActivePort");
    let port = null;
    for (let i = 0; i < 120 && !port; i++) {
      await sleep(100);
      try {
        port = parseInt(fs.readFileSync(portFile, "utf8"), 10) || null;
      } catch {}
    }
    if (!port) throw new Error("Chrome did not start");

    const ver = await (
      await fetch(`http://127.0.0.1:${port}/json/version`)
    ).json();
    const ws = new WebSocket(ver.webSocketDebuggerUrl);
    await new Promise((r) => ws.addEventListener("open", r, { once: true }));

    let id = 0;
    const pend = new Map();
    ws.addEventListener("message", (e) => {
      const m = JSON.parse(e.data);
      if (m.id && pend.has(m.id)) {
        pend.get(m.id)(m);
        pend.delete(m.id);
      }
    });

    const send = (method, params = {}, sessionId) =>
      new Promise((resolve, reject) => {
        const i = ++id;
        const timeout = setTimeout(() => {
          pend.delete(i);
          reject(new Error(`Timeout on ${method}`));
        }, 35000);
        pend.set(i, (res) => {
          clearTimeout(timeout);
          resolve(res);
        });
        ws.send(JSON.stringify({ id: i, method, params, sessionId }));
      });

    const {
      result: { targetId },
    } = await send("Target.createTarget", { url: "about:blank" });
    const {
      result: { sessionId },
    } = await send("Target.attachToTarget", { targetId, flatten: true });
    const S = (m, p) => send(m, p, sessionId).then((r) => r.result);

    await S("Page.enable");
    await S("Runtime.enable");
    await S("Emulation.setDeviceMetricsOverride", {
      width: 1440,
      height: 1000,
      deviceScaleFactor: 2,
      mobile: false,
    });

    console.log("Navigating to /mobile/menu...");
    await S("Page.navigate", { url: "http://127.0.0.1:8000/mobile/menu" });
    await sleep(2000);

    // Dismiss wave loader immediately for clean screenshot
    await S("Runtime.evaluate", {
      expression: `(() => {
        const loader = document.getElementById('daisy-wave-loader');
        if (loader) loader.remove();
        window.scrollTo({ top: 2600, behavior: 'instant' });
      })()`,
    });
    await sleep(1500);

    // Scroll down to cards section
    await S("Runtime.evaluate", {
      expression: `window.scrollTo({ top: 2800, behavior: 'instant' });`,
    });
    await sleep(1000);

    const shot = await S("Page.captureScreenshot", { format: "png" });
    const buf = Buffer.from(shot.data, "base64");
    const outFile = path.join(outDir, "menu-card-no-white-bg.png");
    fs.writeFileSync(outFile, buf);
    console.log("Saved screenshot to:", outFile);

    // Also take a screenshot focused directly on the card
    const cardRect = await S("Runtime.evaluate", {
      expression: `(() => {
        const card = document.querySelector('[data-produk-id="15"]') || document.querySelector('.group\\/card')?.closest('.rounded-\\[32px\\]');
        if (!card) return null;
        const r = card.getBoundingClientRect();
        return { x: r.x, y: r.y, width: r.width, height: r.height };
      })()`,
      returnByValue: true,
    });
    console.log("Card rect:", cardRect.result?.value);

    await send("Target.closeTarget", { targetId });
    ws.close();
  } finally {
    try {
      child.kill("SIGKILL");
    } catch {}
    try {
      fs.rmSync(profileDir, { recursive: true, force: true });
    } catch {}
  }
}

main().catch((err) => {
  console.error(err);
  try {
    child.kill("SIGKILL");
  } catch {}
  process.exit(1);
});
