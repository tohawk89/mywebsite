@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Author / Source</label>
        <input type="text" wire:model="title"
            class="form-control rounded-0 form-control-lg bg-light border-0">
        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Quote Text</label>
        <textarea wire:model="content" class="form-control rounded-0 bg-light border-0" rows="4"
            placeholder="Enter the quote here..."></textarea>
        @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
