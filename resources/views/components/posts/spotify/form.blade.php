@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Spotify Embed URL</label>
        <input type="url" wire:model="typeData.spotify_url"
            class="form-control rounded-0 bg-light border-0"
            placeholder="https://open.spotify.com/track/...">
        @error('typeData.spotify_url') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
