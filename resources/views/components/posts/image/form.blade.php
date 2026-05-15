@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">
            Title <span class="text-muted fw-normal">(optional)</span>
        </label>
        <input type="text" wire:model="title"
            class="form-control rounded-0 form-control-lg bg-light border-0"
            placeholder="Optional caption">
        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
