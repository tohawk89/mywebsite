// Soft, layered sine waves drifting across the bottom of the screen.
export default function create(ctx, { color, background, speed, density }) {
    const layers = 2 + Math.round(density / 2);
    let width = 0;
    let height = 0;
    let time = 0;

    const draw = () => {
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = color;

        for (let layer = 0; layer < layers; layer++) {
            const amplitude = height * (0.04 + layer * 0.012);
            const baseline = height * (0.55 + layer * (0.4 / layers));
            const frequency = 0.0025 + layer * 0.0007;
            const phase = time * (0.6 + layer * 0.25);

            ctx.globalAlpha = 0.08 + (layer / layers) * 0.12;
            ctx.beginPath();
            ctx.moveTo(0, height);
            for (let x = 0; x <= width; x += 12) {
                ctx.lineTo(x, baseline + Math.sin(x * frequency + phase) * amplitude);
            }
            ctx.lineTo(width, height);
            ctx.closePath();
            ctx.fill();
        }
        ctx.globalAlpha = 1;
        time += speed * 0.004;
    };

    return {
        resize(w, h) {
            width = w;
            height = h;
        },
        draw,
        still: draw,
    };
}
