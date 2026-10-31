{{--
    AI writing menu for a PostForm field (see App\Livewire\Admin\PostForm::aiRewrite()).
    Works on the selected text, or the whole field when nothing is selected.
--}}
@props([
    'name',
    'for',
])

<div class="dropdown ai-menu" x-data="{
        run(action) {
            const editor = window.markdownEditors?.[@js($for)];
            const field = document.getElementById(@js($for));
            let start = 0, selected = '';

            if (editor) {
                [start, selected] = editor.selection();
            } else if (field && field.selectionEnd > field.selectionStart) {
                start = field.selectionStart;
                selected = field.value.substring(field.selectionStart, field.selectionEnd);
            }

            $wire.aiRewrite(@js($name), action, start, selected);
        },
    }">
    <button type="button" class="btn btn-light btn-sm ai-menu-toggle" data-bs-toggle="dropdown" aria-expanded="false"
        wire:loading.attr="disabled" wire:target="aiRewrite,suggestTitles">
        <span wire:loading.remove wire:target="aiRewrite,suggestTitles"><i class="bi bi-stars"></i> AI</span>
        <span wire:loading wire:target="aiRewrite,suggestTitles"><span class="spinner-border spinner-border-sm"></span> Thinking…</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><h6 class="dropdown-header">Works on your selection, or the whole field</h6></li>
        @foreach(\App\Ai\Agents\WritingAssistant::ACTIONS as $action => [$label])
            <li><button type="button" class="dropdown-item" @click="run(@js($action))">{{ $label }}</button></li>
        @endforeach
        @if($name === 'title')
            <li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item" wire:click="suggestTitles">Suggest titles from content</button></li>
        @endif
    </ul>
</div>
