{{--
    Color picker with a hex text box, both bound to the same property.
    <x-form.color name="accent_color" label="Accent color" live />
--}}
@props([
    'name',
    'label' => null,
    'optional' => false,
    'hint' => null,
    'live' => false,
    'wrapper' => 'mb-4',
])

@php($id = 'field-'.str_replace('.', '-', $name))
@php($model = $live ? 'wire:model.live' : 'wire:model')

<x-form.field :$name :for="$id" :$label :$optional :$hint :$wrapper>
    <div class="d-flex align-items-center gap-2">
        <input type="color" {{ $model }}="{{ $name }}" class="form-control form-control-color flex-shrink-0"
            style="width: 48px;" aria-label="{{ $label ?? $name }} picker">
        <input type="text" id="{{ $id }}" {{ $model }}{{ $live ? '.debounce.400ms' : '' }}="{{ $name }}"
            {{ $attributes->class(['form-control font-monospace']) }} maxlength="7" placeholder="#000000">
    </div>
</x-form.field>
