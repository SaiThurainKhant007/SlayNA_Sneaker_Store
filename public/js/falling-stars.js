
(() => {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const canvas = document.createElement('canvas');
    canvas.className = 'sl-stars';
    canvas.setAttribute('aria-hidden', 'true');
    document.body.prepend(canvas);
    const ctx = canvas.getContext('2d');

    const NEAR = 0.25, FAR = 4, PIVOT = 1.6;   // depth range; camera tilts around PIVOT
    let w = 0, h = 0, scale = 1, stars = [];
    let tiltX = 0, tiltY = 0, aimX = 0, aimY = 0, lastScroll = window.scrollY;

    // Half the visible world size at depth z (world units), so new stars land on screen.
    const halfW = z => (w / 2 / scale) * z;
    const halfH = z => (h / 2 / scale) * z;

    const spawn = (anywhere) => {
        const z = NEAR + 0.4 + Math.random() * (FAR - NEAR - 0.4);
        const speed = 0.0018 + Math.random() * 0.003;              // world units per 60 fps frame
        return {
            x: (Math.random() * 2.4 - 1.4) * halfW(z),            // biased left: they drift right
            y: anywhere ? (Math.random() * 2 - 1) * halfH(z) : -halfH(z) * (1.05 + Math.random() * 0.6),
            z,
            vx: speed * 0.35,
            vy: speed,
            vz: -speed * (0.2 + Math.random() * 0.5),             // drifting towards the viewer
            trail: 24 + Math.random() * 32,                       // trail length in frames
            size: 0.9 + Math.random() * 1.1,
        };
    };

    // Rotate a world point around the pivot by the current tilt, then project it to the screen.
    const project = (x, y, z) => {
        const cy = Math.cos(tiltX), sy = Math.sin(tiltX), cx = Math.cos(tiltY), sx = Math.sin(tiltY);
        const dz = z - PIVOT;
        const x1 = x * cy - dz * sy, z1 = x * sy + dz * cy;
        const y1 = y * cx - z1 * sx, z2 = y * sx + z1 * cx + PIVOT;
        return z2 > 0.05 ? { x: w / 2 + (x1 / z2) * scale, y: h / 2 + (y1 / z2) * scale, z: z2 } : null;
    };

    const resize = () => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        w = window.innerWidth;
        h = window.innerHeight;
        scale = Math.max(w, h) * 0.6;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const count = Math.round(Math.min(110, Math.max(35, (w * h) / 16000)));   // fewer on phones
        while (stars.length < count) stars.push(spawn(true));
        stars.length = count;
    };

    // Movement is scaled by elapsed time, so stars fall at the same speed on 60, 120 and 144 Hz screens.
    let last = 0;
    const draw = (now) => {
        const step = Math.min(3, (now - (last || now - 16.7)) / 16.7);
        last = now;
        ctx.clearRect(0, 0, w, h);
        tiltX += (aimX - tiltX) * 0.04;
        tiltY += (aimY - tiltY) * 0.04;

        // Far stars first so near ones draw on top.
        stars.sort((a, b) => b.z - a.z);
        for (let i = 0; i < stars.length; i++) {
            const s = stars[i];
            s.x += s.vx * step; s.y += s.vy * step; s.z += s.vz * step;

            const head = project(s.x, s.y, s.z);
            const tail = project(s.x - s.vx * s.trail, s.y - s.vy * s.trail, s.z - s.vz * s.trail);
            if (!head || !tail || s.z < NEAR || head.y > h + 60 || head.x > w + 60 || head.x < -w * 0.6) {
                stars[i] = spawn(false);
                continue;
            }
            if (head.y < -h * 0.6) { s.y = halfH(s.z); continue; }   // scrolled off the top: re-enter below

            const near = 1 / head.z;                                 // ~0.25 (far) .. 4 (near)
            const fog = Math.max(0, Math.min(1, 1.15 - head.z / FAR));
            const alpha = 0.25 + 0.7 * fog;
            const r = s.size * Math.min(3.2, 0.5 + near * 0.8);

            const grad = ctx.createLinearGradient(head.x, head.y, tail.x, tail.y);
            grad.addColorStop(0, `rgba(240, 30, 40, ${alpha})`);
            grad.addColorStop(1, 'rgba(227, 23, 31, 0)');
            ctx.strokeStyle = grad;
            ctx.lineWidth = r * 1.1;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(head.x, head.y);
            ctx.lineTo(tail.x, tail.y);
            ctx.stroke();

            const glow = ctx.createRadialGradient(head.x, head.y, 0, head.x, head.y, r * 4);
            glow.addColorStop(0, `rgba(255, 90, 95, ${alpha})`);
            glow.addColorStop(0.25, `rgba(240, 30, 40, ${alpha * 0.6})`);
            glow.addColorStop(1, 'rgba(227, 23, 31, 0)');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(head.x, head.y, r * 4, 0, Math.PI * 2);
            ctx.fill();
        }
        requestAnimationFrame(draw);
    };

    window.addEventListener('pointermove', e => {
        aimX = (e.clientX / w - 0.5) * 0.35;
        aimY = (e.clientY / h - 0.5) * -0.25;
    }, { passive: true });
    window.addEventListener('scroll', () => {
        const dy = (window.scrollY - lastScroll) / scale;
        lastScroll = window.scrollY;
        for (const s of stars) s.y -= dy * 0.6;                     // world shift: near stars move more on screen
    }, { passive: true });
    window.addEventListener('resize', resize);
    resize();
    requestAnimationFrame(draw);
})();
