const { execSync } = require('child_process');

const targets = [
  { url: 'http://localhost:8000/mobile/beranda', w: 1024, out: 'shots/phase2/beranda-1024.png' },
  { url: 'http://localhost:8000/mobile/beranda', w: 1280, out: 'shots/phase2/beranda-1280.png' },
  { url: 'http://localhost:8000/mobile/beranda', w: 1440, out: 'shots/phase2/beranda-1440.png' },
  { url: 'http://localhost:8000/mobile/beranda', w: 1920, out: 'shots/phase2/beranda-1920.png' },
  { url: 'http://localhost:8000/mobile/beranda', w: 375, out: 'shots/phase2/beranda-375-mobile.png' },
  { url: 'http://localhost:8000/mobile/beranda', w: 768, out: 'shots/phase2/beranda-768-tablet.png' },
];

for (const t of targets) {
  console.log(`Shooting ${t.url} @ ${t.w}px -> ${t.out}`);
  try {
    execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "${t.url}" --w ${t.w} --out "${t.out}" --no-tiles`, {
      stdio: 'inherit',
      env: { ...process.env, no_proxy: '*', NO_PROXY: '*' }
    });
  } catch (err) {
    console.error(`Failed ${t.out}:`, err.message);
  }
}
