{{--
    Textarea bound to a PostForm property.
    <x-form.textarea name="content" label="Content" rows="6" optional />
--}}
@props([
    'name',
    'label' => null,
    'optional' => false,
    'hint' => null,
    'rows' => 4,
    'live' => false,
    'wrapper' => 'mb-4',
    'ai' => null,
])

@php($id = $attributes->get('id', 'field-'.str_replace('.', '-', $name)))

<x-form.field :$name :for="$id" :$label :$optional :$hint :$wrapper :$ai>
    <textarea id="{{ $id }}" rows="{{ $rows }}" {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $name }}"
        {{ $attributes->except('id')->class(['form-control']) }}></textarea>
</x-form.field>
