import 'bootstrap';
import './backgrounds';
import './lightbox';
import './markdown-editor';

// Pinterest-style masonry: each card spans as many 1px grid rows as it is tall.
// ResizeObserver re-measures when images or embeds load; MutationObserver picks up infinite-scroll cards.
function initFeedGrid() {
    const grid = document.getElementById('feed-grid');

    if (!grid || grid.dataset.feedGrid) {
        return;
    }

    grid.dataset.feedGrid = 'true';

    const resize = new ResizeObserver((entries) => {
        entries.forEach(({ target }) => {
            target.style.gridRowEnd = `span ${Math.ceil(target.getBoundingClientRect().height)}`;
        });
        grid.classList.add('is-ready');
    });
    const observeItems = () => [...grid.children].forEach((item) => resize.observe(item));

    observeItems();
    new MutationObserver(observeItems).observe(grid, { childList: true });
}

initFeedGrid();
document.addEventListener('DOMContentLoaded', initFeedGrid);
document.addEventListener('livewire:navigated', initFeedGrid);

// Instagram embeds report their real height via postMessage ({"type":"MEASURE","details":{"height":N}}).
// Resize the matching iframe so posts aren't cropped or padded; the feed grid re-measures the card.
window.addEventListener('message', (event) => {
    if (event.origin !== 'https://www.instagram.com' || typeof event.data !== 'string') {
        return;
    }

    let message;
    try {
        message = JSON.parse(event.data);
    } catch {
        return;
    }

    const height = message?.type === 'MEASURE' ? Number(message.details?.height) : 0;

    if (height > 0) {
        const iframe = [...document.querySelectorAll('iframe[data-instagram-embed]')]
            .find((frame) => frame.contentWindow === event.source);

        if (iframe) {
            iframe.style.height = `${Math.ceil(height)}px`;
        }
    }
});
