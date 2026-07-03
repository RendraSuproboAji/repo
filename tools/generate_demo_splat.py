#!/usr/bin/env python3
"""Generate a demo 3D Gaussian Splat PLY file (a small spiral galaxy).

Useful as placeholder gallery content while you prepare real scenes.
Usage: python3 tools/generate_demo_splat.py [output.ply]
"""

import math
import random
import struct
import sys

SH_C0 = 0.28209479177387814


def logit(p):
    p = min(max(p, 1e-4), 1 - 1e-4)
    return math.log(p / (1 - p))


def hsv_to_rgb(h, s, v):
    i = int(h * 6) % 6
    f = h * 6 - int(h * 6)
    p, q, t = v * (1 - s), v * (1 - f * s), v * (1 - (1 - f) * s)
    return [(v, t, p), (q, v, p), (p, v, t), (p, q, v), (t, p, v), (v, p, q)][i]


def make_splats(n=35000, seed=42):
    rng = random.Random(seed)
    splats = []

    arms = 3
    for _ in range(n):
        kind = rng.random()
        if kind < 0.25:
            # central bulge
            r = abs(rng.gauss(0, 0.35))
            theta = rng.uniform(0, 2 * math.pi)
            y = rng.gauss(0, 0.16) * max(0.2, 1.0 - r)
            hue = 0.09 + rng.uniform(-0.03, 0.03)      # warm golden core
            sat = 0.55
            val = 1.0
            alpha = 0.75
            size = rng.uniform(0.02, 0.06)
        else:
            # spiral arms
            r = 0.35 + 2.6 * (rng.random() ** 0.75)
            arm = rng.randrange(arms)
            swirl = 2.4 * math.log(1 + r)
            theta = arm * (2 * math.pi / arms) + swirl + rng.gauss(0, 0.16 / (0.4 + r * 0.35))
            y = rng.gauss(0, 0.05) * (1.0 - r / 4.0)
            t = min(r / 3.0, 1.0)
            hue = 0.09 + t * 0.5 + rng.uniform(-0.04, 0.04)   # golden -> cyan/blue
            sat = 0.45 + 0.35 * t
            val = 0.95 - 0.35 * t + rng.uniform(-0.1, 0.1)
            alpha = 0.55 * (1.0 - 0.5 * t) + rng.uniform(0, 0.15)
            size = rng.uniform(0.015, 0.05) * (1 + 0.6 * t)

        x = r * math.cos(theta)
        z = r * math.sin(theta)
        rr, gg, bb = hsv_to_rgb(hue % 1.0, min(max(sat, 0), 1), min(max(val, 0.1), 1))

        splats.append((
            x, y, z,
            (rr - 0.5) / SH_C0, (gg - 0.5) / SH_C0, (bb - 0.5) / SH_C0,
            logit(alpha),
            math.log(size), math.log(size), math.log(size * 0.6),
            1.0, 0.0, 0.0, 0.0,
        ))

    # a few bright "stars" sprinkled around
    for _ in range(600):
        r = rng.uniform(0.2, 3.4)
        theta = rng.uniform(0, 2 * math.pi)
        x, z = r * math.cos(theta), r * math.sin(theta)
        y = rng.gauss(0, 0.25)
        size = rng.uniform(0.004, 0.012)
        splats.append((
            x, y, z,
            (1.0 - 0.5) / SH_C0, (1.0 - 0.5) / SH_C0, (0.95 - 0.5) / SH_C0,
            logit(0.9),
            math.log(size), math.log(size), math.log(size),
            1.0, 0.0, 0.0, 0.0,
        ))

    return splats


def write_ply(path, splats):
    props = [
        'x', 'y', 'z',
        'f_dc_0', 'f_dc_1', 'f_dc_2',
        'opacity',
        'scale_0', 'scale_1', 'scale_2',
        'rot_0', 'rot_1', 'rot_2', 'rot_3',
    ]
    header = 'ply\nformat binary_little_endian 1.0\n'
    header += f'element vertex {len(splats)}\n'
    header += ''.join(f'property float {p}\n' for p in props)
    header += 'end_header\n'

    with open(path, 'wb') as f:
        f.write(header.encode('ascii'))
        pack = struct.Struct('<14f').pack
        for s in splats:
            f.write(pack(*s))


if __name__ == '__main__':
    out = sys.argv[1] if len(sys.argv) > 1 else 'scenes/demo-galaxy/scene.ply'
    splats = make_splats()
    write_ply(out, splats)
    print(f'wrote {len(splats)} splats to {out}')
