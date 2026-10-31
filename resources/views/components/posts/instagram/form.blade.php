@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Instagram Post URL</label>
        <input type="url" wire:model="typeData.instagram_url"
            class="form-control rounded-0 bg-light border-0"
            placeholder="https://www.instagram.com/p/... or /reel/...">
        @error('typeData.instagram_url') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Title <span class="fw-normal text-muted text-lowercase">(optional)</span></label>
        <input type="text" wire:model="title"
            class="form-control rounded-0 bg-light border-0">
        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Content <span class="fw-normal text-muted text-lowercase">(optional)</span></label>
        <textarea wire:model="content" class="form-control rounded-0 bg-light border-0" rows="4"></textarea>
        @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
