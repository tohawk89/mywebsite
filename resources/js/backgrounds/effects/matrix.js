// Falling columns of characters with a fading trail.
const CHARSETS = {
    katakana: 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワン0123456789',
    binary: '01',
};

const FONT_SIZE = 16;

export default function create(ctx, { color, speed, density, charset, text }) {
    const glyphs = charset === 'custom' && text ? Array.from(text) : Array.from(CHARSETS[charset] ?? CHARSETS.katakana);
    const step = 0.15 + speed * 0.06;
    const chance = 0.35 + density * 0.065;
    let width = 0;
    let height = 0;
    let columns = [];

    const glyph = (row, column) => charset === 'custom' && text
        ? glyphs[(row + column) % glyphs.length]
        : glyphs[Math.floor(Math.random() * glyphs.length)];

    const draw = () => {
        // Fade old glyphs to transparent (the base color layer shows through) instead of painting
        // the base color over them, which leaves grey streaks behind.
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fillStyle = 'rgba(0, 0, 0, 0.12)';
        ctx.fillRect(0, 0, width, height);
        ctx.globalCompositeOperation = 'source-over';
        ctx.fillStyle = color;
        ctx.font = `${FONT_SIZE}px ui-monospace, monospace`;

        columns.forEach((row, i) => {
            if (Math.random() < chance) {
                ctx.fillText(glyph(Math.floor(row), i), i * FONT_SIZE, row * FONT_SIZE);
            }
            columns[i] = row * FONT_SIZE > height && Math.random() > 0.975 ? 0 : row + step;
        });
    };

    return {
        resize(w, h) {
            width = w;
            height = h;
            columns = Array.from({ length: Math.ceil(w / FONT_SIZE) }, () => Math.random() * -(h / FONT_SIZE));
            ctx.clearRect(0, 0, w, h);
        },
        draw,
        still() {
            columns = columns.map(() => Math.random() * (height / FONT_SIZE));
            for (let i = 0; i < 40; i++) {
                draw();
            }
        },
    };
}
