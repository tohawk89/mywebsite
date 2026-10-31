// Fullscreen image viewer (GLightbox, MIT) for image posts. Loaded from the CDN on first use,
// so pages without image posts never download it. Several images become a swipeable slider.
const GLIGHTBOX = 'https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist';

let loading = null;

function loadGlightbox() {
    loading ??= new Promise((resolve, reject) => {
        const style = document.createElement('link');
        style.rel = 'stylesheet';
        style.href = `${GLIGHTBOX}/css/glightbox.min.css`;
        document.head.appendChild(style);

        const script = document.createElement('script');
        script.src = `${GLIGHTBOX}/js/glightbox.min.js`;
        script.onload = () => resolve(window.GLightbox);
        script.onerror = () => {
            loading = null;
            reject(new Error('Could not load the image viewer.'));
        };
        document.head.appendChild(script);
    });

    return loading;
}

/**
 * @param {Array<{href: string, title?: string}>} slides
 * @param {number} startAt
 */
window.openLightbox = async (slides, startAt = 0) => {
    if (!Array.isArray(slides) || slides.length === 0) {
        return;
    }

    try {
        const GLightbox = await loadGlightbox();
        const viewer = GLightbox({
            elements: slides.map(({ href, title }) => ({ href, type: 'image', title: title || '' })),
            startAt,
            loop: slides.length > 1,
            touchNavigation: true,
            zoomable: true,
            draggable: true,
            openEffect: 'fade',
            closeEffect: 'fade',
        });

        viewer.on('close', () => setTimeout(() => viewer.destroy(), 300));
        viewer.open();
    } catch {
        // Fall back to the plain image if the CDN is unreachable.
        window.open(slides[startAt].href, '_blank', 'noopener');
    }
};
