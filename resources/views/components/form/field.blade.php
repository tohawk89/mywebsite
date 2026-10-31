{{-- Shared wrapper for form controls: label, "(optional)" marker, hint and validation error. --}}
@props([
    'name',
    'for' => null,
    'label' => null,
    'optional' => false,
    'hint' => null,
    'wrapper' => 'mb-4',
    'ai' => null,
])

{{-- AI menu: on by default for the post title and content (PostForm::AI_FIELDS); pass :ai="false" to hide it. --}}
@php($showAi = ($ai ?? in_array($name, \App\Livewire\Admin\PostForm::AI_FIELDS, true)) && $for && \App\Support\AiWriting::enabled())

<div class="{{ $wrapper }}">
    @if($label || $showAi)
        <div @class(['d-flex align-items-end justify-content-between gap-2' => $showAi])>
            @if($label)
                <label @if($for) for="{{ $for }}" @endif class="form-label">
                    {{ $label }}
                    @if($optional)
                        <span class="fw-normal text-muted">(optional)</span>
                    @endif
                </label>
            @endif
            @if($showAi)
                <x-form.ai-menu :$name :$for />
            @endif
        </div>
    @endif

    {{ $slot }}

    @if($hint)
        <div class="form-text small">{{ $hint }}</div>
    @endif

    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>
