// Animated site backgrounds. Each <canvas data-site-background='{...}'> gets the configured effect.
// Effects are loaded on demand, pause while the tab is hidden, and show a still frame
// when the visitor prefers reduced motion.

const effects = {
    matrix: () => import('./effects/matrix.js'),
    particles: () => import('./effects/particles.js'),
    starfield: () => import('./effects/starfield.js'),
    waves: () => import('./effects/waves.js'),
};

const FRAME_MS = 1000 / 40;

async function start(canvas) {
    const config = JSON.parse(canvas.dataset.siteBackground || '{}');
    const load = effects[config.effect];

    if (!load) {
        return;
    }

    if (config.disableOnMobile && window.matchMedia('(max-width: 767.98px)').matches) {
        canvas.hidden = true;
        return;
    }

    const { default: create } = await load();
    const ctx = canvas.getContext('2d');
    const effect = create(ctx, config);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let frame = null;
    let last = 0;

    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
        const { width, height } = canvas.getBoundingClientRect();
        canvas.width = Math.max(1, Math.round(width * ratio));
        canvas.height = Math.max(1, Math.round(height * ratio));
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        effect.resize(width, height);
    };

    const loop = (time) => {
        frame = requestAnimationFrame(loop);
        if (time - last < FRAME_MS) {
            return;
        }
        last = time;
        effect.draw();
    };

    const play = () => {
        if (frame === null && !reducedMotion && !document.hidden) {
            frame = requestAnimationFrame(loop);
        }
    };

    const pause = () => {
        cancelAnimationFrame(frame);
        frame = null;
    };

    const onVisibility = () => (document.hidden ? pause() : play());
    const observer = new ResizeObserver(() => {
        resize();
        if (reducedMotion) {
            effect.still();
        }
    });

    observer.observe(canvas);
    document.addEventListener('visibilitychange', onVisibility);
    resize();

    if (reducedMotion) {
        effect.still();
    } else {
        play();
    }

    canvas.siteBackgroundStop = () => {
        pause();
        effect.destroy?.();
        observer.disconnect();
        document.removeEventListener('visibilitychange', onVisibility);
    };
}

function scan() {
    document.querySelectorAll('canvas[data-site-background]:not([data-started])').forEach((canvas) => {
        canvas.dataset.started = 'true';
        start(canvas);
    });
}

// Start canvases on load and when Livewire adds them (e.g. the settings preview); stop removed ones.
new MutationObserver((mutations) => {
    mutations.forEach(({ removedNodes }) => removedNodes.forEach((node) => {
        if (node.nodeType !== 1) {
            return;
        }
        [node, ...node.querySelectorAll('canvas')].forEach((el) => el.siteBackgroundStop?.());
    }));
    scan();
}).observe(document.documentElement, { childList: true, subtree: true });

scan();
document.addEventListener('DOMContentLoaded', scan);
