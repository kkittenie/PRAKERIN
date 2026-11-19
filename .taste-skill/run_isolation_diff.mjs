import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';
import { execSync } from 'node:child_process';

/* --- PNG decode / encode from shoot.mjs --- */
function decodePNG(buf) {
  let pos = 8, w = 0, h = 0, type = 0;
  const idat = [];
  while (pos < buf.length) {
    const len = buf.readUInt32BE(pos), name = buf.toString('ascii', pos + 4, pos + 8), data = buf.subarray(pos + 8, pos + 8 + len);
    if (name === 'IHDR') { w = data.readUInt32BE(0); h = data.readUInt32BE(4); type = data[9]; if (data[8] !== 8 || data[12]) throw new Error('png: unsupported format'); }
    else if (name === 'IDAT') idat.push(data);
    pos += 12 + len;
  }
  const bpp = type === 6 ? 4 : type === 2 ? 3 : 0;
  if (!bpp) throw new Error('png: colour type ' + type);
  const raw = zlib.inflateSync(Buffer.concat(idat)), stride = w * bpp, px = Buffer.alloc(w * h * 4);
  let prev = Buffer.alloc(stride);
  for (let y = 0; y < h; y++) {
    const f = raw[y * (stride + 1)], line = Buffer.from(raw.subarray(y * (stride + 1) + 1, (y + 1) * (stride + 1)));
    for (let i = 0; i < stride; i++) {
      const a = i >= bpp ? line[i - bpp] : 0, b = prev[i], c = i >= bpp ? prev[i - bpp] : 0;
      let v = line[i];
      if (f === 1) v += a; else if (f === 2) v += b; else if (f === 3) v += (a + b) >> 1;
      else if (f === 4) { const p = a + b - c, pa = Math.abs(p - a), pb = Math.abs(p - b), pc = Math.abs(p - c); v += pa <= pb && pa <= pc ? a : pb <= pc ? b : c; }
      line[i] = v & 255;
    }
    for (let x = 0; x < w; x++) {
      const o = (y * w + x) * 4;
      px[o] = line[x * bpp]; px[o + 1] = line[x * bpp + 1]; px[o + 2] = line[x * bpp + 2]; px[o + 3] = bpp === 4 ? line[x * bpp + 3] : 255;
    }
    prev = line;
  }
  return { w, h, px };
}

function encodePNG(w, h, px) {
  const raw = Buffer.alloc((w * 4 + 1) * h);
  for (let y = 0; y < h; y++) px.copy(raw, y * (w * 4 + 1) + 1, y * w * 4, (y + 1) * w * 4);
  const chunk = (name, data) => {
    const len = Buffer.alloc(4); len.writeUInt32BE(data.length);
    const body = Buffer.concat([Buffer.from(name, 'ascii'), data]);
    const crc = Buffer.alloc(4); crc.writeUInt32BE(zlib.crc32(body) >>> 0);
    return Buffer.concat([len, body, crc]);
  };
  const ihdr = Buffer.alloc(13); ihdr.writeUInt32BE(w, 0); ihdr.writeUInt32BE(h, 4); ihdr[8] = 8; ihdr[9] = 6;
  return Buffer.concat([Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]), chunk('IHDR', ihdr), chunk('IDAT', zlib.deflateSync(raw, { level: 6 })), chunk('IEND', Buffer.alloc(0))]);
}

const targets = [
  // Mobile 375
  { key: 'mobile-beranda-375', url: 'http://localhost:8000/mobile/beranda', w: 375, scope: 'mobile-375' },
  { key: 'mobile-menu-375', url: 'http://localhost:8000/mobile/menu', w: 375, scope: 'mobile-375' },
  { key: 'mobile-produk-375', url: 'http://localhost:8000/mobile/produk/1', w: 375, scope: 'mobile-375' },
  { key: 'mobile-checkout-375', url: 'http://localhost:8000/mobile/checkout', w: 375, scope: 'mobile-375' },
  { key: 'mobile-login-375', url: 'http://localhost:8000/mobile/login', w: 375, scope: 'mobile-375' },

  // Tablet 768
  { key: 'tablet-beranda-768', url: 'http://localhost:8000/mobile/beranda', w: 768, scope: 'tablet-768' },
  { key: 'tablet-menu-768', url: 'http://localhost:8000/mobile/menu', w: 768, scope: 'tablet-768' },
  { key: 'tablet-produk-768', url: 'http://localhost:8000/mobile/produk/1', w: 768, scope: 'tablet-768' },
  { key: 'tablet-checkout-768', url: 'http://localhost:8000/mobile/checkout', w: 768, scope: 'tablet-768' },
  { key: 'tablet-login-768', url: 'http://localhost:8000/mobile/login', w: 768, scope: 'tablet-768' },

  // Admin 1280
  { key: 'admin-login-1280', url: 'http://localhost:8000/admin/login', w: 1280, scope: 'admin-1280' },
  { key: 'admin-dashboard-1280', url: 'http://localhost:8000/admin', w: 1280, profile: '.taste-skill/chrome-admin', scope: 'admin-1280' },
  { key: 'admin-crud-produk-1280', url: 'http://localhost:8000/produk', w: 1280, profile: '.taste-skill/chrome-admin', scope: 'admin-1280' },
  { key: 'admin-kasir-1280', url: 'http://localhost:8000/transaksi', w: 1280, profile: '.taste-skill/chrome-admin', scope: 'admin-1280' }
];

const mode = process.argv[2] || 'all'; // 'after', 'before', 'compare', 'all'

function shootTarget(t, dir) {
  const out = path.join('shots', 'diff', dir, `${t.key}.png`);
  fs.mkdirSync(path.dirname(out), { recursive: true });
  const profCmd = t.profile ? `--profile ${t.profile}` : '';
  console.log(`[${dir.toUpperCase()}] Shooting ${t.key} (${t.w}px) -> ${out}...`);
  try {
    execSync(`node .agents/skills/design-taste-frontend/scripts/shoot.mjs --url "${t.url}" --w ${t.w} --out "${out}" ${profCmd} --no-tiles --wait 600`, {
      stdio: 'inherit',
      env: { ...process.env, no_proxy: '*', NO_PROXY: '*' }
    });
  } catch (e) {
    console.error(`Failed ${t.key}:`, e.message);
  }
}

async function run() {
  if (mode === 'after' || mode === 'all') {
    console.log('\n=== STEP 1: CAPTURING CURRENT (AFTER) STATE ===');
    execSync('node .taste-skill/admin_login.mjs', { stdio: 'inherit' });
    for (const t of targets) {
      shootTarget(t, 'after');
    }
  }

  if (mode === 'before' || mode === 'all') {
    console.log('\n=== STEP 2: STASHING CHANGES & REBUILDING BASELINE ===');
    execSync('git stash save "temp-diff-baseline"', { stdio: 'inherit' });
    try {
      execSync('npm run build', { stdio: 'inherit' });
      execSync('node .taste-skill/admin_login.mjs', { stdio: 'inherit' });
      for (const t of targets) {
        shootTarget(t, 'before');
      }
    } finally {
      console.log('\n=== RESTORING REBRAND BRANCH CHANGES ===');
      execSync('git stash pop', { stdio: 'inherit' });
      execSync('npm run build', { stdio: 'inherit' });
      execSync('node .taste-skill/admin_login.mjs', { stdio: 'inherit' });
    }
  }

  if (mode === 'compare' || mode === 'all') {
    console.log('\n=== STEP 3: COMPUTING EXACT PIXEL DIFF (BEFORE vs AFTER) ===');
    const results = [];
    const diffDir = path.join('shots', 'diff', 'diffs');
    fs.mkdirSync(diffDir, { recursive: true });

    for (const t of targets) {
      const beforeFile = path.join('shots', 'diff', 'before', `${t.key}.png`);
      const afterFile = path.join('shots', 'diff', 'after', `${t.key}.png`);
      const diffOutFile = path.join(diffDir, `diff-${t.key}.png`);

      if (!fs.existsSync(beforeFile) || !fs.existsSync(afterFile)) {
        results.push({ key: t.key, scope: t.scope, status: 'MISSING_FILE', totalPx: 0, diffPx: 0, pct: 'N/A' });
        continue;
      }

      const imgA = decodePNG(fs.readFileSync(beforeFile));
      const imgB = decodePNG(fs.readFileSync(afterFile));

      const w = Math.min(imgA.w, imgB.w);
      const h = Math.min(imgA.h, imgB.h);
      const maxH = Math.max(imgA.h, imgB.h);
      const maxW = Math.max(imgA.w, imgB.w);
      const totalPixels = maxW * maxH;

      let diffCount = 0;
      const diffPx = Buffer.alloc(maxW * maxH * 4);

      for (let y = 0; y < maxH; y++) {
        for (let x = 0; x < maxW; x++) {
          const o = (y * maxW + x) * 4;
          if (x >= w || y >= h) {
            // Dimension mismatch area
            diffCount++;
            diffPx[o] = 255; diffPx[o + 1] = 0; diffPx[o + 2] = 255; diffPx[o + 3] = 255; // Magenta
            continue;
          }

          const oA = (y * imgA.w + x) * 4;
          const oB = (y * imgB.w + x) * 4;

          const dr = Math.abs(imgA.px[oA] - imgB.px[oB]);
          const dg = Math.abs(imgA.px[oA + 1] - imgB.px[oB + 1]);
          const db = Math.abs(imgA.px[oA + 2] - imgB.px[oB + 2]);
          const da = Math.abs(imgA.px[oA + 3] - imgB.px[oB + 3]);

          // Exact pixel match: any channel difference > 0
          if (dr > 0 || dg > 0 || db > 0 || da > 0) {
            diffCount++;
            diffPx[o] = 255; diffPx[o + 1] = 0; diffPx[o + 2] = 255; diffPx[o + 3] = 255; // Highlight diff in Magenta
          } else {
            // Identical: subtle dimmed grayscale
            const lum = Math.round(0.299 * imgA.px[oA] + 0.587 * imgA.px[oA + 1] + 0.114 * imgA.px[oA + 2]);
            const dim = Math.round(lum * 0.4);
            diffPx[o] = dim; diffPx[o + 1] = dim; diffPx[o + 2] = dim; diffPx[o + 3] = 255;
          }
        }
      }

      fs.writeFileSync(diffOutFile, encodePNG(maxW, maxH, diffPx));
      const diffPct = ((diffCount / totalPixels) * 100).toFixed(4);
      const isIdentical = diffCount === 0;

      results.push({
        key: t.key,
        scope: t.scope,
        width: `${maxW}x${maxH}`,
        totalPx: totalPixels,
        diffPx: diffCount,
        pct: `${diffPct}%`,
        status: isIdentical ? 'IDENTICAL (100% MATCH)' : `${diffCount} px diff`
      });
    }

    console.log('\n============================================================');
    console.log('PIXEL DIFF SUMMARY TABLE');
    console.log('============================================================');
    console.table(results);
    fs.writeFileSync('shots/diff/results.json', JSON.stringify(results, null, 2));
  }
}

run().catch(e => { console.error('Diff error:', e); process.exit(1); });
