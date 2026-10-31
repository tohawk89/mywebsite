{{--
    Markdown editor bound to a PostForm property (resources/js/markdown-editor.js).
    <x-form.markdown name="content" label="Content" rows="12" />
    Content is stored as Markdown and rendered with Post::renderedContent().
--}}
@props([
    'name',
    'label' => null,
    'optional' => false,
    'hint' => 'Formatting uses Markdown: **bold**, *italic*, # heading, - list, [link](https://...).',
    'rows' => 12,
    'wrapper' => 'mb-4',
    'ai' => null,
])

@php($id = 'field-'.str_replace('.', '-', $name))

<x-form.field :$name :for="$id" :$label :$optional :$hint :$wrapper :$ai>
    {{-- wire:ignore: the editor owns this DOM; it pushes changes to Livewire itself --}}
    <div wire:ignore class="markdown-editor"
        x-data
        x-init="
            $refs.input.value = $wire.get(@js($name)) ?? '';
            const start = () => window.mountMarkdownEditor
                ? window.mountMarkdownEditor($refs.input, { name: @js($name), wire: $wire, minHeight: @js(($rows * 1.6).'rem') })
                : setTimeout(start, 50);
            start();
        ">
        <textarea id="{{ $id }}" x-ref="input" rows="{{ $rows }}" {{ $attributes->class(['form-control']) }}></textarea>
    </div>
</x-form.field>
