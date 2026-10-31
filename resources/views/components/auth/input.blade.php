{{-- Plain (non-Livewire) input for Fortify's POST forms, with old() value and validation error. --}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
        @if($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
