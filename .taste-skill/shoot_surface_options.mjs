import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const cssPath = 'resources/css/app.css';
let css = fs.readFileSync(cssPath, 'utf8');

const optDir = 'shots/options';
fs.mkdirSync(optDir, { recursive: true });

function setSurface(base, card) {
  css = css.replace(/--color-surface-base:\s*#[a-fA-F0-9]+;/, `--color-surface-base: ${base};`);
  css = css.replace(/--color-surface-card:\s*#[a-fA-F0-9]+;/, `--color-surface-card: ${card};`);
  fs.writeFileSync(cssPath, css);
  execSync('npm run build', { stdio: 'inherit' });
}

function shoot(w, out) {
  console.log(`Shooting ${out} @ ${w}px...`);
  execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "http://localhost:8000/mobile/beranda" --w ${w} --out "${out}" --no-tiles --wait 600`, {
    stdio: 'inherit',
    env: { ...process.env, no_proxy: '*', NO_PROXY: '*' }
  });
}

// Option 1: Custard (#f7f2d1 / #fefdf6)
console.log('\n--- CAPTURING OPTION 1 (CUSTARD: base #f7f2d1, card #fefdf6) ---');
setSurface('#f7f2d1', '#fefdf6');
shoot(1280, `${optDir}/opt1-beranda-1280.png`);
shoot(1440, `${optDir}/opt1-beranda-1440.png`);

// Option 2: Linen (#f8f4d7 / #fefdf9)
console.log('\n--- CAPTURING OPTION 2 (LINEN: base #f8f4d7, card #fefdf9) ---');
setSurface('#f8f4d7', '#fefdf9');
shoot(1280, `${optDir}/opt2-beranda-1280.png`);
shoot(1440, `${optDir}/opt2-beranda-1440.png`);

// Copy to artifact directory
const artifactDir = '/Users/mc/.gemini/antigravity/brain/b3cd2572-7e6e-40c4-bc6e-d1babb007818/shots/options';
fs.mkdirSync(artifactDir, { recursive: true });
for (const f of fs.readdirSync(optDir)) {
  if (f.endsWith('.png')) {
    fs.copyFileSync(path.join(optDir, f), path.join(artifactDir, f));
  }
}
console.log('Saved all option screenshots to artifact directory:', artifactDir);
