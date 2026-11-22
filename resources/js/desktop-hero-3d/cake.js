import * as THREE from 'three';

/**
 * Daisy Bakery 3D Artisanal Vintage Lambeth Cake (Three.js)
 *
 * Photorealistic patisserie buttercream & blush-pink silk satin aesthetic:
 * - Procedural studio softbox PMREM environment lighting (realistic diffuse + gentle sheen, zero plastic glare)
 * - Aerated Swiss meringue vanilla-bean buttercream with micro-pore normal, roughness, and cake-scraper textures
 * - Clean smooth buttercream center top (no center strawberry, no center frosting swirl)
 * - Plump outer crown of 8 realistic pitted-seed strawberries with curled multi-sepal leafy calyxes & soft blush-pink rosebuds
 * - Sculpted silk satin bows in delicate baby/blush pink (#F6CED9) with wrapped fabric pinch knot, volumetric folded loops,
 *   lengthwise crease detail, and dovetail-notched draped streamers (no pink frosting between bows)
 * - Plate-free floating cake silhouette with soft grounded ambient contact shadow
 * - 360° interactive drag-to-spin, momentum inertia, gentle cursor tilt, and subtle idle bobbing
 */

export function initHero3DCake(wrapper) {
  if (!wrapper || !window.matchMedia('(min-width: 1024px)').matches) return;

  const canvas = wrapper.querySelector('#desktop-hero-3d-canvas') || wrapper.querySelector('canvas');
  if (!canvas) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  let width = wrapper.clientWidth || 460;
  let height = wrapper.clientHeight || 400;

  // Scene & Camera (calibrated FOV and distance to prevent any side or bottom clipping)
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(30, width / height, 0.1, 100);
  camera.position.set(0, 2.65, 9.2);
  camera.lookAt(0, 0.15, 0);

  // WebGL Renderer
  const renderer = new THREE.WebGLRenderer({
    canvas,
    antialias: true,
    alpha: true,
    powerPreference: 'high-performance'
  });
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.03;

  const disposables = [];
  function reg(res) {
    disposables.push(res);
    return res;
  }

  // ━━━━ PROCEDURAL WARM PATISSERIE SOFTBOX ENVIRONMENT (PHOTOREALISTIC PBR) ━━━━
  function buildSoftboxEnvironment() {
    try {
      const pmrem = new THREE.PMREMGenerator(renderer);
      const envScene = new THREE.Scene();
      envScene.background = new THREE.Color(0x2a1b14);

      // Overhead warm cream diffused softbox
      const topBox = new THREE.Mesh(
        new THREE.PlaneGeometry(16, 16),
        new THREE.MeshBasicMaterial({ color: 0xfff8eb, side: THREE.DoubleSide })
      );
      topBox.position.set(0, 8, 1);
      topBox.rotation.x = Math.PI / 2;
      envScene.add(topBox);

      // Warm sunlit window panel on right-front
      const keyPanel = new THREE.Mesh(
        new THREE.PlaneGeometry(10, 9),
        new THREE.MeshBasicMaterial({ color: 0xfff3de, side: THREE.DoubleSide })
      );
      keyPanel.position.set(7.5, 3.2, 5.5);
      keyPanel.lookAt(0, 0, 0);
      envScene.add(keyPanel);

      // Soft blush-warm bounce card on left
      const fillPanel = new THREE.Mesh(
        new THREE.PlaneGeometry(9, 8),
        new THREE.MeshBasicMaterial({ color: 0xfbe5d6, side: THREE.DoubleSide })
      );
      fillPanel.position.set(-7.5, 1.5, 4.0);
      fillPanel.lookAt(0, 0, 0);
      envScene.add(fillPanel);

      // Subtle rim strip behind
      const rimPanel = new THREE.Mesh(
        new THREE.PlaneGeometry(12, 4),
        new THREE.MeshBasicMaterial({ color: 0xffeeda, side: THREE.DoubleSide })
      );
      rimPanel.position.set(0, 4.5, -8.5);
      rimPanel.lookAt(0, 0, 0);
      envScene.add(rimPanel);

      const envRT = pmrem.fromScene(envScene, 0.05);
      pmrem.dispose();
      reg(envRT);
      return envRT.texture;
    } catch {
      return null;
    }
  }

  const softboxEnv = buildSoftboxEnvironment();
  if (softboxEnv) {
    scene.environment = softboxEnv;
    scene.environmentIntensity = 0.52;
  }

  // ━━━━ NATURAL PATISSERIE STUDIO LIGHTING ━━━━
  const hemiLight = new THREE.HemisphereLight(0xfff9f0, 0x5c3e30, 0.72);
  scene.add(hemiLight);

  const keyLight = new THREE.DirectionalLight(0xfff7e8, 1.55);
  keyLight.position.set(4.5, 6.2, 4.8);
  scene.add(keyLight);

  const fillLight = new THREE.DirectionalLight(0xf9e6cf, 0.58);
  fillLight.position.set(-4.6, 1.4, 3.6);
  scene.add(fillLight);

  const rimLight = new THREE.DirectionalLight(0xfff1e2, 0.78);
  rimLight.position.set(1.8, 3.8, -5.0);
  scene.add(rimLight);

  // Root Cake Group (floats cleanly without plate, perfectly scaled with ample side and bottom margins)
  const cakeGroup = new THREE.Group();
  cakeGroup.scale.setScalar(0.76);
  cakeGroup.position.y = 0.28;
  scene.add(cakeGroup);

  // ━━━━ NOISE HELPERS FOR REALISTIC PROCEDURAL TEXTURES ━━━━
  function hash2(x, y) {
    const n = Math.sin(x * 127.1 + y * 311.7) * 43758.5453123;
    return n - Math.floor(n);
  }

  function smoothNoise(x, y) {
    const ix = Math.floor(x);
    const iy = Math.floor(y);
    const fx = x - ix;
    const fy = y - iy;
    const wx = fx * fx * (3 - 2 * fx);
    const wy = fy * fy * (3 - 2 * fy);
    const a = hash2(ix, iy) * (1 - wx) + hash2(ix + 1, iy) * wx;
    const b = hash2(ix, iy + 1) * (1 - wx) + hash2(ix + 1, iy + 1) * wx;
    return a * (1 - wy) + b * wy;
  }

  function fbm(x, y) {
    return (
      smoothNoise(x, y) * 0.5 +
      smoothNoise(x * 2.1, y * 2.1) * 0.28 +
      smoothNoise(x * 4.3, y * 4.3) * 0.14 +
      smoothNoise(x * 8.7, y * 8.7) * 0.08
    );
  }

  // ━━━━ 1. WHIPPED SWISS MERINGUE BUTTERCREAM TEXTURES (NORMAL + ROUGHNESS + VANILLA BEAN ALBEDO) ━━━━
  function createButtercreamTextures({ isScraperSide = false, isTopSwirl = false, repeatX = 3, repeatY = 3 } = {}) {
    const size = 256;
    const heightMap = new Float32Array(size * size);

    const colCanvas = document.createElement('canvas');
    colCanvas.width = size;
    colCanvas.height = size;
    const colCtx = colCanvas.getContext('2d');
    const colImg = colCtx.createImageData(size, size);

    const roughCanvas = document.createElement('canvas');
    roughCanvas.width = size;
    roughCanvas.height = size;
    const roughCtx = roughCanvas.getContext('2d');
    const roughImg = roughCtx.createImageData(size, size);

    for (let y = 0; y < size; y++) {
      for (let x = 0; x < size; x++) {
        const u = x / size;
        const v = y / size;
        const idx = y * size + x;
        const pIdx = idx * 4;

        // Creamy aerated micro-pores + gentle spatula/scraper relief
        const macro = fbm(u * 10, v * 10);
        const micro = smoothNoise(u * 42, v * 42);
        const poreNoise = hash2(x * 0.85, y * 0.85);
        // Occasional tiny whipped air pinholes in real buttercream
        const airPore = poreNoise > 0.986 ? -0.35 * (poreNoise - 0.986) / 0.014 : 0;

        let scraper = 0;
        if (isScraperSide) {
          // Delicate horizontal bench-scraper rings around cake wall
          scraper = Math.sin(v * Math.PI * 38) * 0.055 + Math.sin(v * Math.PI * 15 + macro * 2) * 0.04;
        } else if (isTopSwirl) {
          // Delicate concentric offset-spatula swirl on top flat face
          const dx = u - 0.5;
          const dy = v - 0.5;
          const rad = Math.sqrt(dx * dx + dy * dy);
          scraper = Math.sin(rad * 72 + Math.atan2(dy, dx) * 1.0) * 0.055 * Math.min(1, rad * 4);
        } else {
          // Piped buttercream micro-grain
          scraper = Math.sin(u * Math.PI * 24 + macro * 2.5) * 0.04;
        }

        heightMap[idx] = macro * 0.45 + micro * 0.25 + scraper + airPore;

        // Subtle warm buttercream albedo tonal variation + ultra-fine vanilla bean specks
        const warmShift = (macro - 0.5) * 10 + scraper * 18;
        let r = 250 + warmShift;
        let g = 243 + warmShift * 0.95;
        let b = 229 + warmShift * 0.85;

        // Delicate tiny vanilla-bean flecks (very sparse & authentic)
        const speck = hash2(x * 1.73 + 11, y * 1.91 + 7);
        if (speck > 0.9988) {
          r -= 58;
          g -= 66;
          b -= 72;
        }

        colImg.data[pIdx] = Math.max(0, Math.min(255, r));
        colImg.data[pIdx + 1] = Math.max(0, Math.min(255, g));
        colImg.data[pIdx + 2] = Math.max(0, Math.min(255, b));
        colImg.data[pIdx + 3] = 255;

        // Matte-velvet buttercream roughness (0.54–0.78 -> pixel 138..199)
        const roughVal = Math.max(128, Math.min(215, 162 + (micro - 0.5) * 34 - airPore * 45));
        roughImg.data[pIdx] = roughVal;
        roughImg.data[pIdx + 1] = roughVal;
        roughImg.data[pIdx + 2] = roughVal;
        roughImg.data[pIdx + 3] = 255;
      }
    }

    colCtx.putImageData(colImg, 0, 0);
    roughCtx.putImageData(roughImg, 0, 0);

    // Convert heightMap -> tangent-space normal map
    const normCanvas = document.createElement('canvas');
    normCanvas.width = size;
    normCanvas.height = size;
    const normCtx = normCanvas.getContext('2d');
    const normImg = normCtx.createImageData(size, size);
    const strength = 2.6;

    for (let y = 0; y < size; y++) {
      const yPrev = (y - 1 + size) % size;
      const yNext = (y + 1) % size;
      for (let x = 0; x < size; x++) {
        const xPrev = (x - 1 + size) % size;
        const xNext = (x + 1) % size;

        const dx = (heightMap[y * size + xNext] - heightMap[y * size + xPrev]) * strength;
        const dy = (heightMap[yNext * size + x] - heightMap[yPrev * size + x]) * strength;
        const dz = 1.0;
        const len = Math.sqrt(dx * dx + dy * dy + dz * dz);

        const pIdx = (y * size + x) * 4;
        normImg.data[pIdx] = Math.floor(((dx / len) * 0.5 + 0.5) * 255);
        normImg.data[pIdx + 1] = Math.floor(((dy / len) * 0.5 + 0.5) * 255);
        normImg.data[pIdx + 2] = Math.floor(((dz / len) * 0.5 + 0.5) * 255);
        normImg.data[pIdx + 3] = 255;
      }
    }
    normCtx.putImageData(normImg, 0, 0);

    const map = reg(new THREE.CanvasTexture(colCanvas));
    map.colorSpace = THREE.SRGBColorSpace;
    map.wrapS = map.wrapT = THREE.RepeatWrapping;
    map.repeat.set(repeatX, repeatY);

    const normalMap = reg(new THREE.CanvasTexture(normCanvas));
    normalMap.wrapS = normalMap.wrapT = THREE.RepeatWrapping;
    normalMap.repeat.set(repeatX, repeatY);

    const roughnessMap = reg(new THREE.CanvasTexture(roughCanvas));
    roughnessMap.wrapS = roughnessMap.wrapT = THREE.RepeatWrapping;
    roughnessMap.repeat.set(repeatX, repeatY);

    return { map, normalMap, roughnessMap };
  }

  const sideCreamTex = createButtercreamTextures({ isScraperSide: true, repeatX: 3, repeatY: 1 });
  const topCreamTex = createButtercreamTextures({ isTopSwirl: true, repeatX: 1, repeatY: 1 });
  const pipingCreamTex = createButtercreamTextures({ repeatX: 3, repeatY: 3 });

  // ━━━━ 2. FINE SILK SATIN RIBBON WEAVE TEXTURE (BLUSH / BABY PINK) ━━━━
  function createSatinRibbonTextures() {
    const size = 256;
    const normCanvas = document.createElement('canvas');
    normCanvas.width = size;
    normCanvas.height = size;
    const nCtx = normCanvas.getContext('2d');
    const nImg = nCtx.createImageData(size, size);

    const roughCanvas = document.createElement('canvas');
    roughCanvas.width = size;
    roughCanvas.height = size;
    const rCtx = roughCanvas.getContext('2d');
    const rImg = rCtx.createImageData(size, size);

    for (let y = 0; y < size; y++) {
      for (let x = 0; x < size; x++) {
        // Fine diagonal satin twill weave + delicate woven selvedge edge
        const weave = Math.sin((x + y * 0.5) * 0.9) * 0.25 + Math.sin(x * 1.4) * 0.15;
        const pIdx = (y * size + x) * 4;
        const dx = Math.cos((x + y * 0.5) * 0.9) * 0.22;
        const dy = Math.sin(y * 0.6) * 0.12;
        const dz = 1.0;
        const len = Math.sqrt(dx * dx + dy * dy + dz * dz);

        nImg.data[pIdx] = Math.floor(((dx / len) * 0.5 + 0.5) * 255);
        nImg.data[pIdx + 1] = Math.floor(((dy / len) * 0.5 + 0.5) * 255);
        nImg.data[pIdx + 2] = Math.floor(((dz / len) * 0.5 + 0.5) * 255);
        nImg.data[pIdx + 3] = 255;

        // Subtle satin edge weave slightly matte, center silky
        const edgeDist = Math.min(x, size - 1 - x) / (size * 0.12);
        const selvedge = edgeDist < 1 ? 35 * (1 - edgeDist) : 0;
        const rv = Math.max(70, Math.min(185, 102 + weave * 20 + selvedge));
        rImg.data[pIdx] = rv;
        rImg.data[pIdx + 1] = rv;
        rImg.data[pIdx + 2] = rv;
        rImg.data[pIdx + 3] = 255;
      }
    }
    nCtx.putImageData(nImg, 0, 0);
    rCtx.putImageData(rImg, 0, 0);

    const normalMap = reg(new THREE.CanvasTexture(normCanvas));
    normalMap.wrapS = normalMap.wrapT = THREE.RepeatWrapping;
    normalMap.repeat.set(3, 12);

    const roughnessMap = reg(new THREE.CanvasTexture(roughCanvas));
    roughnessMap.wrapS = roughnessMap.wrapT = THREE.RepeatWrapping;
    roughnessMap.repeat.set(1, 6);

    return { normalMap, roughnessMap };
  }

  const satinTex = createSatinRibbonTextures();

  // ━━━━ 3. PHOTOREALISTIC STRAWBERRY ALBEDO + SEED-DIMPLE NORMAL MAP ━━━━
  function createStrawberryTextures() {
    const size = 1024;
    const cCanvas = document.createElement('canvas');
    cCanvas.width = size;
    cCanvas.height = size;
    const cCtx = cCanvas.getContext('2d');

    // Natural strawberry subsurface gradient: pale creamy calyx collar -> juicy ruby -> deep crimson apex
    const grad = cCtx.createLinearGradient(0, 0, 0, size);
    grad.addColorStop(0.00, '#F6EFE2');  // Pale creamy calyx junction
    grad.addColorStop(0.06, '#F8A8AC');  // Soft rosy blush transition
    grad.addColorStop(0.16, '#E01C2E');  // Vibrant fresh strawberry red
    grad.addColorStop(0.52, '#C41322');  // Juicy ruby heart
    grad.addColorStop(0.84, '#B00E1C');  // Deep berry crimson
    grad.addColorStop(1.00, '#820813');  // Rich apex ruby
    cCtx.fillStyle = grad;
    cCtx.fillRect(0, 0, size, size);

    // Subtle organic skin stippling & cellular variation
    for (let i = 0; i < 280; i++) {
      const mx = hash2(i, 3.1) * size;
      const my = hash2(i, 7.9) * size;
      const mr = 16 + hash2(i, 1.7) * 44;
      const mg = cCtx.createRadialGradient(mx, my, 2, mx, my, mr);
      mg.addColorStop(0, 'rgba(130, 8, 16, 0.12)');
      mg.addColorStop(1, 'rgba(130, 8, 16, 0)');
      cCtx.fillStyle = mg;
      cCtx.fillRect(mx - mr, my - mr, mr * 2, mr * 2);
    }

    const hCanvas = document.createElement('canvas');
    hCanvas.width = size;
    hCanvas.height = size;
    const hCtx = hCanvas.getContext('2d');
    hCtx.fillStyle = '#808080';
    hCtx.fillRect(0, 0, size, size);

    // Latitude-proportional geodesic distribution for crisp, visible patisserie seeds
    const rows = 16;
    for (let r = 0; r < rows; r++) {
      const v = (r + 0.55) / rows;
      // Approximate fruit cross-section radius at normalized height v
      const radNorm = Math.sin(Math.pow(v, 0.60) * Math.PI) * (v < 0.16 ? (v / 0.16) * 0.65 + 0.35 : 1.0);
      const colsInRow = Math.max(5, Math.round(15 * radNorm));
      const odd = (r % 2 === 1);

      for (let c = 0; c < colsInRow; c++) {
        const u = ((c + (odd ? 0.5 : 0)) / colsInRow) % 1.0;
        const jx = (hash2(r * 31 + c, 2.7) - 0.5) * (0.16 / colsInRow);
        const jy = (hash2(r * 31 + c, 5.9) - 0.5) * 0.015;

        const cx = ((u + jx + 1.0) % 1.0) * size;
        const cy = Math.max(12, Math.min(size - 12, (v + jy) * size));

        // 1. Physically indented seed pocket (dimple) in heightmap
        // Depression in center (#101010), bulging outer flesh rim (#9c9c9c)
        const dimpleR = Math.max(8, 18 * Math.min(1.0, radNorm + 0.30));
        const dg = hCtx.createRadialGradient(cx, cy, 2.0, cx, cy, dimpleR);
        dg.addColorStop(0.00, '#0c0c0c');
        dg.addColorStop(0.65, '#686868');
        dg.addColorStop(0.84, '#9e9e9e');  // Raised bulging rim of fresh flesh
        dg.addColorStop(1.00, '#808080');
        hCtx.fillStyle = dg;
        hCtx.beginPath();
        hCtx.ellipse(cx, cy, dimpleR * 0.54, dimpleR * 0.88, 0.05, 0, Math.PI * 2);
        hCtx.fill();

        // 2. Raised golden-olive achene seed bump inside the pocket
        const seedW = Math.max(3.2, 5.8 * Math.min(1.0, radNorm + 0.30));
        const seedH = Math.max(5.5, 10.5 * Math.min(1.0, radNorm + 0.30));
        hCtx.fillStyle = '#e4e4e4';
        hCtx.beginPath();
        hCtx.ellipse(cx, cy - 0.6, seedW * 0.52, seedH * 0.52, 0.04, 0, Math.PI * 2);
        hCtx.fill();

        // 3. Realistic pocket shadow shading on color map (deep burgundy indentation)
        const cg = cCtx.createRadialGradient(cx, cy, 1.5, cx, cy, dimpleR * 0.95);
        cg.addColorStop(0.00, 'rgba(52, 2, 8, 0.82)');
        cg.addColorStop(0.70, 'rgba(72, 4, 12, 0.54)');
        cg.addColorStop(1.00, 'rgba(72, 4, 12, 0)');
        cCtx.fillStyle = cg;
        cCtx.beginPath();
        cCtx.ellipse(cx, cy, dimpleR * 0.58, dimpleR * 0.90, 0.05, 0, Math.PI * 2);
        cCtx.fill();

        // 4. Authentic achene seed kernel: warm golden-amber with fresh olive undertone
        cCtx.fillStyle = '#E8C552';
        cCtx.beginPath();
        cCtx.ellipse(cx, cy - 0.5, seedW * 0.50, seedH * 0.50, 0.04, 0, Math.PI * 2);
        cCtx.fill();

        // Fresh olive highlight on seed body
        cCtx.fillStyle = '#9EB538';
        cCtx.beginPath();
        cCtx.ellipse(cx + 0.4, cy - 0.8, seedW * 0.28, seedH * 0.36, 0.04, 0, Math.PI * 2);
        cCtx.fill();

        // Crisp specular highlight dot on seed
        cCtx.fillStyle = '#FFF8D4';
        cCtx.beginPath();
        cCtx.arc(cx - 0.4, cy - 1.8, 1.1, 0, Math.PI * 2);
        cCtx.fill();
      }
    }

    // Build Sobel-filtered normal map from dimple heightmap
    const hData = hCtx.getImageData(0, 0, size, size).data;
    const nCanvas = document.createElement('canvas');
    nCanvas.width = size;
    nCanvas.height = size;
    const nCtx = nCanvas.getContext('2d');
    const nImg = nCtx.createImageData(size, size);

    for (let y = 0; y < size; y++) {
      const yp = (y - 1 + size) % size;
      const yn = (y + 1) % size;
      for (let x = 0; x < size; x++) {
        const xp = (x - 1 + size) % size;
        const xn = (x + 1) % size;
        const hL = hData[(y * size + xp) * 4] / 255;
        const hR = hData[(y * size + xn) * 4] / 255;
        const hD = hData[(yp * size + x) * 4] / 255;
        const hU = hData[(yn * size + x) * 4] / 255;
        const dx = (hR - hL) * 3.6;
        const dy = (hU - hD) * 3.6;
        const dz = 1.0;
        const len = Math.sqrt(dx * dx + dy * dy + dz * dz);
        const idx = (y * size + x) * 4;
        nImg.data[idx] = Math.floor(((dx / len) * 0.5 + 0.5) * 255);
        nImg.data[idx + 1] = Math.floor(((dy / len) * 0.5 + 0.5) * 255);
        nImg.data[idx + 2] = Math.floor(((dz / len) * 0.5 + 0.5) * 255);
        nImg.data[idx + 3] = 255;
      }
    }
    nCtx.putImageData(nImg, 0, 0);

    const map = reg(new THREE.CanvasTexture(cCanvas));
    map.colorSpace = THREE.SRGBColorSpace;
    const normalMap = reg(new THREE.CanvasTexture(nCanvas));
    return { map, normalMap };
  }

  const strawberryTex = createStrawberryTextures();

  // ━━━━ REALISTIC PATISSERIE MATERIALS (NON-CARTOONISH, MATTE-CREAMY BUTTERCREAM & SOFT BLUSH SATIN) ━━━━
  const cakeSideMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xFAF3E6,
    map: sideCreamTex.map,
    normalMap: sideCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.32, 0.32),
    roughnessMap: sideCreamTex.roughnessMap,
    roughness: 0.72,
    metalness: 0.0,
    clearcoat: 0.04,
    clearcoatRoughness: 0.65,
    sheen: 0.45,
    sheenColor: new THREE.Color(0xFFFBF2),
    sheenRoughness: 0.65
  }));

  const cakeTopMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xFBF4E8,
    map: topCreamTex.map,
    normalMap: topCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.28, 0.28),
    roughnessMap: topCreamTex.roughnessMap,
    roughness: 0.70,
    metalness: 0.0,
    clearcoat: 0.04,
    clearcoatRoughness: 0.65,
    sheen: 0.45,
    sheenColor: new THREE.Color(0xFFFBF2),
    sheenRoughness: 0.62
  }));

  const creamTrimMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xF8F0E2,
    map: pipingCreamTex.map,
    normalMap: pipingCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.28, 0.28),
    roughnessMap: pipingCreamTex.roughnessMap,
    roughness: 0.60,
    metalness: 0.0,
    clearcoat: 0.08,
    clearcoatRoughness: 0.52,
    sheen: 0.55,
    sheenColor: new THREE.Color(0xFFF9EE),
    sheenRoughness: 0.52
  }));

  // Matte-creamy fluted piped buttercream (replaces plastic glossy piping)
  const flutedPipingMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xFAF2E4,
    vertexColors: true,
    map: pipingCreamTex.map,
    normalMap: pipingCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.34, 0.34),
    roughnessMap: pipingCreamTex.roughnessMap,
    roughness: 0.58,
    metalness: 0.0,
    clearcoat: 0.10,
    clearcoatRoughness: 0.50,
    sheen: 0.62,
    sheenColor: new THREE.Color(0xFFF8EC),
    sheenRoughness: 0.50
  }));

  const lowerFluteMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xF5E9D6,
    vertexColors: true,
    map: pipingCreamTex.map,
    normalMap: pipingCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.32, 0.32),
    roughnessMap: pipingCreamTex.roughnessMap,
    roughness: 0.62,
    metalness: 0.0,
    clearcoat: 0.08,
    clearcoatRoughness: 0.55,
    sheen: 0.55,
    sheenColor: new THREE.Color(0xFBF2E2),
    sheenRoughness: 0.55
  }));

  // Soft Blush Pink / Baby Pink Silk Satin Ribbon Material (refined fabric luster, NOT plastic!)
  const blushSatinRibbonMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xF7D2DC,             // Delicate blush baby pink
    vertexColors: true,          // Natural crease & inner-loop shadow depth
    normalMap: satinTex.normalMap,
    normalScale: new THREE.Vector2(0.24, 0.24),
    roughnessMap: satinTex.roughnessMap,
    roughness: 0.42,
    metalness: 0.02,
    clearcoat: 0.14,
    clearcoatRoughness: 0.38,
    sheen: 1.0,
    sheenColor: new THREE.Color(0xFFF4F7),
    sheenRoughness: 0.34,
    side: THREE.DoubleSide
  }));

  // Delicate Blush Pink / Baby Pink Piped Buttercream (for top accents between outer strawberries)
  const blushRosetteMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xF9D6E0,             // Lighter soft baby blush pink buttercream
    vertexColors: true,
    normalMap: pipingCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.26, 0.26),
    roughnessMap: pipingCreamTex.roughnessMap,
    roughness: 0.60,
    metalness: 0.0,
    clearcoat: 0.08,
    clearcoatRoughness: 0.52,
    sheen: 0.65,
    sheenColor: new THREE.Color(0xFFF0F4),
    sheenRoughness: 0.48
  }));

  // Creamy Piped Pearl Drops (matte buttercream sheen)
  const pearlPipingMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xFAF3E5,
    normalMap: pipingCreamTex.normalMap,
    normalScale: new THREE.Vector2(0.18, 0.18),
    roughness: 0.48,
    metalness: 0.0,
    clearcoat: 0.16,
    clearcoatRoughness: 0.38,
    sheen: 0.6,
    sheenColor: new THREE.Color(0xFFFBF2),
    sheenRoughness: 0.42
  }));

  const sageLeafMat = reg(new THREE.MeshStandardMaterial({
    color: 0xA8C3AE,
    roughness: 0.62,
    metalness: 0.0
  }));

  const calyxMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0x2E5B20,
    roughness: 0.48,
    metalness: 0.0,
    clearcoat: 0.16,
    clearcoatRoughness: 0.36,
    sheen: 0.42,
    sheenColor: new THREE.Color(0x76B35E),
    side: THREE.DoubleSide
  }));

  const stemMat = reg(new THREE.MeshStandardMaterial({
    color: 0x3A652A,
    roughness: 0.62,
    metalness: 0.0
  }));

  const strawberryMat = reg(new THREE.MeshPhysicalMaterial({
    map: strawberryTex.map,
    normalMap: strawberryTex.normalMap,
    normalScale: new THREE.Vector2(0.85, 0.85),
    roughness: 0.30,
    metalness: 0.0,
    clearcoat: 0.68,
    clearcoatRoughness: 0.18,
    sheen: 0.90,
    sheenColor: new THREE.Color(0xFF384A),
    sheenRoughness: 0.32,
    transmission: 0.07,
    thickness: 0.45,
    attenuationColor: new THREE.Color(0x9E101D),
    attenuationDistance: 0.6
  }));

  const pearlMat = reg(new THREE.MeshPhysicalMaterial({
    color: 0xFFFBF4,
    metalness: 0.08,
    roughness: 0.26,
    clearcoat: 0.45,
    clearcoatRoughness: 0.20
  }));

  // ━━━━ PROCEDURAL FRENCH STAR-TIP FLUTED SHELL GEOMETRY (FINER, SHARPER, NATURAL VALLEYS) ━━━━
  function createFlutedShellGeometry({
    length = 0.58,
    maxRadius = 0.23,
    fluteCount = 9,
    fluteDepth = 0.38,
    twist = 0.38,
    curveY = 0.10,
    curveZ = 0.055,
    segmentsU = 30,
    segmentsV = 48
  } = {}) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    for (let i = 0; i <= segmentsU; i++) {
      const u = i / segmentsU;
      const shape = Math.sin(Math.pow(u, 0.65) * Math.PI);
      const radius = maxRadius * Math.max(0.07, Math.pow(shape, 0.82) * (0.84 + 0.16 * u));

      const spineX = (u - 0.5) * length;
      const spineY = Math.sin(u * Math.PI) * curveY;
      const spineZ = Math.sin(u * Math.PI * 0.88) * curveZ;

      for (let j = 0; j <= segmentsV; j++) {
        const v = (j / segmentsV) * Math.PI * 2;
        const vTwist = v + twist * u;

        const rawFlute = Math.cos(fluteCount * vTwist) - 0.22 * Math.cos(fluteCount * 2 * vTwist);
        const upperMask = Math.sin(v) > -0.25 ? 1.0 : 0.42;
        const fluteMod = 1.0 + fluteDepth * rawFlute * upperMask;

        const rVal = radius * fluteMod;
        const localY = Math.sin(v) * rVal * 1.18;
        const localZ = Math.cos(v) * rVal * 0.88;

        positions.push(spineX, spineY + localY, spineZ + localZ);
        uvs.push(u, j / segmentsV);

        // Soft, realistic buttercream valley AO (not overly dark/cartoonish brown)
        const ao = Math.max(0, Math.min(1, (rawFlute * 0.5 + 0.5) * upperMask + (1 - upperMask) * 0.52));
        const curvedAo = Math.pow(ao, 0.80);
        const rCol = 0.84 + 0.16 * curvedAo;
        const gCol = 0.78 + 0.21 * curvedAo;
        const bCol = 0.68 + 0.29 * curvedAo;
        colors.push(rCol, gCol, bCol);
      }
    }

    const ringStride = segmentsV + 1;
    for (let i = 0; i < segmentsU; i++) {
      for (let j = 0; j < segmentsV; j++) {
        const a = i * ringStride + j;
        const b = (i + 1) * ringStride + j;
        const c = (i + 1) * ringStride + (j + 1);
        const d = i * ringStride + (j + 1);
        indices.push(a, b, d, b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();
    return reg(geo);
  }

  // ━━━━ PROCEDURAL SPIRAL STAR-PIPED ROSETTE GEOMETRY ━━━━
  function createFlutedRosetteGeometry({
    baseRadius = 0.26,
    height = 0.30,
    fluteCount = 9,
    fluteDepth = 0.32,
    spiralTwist = 1.45,
    segmentsH = 18,
    segmentsV = 40
  } = {}) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    for (let i = 0; i <= segmentsH; i++) {
      const hNorm = i / segmentsH;
      const y = hNorm * height;
      const rBase = baseRadius * Math.pow(Math.max(0, 1 - Math.pow(hNorm, 1.38)), 0.62);
      const twist = hNorm * spiralTwist;

      for (let j = 0; j <= segmentsV; j++) {
        const v = (j / segmentsV) * Math.PI * 2;
        const vTwist = v + twist;
        const rawFlute = Math.cos(fluteCount * vTwist) - 0.18 * Math.cos(fluteCount * 2 * vTwist);
        const fluteMod = 1.0 + fluteDepth * rawFlute * (1 - 0.28 * hNorm);

        const r = rBase * fluteMod;
        positions.push(Math.cos(v) * r, y, Math.sin(v) * r);
        uvs.push(j / segmentsV, hNorm);

        const ao = Math.max(0, Math.min(1, rawFlute * 0.5 + 0.5));
        const curvedAo = Math.pow(ao, 0.80);
        colors.push(0.85 + 0.15 * curvedAo, 0.79 + 0.20 * curvedAo, 0.72 + 0.26 * curvedAo);
      }
    }

    const ringStride = segmentsV + 1;
    for (let i = 0; i < segmentsH; i++) {
      for (let j = 0; j < segmentsV; j++) {
        const a = i * ringStride + j;
        const b = (i + 1) * ringStride + j;
        const c = (i + 1) * ringStride + (j + 1);
        const d = i * ringStride + (j + 1);
        indices.push(a, b, d, b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();
    return reg(geo);
  }

  const primaryShellGeo = createFlutedShellGeometry({
    length: 0.60, maxRadius: 0.225, fluteCount: 9, fluteDepth: 0.38, twist: 0.40, curveY: 0.10
  });
  const secondaryShellGeo = createFlutedShellGeometry({
    length: 0.52, maxRadius: 0.185, fluteCount: 9, fluteDepth: 0.35, twist: -0.35, curveY: 0.085
  });
  const swagPipingGeo = createFlutedShellGeometry({
    length: 0.34, maxRadius: 0.145, fluteCount: 9, fluteDepth: 0.28, twist: 0.25, curveY: 0.055
  });
  const crownRosetteGeo = createFlutedRosetteGeometry({
    baseRadius: 0.255, height: 0.29, fluteCount: 9, fluteDepth: 0.32, spiralTwist: 1.42
  });
  const strawberryBedGeo = createFlutedRosetteGeometry({
    baseRadius: 0.27, height: 0.20, fluteCount: 9, fluteDepth: 0.28, spiralTwist: 1.15
  });
  const roseLeafGeo = reg(new THREE.ConeGeometry(0.04, 0.125, 5));

  // ━━━━ SCULPTED COQUETTE BLUSH-PINK SATIN RIBBON BOW BUILDER (REALISTIC FABRIC FOLDS & KNOT) ━━━━
  // Volumetric folded 3D satin loop: pinched tightly into knot, opens into curved silk ribbon band with realistic inner cavity
  function createDetailedRibbonLoopGeometry(isLeft = true) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    const segU = 44; // Along loop perimeter
    const segV = 8;  // Across ribbon width (enables realistic curved cross-section & edge curl)
    const sign = isLeft ? -1 : 1;

    for (let i = 0; i <= segU; i++) {
      const t = i / segU; // 0 = exits knot (top), 0.5 = outer apex of bow loop, 1 = returns into knot (bottom)
      const phi = t * Math.PI * 2;

      // Realistic teardrop bow loop curve with natural slight downward droop at outer tip
      const openFactor = Math.sin(t * Math.PI); // 0 at knot, 1 at outer loop bend
      const x = sign * (0.02 + 0.38 * Math.pow(openFactor, 0.85));
      const y = 0.135 * Math.sin(phi) - 0.042 * Math.pow(openFactor, 1.3);
      // Loop arches forward cleanly (front wall sticks out in +Z, back wall curves back toward cake)
      const isFrontHalf = Math.cos(phi * 0.5) >= 0;
      const zLoop = 0.055 + 0.105 * Math.sin(t * Math.PI) + 0.032 * Math.sin(phi);

      // Ribbon width pinches tightly at knot (t near 0 or 1), widens smoothly across the loop face
      const ribbonW = 0.038 + 0.095 * Math.pow(openFactor, 0.55);

      // Compute loop tangent for proper ribbon orientation
      const dt = 0.002;
      const t2 = Math.min(1, t + dt);
      const phi2 = t2 * Math.PI * 2;
      const of2 = Math.sin(t2 * Math.PI);
      const x2 = sign * (0.02 + 0.38 * Math.pow(of2, 0.85));
      const y2 = 0.135 * Math.sin(phi2) - 0.042 * Math.pow(of2, 1.3);
      const z2 = 0.055 + 0.105 * Math.sin(t2 * Math.PI) + 0.032 * Math.sin(phi2);

      const tangent = new THREE.Vector3(x2 - x, y2 - y, z2 - zLoop).normalize();
      // Ribbon surface normal mostly radiates outward/upward around loop while width spans along Z/Y blend
      const widthAxis = new THREE.Vector3(0, 0.38, 0.92).normalize();
      const perp = new THREE.Vector3().crossVectors(tangent, widthAxis).normalize();
      const binorm = new THREE.Vector3().crossVectors(perp, tangent).normalize();

      for (let j = 0; j <= segV; j++) {
        const s = (j / segV) * 2 - 1; // -1..+1 across ribbon width
        // Subtle silk pleat/cupping across ribbon width + pinch folds near knot
        const cup = (1 - s * s) * 0.016 * openFactor;
        const pinchPleat = Math.sin(s * Math.PI * 2) * 0.005 * (1 - openFactor);

        const vx = x + binorm.x * (s * ribbonW * 0.5) + perp.x * (cup + pinchPleat);
        const vy = y + binorm.y * (s * ribbonW * 0.5) + perp.y * (cup + pinchPleat);
        const vz = zLoop + binorm.z * (s * ribbonW * 0.5) + perp.z * (cup + pinchPleat);

        positions.push(vx, vy, vz);
        uvs.push(j / segV, t * 2.0);

        // Soft baked fabric AO: deeper gentle rose shadow inside knot pinch & back interior of loop
        const knotShade = 0.78 + 0.22 * Math.pow(openFactor, 0.45);
        const innerShade = isFrontHalf ? 1.0 : 0.88;
        const edgeHighlight = 0.96 + 0.04 * (1 - Math.abs(s));
        const shade = knotShade * innerShade * edgeHighlight;
        colors.push(shade, shade * 0.97, shade * 0.98);
      }
    }

    const stride = segV + 1;
    for (let i = 0; i < segU; i++) {
      for (let j = 0; j < segV; j++) {
        const a = i * stride + j;
        const b = (i + 1) * stride + j;
        const c = (i + 1) * stride + (j + 1);
        const d = i * stride + (j + 1);
        indices.push(a, b, d, b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();
    return reg(geo);
  }

  // Sculpted wrapped fabric pillow knot (replaces cartoonish round ball sphere!)
  function createFabricKnotGeometry() {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    const segU = 28; // Wrap around vertical circumference
    const segV = 12; // Across knot width

    for (let i = 0; i <= segU; i++) {
      const u = i / segU;
      const theta = u * Math.PI * 2;
      // Slightly squashed organic rectangular-pillow cross-section (real wrapped satin ribbon band)
      const rx = 0.068;
      const ry = 0.072;
      const rz = 0.056;

      for (let j = 0; j <= segV; j++) {
        const v = (j / segV) * 2 - 1; // -1..+1 across knot width
        // Waist tapers gently inward in the middle with realistic soft fabric gather creases
        const waist = 1.0 - 0.14 * (1 - v * v);
        const crease = Math.sin(v * Math.PI * 3) * 0.0045;
        const x = v * rx + crease * 0.4;
        const y = Math.sin(theta) * ry * waist;
        const z = 0.078 + Math.cos(theta) * (rz * waist + crease);

        positions.push(x, y, z);
        uvs.push(j / segV, u);

        const shade = 0.86 + 0.14 * (0.5 + 0.5 * Math.cos(theta)) + crease * 6;
        colors.push(Math.min(1, shade), Math.min(1, shade * 0.97), Math.min(1, shade * 0.98));
      }
    }

    const stride = segV + 1;
    for (let i = 0; i < segU; i++) {
      for (let j = 0; j < segV; j++) {
        const a = i * stride + j;
        const b = (i + 1) * stride + j;
        const c = (i + 1) * stride + (j + 1);
        const d = i * stride + (j + 1);
        indices.push(a, b, d, b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();
    return reg(geo);
  }

  // Draped flowing satin streamer with lengthwise fabric cup & crisp V-notched dovetail tip
  function createDetailedStreamerGeometry(isLeft = true) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    const segU = 38;
    const segV = 8;
    const sign = isLeft ? -1 : 1;
    const phase = isLeft ? 0.1 : 1.05;
    const baseLen = isLeft ? 0.96 : 0.88; // Slight natural asymmetry looks hand-tied!

    for (let i = 0; i <= segU; i++) {
      const t = i / segU;
      // Emerges pinched from behind knot, relaxes to full satin ribbon width as it drapes
      const width = 0.042 + 0.058 * Math.min(1, Math.pow(t * 2.6, 0.65));

      const xCenter = sign * (0.042 + t * 0.21 + Math.sin(t * 2.3 * Math.PI + phase) * 0.032);
      const yCenter = -0.025 - t * baseLen;
      // Curves smoothly toward cake wall as it hangs down
      const zCenter = 0.068 - t * 0.095 + Math.sin(t * 2.1 * Math.PI + phase) * 0.020;

      const twist = Math.sin(t * 2.3 * Math.PI + phase) * 0.26 * t;
      const cosT = Math.cos(twist);
      const sinT = Math.sin(twist);

      for (let j = 0; j <= segV; j++) {
        const s = (j / segV) * 2 - 1; // -1..+1 across ribbon width
        // V-notch swallowtail cut at the bottom tip (t > 0.88)
        const notchFactor = t > 0.88 ? ((t - 0.88) / 0.12) * (1 - Math.abs(s)) * 0.068 : 0;
        const cup = (1 - s * s) * 0.008;

        const px = xCenter + (s * width * 0.5) * cosT;
        const py = yCenter + notchFactor;
        const pz = zCenter - (s * width * 0.5) * sinT + cup;

        positions.push(px, py, pz);
        uvs.push(j / segV, t * 2.5);

        const topShadow = 0.80 + 0.20 * Math.min(1, t * 4.5);
        const foldShade = 0.95 + 0.05 * Math.sin(t * 2.1 * Math.PI + phase);
        const shade = topShadow * foldShade;
        colors.push(shade, shade * 0.97, shade * 0.98);
      }
    }

    const stride = segV + 1;
    for (let i = 0; i < segU; i++) {
      for (let j = 0; j < segV; j++) {
        const a = i * stride + j;
        const b = (i + 1) * stride + j;
        const c = (i + 1) * stride + (j + 1);
        const d = i * stride + (j + 1);
        indices.push(a, b, d, b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();
    return reg(geo);
  }

  const leftLoopGeo = createDetailedRibbonLoopGeometry(true);
  const rightLoopGeo = createDetailedRibbonLoopGeometry(false);
  const fabricKnotGeo = createFabricKnotGeometry();
  const leftStreamerGeo = createDetailedStreamerGeometry(true);
  const rightStreamerGeo = createDetailedStreamerGeometry(false);

  const pearlDotGeos = [
    reg(new THREE.SphereGeometry(0.048, 14, 14)),
    reg(new THREE.SphereGeometry(0.043, 14, 14)),
    reg(new THREE.SphereGeometry(0.038, 14, 14)),
    reg(new THREE.SphereGeometry(0.033, 14, 14)),
    reg(new THREE.SphereGeometry(0.028, 14, 14))
  ];

  function createRibbonBowGroup() {
    const bowGroup = new THREE.Group();

    const knotMesh = new THREE.Mesh(fabricKnotGeo, blushSatinRibbonMat);
    bowGroup.add(knotMesh);

    const leftLoop = new THREE.Mesh(leftLoopGeo, blushSatinRibbonMat);
    bowGroup.add(leftLoop);

    const rightLoop = new THREE.Mesh(rightLoopGeo, blushSatinRibbonMat);
    bowGroup.add(rightLoop);

    const leftTail = new THREE.Mesh(leftStreamerGeo, blushSatinRibbonMat);
    bowGroup.add(leftTail);

    const rightTail = new THREE.Mesh(rightStreamerGeo, blushSatinRibbonMat);
    bowGroup.add(rightTail);

    return bowGroup;
  }

  // ━━━━ 1. MAIN CAKE CYLINDER (NO PLATE UNDERNEATH — CLEAN ARTISANAL FLOATING CAKE) ━━━━
  const cakeR = 2.05;
  const cakeH = 1.96;
  const cakeY = -0.08;
  const cakeTopY = cakeY + cakeH / 2;
  const cakeBottomY = cakeY - cakeH / 2;

  // Multi-material cylinder: [side scraper texture, top spatula swirl texture, smooth bottom]
  const cakeMesh = new THREE.Mesh(
    reg(new THREE.CylinderGeometry(cakeR, cakeR, cakeH, 72, 1, false)),
    [cakeSideMat, cakeTopMat, cakeSideMat]
  );
  cakeMesh.position.y = cakeY;
  cakeGroup.add(cakeMesh);

  // Subtle rounded top buttercream bevel edge
  const topBevel = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR - 0.025, 0.042, 18, 72)), creamTrimMat);
  topBevel.rotation.x = Math.PI / 2;
  topBevel.position.y = cakeTopY;
  cakeGroup.add(topBevel);

  // ━━━━ 2. FRENCH STAR-TIP SCALLOPED SHELL BORDER (BASE) ━━━━
  const bottomShellCount = 28;
  for (let i = 0; i < bottomShellCount; i++) {
    const angle = (i * Math.PI * 2) / bottomShellCount;
    const bx = Math.cos(angle) * (cakeR + 0.075);
    const bz = Math.sin(angle) * (cakeR + 0.075);

    const shell = new THREE.Mesh(primaryShellGeo, flutedPipingMat);
    shell.position.set(bx, cakeBottomY + 0.13, bz);
    shell.rotation.y = -angle + Math.PI / 2;
    shell.rotation.x = 0.46;
    shell.rotation.z = -0.15;
    cakeGroup.add(shell);

    const midAngle = angle + (Math.PI / bottomShellCount);
    const lx = Math.cos(midAngle) * (cakeR + 0.14);
    const lz = Math.sin(midAngle) * (cakeR + 0.14);

    const lowerShell = new THREE.Mesh(secondaryShellGeo, lowerFluteMat);
    lowerShell.position.set(lx, cakeBottomY + 0.05, lz);
    lowerShell.rotation.y = -midAngle + Math.PI / 2;
    lowerShell.rotation.x = 0.56;
    lowerShell.rotation.z = 0.11;
    lowerShell.scale.set(1.18, 1.02, 1.20);
    cakeGroup.add(lowerShell);
  }

  // ━━━━ 3. SIDE DRAPED BUTTERCREAM GARLANDS + BLUSH PINK SATIN BOWS (NO PINK FROSTING BETWEEN BOWS) ━━━━
  const swagCount = 6;
  const swagY = cakeTopY - 0.44;
  const swagPointsPerCurve = 16;
  const swagAngleOffset = (Math.PI / 2 + 0.45) - (1 * Math.PI * 2 / swagCount);

  for (let s = 0; s < swagCount; s++) {
    const startAngle = swagAngleOffset + (s * Math.PI * 2) / swagCount;
    const endAngle = swagAngleOffset + ((s + 1) * Math.PI * 2) / swagCount;

    // 1. Ivory buttercream fluted garland swag
    for (let p = 0; p <= swagPointsPerCurve; p++) {
      const t = p / swagPointsPerCurve;
      const currentAngle = startAngle + (endAngle - startAngle) * t;
      const droop = Math.sin(t * Math.PI) * 0.39;
      const py = swagY - droop;

      const px = Math.cos(currentAngle) * (cakeR + 0.055);
      const pz = Math.sin(currentAngle) * (cakeR + 0.055);

      const seg = new THREE.Mesh(swagPipingGeo, flutedPipingMat);
      seg.position.set(px, py, pz);
      seg.rotation.y = -currentAngle + Math.PI / 2;
      seg.rotation.x = 0.28;
      seg.scale.set(1.04, 0.94, 1.12);
      cakeGroup.add(seg);

      if (p % 2 === 0) {
        const segUnder = new THREE.Mesh(secondaryShellGeo, lowerFluteMat);
        segUnder.scale.set(0.72, 0.68, 0.82);
        segUnder.position.set(px * 0.992, py - 0.065, pz * 0.992);
        segUnder.rotation.y = -currentAngle + Math.PI / 2;
        segUnder.rotation.x = 0.36;
        cakeGroup.add(segUnder);
      }
    }

    // 2. Descending ivory buttercream pearl droplets behind each bow
    for (let d = 0; d < 5; d++) {
      const dotMesh = new THREE.Mesh(pearlDotGeos[d], pearlPipingMat);
      const dotPy = swagY - 0.16 - d * 0.112;
      const dotPx = Math.cos(startAngle) * (cakeR + 0.036);
      const dotPz = Math.sin(startAngle) * (cakeR + 0.036);
      dotMesh.position.set(dotPx, dotPy, dotPz);
      cakeGroup.add(dotMesh);
    }

    // 3. Sculpted Baby/Blush Pink Satin Ribbon Bow
    const bow = createRibbonBowGroup();
    const bowR = cakeR + 0.195;
    const bx = Math.cos(startAngle) * bowR;
    const bz = Math.sin(startAngle) * bowR;
    bow.position.set(bx, swagY + 0.035, bz);
    bow.lookAt(bx * 2, swagY + 0.035, bz * 2);
    cakeGroup.add(bow);
  }

  // Upper horizontal lace piping cord below top crown
  const swagRim = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR + 0.035, 0.048, 16, 72)), creamTrimMat);
  swagRim.rotation.x = Math.PI / 2;
  swagRim.position.y = cakeTopY - 0.14;
  cakeGroup.add(swagRim);

  // ━━━━ 4. TOP RIM SPIRAL STAR-PIPED BUTTERCREAM CROWN ━━━━
  const topRuffleCount = 24;
  for (let i = 0; i < topRuffleCount; i++) {
    const angle = (i * Math.PI * 2) / topRuffleCount;
    const rx = Math.cos(angle) * (cakeR - 0.065);
    const rz = Math.sin(angle) * (cakeR - 0.065);

    const rosette = new THREE.Mesh(crownRosetteGeo, flutedPipingMat);
    rosette.position.set(rx, cakeTopY + 0.015, rz);
    rosette.rotation.y = -angle + (i * 0.22);
    rosette.rotation.x = -0.10;
    rosette.scale.set(1.10, 1.04, 1.10);
    cakeGroup.add(rosette);
  }

  // ━━━━ 5. OUTER CROWN OF REALISTIC STRAWBERRIES & LIGHT BLUSH-PINK ROSEBUDS (CLEAN CENTER TOP) ━━━━
  function createBotanicalSepalGeometry({ length = 0.32, width = 0.088, isOuter = true } = {}) {
    const shape = new THREE.Shape();
    const w = width;
    const len = length;
    shape.moveTo(0, 0);
    shape.lineTo(w * 0.44, len * 0.22);
    shape.lineTo(w * 0.32, len * 0.32);   // Serration notch
    shape.lineTo(w * 0.52, len * 0.56);   // Main lateral lobe
    shape.lineTo(w * 0.36, len * 0.72);   // Upper notch
    shape.lineTo(0, len);                 // Acuminate leaf tip
    shape.lineTo(-w * 0.36, len * 0.72);
    shape.lineTo(-w * 0.52, len * 0.56);
    shape.lineTo(-w * 0.32, len * 0.32);
    shape.lineTo(-w * 0.44, len * 0.22);
    shape.closePath();

    const geo = new THREE.ShapeGeometry(shape, 8);
    // Botanical 3D leaf curl: arches out over cream rosette with slightly curled tip
    const pos = geo.attributes.position;
    for (let k = 0; k < pos.count; k++) {
      const py = pos.getY(k);
      const t = Math.max(0, Math.min(1, py / len));
      const curl = isOuter
        ? (Math.sin(t * Math.PI) * 0.038 - Math.pow(t, 1.7) * 0.065)
        : (Math.sin(t * Math.PI) * 0.024 - Math.pow(t, 2.0) * 0.040);
      pos.setZ(k, curl);
    }
    geo.computeVertexNormals();
    return reg(geo);
  }

  const outerSepalGeo = createBotanicalSepalGeometry({ length: 0.38, width: 0.11, isOuter: true });
  const innerSepalGeo = createBotanicalSepalGeometry({ length: 0.27, width: 0.082, isOuter: false });
  const strawberryStemGeo = reg(new THREE.CylinderGeometry(0.016, 0.022, 0.08, 8));

  function createStrawberryMesh(scale = 1.0) {
    const group = new THREE.Group();

    // Natural heart-conical patisserie strawberry contour with rounded shoulders & blunt apex
    const points = [
      new THREE.Vector2(0.001, 0.0),       // Stem junction center
      new THREE.Vector2(0.085, 0.022),     // Stem recess indentation
      new THREE.Vector2(0.180, 0.072),     // Rounded shoulder curve
      new THREE.Vector2(0.245, 0.155),     // Plump upper shoulder
      new THREE.Vector2(0.252, 0.265),     // Body maximum width
      new THREE.Vector2(0.220, 0.385),     // Natural mid taper
      new THREE.Vector2(0.160, 0.495),     // Lower taper
      new THREE.Vector2(0.082, 0.585),     // Rounded apex shoulder
      new THREE.Vector2(0.001, 0.620)      // Blunt tip apex
    ];

    const geo = reg(new THREE.LatheGeometry(points, 32));
    const mesh = new THREE.Mesh(geo, strawberryMat);
    // Natural slight berry ovality (organic fruit cross-section)
    mesh.scale.set(1.0, 1.0, 0.93);
    group.add(mesh);

    // Botanical layered fresh green calyx:
    // 6 prominent outer sepals flaring gracefully out over the whipped cream rosette
    const outerCount = 6;
    for (let l = 0; l < outerCount; l++) {
      const lAngle = (l * Math.PI * 2) / outerCount;
      const leaf = new THREE.Mesh(outerSepalGeo, calyxMat);
      leaf.position.set(Math.cos(lAngle) * 0.076, 0.052, Math.sin(lAngle) * 0.076);
      leaf.rotation.y = -lAngle + Math.PI / 2;
      leaf.rotation.x = Math.PI * 0.46;
      group.add(leaf);
    }

    // 6 inner sepals staggered, hugging the base shoulder of the fruit
    const innerCount = 6;
    for (let l = 0; l < innerCount; l++) {
      const lAngle = (l * Math.PI * 2) / innerCount + (Math.PI / innerCount);
      const leaf = new THREE.Mesh(innerSepalGeo, calyxMat);
      leaf.position.set(Math.cos(lAngle) * 0.062, 0.064, Math.sin(lAngle) * 0.062);
      leaf.rotation.y = -lAngle + Math.PI / 2;
      leaf.rotation.x = Math.PI * 0.36;
      group.add(leaf);
    }

    // Fresh botanical stem stub
    const stem = new THREE.Mesh(strawberryStemGeo, stemMat);
    stem.position.set(0, 0.024, 0);
    stem.rotation.x = 0.15;
    group.add(stem);

    group.scale.setScalar(scale);
    return group;
  }

  const strawberryCount = 8;
  const strawberryRingR = cakeR * 0.64;

  for (let i = 0; i < strawberryCount; i++) {
    const angle = (i * Math.PI * 2) / strawberryCount;
    const sx = Math.cos(angle) * strawberryRingR;
    const sz = Math.sin(angle) * strawberryRingR;

    const creamBed = new THREE.Mesh(strawberryBedGeo, flutedPipingMat);
    creamBed.position.set(sx, cakeTopY + 0.02, sz);
    creamBed.rotation.y = angle * 2;
    cakeGroup.add(creamBed);

    const strawberry = createStrawberryMesh(1.05);
    strawberry.position.set(sx, cakeTopY + 0.13, sz);
    strawberry.rotation.y = angle;
    strawberry.rotation.x = -0.20;
    strawberry.rotation.z = 0.06;
    cakeGroup.add(strawberry);

    // Soft baby/blush pink piped buttercream rosebud & sugar pearl between outer strawberries
    const midAngle = angle + (Math.PI / strawberryCount);
    const px = Math.cos(midAngle) * (strawberryRingR * 1.04);
    const pz = Math.sin(midAngle) * (strawberryRingR * 1.04);

    const dollop = new THREE.Mesh(strawberryBedGeo, blushRosetteMat);
    dollop.scale.set(0.58, 0.46, 0.58);
    dollop.position.set(px, cakeTopY + 0.015, pz);
    dollop.rotation.y = midAngle;
    cakeGroup.add(dollop);

    const topLeaf = new THREE.Mesh(roseLeafGeo, sageLeafMat);
    topLeaf.position.set(
      Math.cos(midAngle) * (strawberryRingR * 1.16),
      cakeTopY + 0.032,
      Math.sin(midAngle) * (strawberryRingR * 1.16)
    );
    topLeaf.rotation.y = -midAngle;
    topLeaf.rotation.x = -0.55;
    cakeGroup.add(topLeaf);

    const pearl = new THREE.Mesh(reg(new THREE.SphereGeometry(0.056, 16, 16)), pearlMat);
    pearl.position.set(px, cakeTopY + 0.11, pz);
    cakeGroup.add(pearl);
  }

  // Note: Center of cake top is intentionally clean smooth vanilla-bean buttercream (no center swirl, no center strawberry).

  // ━━━━ 6. SOFT FLOATING CONTACT SHADOW (NO PLATE) ━━━━
  const shadowCanvas = document.createElement('canvas');
  shadowCanvas.width = 256;
  shadowCanvas.height = 256;
  const shCtx = shadowCanvas.getContext('2d');
  const shGrad = shCtx.createRadialGradient(128, 128, 18, 128, 128, 118);
  shGrad.addColorStop(0, 'rgba(18, 9, 6, 0.58)');
  shGrad.addColorStop(0.55, 'rgba(18, 9, 6, 0.18)');
  shGrad.addColorStop(1, 'rgba(18, 9, 6, 0)');
  shCtx.fillStyle = shGrad;
  shCtx.fillRect(0, 0, 256, 256);

  const shadowTex = reg(new THREE.CanvasTexture(shadowCanvas));
  const shadowMat = reg(new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, opacity: 0.78 }));

  const shadowPlane = new THREE.Mesh(reg(new THREE.PlaneGeometry(5.6, 5.6)), shadowMat);
  shadowPlane.rotation.x = -Math.PI / 2;
  shadowPlane.position.y = cakeBottomY - 0.16;
  cakeGroup.add(shadowPlane);

  // Initial Rest Angle & Position
  const baseRotX = 0.22;
  const baseRotY = 0.45;
  cakeGroup.rotation.x = baseRotX;
  cakeGroup.rotation.y = baseRotY;

  let entranceStartTime = performance.now();
  let isEntering = true;

  let targetTiltX = 0;
  let targetTiltY = 0;
  let currentTiltX = 0;
  let currentTiltY = 0;

  let isDragging = false;
  let previousMouseX = 0;
  let spinVelocity = 0;

  let isVisible = true;
  let isTabActive = !document.hidden;
  let needsRender = true;
  let animFrameId = null;

  const heroSection = wrapper.closest('section') || wrapper;

  const onPointerMove = (e) => {
    const rect = heroSection.getBoundingClientRect();
    const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    const ny = -(((e.clientY - rect.top) / rect.height) * 2 - 1);

    if (isDragging) {
      const deltaX = e.clientX - previousMouseX;
      spinVelocity = deltaX * 0.007;
      cakeGroup.rotation.y += spinVelocity;
      previousMouseX = e.clientX;
      needsRender = true;
    } else {
      targetTiltY = nx * 0.30;
      targetTiltX = -ny * 0.15;
      needsRender = true;
    }
  };

  const onPointerDown = (e) => {
    isDragging = true;
    previousMouseX = e.clientX;
    spinVelocity = 0;
    canvas.style.cursor = 'grabbing';
  };

  const onPointerUp = () => {
    if (isDragging) {
      isDragging = false;
      canvas.style.cursor = 'grab';
    }
  };

  const onPointerLeave = () => {
    targetTiltX = 0;
    targetTiltY = 0;
    if (isDragging) {
      isDragging = false;
      canvas.style.cursor = 'grab';
    }
  };

  canvas.style.cursor = 'grab';
  canvas.addEventListener('pointerdown', onPointerDown);
  window.addEventListener('pointerup', onPointerUp);
  window.addEventListener('pointercancel', onPointerUp);
  heroSection.addEventListener('pointermove', onPointerMove, { passive: true });
  heroSection.addEventListener('pointerleave', onPointerLeave);

  const onResize = () => {
    if (!renderer || !canvas) return;
    width = wrapper.clientWidth || 460;
    height = wrapper.clientHeight || 400;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
    needsRender = true;
  };
  window.addEventListener('resize', onResize, { passive: true });

  function loop(now) {
    animFrameId = null;
    if (!isVisible || !isTabActive) return;

    if (!prefersReducedMotion) {
      const t = now * 0.001;

      let entranceScale = 1;
      let entranceYOffset = 0;
      if (isEntering) {
        const elapsed = now - entranceStartTime;
        const p = Math.min(1, elapsed / 1400);
        const ease = p === 1 ? 1 : 1 - Math.pow(2, -10 * p) * Math.cos((p * 10 - 0.75) * (2 * Math.PI / 3));
        entranceScale = 0.52 + 0.48 * ease;
        entranceYOffset = (1 - ease) * -0.42;
        cakeGroup.rotation.y += (1 - ease) * 0.11;
        if (p >= 1) isEntering = false;
        needsRender = true;
      }

      cakeGroup.scale.setScalar(0.88 * entranceScale);

      const bob = Math.sin(t * 1.45) * 0.038;
      cakeGroup.position.y = 0.24 + entranceYOffset + bob;

      shadowPlane.scale.setScalar(1 - bob * 0.20);
      shadowMat.opacity = 0.78 - bob * 0.26;

      const lerpFactor = 0.07;
      currentTiltX += (targetTiltX - currentTiltX) * lerpFactor;
      currentTiltY += (targetTiltY - currentTiltY) * lerpFactor;
      cakeGroup.rotation.x = baseRotX + currentTiltX;

      if (!isDragging) {
        if (Math.abs(spinVelocity) > 0.0002) {
          cakeGroup.rotation.y += spinVelocity;
          spinVelocity *= 0.94;
          needsRender = true;
        } else {
          spinVelocity = 0;
          cakeGroup.rotation.y += 0.0024;
          needsRender = true;
        }
      }

      needsRender = true;
    }

    if (needsRender) {
      renderer.render(scene, camera);
      needsRender = false;
    }

    if (!prefersReducedMotion) {
      animFrameId = requestAnimationFrame(loop);
    }
  }

  const observer = new IntersectionObserver((entries) => {
    isVisible = entries[0].isIntersecting;
    if (isVisible && isTabActive) {
      needsRender = true;
      if (!animFrameId && !prefersReducedMotion) {
        animFrameId = requestAnimationFrame(loop);
      }
    }
  }, { threshold: 0.1 });
  observer.observe(wrapper);

  const onVisibilityChange = () => {
    isTabActive = !document.hidden;
    if (isTabActive && isVisible) {
      needsRender = true;
      if (!animFrameId && !prefersReducedMotion) {
        animFrameId = requestAnimationFrame(loop);
      }
    }
  };
  document.addEventListener('visibilitychange', onVisibilityChange);

  renderer.render(scene, camera);
  if (!prefersReducedMotion) {
    animFrameId = requestAnimationFrame(loop);
  }

  const cleanup = () => {
    if (animFrameId) cancelAnimationFrame(animFrameId);
    canvas.removeEventListener('pointerdown', onPointerDown);
    window.removeEventListener('pointerup', onPointerUp);
    window.removeEventListener('pointercancel', onPointerUp);
    heroSection.removeEventListener('pointermove', onPointerMove);
    heroSection.removeEventListener('pointerleave', onPointerLeave);
    window.removeEventListener('resize', onResize);
    document.removeEventListener('visibilitychange', onVisibilityChange);
    observer.disconnect();

    renderer.dispose();
    disposables.forEach(d => d?.dispose && d.dispose());
  };

  window.addEventListener('pagehide', cleanup, { once: true });
  window.addEventListener('beforeunload', cleanup, { once: true });
}
