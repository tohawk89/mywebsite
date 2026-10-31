// Stars flying towards the viewer.
export default function create(ctx, { color, background, speed, density }) {
    const velocity = speed * 0.0015;
    let width = 0;
    let height = 0;
    let stars = [];

    const reset = (star) => {
        star.x = Math.random() * 2 - 1;
        star.y = Math.random() * 2 - 1;
        star.z = 1;
        return star;
    };

    const draw = (move = true) => {
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = color;

        stars.forEach((star) => {
            if (move) {
                star.z -= velocity;
                if (star.z <= 0.02) {
                    reset(star);
                }
            }
            const x = width / 2 + (star.x / star.z) * (width / 2);
            const y = height / 2 + (star.y / star.z) * (height / 2);
            if (x < 0 || x > width || y < 0 || y > height) {
                return;
            }
            ctx.globalAlpha = Math.min(1, (1 - star.z) * 1.5);
            ctx.beginPath();
            ctx.arc(x, y, (1 - star.z) * 2.2, 0, Math.PI * 2);
            ctx.fill();
        });
        ctx.globalAlpha = 1;
    };

    return {
        resize(w, h) {
            width = w;
            height = h;
            stars = Array.from({ length: density * 60 }, () => ({ ...reset({}), z: Math.random() }));
        },
        draw: () => draw(true),
        still: () => draw(false),
    };
}
