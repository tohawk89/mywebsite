{{--
    Text-like input bound to a PostForm property.
    <x-form.input name="typeData.url" type="url" label="URL" placeholder="https://..." />
    Extra attributes (placeholder, maxlength, required...) are passed to the <input>.
--}}
@props([
    'name',
    'type' => 'text',
    'label' => null,
    'optional' => false,
    'hint' => null,
    'size' => null,
    'live' => false,
    'wrapper' => 'mb-4',
    'ai' => null,
])

@php($id = $attributes->get('id', 'field-'.str_replace('.', '-', $name)))

<x-form.field :$name :for="$id" :$label :$optional :$hint :$wrapper :$ai>
    <input type="{{ $type }}" id="{{ $id }}" {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $name }}"
        {{ $attributes->except('id')->class(['form-control', 'form-control-lg' => $size === 'lg']) }}>
</x-form.field>
