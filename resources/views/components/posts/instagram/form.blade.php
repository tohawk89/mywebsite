@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Instagram Post URL</label>
        <input type="url" wire:model="typeData.instagram_url"
            class="form-control rounded-0 bg-light border-0"
            placeholder="https://www.instagram.com/p/... or /reel/...">
        @error('typeData.instagram_url') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
