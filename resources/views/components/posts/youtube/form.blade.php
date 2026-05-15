@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">YouTube Video URL</label>
        <input type="url" wire:model="typeData.youtube_url"
            class="form-control rounded-0 bg-light border-0"
            placeholder="https://www.youtube.com/watch?v=...">
        @error('typeData.youtube_url') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
