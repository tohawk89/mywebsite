// Markdown editor (EasyMDE, MIT) for long post content. Loaded from the CDN only on pages that use it.
// Each editor registers itself in window.markdownEditors[id] so the AI menu can read the selection.
const EASYMDE = 'https://cdn.jsdelivr.net/npm/easymde@2.21.0/dist';

let loading = null;

function loadEasyMDE() {
    loading ??= new Promise((resolve, reject) => {
        const style = document.createElement('link');
        style.rel = 'stylesheet';
        style.href = `${EASYMDE}/easymde.min.css`;
        document.head.appendChild(style);

        const script = document.createElement('script');
        script.src = `${EASYMDE}/easymde.min.js`;
        script.onload = () => resolve(window.EasyMDE);
        script.onerror = () => {
            loading = null;
            reject(new Error('Could not load the editor.'));
        };
        document.head.appendChild(script);
    });

    return loading;
}

window.markdownEditors = window.markdownEditors || {};

/**
 * Turn a textarea into a Markdown editor kept in sync with a Livewire property.
 *
 * @param {HTMLTextAreaElement} textarea
 * @param {{ name: string, wire: object, minHeight?: string }} options
 */
window.mountMarkdownEditor = async (textarea, { name, wire, minHeight = '240px' }) => {
    let EasyMDE;

    try {
        EasyMDE = await loadEasyMDE();
    } catch {
        // Without the CDN the plain textarea keeps working.
        textarea.addEventListener('input', () => wire.set(name, textarea.value, false));
        return;
    }

    const button = (action, icon, title) => ({ name: title.toLowerCase(), action, className: `bi ${icon}`, title });

    const editor = new EasyMDE({
        element: textarea,
        initialValue: textarea.value,
        autoDownloadFontAwesome: false,
        spellChecker: false,
        status: false,
        minHeight,
        placeholder: textarea.placeholder,
        toolbar: [
            button(EasyMDE.toggleBold, 'bi-type-bold', 'Bold'),
            button(EasyMDE.toggleItalic, 'bi-type-italic', 'Italic'),
            button(EasyMDE.toggleHeadingSmaller, 'bi-type-h2', 'Heading'),
            '|',
            button(EasyMDE.toggleBlockquote, 'bi-quote', 'Quote'),
            button(EasyMDE.toggleUnorderedList, 'bi-list-ul', 'Bulleted list'),
            button(EasyMDE.toggleOrderedList, 'bi-list-ol', 'Numbered list'),
            button(EasyMDE.drawLink, 'bi-link-45deg', 'Link'),
            button(EasyMDE.toggleCodeBlock, 'bi-code-slash', 'Code'),
            '|',
            button(EasyMDE.togglePreview, 'bi-eye', 'Preview'),
        ],
    });

    editor.codemirror.on('change', () => wire.set(name, editor.value(), false));

    window.markdownEditors[textarea.id] = {
        selection() {
            const cm = editor.codemirror;
            return [cm.indexFromPos(cm.getCursor('from')), cm.getSelection()];
        },
    };

    // Accepted AI rewrites change the property on the server; show them in the editor.
    window.addEventListener('ai-applied', (event) => {
        if (event.detail?.field === name && editor.value() !== event.detail.value) {
            editor.value(event.detail.value);
        }
    });
};
