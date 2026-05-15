@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Title</label>
        <input type="text" wire:model="title"
            class="form-control rounded-0 form-control-lg bg-light border-0">
        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Content</label>
        <textarea wire:model="content" class="form-control rounded-0 bg-light border-0" rows="12"></textarea>
        @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
