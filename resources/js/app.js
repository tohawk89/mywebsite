import 'bootstrap';

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
