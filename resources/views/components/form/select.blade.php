{{--
    Select bound to a PostForm property. Options are [value => label].
    <x-form.select name="typeData.platform" label="Platform" :options="$platforms" placeholder="Choose..." />
--}}
@props([
    'name',
    'options' => [],
    'label' => null,
    'optional' => false,
    'hint' => null,
    'placeholder' => null,
    'live' => false,
    'wrapper' => 'mb-4',
])

@php($id = $attributes->get('id', 'field-'.str_replace('.', '-', $name)))

<x-form.field :$name :for="$id" :$label :$optional :$hint :$wrapper>
    <select id="{{ $id }}" {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $name }}"
        {{ $attributes->except('id')->class(['form-select']) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-form.field>
