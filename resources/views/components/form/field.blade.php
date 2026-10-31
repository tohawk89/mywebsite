{{-- Shared wrapper for form controls: label, "(optional)" marker, hint and validation error. --}}
@props([
    'name',
    'for' => null,
    'label' => null,
    'optional' => false,
    'hint' => null,
    'wrapper' => 'mb-4',
])

<div class="{{ $wrapper }}">
    @if($label)
        <label @if($for) for="{{ $for }}" @endif class="form-label fw-bold small text-uppercase">
            {{ $label }}
            @if($optional)
                <span class="fw-normal text-muted text-lowercase">(optional)</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($hint)
        <div class="form-text small">{{ $hint }}</div>
    @endif

    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>
