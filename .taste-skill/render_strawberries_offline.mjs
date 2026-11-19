import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import { spawn } from 'node:child_process';

const outDir = 'public/images/decor';
fs.mkdirSync(outDir, { recursive: true });

// Create a minimal HTML page that uses Three.js from node_modules to render procedural strawberry
const html = `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { margin: 0; padding: 0; background: transparent; overflow: hidden; }
    canvas { display: block; width: 240px; height: 240px; }
  </style>
</head>
<body>
  <div id="container"></div>
  <script type="importmap">
    {
      "imports": {
        "three": "/node_modules/three/build/three.module.js"
      }
    }
  </script>
  <script type="module">
    import * as THREE from 'three';

    const width = 240;
    const height = 240;
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 100);
    camera.position.set(0, 0.8, 3.8);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(1);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.1;
    document.getElementById('container').appendChild(renderer.domElement);

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xfffaed, 2.0);
    dirLight.position.set(3, 5, 4);
    scene.add(dirLight);

    const rimLight = new THREE.DirectionalLight(0xa5bcd6, 1.2);
    rimLight.position.set(-3, -2, -3);
    scene.add(rimLight);

    // Build procedural strawberry
    const strawberryGroup = new THREE.Group();

    // 1. Body via LatheGeometry
    const points = [];
    // Profile from top center to tip
    const profile = [
      [0.0, 0.85],
      [0.35, 0.88],
      [0.65, 0.75],
      [0.82, 0.45],
      [0.85, 0.10],
      [0.75, -0.30],
      [0.55, -0.70],
      [0.30, -1.05],
      [0.10, -1.25],
      [0.0, -1.30]
    ];
    for (const [r, y] of profile) {
      points.push(new THREE.Vector2(r, y));
    }
    const bodyGeo = new THREE.LatheGeometry(points, 32);
    const bodyMat = new THREE.MeshPhysicalMaterial({
      color: 0x9e1a24,
      roughness: 0.28,
      metalness: 0.05,
      clearcoat: 0.65,
      clearcoatRoughness: 0.15,
      sheen: 0.2,
      sheenColor: 0xff8888
    });
    const bodyMesh = new THREE.Mesh(bodyGeo, bodyMat);
    strawberryGroup.add(bodyMesh);

    // 2. Seeds via phyllotaxis instanced mesh
    const seedGeo = new THREE.ConeGeometry(0.022, 0.045, 6);
    seedGeo.rotateX(Math.PI / 2);
    const seedMat = new THREE.MeshStandardMaterial({
      color: 0xe5c158,
      roughness: 0.4
    });
    const seedCount = 140;
    const instancedSeeds = new THREE.InstancedMesh(seedGeo, seedMat, seedCount);
    const dummy = new THREE.Object3D();
    const goldenAngle = Math.PI * (3 - Math.sqrt(5)); // ~137.5 deg

    for (let i = 0; i < seedCount; i++) {
      const t = i / seedCount;
      const y = 0.75 - t * 1.95; // from top to near tip
      // Approximate radius from profile curve
      let r = 0.85 * Math.sin(Math.acos((y + 0.3) / 1.55));
      if (isNaN(r) || r < 0.05) r = 0.05;
      r *= 0.98; // embed slightly

      const phi = i * goldenAngle;
      const x = r * Math.cos(phi);
      const z = r * Math.sin(phi);

      dummy.position.set(x, y, z);
      // Normal direction outward
      const normal = new THREE.Vector3(x, (y > 0 ? 0.3 : -0.4), z).normalize();
      dummy.quaternion.setFromUnitVectors(new THREE.Vector3(0, 0, 1), normal);
      dummy.scale.set(1, 1, 1);
      dummy.updateMatrix();
      instancedSeeds.setMatrixAt(i, dummy.matrix);
    }
    instancedSeeds.instanceMatrix.needsUpdate = true;
    strawberryGroup.add(instancedSeeds);

    // 3. Calyx (Kelopak) in Pine Green #2C5835
    const calyxMat = new THREE.MeshStandardMaterial({
      color: 0x2C5835,
      roughness: 0.6,
      side: THREE.DoubleSide
    });
    const leafCount = 6;
    for (let i = 0; i < leafCount; i++) {
      const angle = (i * 2 * Math.PI) / leafCount;
      const leafGeo = new THREE.ConeGeometry(0.18, 0.55, 5);
      leafGeo.translate(0, 0.25, 0);
      leafGeo.rotateX(-0.5); // bend outwards and down
      const leafMesh = new THREE.Mesh(leafGeo, calyxMat);
      leafMesh.position.set(0, 0.86, 0);
      leafMesh.rotation.y = angle;
      strawberryGroup.add(leafMesh);
    }

    // Stem
    const stemGeo = new THREE.CylinderGeometry(0.04, 0.05, 0.35, 8);
    stemGeo.translate(0, 0.17, 0);
    const stemMesh = new THREE.Mesh(stemGeo, calyxMat);
    stemMesh.position.set(0, 0.85, 0);
    stemMesh.rotation.z = -0.15;
    strawberryGroup.add(stemMesh);

    scene.add(strawberryGroup);

    window.renderAngle = (angleIndex) => {
      if (angleIndex === 1) {
        // Angle 1: 3/4 perspective, slightly upright
        strawberryGroup.rotation.set(0.35, 0.45, -0.2);
        camera.position.set(0, 0.2, 3.6);
      } else if (angleIndex === 2) {
        // Angle 2: tilted down, showing crown and calyx
        strawberryGroup.rotation.set(0.75, 1.8, 0.3);
        camera.position.set(0, 0.5, 3.6);
      } else {
        // Angle 3: subtle side profile, gentle angle
        strawberryGroup.rotation.set(-0.25, -0.65, 0.35);
        camera.position.set(0, -0.1, 3.6);
      }
      camera.lookAt(0, -0.1, 0);
      renderer.render(scene, camera);
      return renderer.domElement.toDataURL('image/webp', 0.92);
    };

    window.__threeReady = true;
  </script>
</body>
</html>`;

// Start minimal static server
const server = http.createServer((req, res) => {
  if (req.url === '/' || req.url === '/index.html') {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(html);
  } else {
    // Serve from workspace
    const filePath = path.join(process.cwd(), req.url);
    if (fs.existsSync(filePath)) {
      const ext = path.extname(filePath);
      const ct = ext === '.js' ? 'application/javascript' : 'application/octet-stream';
      res.writeHead(200, { 'Content-Type': ct });
      res.end(fs.readFileSync(filePath));
    } else {
      res.writeHead(404);
      res.end('Not found');
    }
  }
});

server.listen(8999, '127.0.0.1', async () => {
  console.log('Static server for offline rendering running on http://127.0.0.1:8999');

  // Launch headless Chrome to render 3 angles
  const profileDir = '/tmp/chrome-strawberry-offline';
  fs.mkdirSync(profileDir, { recursive: true });
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  try { fs.rmSync(portFile); } catch {}

  const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
  const child = spawn(chromePath, [
    '--headless=new',
    `--user-data-dir=${profileDir}`,
    '--remote-debugging-port=0',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    '--window-size=400,400',
    'about:blank'
  ], { stdio: 'ignore' });

  let port = null;
  for (let i = 0; i < 150 && !port; i++) {
    await new Promise(r => setTimeout(r, 100));
    try { port = parseInt(fs.readFileSync(portFile, 'utf8'), 10) || null; } catch {}
  }

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

  const { result: { targetId } } = await send('Target.createTarget', { url: 'http://127.0.0.1:8999/' });
  const { result: { sessionId } } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p) => send(m, p, sessionId).then(r => r.result);
  const ev = x => S('Runtime.evaluate', { expression: x, awaitPromise: true, returnByValue: true }).then(r => r?.result?.value);

  await S('Page.enable');
  await S('Runtime.enable');

  // Wait for __threeReady
  console.log('Waiting for Three.js initialization...');
  for (let i = 0; i < 50; i++) {
    const ready = await ev('window.__threeReady === true');
    if (ready) break;
    await new Promise(r => setTimeout(r, 100));
  }

  // Render 3 angles
  for (let angle = 1; angle <= 3; angle++) {
    console.log(`Rendering strawberry angle ${angle}...`);
    const dataUrl = await ev(`window.renderAngle(${angle})`);
    if (!dataUrl) {
      console.error(`Failed to get dataUrl for angle ${angle}`);
      continue;
    }
    const base64Data = dataUrl.replace(/^data:image\/[a-z]+;base64,/, '');
    const webpPath = path.join(outDir, `strawberry-${angle}.webp`);
    fs.writeFileSync(webpPath, Buffer.from(base64Data, 'base64'));
    console.log(`Saved WebP: ${webpPath} (${fs.statSync(webpPath).size} bytes)`);
  }

  try { child.kill(); } catch {}
  server.close();
  console.log('Strawberry offline rendering finished.');
  process.exit(0);
});
