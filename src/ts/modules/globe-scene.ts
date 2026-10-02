/**
 * Planeta 3D de la sección Distribuidores (front-page.php).
 *
 * Textura de la Tierra con las fronteras dibujadas encima (topología
 * world-atlas 110m) y los países conectados remarcados. Alrededor: halo de
 * atmósfera, órbitas con satélites, partículas y bokeh — todo dentro del
 * mismo canvas, que ocupa la sección entera (el encuadre corre el planeta a
 * la derecha con `setViewOffset`).
 *
 * Interacción:
 *  - Arrastre SOLO horizontal, acotado a la franja de Latinoamérica (con
 *    rebote elástico al pasarse) e inercia al soltar.
 *  - `focusCountry(slug)`: gira hasta el país, inclina hacia su latitud, lo
 *    ilumina (mapa emisivo) y lo señala con un pulso y un tooltip fijo.
 *  - Hover sobre un país conectado → tooltip con su nombre; clic → lo elige
 *    (y deja una onda donde se tocó). El país se detecta por su polígono con
 *    un mapa de IDs pintado offscreen; sin código ISO, por su marcador.
 *
 * No dibuja mientras la sección está fuera de pantalla o la pestaña oculta.
 */

import {
  AdditiveBlending,
  AddEquation,
  BackSide,
  BufferAttribute,
  BufferGeometry,
  CanvasTexture,
  Clock,
  Color,
  CustomBlending,
  DirectionalLight,
  Group,
  HemisphereLight,
  Line,
  LineBasicMaterial,
  LineDashedMaterial,
  Material,
  Mesh,
  MeshStandardMaterial,
  OneFactor,
  PerspectiveCamera,
  Points,
  PointsMaterial,
  Raycaster,
  Scene,
  ShaderMaterial,
  SphereGeometry,
  Sprite,
  SpriteMaterial,
  SRGBColorSpace,
  Texture,
  Vector2,
  Vector3,
  WebGLRenderer,
  ACESFilmicToneMapping,
} from 'three';
import { feature } from 'topojson-client';
import type { Topology, GeometryCollection } from 'topojson-specification';
import type { Geometry } from 'geojson';

// Giro horizontal permitido (grados). Se amplía solo si algún país cargado
// queda fuera de la franja, para que siempre se pueda llegar a él.
const ROT_MIN = -60;
const ROT_MAX = 30;
const D2R = Math.PI / 180;
// Ancho de las texturas auxiliares (rugosidad / emisivo) y del mapa de IDs.
const AUX_W = 2048;
const ID_W = 1024;

export interface GlobeCountry {
  slug: string;
  name: string;
  lat: number;
  lng: number;
  /** ISO 3166-1 numérico de 3 dígitos; vacío si no se conoce. */
  iso: string;
}

export interface GlobeTextures {
  map: string;
  topology: string;
}

export interface GlobeUI {
  hoverTip: HTMLElement | null;
  selTip: HTMLElement | null;
  selTipLabel: HTMLElement | null;
}

export interface GlobeHandle {
  focusCountry: (slug: string, fromTap?: boolean) => void;
  destroy: () => void;
}

type Ring = number[][];

const makeCanvas = (w: number, h: number): HTMLCanvasElement => {
  const c = document.createElement('canvas');
  c.width = w;
  c.height = h;
  return c;
};

const ctx2d = (c: HTMLCanvasElement, opts?: CanvasRenderingContext2DSettings): CanvasRenderingContext2D => {
  const x = c.getContext('2d', opts);
  if (!x) throw new Error('Canvas 2D no disponible');
  return x;
};

const loadImage = (src: string): Promise<HTMLImageElement> =>
  new Promise((resolve, reject) => {
    const img = new Image();
    img.decoding = 'async';
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error(`No se pudo cargar ${src}`));
    img.src = src;
  });

/** Punto unitario de la esfera para lat/lng (mismo mapeo UV que SphereGeometry). */
const ll2v = (lat: number, lng: number): Vector3 => {
  const la = lat * D2R;
  const lo = lng * D2R;
  return new Vector3(Math.cos(la) * Math.cos(lo), Math.sin(la), -Math.cos(la) * Math.sin(lo));
};

/** Dibuja un polígono GeoJSON en proyección equirectangular (corrige el antimeridiano). */
function trace(ctx: CanvasRenderingContext2D, geom: Geometry, W: number, H: number): void {
  const polys: Ring[][] =
    geom.type === 'Polygon' ? [geom.coordinates] : geom.type === 'MultiPolygon' ? geom.coordinates : [];

  for (const poly of polys) {
    for (const ring of poly) {
      let cross = false;
      let maxLat = -90;
      for (let k = 0; k < ring.length; k++) {
        if (k && Math.abs(ring[k][0] - ring[k - 1][0]) > 180) cross = true;
        maxLat = Math.max(maxLat, ring[k][1]);
      }

      let pts = ring;
      let offs = [0];
      if (cross && maxLat > -55) {
        let off = 0;
        pts = [ring[0]];
        for (let k = 1; k < ring.length; k++) {
          const dl = ring[k][0] - ring[k - 1][0];
          if (dl > 180) off -= 360;
          else if (dl < -180) off += 360;
          pts.push([ring[k][0] + off, ring[k][1]]);
        }
        offs = [-W, 0, W];
      }

      for (const o of offs) {
        pts.forEach((p, k) => {
          const x = ((p[0] + 180) / 360) * W + o;
          const y = ((90 - p[1]) / 180) * H;
          if (k) ctx.lineTo(x, y);
          else ctx.moveTo(x, y);
        });
        ctx.closePath();
      }
    }
  }
}

const traceAll = (ctx: CanvasRenderingContext2D, geoms: (Geometry | undefined)[], W: number, H: number): void => {
  ctx.beginPath();
  geoms.forEach((g) => g && trace(ctx, g, W, H));
};

export async function initGlobeScene(
  container: HTMLElement,
  countries: GlobeCountry[],
  textures: GlobeTextures,
  ui: GlobeUI,
  isNarrow: () => boolean,
  onCountryClick?: (slug: string) => void,
): Promise<GlobeHandle> {
  const [world, diffImg] = await Promise.all([
    fetch(textures.topology).then((r) => {
      if (!r.ok) throw new Error(`topology ${r.status}`);
      return r.json() as Promise<Topology<{ countries: GeometryCollection; land: GeometryCollection }>>;
    }),
    loadImage(textures.map),
  ]);

  const worldCountries = feature(world, world.objects.countries).features;
  const landGeoms = feature(world, world.objects.land).features.map((f) => f.geometry);
  const byIso = new Map<string, Geometry>();
  worldCountries.forEach((f) => byIso.set(String(f.id).padStart(3, '0'), f.geometry));
  const feats = countries.map((c) => (c.iso ? byIso.get(c.iso) : undefined));

  // ---------- Texturas ----------
  // Color: imagen + todas las fronteras suaves + las de los países conectados más marcadas.
  const big = Math.max(screen.width, screen.height) * (window.devicePixelRatio || 1) > 2000;
  const W = big ? 4096 : 2048;
  const H = W / 2;
  const colorCanvas = makeCanvas(W, H);
  const cx = ctx2d(colorCanvas);
  cx.filter = 'saturate(1.12) contrast(1.04)';
  cx.imageSmoothingQuality = 'high';
  cx.drawImage(diffImg, 0, 0, W, H);
  cx.filter = 'none';
  traceAll(cx, worldCountries.map((f) => f.geometry), W, H);
  cx.lineWidth = 1.6;
  cx.strokeStyle = 'rgba(230,255,235,0.2)';
  cx.stroke();
  traceAll(cx, feats, W, H);
  cx.lineWidth = 2.4;
  cx.strokeStyle = 'rgba(255,255,255,0.45)';
  cx.stroke();

  // Rugosidad: mar brillante, tierra mate.
  const roughCanvas = makeCanvas(AUX_W, AUX_W / 2);
  const rx = ctx2d(roughCanvas);
  rx.fillStyle = 'rgb(62,62,62)';
  rx.fillRect(0, 0, AUX_W, AUX_W / 2);
  traceAll(rx, landGeoms, AUX_W, AUX_W / 2);
  rx.fillStyle = 'rgb(242,242,242)';
  rx.fill('evenodd');

  // Emisivo: se repinta con el país seleccionado en focusCountry().
  const emCanvas = makeCanvas(AUX_W, AUX_W / 2);
  const ex = ctx2d(emCanvas);
  ex.fillStyle = '#000';
  ex.fillRect(0, 0, AUX_W, AUX_W / 2);

  // Mapa de IDs para detectar el país bajo el puntero: índice+1 repartido en
  // R y G de a pasos de 16 (tolera el antialias de los bordes al redondear).
  const ID_H = ID_W / 2;
  const idCanvas = makeCanvas(ID_W, ID_H);
  const ix = ctx2d(idCanvas, { willReadFrequently: true });
  ix.fillStyle = '#000';
  ix.fillRect(0, 0, ID_W, ID_H);
  feats.forEach((g, i) => {
    if (!g) return;
    const n = i + 1;
    traceAll(ix, [g], ID_W, ID_H);
    ix.fillStyle = `rgb(${(n % 16) * 16},${Math.floor(n / 16) * 16},0)`;
    ix.fill('evenodd');
  });
  const idData = ix.getImageData(0, 0, ID_W, ID_H).data;

  // ---------- Escena ----------
  const coarse = window.matchMedia('(pointer: coarse)').matches;
  const pixelRatio = (): number => Math.min(window.devicePixelRatio || 1, isNarrow() || coarse ? 1.5 : 2);

  const renderer = new WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(pixelRatio());
  renderer.outputColorSpace = SRGBColorSpace;
  renderer.toneMapping = ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  container.appendChild(renderer.domElement);
  const el = renderer.domElement;
  const aniso = renderer.capabilities.getMaxAnisotropy();
  const textureList: Texture[] = [];
  const tex = (c: HTMLCanvasElement, srgb: boolean): CanvasTexture => {
    const t = new CanvasTexture(c);
    if (srgb) t.colorSpace = SRGBColorSpace;
    t.anisotropy = aniso;
    textureList.push(t);
    return t;
  };

  const scene = new Scene();
  const camera = new PerspectiveCamera(32, 1, 0.1, 100);
  scene.add(new HemisphereLight(0xeaf5ff, 0x0a2058, 0.9));
  const sun = new DirectionalLight(0xfff4dc, 2.4);
  sun.position.set(-3, 2.2, 4);
  scene.add(sun);
  const rim = new DirectionalLight(0x8fd0ff, 0.9);
  rim.position.set(4, 1, -3);
  scene.add(rim);

  const rootG = new Group();
  const tilt = new Group();
  const spin = new Group();
  scene.add(rootG);
  rootG.add(tilt);
  tilt.add(spin);

  const emTex = tex(emCanvas, true);
  const globeMat = new MeshStandardMaterial({
    map: tex(colorCanvas, true),
    roughnessMap: tex(roughCanvas, false),
    roughness: 1,
    metalness: 0,
    emissive: 0xffffff,
    emissiveMap: emTex,
    emissiveIntensity: 0.7,
  });
  const globe = new Mesh(new SphereGeometry(1, 96, 64), globeMat);
  spin.add(globe);

  // Marcadores: un punto blanco por país.
  const markerCanvas = makeCanvas(64, 64);
  const mx = ctx2d(markerCanvas);
  const mg = mx.createRadialGradient(32, 32, 14, 32, 32, 32);
  mg.addColorStop(0, 'rgba(5,30,90,0.35)');
  mg.addColorStop(1, 'rgba(5,30,90,0)');
  mx.fillStyle = mg;
  mx.fillRect(0, 0, 64, 64);
  mx.fillStyle = '#ffffff';
  mx.beginPath();
  mx.arc(32, 32, 15, 0, Math.PI * 2);
  mx.fill();
  const markerTex = tex(markerCanvas, true);
  const markers = countries.map((c, i) => {
    const sp = new Sprite(new SpriteMaterial({ map: markerTex, transparent: true, depthWrite: false }));
    sp.position.copy(ll2v(c.lat, c.lng).multiplyScalar(1.008));
    sp.scale.setScalar(0.05);
    sp.userData.index = i;
    spin.add(sp);
    return sp;
  });

  // Efectos sobre la superficie: borde, ondas al tocar y pulso del país elegido.
  const acc = new Color('#d4f1ff');
  const U = {
    uTime: { value: 0 },
    uAcc: { value: new Vector3(acc.r, acc.g, acc.b) },
    uRim: { value: new Vector3(0.7, 0.88, 1.0) },
    uRimI: { value: 0.26 },
    uRip: { value: [0, 1, 2, 3].map(() => new Vector3(0, 0, 1)) },
    uRipT: { value: [-1, -1, -1, -1] },
    uSel: { value: new Vector3(0, 0, 1) },
    uSelOn: { value: 0 },
  };
  const addBlend = {
    transparent: true,
    depthWrite: false,
    blending: CustomBlending,
    blendEquation: AddEquation,
    blendSrc: OneFactor,
    blendDst: OneFactor,
    blendSrcAlpha: OneFactor,
    blendDstAlpha: OneFactor,
  } as const;

  spin.add(new Mesh(new SphereGeometry(1.004, 96, 64), new ShaderMaterial({
    uniforms: U,
    ...addBlend,
    vertexShader: `varying vec3 vL; varying vec3 vN; varying vec3 vV;
      void main(){ vL = normalize(position); vN = normalize(normalMatrix*normal);
      vec4 mv = modelViewMatrix*vec4(position,1.0); vV = -mv.xyz; gl_Position = projectionMatrix*mv; }`,
    fragmentShader: `uniform float uTime; uniform vec3 uAcc; uniform vec3 uRim; uniform float uRimI;
      uniform vec3 uRip[4]; uniform float uRipT[4]; uniform vec3 uSel; uniform float uSelOn;
      varying vec3 vL; varying vec3 vN; varying vec3 vV;
      float ang(vec3 a, vec3 b){ return acos(clamp(dot(a,b),-1.0,1.0)); }
      void main(){
        vec3 n = normalize(vL);
        float fres = pow(1.0 - max(dot(normalize(vN), normalize(vV)), 0.0), 3.0);
        vec3 col = uRim * fres * uRimI;
        float lat = asin(n.y); float lon = atan(n.z, n.x);
        vec2 gv = vec2(lon, lat) * (110.0/3.14159);
        float dots = smoothstep(0.2, 0.06, length(fract(gv) - 0.5));
        for (int i = 0; i < 4; i++) {
          float t = uRipT[i];
          if (t >= 0.0 && t < 2.2) {
            float d = ang(n, uRip[i]);
            float r = 0.03 + t * 0.5;
            float ring = exp(-pow((d - r) / 0.006, 2.0));
            float ring2 = exp(-pow((d - r * 0.62) / 0.004, 2.0)) * 0.25;
            float trail = smoothstep(r - 0.32, r, d) * step(d, r);
            float fade = pow(1.0 - t / 2.2, 1.6);
            col += uAcc * (ring * 0.75 + ring2 + trail * dots * 0.22) * fade;
            col += vec3(0.9, 1.0, 0.85) * exp(-d * d * 900.0) * exp(-t * 6.0) * 0.35;
          }
        }
        if (uSelOn > 0.001) {
          float d = ang(n, uSel);
          float core = exp(-pow(d / 0.011, 2.0));
          float p1 = fract(uTime * 0.45); float p2 = fract(uTime * 0.45 + 0.5);
          float pulse = exp(-pow((d - p1 * 0.16) / 0.003, 2.0)) * (1.0 - p1) + exp(-pow((d - p2 * 0.16) / 0.003, 2.0)) * (1.0 - p2);
          float halo = exp(-pow(d / 0.06, 2.0)) * 0.18;
          col += uAcc * (core * 1.6 + pulse * 1.1 + halo) * uSelOn;
        }
        col = clamp(col, 0.0, 1.0); gl_FragColor = vec4(col, max(col.r, max(col.g, col.b)));
      }`,
  })));

  // Línea de atmósfera sutil.
  rootG.add(new Mesh(new SphereGeometry(1.055, 64, 48), new ShaderMaterial({
    side: BackSide,
    ...addBlend,
    vertexShader: `varying vec3 vN; varying vec3 vV; void main(){ vN = normalize(normalMatrix*normal);
      vec4 mv = modelViewMatrix*vec4(position,1.0); vV = -mv.xyz; gl_Position = projectionMatrix*mv; }`,
    fragmentShader: `varying vec3 vN; varying vec3 vV; void main(){
      float d = dot(normalize(vN), normalize(vV));
      float line = exp(-pow((d + 0.02) / 0.014, 2.0)) * 0.42;
      float haze = pow(clamp(-d / 0.32, 0.0, 1.0), 2.2) * 0.14;
      float a = clamp(line + haze, 0.0, 1.0); gl_FragColor = vec4(vec3(0.85, 0.94, 1.0) * a, a); }`,
  })));

  // Partículas de fondo y bokeh.
  const particleCanvas = makeCanvas(64, 64);
  const px = ctx2d(particleCanvas);
  const pg = px.createRadialGradient(32, 32, 0, 32, 32, 32);
  pg.addColorStop(0, 'rgba(255,255,255,1)');
  pg.addColorStop(0.4, 'rgba(255,255,255,0.35)');
  pg.addColorStop(1, 'rgba(255,255,255,0)');
  px.fillStyle = pg;
  px.fillRect(0, 0, 64, 64);
  const particleTex = tex(particleCanvas, true);

  const N = coarse ? 400 : 700;
  const pos = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) {
    const v = new Vector3(Math.random() * 2 - 1, Math.random() * 2 - 1, Math.random() * 2 - 1)
      .normalize()
      .multiplyScalar(3 + Math.random() * 9);
    if (v.z > 1.2) v.z = -v.z;
    pos.set([v.x, v.y, v.z], i * 3);
  }
  const pgeo = new BufferGeometry();
  pgeo.setAttribute('position', new BufferAttribute(pos, 3));
  const parts = new Points(pgeo, new PointsMaterial({
    size: 0.06, map: particleTex, color: 0xd6ecff, transparent: true, opacity: 0.35, depthWrite: false, blending: AdditiveBlending,
  }));
  scene.add(parts);

  const BN = 90;
  const bpos = new Float32Array(BN * 3);
  for (let i = 0; i < BN; i++) bpos.set([(Math.random() - 0.5) * 14, (Math.random() - 0.5) * 9, -2 - Math.random() * 6], i * 3);
  const bgeo = new BufferGeometry();
  bgeo.setAttribute('position', new BufferAttribute(bpos, 3));
  const bokeh = new Points(bgeo, new PointsMaterial({
    size: 0.32, map: particleTex, color: 0xcfe8ff, transparent: true, opacity: 0.14, depthWrite: false, blending: AdditiveBlending,
  }));
  scene.add(bokeh);

  // Órbitas de fondo, tres con un satélite.
  const orbits = new Group();
  orbits.position.z = -0.35;
  scene.add(orbits);
  const orbitDots: { dot: Sprite; r: number; sp: number; ph: number }[] = [];
  ([[1.38, 0.2, false], [1.72, 0.14, true], [2.12, 0.1, false], [2.62, 0.06, false]] as const).forEach(([r, op, dashed], i) => {
    const pts: Vector3[] = [];
    for (let k = 0; k <= 256; k++) {
      const a = (k / 256) * Math.PI * 2;
      pts.push(new Vector3(Math.cos(a) * r, Math.sin(a) * r, 0));
    }
    const g = new BufferGeometry().setFromPoints(pts);
    const m = dashed
      ? new LineDashedMaterial({ color: 0xe6f3ff, transparent: true, opacity: op, dashSize: 0.03, gapSize: 0.05, depthWrite: false })
      : new LineBasicMaterial({ color: 0xe6f3ff, transparent: true, opacity: op, depthWrite: false });
    const line = new Line(g, m);
    if (dashed) line.computeLineDistances();
    orbits.add(line);
    if (i < 3) {
      const dot = new Sprite(new SpriteMaterial({
        map: particleTex, color: 0xffffff, transparent: true, opacity: 0.55 - i * 0.12, depthWrite: false, blending: AdditiveBlending,
      }));
      dot.scale.setScalar(0.09 - i * 0.015);
      orbits.add(dot);
      orbitDots.push({ dot, r, sp: (0.06 - i * 0.015) * (i % 2 ? -1 : 1), ph: Math.random() * 6.28 });
    }
  });

  // ---------- Encuadre ----------
  // Se agrupa en un frame y se renderiza en el acto, así el canvas no queda en blanco.
  let lastW = 0;
  let lastH = 0;
  let resizeQueued = false;
  const resize = (): void => {
    resizeQueued = false;
    const w = container.clientWidth || 1;
    const h = container.clientHeight || 1;
    if (w === lastW && h === lastH) return;
    lastW = w;
    lastH = h;
    const a = w / h;
    const T = 0.5735; // tan(16°): semi-FOV vertical de la cámara
    const narrow = isNarrow();
    renderer.setPixelRatio(pixelRatio());
    renderer.setSize(w, h, false);
    camera.aspect = a;
    const D = narrow
      ? Math.max(2 / (0.8 * T), 2 / (0.8 * T * a))
      : Math.max(2 / (0.78 * T), 2 / (0.36 * T * a));
    camera.position.set(0, 0, D);
    camera.setViewOffset(w, h, narrow ? 0 : -w * 0.08, 0, w, h);
    camera.updateProjectionMatrix();
    renderer.render(scene, camera);
  };
  const queueResize = (): void => {
    if (!resizeQueued) {
      resizeQueued = true;
      requestAnimationFrame(resize);
    }
  };
  resize();
  const resizeObserver = new ResizeObserver(queueResize);
  resizeObserver.observe(container);

  // ---------- Estado de movimiento ----------
  const S = { rotY: 0.5, targetY: 0.5, vel: 0, tiltX: -0.1, targetTilt: -0.1, scale: 0.86, scaleV: 0, selOn: 0, selTarget: 0, rimBoost: 0 };
  const ripStart = [-1, -1, -1, -1];
  let ripIdx = 0;
  const clock = new Clock();
  const addRipple = (v: Vector3): void => {
    const i = ripIdx++ % 4;
    U.uRip.value[i].copy(v);
    ripStart[i] = clock.getElapsedTime();
  };

  const targetFor = (c: GlobeCountry): number => -Math.PI / 2 - c.lng * D2R;
  const targets = countries.map(targetFor);
  const lo = Math.min(ROT_MIN * D2R, ...targets.map((t) => t - 0.15));
  const hi = Math.max(ROT_MAX * D2R, ...targets.map((t) => t + 0.15));
  const clamp = (v: number): number => Math.min(hi, Math.max(lo, v));

  // ---------- Interacción ----------
  const ray = new Raycaster();
  const ndc = new Vector2();
  const aim = (e: PointerEvent): void => {
    const r = el.getBoundingClientRect();
    ndc.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
    ray.setFromCamera(ndc, camera);
  };
  // Devuelve el punto del globo bajo el puntero y el país (polígono o marcador), o -1.
  const hitTest = (e: PointerEvent): { point: Vector3 | null; index: number } => {
    aim(e);
    const marker = ray.intersectObjects(markers, false)[0];
    const hit = ray.intersectObject(globe, false)[0];
    let index = -1;
    if (hit?.uv) {
      const x = Math.min(ID_W - 1, Math.floor(hit.uv.x * ID_W));
      const y = Math.min(ID_H - 1, Math.floor((1 - hit.uv.y) * ID_H));
      const o = (y * ID_W + x) * 4;
      const n = Math.round(idData[o] / 16) + Math.round(idData[o + 1] / 16) * 16;
      if (n >= 1 && n <= countries.length) index = n - 1;
    }
    if (index < 0 && marker && (!hit || marker.distance <= hit.distance + 0.02)) {
      index = marker.object.userData.index as number;
    }
    return { point: hit?.point ?? null, index };
  };

  let selected = -1;
  const showTip = (name: string, e: PointerEvent): void => {
    if (!ui.hoverTip) return;
    const r = container.getBoundingClientRect();
    ui.hoverTip.textContent = name;
    ui.hoverTip.style.transform = `translate(${e.clientX - r.left + 14}px, ${e.clientY - r.top + 14}px)`;
    ui.hoverTip.style.opacity = '1';
  };
  const hideTip = (): void => {
    if (ui.hoverTip) ui.hoverTip.style.opacity = '0';
  };

  let drag = false;
  let lastX = 0;
  let moved = 0;
  const onPointerDown = (e: PointerEvent): void => {
    drag = true;
    lastX = e.clientX;
    moved = 0;
    S.vel = 0;
    el.setPointerCapture(e.pointerId);
    el.style.cursor = 'grabbing';
    hideTip();
  };
  const onPointerMove = (e: PointerEvent): void => {
    if (drag) {
      const dx = e.clientX - lastX;
      lastX = e.clientX;
      moved += Math.abs(dx) + Math.abs(e.movementY || 0);
      let d = dx * 0.0055;
      const nr = S.rotY + d;
      if (nr > hi || nr < lo) d *= 0.3; // resistencia elástica al pasarse
      S.rotY += d;
      S.targetY = S.rotY;
      S.vel = d;
      return;
    }
    if (e.pointerType !== 'mouse') return;
    const { index } = hitTest(e);
    el.style.cursor = index >= 0 ? 'pointer' : 'grab';
    if (index >= 0 && index !== selected) showTip(countries[index].name, e);
    else hideTip();
  };
  const onPointerUp = (e: PointerEvent): void => {
    if (!drag) return;
    drag = false;
    el.style.cursor = 'grab';
    if (el.hasPointerCapture(e.pointerId)) el.releasePointerCapture(e.pointerId);
    if (moved >= 6) return;
    S.vel = 0;
    const { point, index } = hitTest(e);
    if (!point) return;
    addRipple(spin.worldToLocal(point.clone()).normalize());
    S.scaleV -= 0.0025;
    S.rimBoost = 0.18;
    if (index >= 0 && onCountryClick) onCountryClick(countries[index].slug);
  };
  const onPointerCancel = (): void => {
    drag = false;
  };

  el.style.cursor = 'grab';
  el.addEventListener('pointerdown', onPointerDown);
  el.addEventListener('pointermove', onPointerMove);
  el.addEventListener('pointerup', onPointerUp);
  el.addEventListener('pointercancel', onPointerCancel);
  el.addEventListener('pointerleave', hideTip);

  // ---------- Bucle ----------
  let inViewport = true;
  const intersection = new IntersectionObserver((entries) => {
    inViewport = entries.some((en) => en.isIntersecting);
  }, { rootMargin: '120px' });
  intersection.observe(container);

  const projected = new Vector3();
  const toCamera = new Vector3();
  let first = true;
  let rafId = 0;
  const loop = (): void => {
    rafId = requestAnimationFrame(loop);
    if (!inViewport || document.hidden) return;
    if (resizeQueued) resize();
    const t = clock.getElapsedTime();

    if (!drag) {
      if (Math.abs(S.vel) > 1e-5) {
        S.rotY += S.vel;
        S.vel *= S.rotY > hi || S.rotY < lo ? 0.6 : 0.94;
        S.targetY = clamp(S.rotY);
      } else {
        S.targetY = clamp(S.targetY);
        S.rotY += (S.targetY - S.rotY) * 0.055;
      }
    }
    S.tiltX += (S.targetTilt - S.tiltX) * 0.05;
    S.scaleV += (1 - S.scale) * 0.1;
    S.scaleV *= 0.8;
    S.scale += S.scaleV;
    spin.rotation.y = S.rotY;
    tilt.rotation.x = S.tiltX;
    rootG.scale.setScalar(S.scale);
    parts.rotation.y = t * 0.008;
    bokeh.position.set(Math.cos(t * 0.1) * 0.2, Math.sin(t * 0.15) * 0.15, 0);
    orbits.rotation.z = t * 0.01;
    orbitDots.forEach((o) => {
      const a = o.ph + t * o.sp;
      o.dot.position.set(Math.cos(a) * o.r, Math.sin(a) * o.r, 0);
    });
    S.selOn += (S.selTarget - S.selOn) * 0.06;
    U.uTime.value = t;
    U.uSelOn.value = S.selOn;
    for (let i = 0; i < 4; i++) {
      const s = ripStart[i];
      U.uRipT.value[i] = s >= 0 && t - s < 2.2 ? t - s : -1;
    }
    S.rimBoost *= 0.94;
    U.uRimI.value = 0.26 + S.rimBoost;
    globeMat.emissiveIntensity = 0.6 + 0.22 * Math.sin(t * 2.2);
    renderer.render(scene, camera);

    // Tooltip clavado sobre el país elegido: visible solo de frente y quieto.
    if (ui.selTip) {
      if (S.selTarget > 0) {
        const wp = spin.localToWorld(projected.copy(U.uSel.value).multiplyScalar(1.01));
        const facing = toCamera.copy(camera.position).sub(wp).normalize().dot(wp.clone().normalize());
        const p = wp.project(camera);
        ui.selTip.style.transform = `translate(${(((p.x + 1) / 2) * container.clientWidth).toFixed(1)}px, ${(((1 - p.y) / 2) * container.clientHeight).toFixed(1)}px)`;
        ui.selTip.style.opacity = facing > 0.35 && Math.abs(S.targetY - S.rotY) < 0.12 && !drag ? '1' : '0';
      } else {
        ui.selTip.style.opacity = '0';
      }
    }

    if (first) {
      first = false;
      el.classList.add('is-ready');
    }
  };
  loop();

  let rippleTimer = 0;

  const focusCountry = (slug: string, fromTap = false): void => {
    const i = countries.findIndex((c) => c.slug === slug);
    if (i < 0) return;
    const c = countries[i];
    selected = i;

    S.targetY = clamp(targets[i]);
    S.vel = 0;
    S.targetTilt = c.lat * D2R * 0.85;

    ex.fillStyle = '#000';
    ex.fillRect(0, 0, AUX_W, AUX_W / 2);
    const g = feats[i];
    if (g) {
      ex.save();
      ex.beginPath();
      trace(ex, g, AUX_W, AUX_W / 2);
      ex.shadowColor = '#fff';
      ex.shadowBlur = 10;
      ex.fillStyle = 'rgba(235,250,255,0.6)';
      ex.fill('evenodd');
      ex.restore();
    }
    emTex.needsUpdate = true;

    markers.forEach((m, k) => m.scale.setScalar(k === i ? 0.07 : 0.05));
    U.uSel.value.copy(ll2v(c.lat, c.lng));
    S.selOn = 0;
    S.selTarget = 1;
    if (ui.selTipLabel) ui.selTipLabel.textContent = c.name;
    hideTip();

    window.clearTimeout(rippleTimer);
    if (!fromTap) {
      rippleTimer = window.setTimeout(() => {
        addRipple(ll2v(c.lat, c.lng));
        S.rimBoost = 0.1;
      }, 700);
    }
  };

  const destroy = (): void => {
    cancelAnimationFrame(rafId);
    window.clearTimeout(rippleTimer);
    resizeObserver.disconnect();
    intersection.disconnect();
    el.removeEventListener('pointerdown', onPointerDown);
    el.removeEventListener('pointermove', onPointerMove);
    el.removeEventListener('pointerup', onPointerUp);
    el.removeEventListener('pointercancel', onPointerCancel);
    el.removeEventListener('pointerleave', hideTip);
    scene.traverse((obj) => {
      const o = obj as Partial<Mesh>;
      o.geometry?.dispose();
      const mat = o.material as Material | Material[] | undefined;
      (Array.isArray(mat) ? mat : mat ? [mat] : []).forEach((m) => m.dispose());
    });
    textureList.forEach((t) => t.dispose());
    renderer.dispose();
    el.remove();
  };

  return { focusCountry, destroy };
}
