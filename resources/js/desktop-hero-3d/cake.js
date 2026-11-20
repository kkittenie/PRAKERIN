import * as THREE from 'three';

/**
 * Daisy Bakery 3D Artisanal Cake (Three.js)
 * 
 * Re-sculpted after authentic vintage French pastry artisan piping:
 * - Handcrafted French star-tip fluted shell border with crisp, sharp ridges,
 *   warm buttery valley shading and graceful overlapping swoops encircling the base (matching reference image)
 * - Double-layered scalloped buttercream garland fanning out onto the plate base
 * - Luscious whipped buttercream texture with procedural normal micro-relief,
 *   warm French vanilla ivory tone (#FFFDF6 / #FFF8EB), velvety sheen and satin clearcoat
 * - Draped ruffled side swags with fluted ridges and drop rosettes
 * - Top crown of 24 spiral star-tip piped rosettes
 * - Jewel crown of 8 plump conical strawberries with seed facets, fresh calyx sepals & edible sugar pearls
 * - Roasted Cocoa Ceramic Plate (#3A221B) with Daisy Gold rim (#EACE80)
 * - 360° interactive drag-to-spin, momentum, cursor tilt, and gentle idle bobbing
 */

export function initHero3DCake(wrapper) {
  if (!wrapper || !window.matchMedia('(min-width: 1024px)').matches) return;

  const canvas = wrapper.querySelector('#desktop-hero-3d-canvas') || wrapper.querySelector('canvas');
  if (!canvas) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Sizing
  let width = wrapper.clientWidth || 460;
  let height = wrapper.clientHeight || 400;

  // Scene & Camera
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(29, width / height, 0.1, 100);
  camera.position.set(0, 2.75, 8.6);
  camera.lookAt(0, 0.18, 0);

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
  renderer.toneMappingExposure = 1.08;

  // ━━━━ BAKERY STUDIO LIGHTING (WARM BUTTERCREAM ACCENTS) ━━━━
  // Ambient light: Soft warm vanilla glow (balanced so groove depth is clearly visible)
  const ambientLight = new THREE.AmbientLight(0xfffaee, 0.78);
  scene.add(ambientLight);

  // Key light: Warm sunlit cream tone with crisp directional ridge shading
  const keyLight = new THREE.DirectionalLight(0xfffaec, 1.85);
  keyLight.position.set(4.2, 5.8, 4.6);
  scene.add(keyLight);

  // Fill light: Gentle warm golden custard fill
  const fillLight = new THREE.DirectionalLight(0xf6e2b8, 0.65);
  fillLight.position.set(-4.0, 0.2, 3.2);
  scene.add(fillLight);

  // Soft warm bounce light from plate base
  const bounceLight = new THREE.DirectionalLight(0xf2dac0, 0.40);
  bounceLight.position.set(0, -3.5, 2.2);
  scene.add(bounceLight);

  // Subtle contour rim light
  const rimLight = new THREE.DirectionalLight(0xfff6ec, 1.05);
  rimLight.position.set(2.2, 4.0, -4.5);
  scene.add(rimLight);

  // Root Cake Group
  const cakeGroup = new THREE.Group();
  cakeGroup.scale.setScalar(0.85);
  cakeGroup.position.y = 0.32;
  scene.add(cakeGroup);

  const disposables = [];
  function reg(geo) {
    disposables.push(geo);
    return geo;
  }

  // ━━━━ PROCEDURAL BUTTERCREAM NORMAL TEXTURE ━━━━
  function createButtercreamNormalTexture() {
    const size = 512;
    const nCanvas = document.createElement('canvas');
    nCanvas.width = size;
    nCanvas.height = size;
    const ctx = nCanvas.getContext('2d');

    const heightData = new Float32Array(size * size);

    function hash(x, y) {
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

      const s00 = hash(ix, iy);
      const s10 = hash(ix + 1, iy);
      const s01 = hash(ix, iy + 1);
      const s11 = hash(ix + 1, iy + 1);

      const a = s00 * (1 - wx) + s10 * wx;
      const b = s01 * (1 - wx) + s11 * wx;
      return a * (1 - wy) + b * wy;
    }

    for (let y = 0; y < size; y++) {
      for (let x = 0; x < size; x++) {
        const u = x / size;
        const v = y / size;

        // Gentle whipping folds & spatula stroke waves
        const strokeWave = Math.sin(u * 16.0 + v * 8.0 + Math.cos(u * 6.0) * 1.8) * 0.18;
        // Fine airy cream bubbles
        const n1 = smoothNoise(x * 0.10, y * 0.10) * 0.45;
        const n2 = smoothNoise(x * 0.28, y * 0.28) * 0.25;
        const n3 = smoothNoise(x * 0.65, y * 0.65) * 0.12;

        heightData[y * size + x] = strokeWave + n1 + n2 + n3;
      }
    }

    const imgData = ctx.createImageData(size, size);
    const data = imgData.data;
    const bumpStrength = 2.4;

    for (let y = 0; y < size; y++) {
      const yPrev = (y - 1 + size) % size;
      const yNext = (y + 1) % size;
      for (let x = 0; x < size; x++) {
        const xPrev = (x - 1 + size) % size;
        const xNext = (x + 1) % size;

        const hL = heightData[y * size + xPrev];
        const hR = heightData[y * size + xNext];
        const hD = heightData[yPrev * size + x];
        const hU = heightData[yNext * size + x];

        let dx = (hR - hL) * bumpStrength;
        let dy = (hU - hD) * bumpStrength;
        let dz = 1.0;

        const len = Math.sqrt(dx * dx + dy * dy + dz * dz);
        dx /= len;
        dy /= len;
        dz /= len;

        const idx = (y * size + x) * 4;
        data[idx] = Math.floor((dx * 0.5 + 0.5) * 255);
        data[idx + 1] = Math.floor((dy * 0.5 + 0.5) * 255);
        data[idx + 2] = Math.floor((dz * 0.5 + 0.5) * 255);
        data[idx + 3] = 255;
      }
    }

    ctx.putImageData(imgData, 0, 0);

    const texture = new THREE.CanvasTexture(nCanvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(4, 4);
    return texture;
  }

  const buttercreamNormalTex = createButtercreamNormalTexture();
  disposables.push(buttercreamNormalTex);

  // ━━━━ PROCEDURAL STRAWBERRY SEED TEXTURE ━━━━
  const sCanvas = document.createElement('canvas');
  sCanvas.width = 512;
  sCanvas.height = 512;
  const sCtx = sCanvas.getContext('2d');

  const sGrad = sCtx.createRadialGradient(256, 180, 50, 256, 256, 260);
  sGrad.addColorStop(0, '#D82231');
  sGrad.addColorStop(0.6, '#AA1320');
  sGrad.addColorStop(1, '#780B14');
  sCtx.fillStyle = sGrad;
  sCtx.fillRect(0, 0, 512, 512);

  const rows = 18;
  const cols = 18;
  for (let r = 0; r < rows; r++) {
    const y = (r / rows) * 512 + 14;
    const isOdd = r % 2 === 1;
    for (let c = 0; c < cols; c++) {
      const x = ((c + (isOdd ? 0.5 : 0)) / cols) * 512 + 10;

      sCtx.fillStyle = 'rgba(70, 6, 12, 0.55)';
      sCtx.beginPath();
      sCtx.ellipse(x, y, 4.5, 6.5, 0.1, 0, Math.PI * 2);
      sCtx.fill();

      sCtx.fillStyle = '#F5D77F';
      sCtx.beginPath();
      sCtx.ellipse(x, y - 0.5, 2.2, 4.0, 0.05, 0, Math.PI * 2);
      sCtx.fill();

      sCtx.fillStyle = 'rgba(255, 255, 255, 0.45)';
      sCtx.beginPath();
      sCtx.arc(x - 0.8, y - 1.5, 1.0, 0, Math.PI * 2);
      sCtx.fill();
    }
  }

  const strawberryTex = new THREE.CanvasTexture(sCanvas);
  strawberryTex.colorSpace = THREE.SRGBColorSpace;
  disposables.push(strawberryTex);

  // ━━━━ LUSCIOUS BUTTERCREAM & PASTRY MATERIALS (EXACT HEART CAKE PALETTE) ━━━━
  // Main cake body: Smooth French vanilla buttermilk buttercream (matching reference heart cake)
  const cakeBodyMat = new THREE.MeshPhysicalMaterial({
    color: 0xFBF3E6,              // Warm French vanilla buttermilk cream (matte, authentic crumb coat)
    roughness: 0.62,              // Non-glossy matte velvety texture (tidak mengkilap)
    metalness: 0.0,
    clearcoat: 0.0,               // Completely non-glossy (zero clearcoat specular reflection)
    clearcoatRoughness: 0.0,
    sheen: 0.50,                  // Soft velvety bakery crumb diffuse scatter
    sheenColor: new THREE.Color(0xFFF9EE),
    sheenRoughness: 0.50,
    normalMap: buttercreamNormalTex,
    normalScale: new THREE.Vector2(0.12, 0.12)
  });

  // Bevel & trim cream (matching heart-shaped cake's warm ivory buttercream)
  const creamBevelMat = new THREE.MeshPhysicalMaterial({
    color: 0xF8EEDC,              // Warm French vanilla bean ivory (matching heart cake photo)
    roughness: 0.22,
    metalness: 0.0,
    clearcoat: 0.52,
    clearcoatRoughness: 0.16,
    sheen: 1.0,
    sheenColor: new THREE.Color(0xFBEFD6),
    sheenRoughness: 0.24,
    normalMap: buttercreamNormalTex,
    normalScale: new THREE.Vector2(0.18, 0.18)
  });

  // Silky Pink Satin Ribbon Material (for bows & flowing streamers matching reference image)
  const pinkSatinRibbonMat = new THREE.MeshPhysicalMaterial({
    color: 0xE882A0,              // Soft pastel baby pink satin matching heart cake accents
    roughness: 0.18,
    metalness: 0.04,
    clearcoat: 0.85,              // Silky fabric luster
    clearcoatRoughness: 0.14,
    sheen: 1.0,                   // Luxurious satin light scattering
    sheenColor: new THREE.Color(0xFFE5EE),
    sheenRoughness: 0.18,
    side: THREE.DoubleSide
  });

  // Glossy Whipped Buttercream Pearls (matching heart-shaped cake's warm ivory buttercream)
  const pearlPipingMat = new THREE.MeshPhysicalMaterial({
    color: 0xFAF3E4,              // Warm ivory whipped buttercream pearls (matching heart cake)
    roughness: 0.18,
    metalness: 0.0,
    clearcoat: 0.72,              // Glossy highlight on piped pearl dots
    clearcoatRoughness: 0.10,
    sheen: 1.0,
    sheenColor: new THREE.Color(0xFFF7EC),
    sheenRoughness: 0.18
  });
  disposables.push(pearlPipingMat);

  // Piped French star-tip fluted shell frosting (warm vanilla bean ivory matching heart cake)
  const flutedPipingMat = new THREE.MeshPhysicalMaterial({
    color: 0xF8EEDC,              // Warm French vanilla bean ivory (matching heart cake photo)
    vertexColors: true,           // Enables baked deep golden butter valley ambient occlusion
    roughness: 0.22,              // Freshly piped buttery luster
    metalness: 0.0,
    clearcoat: 0.55,              // Lustrous crest highlights on sharp ridges
    clearcoatRoughness: 0.14,
    sheen: 1.0,                   // Soft velvety dairy sheen
    sheenColor: new THREE.Color(0xFBEFD6),
    sheenRoughness: 0.22,
    normalMap: buttercreamNormalTex,
    normalScale: new THREE.Vector2(0.20, 0.20)
  });

  // Lower scallop accent cream (warm golden custard depth matching heart cake)
  const lowerFluteMat = new THREE.MeshPhysicalMaterial({
    color: 0xF5E4CA,              // Warm golden custard depth (matching heart cake crevices)
    vertexColors: true,
    roughness: 0.24,
    metalness: 0.0,
    clearcoat: 0.48,
    clearcoatRoughness: 0.15,
    sheen: 0.98,
    sheenColor: new THREE.Color(0xF7E6C4),
    sheenRoughness: 0.26,
    normalMap: buttercreamNormalTex,
    normalScale: new THREE.Vector2(0.20, 0.20)
  });

  // Pastel baby pink piped rosebuds (matching roses in heart cake reference photo)
  const pinkRosetteMat = new THREE.MeshPhysicalMaterial({
    color: 0xF5A3B6,              // Sweet pastel French baby pink (matching heart cake roses)
    vertexColors: true,           // Warm shaded crevices in rose petals
    roughness: 0.22,
    metalness: 0.0,
    clearcoat: 0.50,
    clearcoatRoughness: 0.15,
    sheen: 1.0,
    sheenColor: new THREE.Color(0xFFE5ED),
    sheenRoughness: 0.20,
    normalMap: buttercreamNormalTex,
    normalScale: new THREE.Vector2(0.16, 0.16)
  });
  disposables.push(pinkRosetteMat);

  // Muted vintage sage mint leaves (matching leaves in heart cake reference photo)
  const sageLeafMat = new THREE.MeshStandardMaterial({
    color: 0x9CBBA3,              // Muted vintage sage mint
    roughness: 0.35,
    metalness: 0.02
  });
  disposables.push(sageLeafMat);

  // Ceramic cake stand plate (Roasted Cocoa + Gold Rim)
  const cocoaPlateMat = new THREE.MeshStandardMaterial({
    color: 0x3A221B,
    roughness: 0.28,
    metalness: 0.15
  });

  const plateGoldRimMat = new THREE.MeshStandardMaterial({
    color: 0xEACE80,
    metalness: 0.88,
    roughness: 0.20
  });

  const calyxMat = new THREE.MeshStandardMaterial({
    color: 0x3E7134,
    roughness: 0.40,
    metalness: 0.02
  });

  const strawberryMat = new THREE.MeshPhysicalMaterial({
    map: strawberryTex,
    roughness: 0.18,
    metalness: 0.04,
    clearcoat: 0.96,
    clearcoatRoughness: 0.08
  });

  const pearlMat = new THREE.MeshStandardMaterial({
    color: 0xFFFBF2,
    metalness: 0.18,
    roughness: 0.18
  });

  disposables.push(
    cakeBodyMat, creamBevelMat, flutedPipingMat, lowerFluteMat,
    cocoaPlateMat, plateGoldRimMat, calyxMat, strawberryMat, pearlMat,
    pinkSatinRibbonMat
  );

  // ━━━━ CUSTOM PROCEDURAL FLUTED STAR-TIP SHELL GEOMETRY ━━━━
  // Sculpted to exactly replicate the French star pastry tip in reference image:
  // - Distinct sharp curved flutes/ridges running along the length
  // - Baked vertex color gradient: bright ivory crests, warm rich butter valleys (ambient occlusion)
  // - Plump, rounded crest at head with tapered tail
  // - Tail tucks smoothly under overlapping neighbor
  function createFlutedShellGeometry({
    length = 0.58,
    maxRadius = 0.24,
    fluteCount = 7,
    fluteDepth = 0.44,
    twist = 0.42,
    curveY = 0.11,
    curveZ = 0.06,
    segmentsU = 28,
    segmentsV = 44
  } = {}) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    for (let i = 0; i <= segmentsU; i++) {
      const u = i / segmentsU;

      // Longitudinal envelope: u=0 tail, u=0.58 bulbous crest, u=1.0 tuck
      const shape = Math.sin(Math.pow(u, 0.64) * Math.PI);
      const radius = maxRadius * Math.max(0.08, Math.pow(shape, 0.80) * (0.82 + 0.18 * u));

      // Arched spine curve
      const spineX = (u - 0.5) * length;
      const spineY = Math.sin(u * Math.PI) * curveY;
      const spineZ = Math.sin(u * Math.PI * 0.88) * curveZ;

      for (let j = 0; j <= segmentsV; j++) {
        const v = (j / segmentsV) * Math.PI * 2;
        const vTwist = v + twist * u;

        // Sharpened pastry star ridges (cos - 0.25*cos(2x))
        const rawFlute = Math.cos(fluteCount * vTwist) - 0.25 * Math.cos(fluteCount * 2 * vTwist);
        
        // Upper exposed face gets deep crisp flutes, underside softens towards base
        const upperMask = Math.sin(v) > -0.25 ? 1.0 : 0.40;
        const fluteMod = 1.0 + fluteDepth * rawFlute * upperMask;

        const rVal = radius * fluteMod;
        const localY = Math.sin(v) * rVal * 1.22;
        const localZ = Math.cos(v) * rVal * 0.90;

        positions.push(spineX, spineY + localY, spineZ + localZ);
        uvs.push(u, j / segmentsV);

        // Deep warm golden custard groove contrast (matching heart cake photo)
        const ao = Math.max(0, Math.min(1, (rawFlute * 0.5 + 0.5) * upperMask + (1 - upperMask) * 0.5));
        const curvedAo = Math.pow(ao, 0.75);
        const rCol = 0.75 + 0.25 * curvedAo;
        const gCol = 0.63 + 0.35 * curvedAo;
        const bCol = 0.44 + 0.49 * curvedAo;
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

        indices.push(a, b, d);
        indices.push(b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();

    return reg(geo);
  }

  // ━━━━ CUSTOM SPIRAL STAR-PIPED ROSETTE GEOMETRY ━━━━
  // For crown rim rosettes, strawberry cream beds, and festoon drops
  function createFlutedRosetteGeometry({
    baseRadius = 0.26,
    height = 0.32,
    fluteCount = 8,
    fluteDepth = 0.34,
    spiralTwist = 1.45,
    segmentsH = 16,
    segmentsV = 36
  } = {}) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const colors = [];
    const uvs = [];
    const indices = [];

    for (let i = 0; i <= segmentsH; i++) {
      const hNorm = i / segmentsH;
      const y = hNorm * height;

      // Dome taper
      const rBase = baseRadius * Math.pow(Math.max(0, 1 - Math.pow(hNorm, 1.35)), 0.6);
      const twist = hNorm * spiralTwist;

      for (let j = 0; j <= segmentsV; j++) {
        const v = (j / segmentsV) * Math.PI * 2;
        const vTwist = v + twist;

        const rawFlute = Math.cos(fluteCount * vTwist) - 0.20 * Math.cos(fluteCount * 2 * vTwist);
        const fluteMod = 1.0 + fluteDepth * rawFlute * (1 - 0.30 * hNorm);

        const r = rBase * fluteMod;
        const x = Math.cos(v) * r;
        const z = Math.sin(v) * r;

        positions.push(x, y, z);
        uvs.push(j / segmentsV, hNorm);

        const ao = Math.max(0, Math.min(1, rawFlute * 0.5 + 0.5));
        const curvedAo = Math.pow(ao, 0.75);
        const rCol = 0.76 + 0.24 * curvedAo;
        const gCol = 0.64 + 0.34 * curvedAo;
        const bCol = 0.46 + 0.48 * curvedAo;
        colors.push(rCol, gCol, bCol);
      }
    }

    const ringStride = segmentsV + 1;
    for (let i = 0; i < segmentsH; i++) {
      for (let j = 0; j < segmentsV; j++) {
        const a = i * ringStride + j;
        const b = (i + 1) * ringStride + j;
        const c = (i + 1) * ringStride + (j + 1);
        const d = i * ringStride + (j + 1);

        indices.push(a, b, d);
        indices.push(b, c, d);
      }
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();

    return reg(geo);
  }

  // Pre-compiled shared fluted geometries
  const primaryShellGeo = createFlutedShellGeometry({
    length: 0.62,
    maxRadius: 0.24,
    fluteCount: 7,
    fluteDepth: 0.44,
    twist: 0.44,
    curveY: 0.11
  });

  const secondaryShellGeo = createFlutedShellGeometry({
    length: 0.54,
    maxRadius: 0.20,
    fluteCount: 7,
    fluteDepth: 0.40,
    twist: -0.38,
    curveY: 0.09
  });

  const swagPipingGeo = createFlutedShellGeometry({
    length: 0.36,
    maxRadius: 0.155,
    fluteCount: 8,
    fluteDepth: 0.30,
    twist: 0.28,
    curveY: 0.06
  });

  const crownRosetteGeo = createFlutedRosetteGeometry({
    baseRadius: 0.27,
    height: 0.32,
    fluteCount: 8,
    fluteDepth: 0.34,
    spiralTwist: 1.40
  });

  const strawberryBedGeo = createFlutedRosetteGeometry({
    baseRadius: 0.29,
    height: 0.23,
    fluteCount: 8,
    fluteDepth: 0.30,
    spiralTwist: 1.15
  });

  // Reusable delicate sage mint leaf geometry for piped rosebud accents
  const roseLeafGeo = reg(new THREE.ConeGeometry(0.045, 0.14, 5));

  const centerSwirlGeo = createFlutedRosetteGeometry({
    baseRadius: 0.54,
    height: 0.46,
    fluteCount: 10,
    fluteDepth: 0.36,
    spiralTwist: 1.65
  });

  // ━━━━ SILKY PINK SATIN RIBBON BOW BUILDER ━━━━
  // Parametric silky satin ribbon loops with wide fabric face matching reference photo
  function createRibbonLoopGeometry(isLeft = true, width = 0.106) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const uvs = [];
    const indices = [];

    const segments = 36;
    const sign = isLeft ? -1 : 1;

    for (let i = 0; i <= segments; i++) {
      const t = i / segments;
      const phi = t * Math.PI * 2;

      // Closed elliptical tear-drop centerline
      const x = sign * (0.44 * (1.0 - Math.cos(phi)) * 0.5);
      const y = 0.130 * Math.sin(phi) - 0.035 * (1.0 - Math.cos(phi)) * 0.5;
      const z = 0.065 + 0.075 * Math.sin(t * Math.PI);

      // Tangent & in-plane normal for wide face facing viewer (+Z)
      const dx = sign * 0.44 * Math.sin(phi) * Math.PI;
      const dy = 0.130 * Math.cos(phi) * 2 * Math.PI - 0.035 * Math.sin(phi) * Math.PI;
      const len = Math.sqrt(dx * dx + dy * dy) || 1;
      const nx = -dy / len;
      const ny = dx / len;

      const halfW = width * 0.5;

      // Two edges separated across in-plane normal + 3D forward arch
      const pFront = new THREE.Vector3(x + nx * halfW, y + ny * halfW, z + 0.024 * Math.sin(t * Math.PI));
      const pBack  = new THREE.Vector3(x - nx * halfW, y - ny * halfW, z - 0.024 * Math.sin(t * Math.PI));

      positions.push(pFront.x, pFront.y, pFront.z);
      positions.push(pBack.x, pBack.y, pBack.z);

      uvs.push(0, t);
      uvs.push(1, t);
    }

    for (let i = 0; i < segments; i++) {
      const a = i * 2;
      const b = i * 2 + 1;
      const c = (i + 1) * 2;
      const d = (i + 1) * 2 + 1;

      indices.push(a, c, b);
      indices.push(b, c, d);
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();

    return reg(geo);
  }

  // Parametric fluttering silky streamers cascading down the cake
  function createRibbonStreamerGeometry(isLeft = true, width = 0.096) {
    const geo = new THREE.BufferGeometry();
    const positions = [];
    const uvs = [];
    const indices = [];

    const segments = 36;
    const sign = isLeft ? -1 : 1;
    const phase = isLeft ? 0 : 0.85;

    for (let i = 0; i <= segments; i++) {
      const t = i / segments;

      // Flowing downward flutter path flanking the pearl dots
      const x = sign * (0.048 + t * 0.19 + Math.sin(t * 2.8 * Math.PI + phase) * 0.040);
      const y = -0.04 - t * 1.02; // Long cascading streamer reaches down near bottom scalloped shells
      // Drapes gently towards the cake cylinder as it descends
      const z = 0.065 - t * 0.11 + Math.sin(t * 2.5 * Math.PI + phase) * 0.024;

      // Graceful taper and fluttering twist
      const curWidth = width * (1.0 - 0.22 * t);
      const halfW = curWidth * 0.5;
      const twist = Math.sin(t * 2.8 * Math.PI + phase) * 0.32;
      const cosTwist = Math.cos(twist);
      const sinTwist = Math.sin(twist);

      const pL = new THREE.Vector3(x - halfW * cosTwist, y, z + halfW * sinTwist);
      const pR = new THREE.Vector3(x + halfW * cosTwist, y, z - halfW * sinTwist);

      positions.push(pL.x, pL.y, pL.z);
      positions.push(pR.x, pR.y, pR.z);

      uvs.push(0, t);
      uvs.push(1, t);
    }

    for (let i = 0; i < segments; i++) {
      const a = i * 2;
      const b = i * 2 + 1;
      const c = (i + 1) * 2;
      const d = (i + 1) * 2 + 1;

      indices.push(a, c, b);
      indices.push(b, c, d);
    }

    geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geo.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geo.setIndex(indices);
    geo.computeVertexNormals();

    return reg(geo);
  }

  // Pre-compiled Ribbon Bow Geometries
  const leftLoopGeo = createRibbonLoopGeometry(true, 0.106);
  const rightLoopGeo = createRibbonLoopGeometry(false, 0.106);
  const leftStreamerGeo = createRibbonStreamerGeometry(true, 0.096);
  const rightStreamerGeo = createRibbonStreamerGeometry(false, 0.096);

  // Center knot geometries
  const knotGeo = reg(new THREE.SphereGeometry(0.082, 16, 16));
  const knotBandGeo = reg(new THREE.TorusGeometry(0.070, 0.026, 14, 24));

  // Pre-compiled pearl dot geometries (warm ivory buttercream)
  const pearlDotGeos = [
    reg(new THREE.SphereGeometry(0.052, 14, 14)),
    reg(new THREE.SphereGeometry(0.047, 14, 14)),
    reg(new THREE.SphereGeometry(0.042, 14, 14)),
    reg(new THREE.SphereGeometry(0.037, 14, 14)),
    reg(new THREE.SphereGeometry(0.032, 14, 14))
  ];

  function createRibbonBowGroup() {
    const bowGroup = new THREE.Group();

    // Silky Center Cushion Knot (proud of loops)
    const knotMesh = new THREE.Mesh(knotGeo, pinkSatinRibbonMat);
    knotMesh.scale.set(1.30, 0.95, 1.20);
    knotMesh.position.set(0, 0, 0.085);
    bowGroup.add(knotMesh);

    const knotBand = new THREE.Mesh(knotBandGeo, pinkSatinRibbonMat);
    knotBand.rotation.y = Math.PI / 2;
    knotBand.position.set(0, 0, 0.085);
    bowGroup.add(knotBand);

    // Left & Right Wings / Loops (resting proudly on top of frosting)
    const leftLoop = new THREE.Mesh(leftLoopGeo, pinkSatinRibbonMat);
    bowGroup.add(leftLoop);

    const rightLoop = new THREE.Mesh(rightLoopGeo, pinkSatinRibbonMat);
    bowGroup.add(rightLoop);

    // Cascading Fluttering Streamers (draping gracefully down the cake front)
    const leftTail = new THREE.Mesh(leftStreamerGeo, pinkSatinRibbonMat);
    bowGroup.add(leftTail);

    const rightTail = new THREE.Mesh(rightStreamerGeo, pinkSatinRibbonMat);
    bowGroup.add(rightTail);

    return bowGroup;
  }

  // ━━━━ 1. CERAMIC CAKE PLATE (Matching Website Dark Cocoa + Gold Rim) ━━━━
  const plateR = 2.45;
  const plateH = 0.09;
  const plateMesh = new THREE.Mesh(reg(new THREE.CylinderGeometry(plateR, plateR * 0.92, plateH, 64)), cocoaPlateMat);
  plateMesh.position.y = -1.15;
  cakeGroup.add(plateMesh);

  const plateRim = new THREE.Mesh(reg(new THREE.TorusGeometry(plateR, 0.035, 16, 64)), plateGoldRimMat);
  plateRim.rotation.x = Math.PI / 2;
  plateRim.position.y = -1.15 + plateH / 2;
  cakeGroup.add(plateRim);

  const footMesh = new THREE.Mesh(reg(new THREE.CylinderGeometry(plateR * 0.72, plateR * 0.76, 0.08, 64)), cocoaPlateMat);
  footMesh.position.y = -1.15 - 0.08;
  cakeGroup.add(footMesh);

  // ━━━━ 2. MAIN CAKE CYLINDER ━━━━
  const cakeR = 2.05;
  const cakeH = 1.95;
  const cakeY = -1.15 + plateH / 2 + cakeH / 2;

  const cakeMesh = new THREE.Mesh(reg(new THREE.CylinderGeometry(cakeR, cakeR, cakeH, 64)), cakeBodyMat);
  cakeMesh.position.y = cakeY;
  cakeGroup.add(cakeMesh);

  const topBevel = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR - 0.02, 0.045, 16, 64)), creamBevelMat);
  topBevel.rotation.x = Math.PI / 2;
  topBevel.position.y = cakeY + cakeH / 2;
  cakeGroup.add(topBevel);

  const cakeTopY = cakeY + cakeH / 2;
  const cakeBottomY = cakeY - cakeH / 2;

  // ━━━━ 3. AUTHENTIC FRENCH STAR-TIP SCALLOPED SHELL BORDER ━━━━
  // (Matching the reference photo: double overlapping fluted shells encircling the base)
  const bottomShellCount = 28;
  for (let i = 0; i < bottomShellCount; i++) {
    const angle = (i * Math.PI * 2) / bottomShellCount;

    // --- Upper Primary Shell Chain (hugs cylinder base with distinct sharp ridges) ---
    const bx = Math.cos(angle) * (cakeR + 0.08);
    const bz = Math.sin(angle) * (cakeR + 0.08);

    const shell = new THREE.Mesh(primaryShellGeo, flutedPipingMat);
    shell.position.set(bx, cakeBottomY + 0.12, bz);
    shell.rotation.y = -angle + Math.PI / 2;
    shell.rotation.x = 0.48; // Angled directly towards viewer camera for maximum flute visibility
    shell.rotation.z = -0.16;
    cakeGroup.add(shell);

    // --- Lower Interlocking Scallop Wave (fans out onto plate base, exactly like reference) ---
    const midAngle = angle + (Math.PI / bottomShellCount);
    const lx = Math.cos(midAngle) * (cakeR + 0.155);
    const lz = Math.sin(midAngle) * (cakeR + 0.155);

    const lowerShell = new THREE.Mesh(secondaryShellGeo, lowerFluteMat);
    lowerShell.position.set(lx, cakeBottomY + 0.045, lz);
    lowerShell.rotation.y = -midAngle + Math.PI / 2;
    lowerShell.rotation.x = 0.58;
    lowerShell.rotation.z = 0.12;
    lowerShell.scale.set(1.22, 1.05, 1.25);
    cakeGroup.add(lowerShell);
  }

  // ━━━━ 4. SIDE DRAPED FESTOONS / GARLANDS WITH FLUTED RIDGES ━━━━
  const swagCount = 6;
  const swagY = cakeTopY - 0.44;
  const swagPointsPerCurve = 16;
  // Offset phase so swag index 1 is centered straight in front of the camera view
  const swagAngleOffset = (Math.PI / 2 + 0.45) - (1 * Math.PI * 2 / swagCount);

  for (let s = 0; s < swagCount; s++) {
    const startAngle = swagAngleOffset + (s * Math.PI * 2) / swagCount;
    const endAngle = swagAngleOffset + ((s + 1) * Math.PI * 2) / swagCount;

    // 1. Draped scallop garland loop with fluted ribbed segments (piped onto cake first)
    for (let p = 0; p <= swagPointsPerCurve; p++) {
      const t = p / swagPointsPerCurve;
      const currentAngle = startAngle + (endAngle - startAngle) * t;
      const droop = Math.sin(t * Math.PI) * 0.40;
      const py = swagY - droop;

      const px = Math.cos(currentAngle) * (cakeR + 0.06);
      const pz = Math.sin(currentAngle) * (cakeR + 0.06);

      const seg = new THREE.Mesh(swagPipingGeo, flutedPipingMat);
      seg.position.set(px, py, pz);
      seg.rotation.y = -currentAngle + Math.PI / 2;
      seg.rotation.x = 0.30;
      seg.scale.set(1.05, 0.95, 1.15);
      cakeGroup.add(seg);

      // Secondary soft under-fold for voluptuous scalloped depth
      if (p % 2 === 0) {
        const segUnder = new THREE.Mesh(secondaryShellGeo, lowerFluteMat);
        segUnder.scale.set(0.75, 0.70, 0.85);
        segUnder.position.set(px * 0.99, py - 0.07, pz * 0.99);
        segUnder.rotation.y = -currentAngle + Math.PI / 2;
        segUnder.rotation.x = 0.38;
        cakeGroup.add(segUnder);
      }
    }

    // 2. 5 descending warm ivory buttercream pearl dots on the cake cylinder (matching heart cake photo)
    for (let d = 0; d < 5; d++) {
      const dotMesh = new THREE.Mesh(pearlDotGeos[d], pearlPipingMat);
      const dotPy = swagY - 0.16 - d * 0.115;
      const dotPx = Math.cos(startAngle) * (cakeR + 0.038);
      const dotPz = Math.sin(startAngle) * (cakeR + 0.038);
      dotMesh.position.set(dotPx, dotPy, dotPz);
      cakeGroup.add(dotMesh);
    }

    // 3. Vintage Coquette Pink Satin Ribbon Bow (sitting proudly ON TOP OF the garland frosting)
    const bow = createRibbonBowGroup();
    const bowR = cakeR + 0.22;
    const bx = Math.cos(startAngle) * bowR;
    const bz = Math.sin(startAngle) * bowR;
    bow.position.set(bx, swagY + 0.04, bz);
    // Explicitly orient bow facing outward along radial normal away from cake center
    bow.lookAt(bx * 2, swagY + 0.04, bz * 2);
    cakeGroup.add(bow);

    // 4. Delicate pastel baby pink rosebud nestled at lowest droop of swag (matching heart cake reference)
    const midSwagAngle = startAngle + (endAngle - startAngle) * 0.5;
    const midR = cakeR + 0.21;
    const midPx = Math.cos(midSwagAngle) * midR;
    const midPz = Math.sin(midSwagAngle) * midR;
    const midPy = swagY - 0.40;

    const swagRose = new THREE.Mesh(strawberryBedGeo, pinkRosetteMat);
    swagRose.scale.set(0.52, 0.40, 0.52);
    swagRose.position.set(midPx, midPy, midPz);
    // Orient rosette tip pointing outward away from cake center
    const radialDir = new THREE.Vector3(Math.cos(midSwagAngle), 0.22, Math.sin(midSwagAngle)).normalize();
    swagRose.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), radialDir);
    cakeGroup.add(swagRose);

    // Two delicate sage mint leaves flanking the pink rose
    const leafTangent1 = new THREE.Vector3(-Math.sin(midSwagAngle) * 0.65 + Math.cos(midSwagAngle) * 0.75, 0.12, Math.cos(midSwagAngle) * 0.65 + Math.sin(midSwagAngle) * 0.75).normalize();
    const leafTangent2 = new THREE.Vector3(Math.sin(midSwagAngle) * 0.65 + Math.cos(midSwagAngle) * 0.75, 0.12, -Math.cos(midSwagAngle) * 0.65 + Math.sin(midSwagAngle) * 0.75).normalize();

    const leaf1 = new THREE.Mesh(roseLeafGeo, sageLeafMat);
    leaf1.position.set(
      Math.cos(midSwagAngle + 0.05) * (midR - 0.02),
      midPy - 0.02,
      Math.sin(midSwagAngle + 0.05) * (midR - 0.02)
    );
    leaf1.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), leafTangent1);
    cakeGroup.add(leaf1);

    const leaf2 = new THREE.Mesh(roseLeafGeo, sageLeafMat);
    leaf2.position.set(
      Math.cos(midSwagAngle - 0.05) * (midR - 0.02),
      midPy - 0.02,
      Math.sin(midSwagAngle - 0.05) * (midR - 0.02)
    );
    leaf2.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), leafTangent2);
    cakeGroup.add(leaf2);
  }

  // Upper horizontal lace piping band (placed elegantly just below top crown rim)
  const swagRim = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR + 0.04, 0.055, 16, 64)), creamBevelMat);
  swagRim.rotation.x = Math.PI / 2;
  swagRim.position.y = cakeTopY - 0.14;
  cakeGroup.add(swagRim);

  // ━━━━ 5. TOP RIM SPIRAL STAR-PIPED ROSETTES (LUXURIOUS VINTAGE CROWN) ━━━━
  const topRuffleCount = 24;
  for (let i = 0; i < topRuffleCount; i++) {
    const angle = (i * Math.PI * 2) / topRuffleCount;
    const rx = Math.cos(angle) * (cakeR - 0.06);
    const rz = Math.sin(angle) * (cakeR - 0.06);

    const rosette = new THREE.Mesh(crownRosetteGeo, flutedPipingMat);
    rosette.position.set(rx, cakeTopY + 0.02, rz);
    rosette.rotation.y = -angle + (i * 0.2);
    rosette.rotation.x = -0.12;
    rosette.scale.set(1.12, 1.08, 1.12);
    cakeGroup.add(rosette);
  }

  // ━━━━ 6. TOP UPSTREAM STRAWBERRIES & FLUTED CREAM BEDS ━━━━
  function createStrawberryMesh(scale = 1.0) {
    const group = new THREE.Group();

    const points = [];
    points.push(new THREE.Vector2(0.001, 0));
    points.push(new THREE.Vector2(0.10, 0.02));
    points.push(new THREE.Vector2(0.18, 0.08));
    points.push(new THREE.Vector2(0.22, 0.16));
    points.push(new THREE.Vector2(0.21, 0.28));
    points.push(new THREE.Vector2(0.16, 0.42));
    points.push(new THREE.Vector2(0.08, 0.54));
    points.push(new THREE.Vector2(0.001, 0.58));

    const geo = reg(new THREE.LatheGeometry(points, 24));
    const mesh = new THREE.Mesh(geo, strawberryMat);
    group.add(mesh);

    // Fresh green calyx sepals
    const leafGeo = reg(new THREE.ConeGeometry(0.045, 0.14, 4));
    for (let l = 0; l < 5; l++) {
      const lAngle = (l * Math.PI * 2) / 5;
      const leaf = new THREE.Mesh(leafGeo, calyxMat);
      leaf.position.set(Math.cos(lAngle) * 0.10, 0.03, Math.sin(lAngle) * 0.10);
      leaf.rotation.y = -lAngle;
      leaf.rotation.z = Math.PI / 2.4;
      group.add(leaf);
    }

    group.scale.set(scale, scale, scale);
    return group;
  }

  const strawberryCount = 8;
  const strawberryRingR = cakeR * 0.62;

  for (let i = 0; i < strawberryCount; i++) {
    const angle = (i * Math.PI * 2) / strawberryCount;
    const sx = Math.cos(angle) * strawberryRingR;
    const sz = Math.sin(angle) * strawberryRingR;

    // Star-piped cream rosette bed under each strawberry
    const creamBed = new THREE.Mesh(strawberryBedGeo, flutedPipingMat);
    creamBed.position.set(sx, cakeTopY + 0.03, sz);
    creamBed.rotation.y = angle * 2;
    cakeGroup.add(creamBed);

    const strawberry = createStrawberryMesh(1.08);
    strawberry.position.set(sx, cakeTopY + 0.14, sz);
    strawberry.rotation.y = angle;
    strawberry.rotation.x = -0.12;
    cakeGroup.add(strawberry);

    // Delicate pastel baby pink rosebud and sugar pearl between strawberries (matching heart cake photo)
    const midAngle = angle + (Math.PI / strawberryCount);
    const px = Math.cos(midAngle) * (strawberryRingR * 1.05);
    const pz = Math.sin(midAngle) * (strawberryRingR * 1.05);

    const dollop = new THREE.Mesh(strawberryBedGeo, pinkRosetteMat);
    dollop.scale.set(0.62, 0.50, 0.62);
    dollop.position.set(px, cakeTopY + 0.02, pz);
    dollop.rotation.y = midAngle;
    cakeGroup.add(dollop);

    // Subtle sage leaf accent tucked beside each top rosebud
    const topLeaf = new THREE.Mesh(roseLeafGeo, sageLeafMat);
    topLeaf.position.set(
      Math.cos(midAngle) * (strawberryRingR * 1.18),
      cakeTopY + 0.035,
      Math.sin(midAngle) * (strawberryRingR * 1.18)
    );
    topLeaf.rotation.y = -midAngle;
    topLeaf.rotation.x = -0.55;
    cakeGroup.add(topLeaf);

    const pearl = new THREE.Mesh(reg(new THREE.SphereGeometry(0.065, 16, 16)), pearlMat);
    pearl.position.set(px, cakeTopY + 0.125, pz);
    cakeGroup.add(pearl);
  }

  // Grand center fluted whipped cream swirl dome
  const centerSwirl = new THREE.Mesh(centerSwirlGeo, flutedPipingMat);
  centerSwirl.position.set(0, cakeTopY + 0.04, 0);
  cakeGroup.add(centerSwirl);

  // Center ruby strawberry jewel atop center swirl
  const centerBerry = createStrawberryMesh(0.92);
  centerBerry.position.set(0, cakeTopY + 0.44, 0);
  cakeGroup.add(centerBerry);

  // ━━━━ 7. SOFT CONTACT SHADOW ━━━━
  const shadowCanvas = document.createElement('canvas');
  shadowCanvas.width = 256;
  shadowCanvas.height = 256;
  const shCtx = shadowCanvas.getContext('2d');
  const shGrad = shCtx.createRadialGradient(128, 128, 20, 128, 128, 120);
  shGrad.addColorStop(0, 'rgba(15, 8, 6, 0.78)');
  shGrad.addColorStop(0.48, 'rgba(15, 8, 6, 0.25)');
  shGrad.addColorStop(1, 'rgba(15, 8, 6, 0)');
  shCtx.fillStyle = shGrad;
  shCtx.fillRect(0, 0, 256, 256);

  const shadowTex = new THREE.CanvasTexture(shadowCanvas);
  disposables.push(shadowTex);

  const shadowMat = new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, opacity: 0.85 });
  disposables.push(shadowMat);

  const shadowPlane = new THREE.Mesh(reg(new THREE.PlaneGeometry(6.2, 6.2)), shadowMat);
  shadowPlane.rotation.x = -Math.PI / 2;
  shadowPlane.position.y = -1.24;
  cakeGroup.add(shadowPlane);

  // Initial Rest Angle & Position
  const baseRotX = 0.20;
  const baseRotY = 0.45;
  cakeGroup.rotation.x = baseRotX;
  cakeGroup.rotation.y = baseRotY;
  cakeGroup.position.y = 0.36;

  // Entrance animation state
  let entranceStartTime = performance.now();
  let isEntering = true;

  let targetTiltX = 0;
  let targetTiltY = 0;
  let currentTiltX = 0;
  let currentTiltY = 0;

  let isDragging = false;
  let previousMouseX = 0;
  let spinVelocity = 0;
  let isHovered = false;

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
      targetTiltY = nx * 0.32;
      targetTiltX = -ny * 0.16;
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

  const onPointerEnter = () => {
    isHovered = true;
  };

  const onPointerLeave = () => {
    isHovered = false;
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
  heroSection.addEventListener('pointerenter', onPointerEnter);
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

  // Animation Loop
  function loop(now) {
    animFrameId = null;
    if (!isVisible || !isTabActive) return;

    if (!prefersReducedMotion) {
      const t = now * 0.001;

      // 1. Interactive 3D Entrance Animation
      let entranceScale = 1;
      let entranceYOffset = 0;
      if (isEntering) {
        const elapsed = now - entranceStartTime;
        const p = Math.min(1, elapsed / 1400);
        const ease = p === 1 ? 1 : 1 - Math.pow(2, -10 * p) * Math.cos((p * 10 - 0.75) * (2 * Math.PI / 3));
        entranceScale = 0.50 + 0.50 * ease;
        entranceYOffset = (1 - ease) * -0.45;
        cakeGroup.rotation.y += (1 - ease) * 0.12;
        if (p >= 1) isEntering = false;
        needsRender = true;
      }

      cakeGroup.scale.setScalar(0.85 * entranceScale);

      // 2. Gentle Idle Floating
      const bob = Math.sin(t * 1.5) * 0.04;
      cakeGroup.position.y = 0.32 + entranceYOffset + bob;

      shadowPlane.scale.setScalar(1 - bob * 0.22);
      shadowMat.opacity = 0.85 - bob * 0.30;

      // 3. Smooth Tilt Easing
      const lerpFactor = 0.07;
      currentTiltX += (targetTiltX - currentTiltX) * lerpFactor;
      currentTiltY += (targetTiltY - currentTiltY) * lerpFactor;
      cakeGroup.rotation.x = baseRotX + currentTiltX;

      // 4. Spin Velocity / Inertia & Idle Turn
      if (!isDragging) {
        if (Math.abs(spinVelocity) > 0.0002) {
          cakeGroup.rotation.y += spinVelocity;
          spinVelocity *= 0.94;
          needsRender = true;
        } else {
          spinVelocity = 0;
          cakeGroup.rotation.y += 0.0025;
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

  // Initial render
  renderer.render(scene, camera);
  if (!prefersReducedMotion) {
    animFrameId = requestAnimationFrame(loop);
  }

  // Cleanup
  const cleanup = () => {
    if (animFrameId) cancelAnimationFrame(animFrameId);
    canvas.removeEventListener('pointerdown', onPointerDown);
    window.removeEventListener('pointerup', onPointerUp);
    window.removeEventListener('pointercancel', onPointerUp);
    heroSection.removeEventListener('pointermove', onPointerMove);
    heroSection.removeEventListener('pointerenter', onPointerEnter);
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
