// Drifting dots joined by lines when close, nudged away from the mouse.
const LINK_DISTANCE = 120;

export default function create(ctx, { color, background, speed, density }) {
    const velocity = speed * 0.12;
    let width = 0;
    let height = 0;
    let points = [];
    const mouse = { x: -9999, y: -9999 };

    const onPointerMove = (event) => {
        const rect = ctx.canvas.getBoundingClientRect();
        mouse.x = event.clientX - rect.left;
        mouse.y = event.clientY - rect.top;
    };
    window.addEventListener('pointermove', onPointerMove, { passive: true });

    const draw = () => {
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = color;
        ctx.strokeStyle = color;

        points.forEach((p) => {
            const dx = p.x - mouse.x;
            const dy = p.y - mouse.y;
            const distance = Math.hypot(dx, dy);
            if (distance < 100) {
                p.x += (dx / distance) * 1.5;
                p.y += (dy / distance) * 1.5;
            }

            p.x += p.vx * velocity;
            p.y += p.vy * velocity;
            if (p.x < 0 || p.x > width) p.vx *= -1;
            if (p.y < 0 || p.y > height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
            ctx.fill();
        });

        for (let i = 0; i < points.length; i++) {
            for (let j = i + 1; j < points.length; j++) {
                const distance = Math.hypot(points[i].x - points[j].x, points[i].y - points[j].y);
                if (distance < LINK_DISTANCE) {
                    ctx.globalAlpha = 1 - distance / LINK_DISTANCE;
                    ctx.beginPath();
                    ctx.moveTo(points[i].x, points[i].y);
                    ctx.lineTo(points[j].x, points[j].y);
                    ctx.stroke();
                }
            }
        }
        ctx.globalAlpha = 1;
    };

    return {
        resize(w, h) {
            width = w;
            height = h;
            // Scale with screen area so phones don't get a crowded (and slow) field.
            const count = Math.round((w * h) / 18000 * (density / 5));
            points = Array.from({ length: Math.min(count, 160) }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: Math.random() * 2 - 1,
                vy: Math.random() * 2 - 1,
            }));
        },
        draw,
        still: draw,
        destroy() {
            window.removeEventListener('pointermove', onPointerMove);
        },
    };
}
