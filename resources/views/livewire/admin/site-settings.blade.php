<div class="container py-4" style="max-width: 720px;">
    <h2 class="fw-bold mb-4">Site Settings</h2>

    @if(session('status'))
        <div class="alert alert-success rounded-0 border-0 mb-4">{{ session('status') }}</div>
    @endif

    <form wire:submit="save">

        {{-- Identity --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-uppercase small mb-4">Identity</h6>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase">Site Title</label>
                    <input type="text" wire:model="site_title" class="form-control rounded-0 bg-light border-0">
                    @error('site_title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase">Site Description</label>
                    <input type="text" wire:model="site_description" class="form-control rounded-0 bg-light border-0"
                        placeholder="Used as meta description for SEO">
                    @error('site_description') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold small text-uppercase">Footer Text</label>
                    <input type="text" wire:model="footer_text" class="form-control rounded-0 bg-light border-0"
                        placeholder="© 2026 My Website. All rights reserved.">
                    @error('footer_text') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Appearance --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-uppercase small mb-4">Appearance</h6>

                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label fw-bold small text-uppercase">Background Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" wire:model.live="background_color"
                                class="form-control form-control-color rounded-0 border-0 bg-light"
                                style="width: 48px; height: 38px; padding: 2px;">
                            <input type="text" wire:model.live="background_color"
                                class="form-control rounded-0 bg-light border-0 font-monospace"
                                maxlength="7" placeholder="#fdfbf7">
                        </div>
                        @error('background_color') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-bold small text-uppercase">Accent Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" wire:model.live="accent_color"
                                class="form-control form-control-color rounded-0 border-0 bg-light"
                                style="width: 48px; height: 38px; padding: 2px;">
                            <input type="text" wire:model.live="accent_color"
                                class="form-control rounded-0 bg-light border-0 font-monospace"
                                maxlength="7" placeholder="#2c3e50">
                        </div>
                        @error('accent_color') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold small text-uppercase">Font</label>
                    <select wire:model="font" class="form-select rounded-0 bg-light border-0">
                        @foreach($availableFonts as $fontOption)
                            <option value="{{ $fontOption }}">{{ $fontOption }}</option>
                        @endforeach
                    </select>
                    @error('font') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Feed --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-uppercase small mb-4">Feed</h6>

                <div class="mb-0">
                    <label class="form-label fw-bold small text-uppercase">Posts Per Page</label>
                    <input type="number" wire:model="posts_per_page" min="1" max="100"
                        class="form-control rounded-0 bg-light border-0" style="max-width: 120px;">
                    @error('posts_per_page') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold text-uppercase">
            Save Settings
        </button>

    </form>
</div>
