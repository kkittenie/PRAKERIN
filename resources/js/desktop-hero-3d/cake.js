import * as THREE from 'three';

/**
 * Daisy Bakery 3D Artisanal Cake (Three.js)
 * 
 * Modeled after classic vintage bakery strawberry shortcake:
 * - Single-tier tall barrel cake in warm vanilla buttercream with delicate baby pink blush (#F8E8EC / #F7CCD7)
 * - Bottom rim: 38 sculpted whipped cream shell piping beads encircling the plate
 * - Side swags: 6 wide draped ruffled festoons/garlands hanging in graceful arcs around upper cylinder with horizontal lace band
 * - Top rim: 28 dense fluted whipped cream shell rosettes
 * - Top surface: Crown of 8 plump, upright conical strawberries with seed indentations and ruby gloss, nestled with cream rosettes and pearls
 * - Roasted Cocoa Ceramic Plate (#3A221B) matching website palette with fine Daisy Gold rim (#EACE80)
 * - Soft contact shadow beneath
 * - Full 360° drag-to-spin with momentum, cursor tilt, and gentle idle bobbing
 * - Zero text underneath
 */

export function initHero3DCake(wrapper) {
  if (!wrapper || !window.matchMedia('(min-width: 1024px)').matches) return;

  const canvas = wrapper.querySelector('#desktop-hero-3d-canvas') || wrapper.querySelector('canvas');
  if (!canvas) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Sizing
  let width = wrapper.clientWidth || 460;
  let height = wrapper.clientHeight || 400;

  // Scene & Camera (balanced isometric view showcasing crown of strawberries & side swags)
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(30, width / height, 0.1, 100);
  camera.position.set(0, 2.7, 8.5);
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
  renderer.toneMappingExposure = 1.18;

  // Bakery Studio Lighting
  const ambientLight = new THREE.AmbientLight(0xfff6eb, 1.55);
  scene.add(ambientLight);

  const keyLight = new THREE.DirectionalLight(0xfffaee, 2.85);
  keyLight.position.set(4.2, 5.8, 4.8);
  scene.add(keyLight);

  const fillLight = new THREE.DirectionalLight(0xeace80, 1.35);
  fillLight.position.set(-4.0, -1.0, 3.0);
  scene.add(fillLight);

  const rimLight = new THREE.DirectionalLight(0xa5bcd6, 2.1);
  rimLight.position.set(2.5, 4.0, -4.5);
  scene.add(rimLight);

  // Root Cake Group
  const cakeGroup = new THREE.Group();
  cakeGroup.scale.setScalar(0.85);
  cakeGroup.position.y = 0.32;
  scene.add(cakeGroup);

  // ━━━━ PROCEDURAL STRAWBERRY SEED TEXTURE ━━━━
  const sCanvas = document.createElement('canvas');
  sCanvas.width = 512;
  sCanvas.height = 512;
  const sCtx = sCanvas.getContext('2d');

  const sGrad = sCtx.createRadialGradient(256, 180, 50, 256, 256, 260);
  sGrad.addColorStop(0, '#D41E2D');
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

  // ━━━━ MATERIALS ━━━━
  // Soft baby pink buttercream with delicate strawberry pastel tone
  const babyPinkCakeMat = new THREE.MeshStandardMaterial({
    color: 0xF6C8D3, // Delicate pale baby pink / pastel strawberry cream
    roughness: 0.38,
    metalness: 0.02
  });

  const creamBevelMat = new THREE.MeshStandardMaterial({
    color: 0xFFFCF5,
    roughness: 0.30,
    metalness: 0.02
  });

  const ruffleCreamMat = new THREE.MeshStandardMaterial({
    color: 0xFFFFFF, // Pure white whipped cream piping to contrast with baby pink
    roughness: 0.26,
    metalness: 0.02
  });

  const cocoaPlateMat = new THREE.MeshStandardMaterial({
    color: 0x3A221B, // Website cocoa
    roughness: 0.28,
    metalness: 0.15
  });

  const plateGoldRimMat = new THREE.MeshStandardMaterial({
    color: 0xEACE80,
    metalness: 0.88,
    roughness: 0.20
  });

  const calyxMat = new THREE.MeshStandardMaterial({
    color: 0x3E7134, // Fresh green strawberry calyx leaves
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
    metalness: 0.15,
    roughness: 0.20
  });

  const disposables = [
    babyPinkCakeMat, creamBevelMat, ruffleCreamMat, cocoaPlateMat,
    plateGoldRimMat, strawberryMat, calyxMat, pearlMat, strawberryTex
  ];

  function reg(geo) {
    disposables.push(geo);
    return geo;
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

  const cakeMesh = new THREE.Mesh(reg(new THREE.CylinderGeometry(cakeR, cakeR, cakeH, 64)), babyPinkCakeMat);
  cakeMesh.position.y = cakeY;
  cakeGroup.add(cakeMesh);

  const topBevel = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR - 0.03, 0.05, 16, 64)), creamBevelMat);
  topBevel.rotation.x = Math.PI / 2;
  topBevel.position.y = cakeY + cakeH / 2;
  cakeGroup.add(topBevel);

  const cakeTopY = cakeY + cakeH / 2;
  const cakeBottomY = cakeY - cakeH / 2;

  // ━━━━ 3. BOTTOM SHELL PIPING BORDER (Decadent Fluffy Whipped Cream) ━━━━
  const bottomShellCount = 36;
  for (let i = 0; i < bottomShellCount; i++) {
    const angle = (i * Math.PI * 2) / bottomShellCount;
    const bx = Math.cos(angle) * (cakeR + 0.06);
    const bz = Math.sin(angle) * (cakeR + 0.06);

    const shell = new THREE.Mesh(reg(new THREE.SphereGeometry(0.17, 16, 16)), ruffleCreamMat);
    shell.scale.set(1.2, 0.88, 1.6);
    shell.position.set(bx, cakeBottomY + 0.09, bz);
    shell.rotation.y = -angle + Math.PI / 2;
    shell.rotation.x = 0.22;
    cakeGroup.add(shell);
  }

  // ━━━━ 4. SIDE DRAPED FESTOONS / GARLANDS (LUSCIOUS RUFFLED SWAGS) ━━━━
  const swagCount = 6;
  const swagY = cakeTopY - 0.44;
  const swagPointsPerCurve = 18;

  for (let s = 0; s < swagCount; s++) {
    const startAngle = (s * Math.PI * 2) / swagCount;
    const endAngle = ((s + 1) * Math.PI * 2) / swagCount;

    // Rosette junction knot at the start of each swag
    const jx = Math.cos(startAngle) * (cakeR + 0.08);
    const jz = Math.sin(startAngle) * (cakeR + 0.08);
    const knot = new THREE.Mesh(reg(new THREE.SphereGeometry(0.20, 16, 16)), ruffleCreamMat);
    knot.position.set(jx, swagY + 0.03, jz);
    cakeGroup.add(knot);

    const knotDrop = new THREE.Mesh(reg(new THREE.ConeGeometry(0.12, 0.26, 12)), ruffleCreamMat);
    knotDrop.position.set(jx, swagY - 0.12, jz);
    knotDrop.rotation.x = Math.PI;
    cakeGroup.add(knotDrop);

    // Draped scallop garland loop with fluffy layered ruffles
    for (let p = 0; p <= swagPointsPerCurve; p++) {
      const t = p / swagPointsPerCurve;
      const currentAngle = startAngle + (endAngle - startAngle) * t;
      const droop = Math.sin(t * Math.PI) * 0.42;
      const py = swagY - droop;

      const px = Math.cos(currentAngle) * (cakeR + 0.06);
      const pz = Math.sin(currentAngle) * (cakeR + 0.06);

      // Primary voluminous ruffle fold
      const seg = new THREE.Mesh(reg(new THREE.SphereGeometry(0.165, 14, 14)), ruffleCreamMat);
      seg.scale.set(1.3, 0.96, 1.55);
      seg.position.set(px, py, pz);
      seg.rotation.y = -currentAngle + Math.PI / 2;
      seg.rotation.x = 0.28;
      cakeGroup.add(seg);

      // Secondary soft under-fold for maximum cloud fluffiness
      if (p % 2 === 0) {
        const segUnder = new THREE.Mesh(reg(new THREE.SphereGeometry(0.13, 12, 12)), ruffleCreamMat);
        segUnder.scale.set(1.2, 0.85, 1.4);
        segUnder.position.set(px * 0.99, py - 0.08, pz * 0.99);
        segUnder.rotation.y = -currentAngle + Math.PI / 2;
        segUnder.rotation.x = 0.35;
        cakeGroup.add(segUnder);
      }
    }
  }

  // Upper horizontal lace piping band
  const swagRim = new THREE.Mesh(reg(new THREE.TorusGeometry(cakeR + 0.04, 0.065, 16, 64)), creamBevelMat);
  swagRim.rotation.x = Math.PI / 2;
  swagRim.position.y = swagY + 0.05;
  cakeGroup.add(swagRim);

  // ━━━━ 5. TOP RIM FLUTED WHIPPED CREAM RUFFLES (EXTREMELY FLUFFY & VOLUMINOUS) ━━━━
  const topRuffleCount = 26;
  for (let i = 0; i < topRuffleCount; i++) {
    const angle = (i * Math.PI * 2) / topRuffleCount;
    const rx = Math.cos(angle) * (cakeR - 0.06);
    const rz = Math.sin(angle) * (cakeR - 0.06);

    const swirl = new THREE.Group();
    swirl.position.set(rx, cakeTopY, rz);

    // Thick puffy outer cream pillow
    const b1 = new THREE.Mesh(reg(new THREE.SphereGeometry(0.28, 16, 16)), ruffleCreamMat);
    b1.scale.set(1.25, 0.96, 1.2);
    b1.position.y = 0.12;
    swirl.add(b1);

    // Inner fluffy crest fold
    const b1Inner = new THREE.Mesh(reg(new THREE.SphereGeometry(0.22, 14, 14)), ruffleCreamMat);
    b1Inner.scale.set(1.1, 0.88, 1.1);
    b1Inner.position.set(-0.06, 0.20, 0);
    swirl.add(b1Inner);

    // Fluted decorative swirl cone
    const b2 = new THREE.Mesh(reg(new THREE.ConeGeometry(0.16, 0.30, 14)), ruffleCreamMat);
    b2.position.set(0, 0.30, 0);
    b2.rotation.z = 0.28;
    swirl.add(b2);

    swirl.rotation.y = -angle + Math.PI / 2;
    cakeGroup.add(swirl);
  }

  // ━━━━ 6. TOP UPSTREAM STRAWBERRIES (CONICAL, UPRIGHT, JEWEL CROWN) ━━━━
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

    // Plump whipped cream bed under each strawberry
    const creamBed = new THREE.Mesh(reg(new THREE.SphereGeometry(0.26, 16, 16)), ruffleCreamMat);
    creamBed.scale.set(1.15, 0.70, 1.15);
    creamBed.position.set(sx, cakeTopY + 0.08, sz);
    cakeGroup.add(creamBed);

    const strawberry = createStrawberryMesh(1.08);
    strawberry.position.set(sx, cakeTopY + 0.12, sz);
    strawberry.rotation.y = angle;
    strawberry.rotation.x = -0.12; // slight inward tilt
    cakeGroup.add(strawberry);

    // Fluffy cream rosette between each strawberry
    const midAngle = angle + (Math.PI / strawberryCount);
    const px = Math.cos(midAngle) * (strawberryRingR * 1.05);
    const pz = Math.sin(midAngle) * (strawberryRingR * 1.05);
    const pearl = new THREE.Mesh(reg(new THREE.SphereGeometry(0.15, 14, 14)), ruffleCreamMat);
    pearl.scale.set(1.1, 0.85, 1.1);
    pearl.position.set(px, cakeTopY + 0.12, pz);
    cakeGroup.add(pearl);
  }

  // Center large fluffy whipped cream dome
  const centerPuff = new THREE.Mesh(reg(new THREE.SphereGeometry(0.35, 18, 18)), creamBevelMat);
  centerPuff.scale.set(1.15, 0.60, 1.15);
  centerPuff.position.set(0, cakeTopY + 0.09, 0);
  cakeGroup.add(centerPuff);

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

  // Initial Rest Angle & Position (Raised up so plate & cream are 100% visible with ample margin)
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
  let lastTime = performance.now();

  function loop(now) {
    animFrameId = null;
    if (!isVisible || !isTabActive) return;

    lastTime = now;

    if (!prefersReducedMotion) {
      const t = now * 0.001;

      // 1. Interactive 3D Entrance Animation
      let entranceScale = 1;
      let entranceYOffset = 0;
      if (isEntering) {
        const elapsed = now - entranceStartTime;
        const p = Math.min(1, elapsed / 1400);
        // Smooth elastic ease-out curve with delightful scale pop-in & lift
        const ease = p === 1 ? 1 : 1 - Math.pow(2, -10 * p) * Math.cos((p * 10 - 0.75) * (2 * Math.PI / 3));
        entranceScale = 0.50 + 0.50 * ease;
        entranceYOffset = (1 - ease) * -0.45;
        cakeGroup.rotation.y += (1 - ease) * 0.12;
        if (p >= 1) isEntering = false;
        needsRender = true;
      }

      cakeGroup.scale.setScalar(0.85 * entranceScale);

      // 2. Gentle Idle Floating (Bobbing, with ample bottom clearance)
      const bob = Math.sin(t * 1.5) * 0.04;
      cakeGroup.position.y = 0.32 + entranceYOffset + bob;

      // Dynamic shadow scale & opacity
      shadowPlane.scale.setScalar(1 - bob * 0.22);
      shadowMat.opacity = 0.85 - bob * 0.30;

      // 3. Smooth Tilt Easing
      const lerpFactor = 0.07;
      currentTiltX += (targetTiltX - currentTiltX) * lerpFactor;
      currentTiltY += (targetTiltY - currentTiltY) * lerpFactor;
      cakeGroup.rotation.x = baseRotX + currentTiltX;

      // 4. Spin Velocity / Inertia & Peaceful Idle Turn
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

  // Observers
  const observer = new IntersectionObserver((entries) => {
    isVisible = entries[0].isIntersecting;
    if (isVisible && isTabActive) {
      lastTime = performance.now();
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
      lastTime = performance.now();
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
