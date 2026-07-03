import * as pc from '../vendor/playcanvas.mjs';

const overlay = document.getElementById('overlay');
const progressEl = document.getElementById('progress');
const spinnerEl = document.getElementById('spinner');
const hintEl = document.getElementById('viewer-hint');

const fail = (message) => {
    spinnerEl.style.display = 'none';
    progressEl.className = 'error';
    progressEl.innerHTML = message;
    overlay.classList.remove('hidden');
};

const loadSceneEntry = async () => {
    const id = new URLSearchParams(location.search).get('scene');
    const res = await fetch('scenes/index.json');
    if (!res.ok) throw new Error(`scenes/index.json: HTTP ${res.status}`);
    const index = await res.json();
    const scenes = index.scenes ?? [];
    const entry = id ? scenes.find(s => s.id === id) : scenes[0];
    if (!entry) throw new Error(`Scene "${id}" tidak ditemukan di scenes/index.json`);
    return entry;
};

// --- kontrol kamera orbit sederhana (putar / geser / zoom, dengan damping) ---
class OrbitControls {
    constructor(camera, canvas) {
        this.camera = camera;
        this.canvas = canvas;

        this.target = new pc.Vec3();
        this.yaw = 0;            // derajat
        this.pitch = -15;
        this.distance = 5;

        this.smoothTarget = this.target.clone();
        this.smoothYaw = this.yaw;
        this.smoothPitch = this.pitch;
        this.smoothDistance = this.distance;

        this.pointers = new Map();
        this.lastPinchDist = 0;

        canvas.addEventListener('pointerdown', (e) => {
            canvas.setPointerCapture(e.pointerId);
            this.pointers.set(e.pointerId, { x: e.clientX, y: e.clientY, button: e.button });
            if (this.pointers.size === 2) {
                const [a, b] = [...this.pointers.values()];
                this.lastPinchDist = Math.hypot(a.x - b.x, a.y - b.y);
            }
        });

        canvas.addEventListener('pointermove', (e) => {
            const p = this.pointers.get(e.pointerId);
            if (!p) return;
            const dx = e.clientX - p.x;
            const dy = e.clientY - p.y;

            if (this.pointers.size === 2) {
                p.x = e.clientX;
                p.y = e.clientY;
                const [a, b] = [...this.pointers.values()];
                const dist = Math.hypot(a.x - b.x, a.y - b.y);
                if (this.lastPinchDist > 0) {
                    this.zoom(this.lastPinchDist / dist);
                }
                this.lastPinchDist = dist;
                this.pan(dx * 0.5, dy * 0.5);
                return;
            }

            p.x = e.clientX;
            p.y = e.clientY;

            if (p.button === 2 || p.button === 1 || e.shiftKey) {
                this.pan(dx, dy);
            } else {
                this.yaw -= dx * 0.25;
                this.pitch = pc.math.clamp(this.pitch - dy * 0.25, -89, 89);
            }
        });

        const release = (e) => {
            this.pointers.delete(e.pointerId);
            this.lastPinchDist = 0;
        };
        canvas.addEventListener('pointerup', release);
        canvas.addEventListener('pointercancel', release);

        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            this.zoom(Math.pow(1.0018, e.deltaY));
        }, { passive: false });

        canvas.addEventListener('contextmenu', e => e.preventDefault());
    }

    zoom(factor) {
        this.distance = pc.math.clamp(this.distance * factor, 0.05, 1000);
    }

    pan(dx, dy) {
        const scale = this.distance / this.canvas.clientHeight * 1.6;
        const t = this.camera.getWorldTransform();
        const right = t.getX().clone().mulScalar(-dx * scale);
        const up = t.getY().clone().mulScalar(dy * scale);
        this.target.add(right).add(up);
    }

    frame(center, radius, position) {
        this.target.copy(center);
        if (position) {
            const offset = new pc.Vec3().sub2(position, center);
            this.distance = Math.max(offset.length(), 0.05);
            this.yaw = Math.atan2(offset.x, offset.z) * pc.math.RAD_TO_DEG;
            this.pitch = Math.asin(pc.math.clamp(offset.y / this.distance, -1, 1)) * pc.math.RAD_TO_DEG;
        } else {
            this.distance = radius * 2.2;
            this.yaw = 35;
            this.pitch = -18;
        }
        this.smoothTarget.copy(this.target);
        this.smoothYaw = this.yaw;
        this.smoothPitch = this.pitch;
        this.smoothDistance = this.distance;
    }

    update(dt) {
        const k = 1 - Math.pow(0.0001, dt);   // damping halus, frame-rate independent
        this.smoothYaw += (this.yaw - this.smoothYaw) * k;
        this.smoothPitch += (this.pitch - this.smoothPitch) * k;
        this.smoothDistance += (this.distance - this.smoothDistance) * k;
        this.smoothTarget.lerp(this.smoothTarget, this.target, k);

        const yawR = this.smoothYaw * pc.math.DEG_TO_RAD;
        const pitchR = this.smoothPitch * pc.math.DEG_TO_RAD;
        const cp = Math.cos(pitchR);
        const dir = new pc.Vec3(Math.sin(yawR) * cp, Math.sin(pitchR), Math.cos(yawR) * cp);

        const pos = new pc.Vec3().add2(this.smoothTarget, dir.mulScalar(this.smoothDistance));
        this.camera.setPosition(pos);
        this.camera.lookAt(this.smoothTarget);
    }
}

const getSceneAabb = (entity, asset) => {
    const aabb = entity?.gsplat?.instance?.meshInstance?.aabb ?? asset.resource?.aabb;
    if (aabb) return aabb;
    const data = asset.resource?.gsplatData;
    if (data?.calcAabb) {
        const box = new pc.BoundingBox();
        data.calcAabb(box);
        return box;
    }
    return new pc.BoundingBox(new pc.Vec3(), new pc.Vec3(2, 2, 2));
};

const main = async () => {
    const entry = await loadSceneEntry();

    document.title = `${entry.title ?? entry.id} — Splat Viewer`;
    document.getElementById('scene-title').textContent = entry.title ?? entry.id;

    const canvas = document.getElementById('viewer-canvas');
    const app = new pc.Application(canvas, {
        graphicsDeviceOptions: {
            antialias: false,
            devicePixelRatio: Math.min(window.devicePixelRatio, 2)
        }
    });
    app.setCanvasFillMode(pc.FILLMODE_FILL_WINDOW);
    app.setCanvasResolution(pc.RESOLUTION_AUTO);
    window.addEventListener('resize', () => app.resizeCanvas());

    const camera = new pc.Entity('camera');
    camera.addComponent('camera', {
        clearColor: new pc.Color(0.063, 0.063, 0.078),
        fov: entry.camera?.fov ?? 60,
        nearClip: 0.01,
        farClip: 1000
    });
    app.root.addChild(camera);

    const controls = new OrbitControls(camera, canvas);
    app.on('update', dt => controls.update(dt));
    app.start();

    // muat aset gsplat (.ply / .compressed.ply / .sog)
    const asset = new pc.Asset(entry.id ?? 'scene', 'gsplat', { url: entry.src });
    asset.on('progress', (received, length) => {
        if (length > 0) {
            progressEl.textContent = `Memuat scene… ${Math.round(received / length * 100)}%`;
        }
    });

    await new Promise((resolve, reject) => {
        asset.once('load', resolve);
        asset.once('error', reject);
        app.assets.add(asset);
        app.assets.load(asset);
    });

    const entity = new pc.Entity(entry.id ?? 'splat');
    entity.addComponent('gsplat', { asset });
    if (entry.rotation) entity.setLocalEulerAngles(...entry.rotation);
    if (entry.position) entity.setLocalPosition(...entry.position);
    if (entry.scale) entity.setLocalScale(entry.scale, entry.scale, entry.scale);
    app.root.addChild(entity);

    // arahkan kamera: pakai konfigurasi scene bila ada, kalau tidak auto-frame dari aabb
    const aabb = getSceneAabb(entity, asset);
    const center = entry.camera?.target ? new pc.Vec3(...entry.camera.target) : aabb.center.clone();
    const position = entry.camera?.position ? new pc.Vec3(...entry.camera.position) : null;
    controls.frame(center, Math.max(aabb.halfExtents.length(), 0.5), position);

    overlay.classList.add('hidden');
    setTimeout(() => { hintEl.style.opacity = '0'; }, 6000);
};

document.getElementById('btn-fullscreen').addEventListener('click', () => {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        document.documentElement.requestFullscreen();
    }
});

main().catch((err) => {
    console.error(err);
    fail(`Gagal memuat scene.<br><small>${err?.message ?? err}</small>`);
});
