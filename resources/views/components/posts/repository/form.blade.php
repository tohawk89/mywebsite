@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Repository URL</label>
        <input type="url" wire:model="typeData.repo_url"
            class="form-control rounded-0 bg-light border-0"
            placeholder="https://github.com/owner/repo">
        <div class="form-text small">GitHub repository URL. Metadata will be fetched automatically on save.</div>
        @error('typeData.repo_url') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">
            Personal Caption <span class="text-muted fw-normal">(optional)</span>
        </label>
        <input type="text" wire:model="typeData.repo_title"
            class="form-control rounded-0 bg-light border-0"
            placeholder="e.g. What I used for my website">
        @error('typeData.repo_title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">
            Personal Note <span class="text-muted fw-normal">(optional)</span>
        </label>
        <textarea wire:model="typeData.repo_note"
            class="form-control rounded-0 bg-light border-0" rows="3"
            placeholder="Great package, makes uploads trivial..."></textarea>
        @error('typeData.repo_note') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
