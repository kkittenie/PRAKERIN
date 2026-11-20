import puppeteer from 'puppeteer-core';
import fs from 'fs';

const html = `
<!DOCTYPE html>
<html>
<head>
<style>
  body { margin: 0; background: #FAF5E8; }
  .wrapper { width: 1440px; height: 500px; position: relative; background: #FAF5E8; overflow: hidden; }
  .header-canopy { background: #3C2415; width: 1440px; height: 180px; }
  svg { display: block; width: 1440px; height: 260px; margin-top: -1px; }
  .stamp {
    position: absolute;
    left: 720px;
    top: 310px;
    transform: translate(-50%, -50%);
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: #FAF5E8;
    border: 2px solid rgba(60,36,21,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
    font-size: 11px;
    color: #3C2415;
    font-weight: bold;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  }
  .cake {
    position: absolute;
    left: 720px;
    top: 150px;
    transform: translate(-50%, -50%);
    width: 480px;
    filter: drop-shadow(0 25px 35px rgba(25,12,6,0.5));
  }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header-canopy"></div>
  <svg viewBox="0 0 1440 260" fill="none">
    <!-- Melting Wave:
         Starts at (0, 0)
         (0, 70)
         Trough 1: dips down to ~95 at x=140
         Crest 1: rises to ~55 at x=340
         Deep Center Scoop: plunges down to ~220 at x=720 with wide rounded bottom
         Crest 2: rises to ~55 at x=1100
         Trough 2: dips down to ~95 at x=1300
         (1440, 70)
         (1440, 0) Z
    -->
    <path d="M 0 0 
             L 1440 0 
             L 1440 70 
             C 1370 70, 1340 100, 1280 100 
             C 1200 100, 1160 55, 1080 55 
             C 960 55, 870 230, 720 230 
             C 570 230, 480 55, 360 55 
             C 280 55, 240 100, 160 100 
             C 100 100, 70 70, 0 70 
             Z" fill="#3C2415"/>
  </svg>
  <img class="cake" src="file://${process.cwd()}/public/images/decor/vintage-strawberry-cake.png" />
  <div class="stamp">DISCOVER</div>
</div>
</body>
</html>
`;

fs.writeFileSync('.taste-skill/test_melt.html', html);

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 600, deviceScaleFactor: 1 });
  await page.goto(`file://${process.cwd()}/.taste-skill/test_melt.html`, { waitUntil: 'networkidle0' });
  await page.screenshot({ path: 'shots/menu/test-melt-render.png' });
  await browser.close();
  console.log('Done test melt render');
}
run();
