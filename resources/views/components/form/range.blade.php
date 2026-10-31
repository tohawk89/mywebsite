{{--
    Slider with its current value shown next to the label.
    <x-form.range name="background.effect_speed" label="Speed" min="1" max="10" live />
--}}
@props([
    'name',
    'label' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'suffix' => '',
    'hint' => null,
    'live' => false,
    'wrapper' => 'mb-4',
])

@php($id = 'field-'.str_replace('.', '-', $name))

<x-form.field :$name :$hint :$wrapper>
    <div class="d-flex justify-content-between align-items-baseline">
        @if($label)
            <label for="{{ $id }}" class="form-label">{{ $label }}</label>
        @endif
        <span class="small text-muted" x-data x-text="$wire.{{ $name }} + @js($suffix)"></span>
    </div>
    <input type="range" id="{{ $id }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
        {{ $live ? 'wire:model.live' : 'wire:model' }}="{{ $name }}" {{ $attributes->class(['form-range']) }}>
</x-form.field>
