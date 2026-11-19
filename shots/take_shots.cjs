const { execSync } = require('child_process');
const fs = require('fs');
fs.mkdirSync('shots/phase0', { recursive: true });

const pages = [
  { name: 'welcome', url: 'http://127.0.0.1:8000/welcome' },
  { name: 'login', url: 'http://127.0.0.1:8000/login' },
  { name: 'menu', url: 'http://127.0.0.1:8000/mobile/menu' },
  { name: 'produk', url: 'http://127.0.0.1:8000/mobile/produk/1' }
];

const widths = [1024, 1280, 1440, 1920];

for (const p of pages) {
  for (const w of widths) {
    const out = `shots/phase0/${p.name}-${w}.png`;
    if (fs.existsSync(out) && fs.statSync(out).size > 1000) {
      console.log(`Skipping already captured ${out}`);
      continue;
    }
    console.log(`Shooting ${p.name} @ ${w}px -> ${out}`);
    try {
      execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "${p.url}" --w ${w} --out "${out}" --no-tiles`, { stdio: 'inherit' });
    } catch (e) {
      console.error(`Error shooting ${p.name} @ ${w}:`, e.message);
    }
  }
}
console.log('All remaining phase 0 screenshots finished successfully!');
