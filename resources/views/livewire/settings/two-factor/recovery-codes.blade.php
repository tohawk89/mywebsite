<div class="border rounded-3 p-3" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-lock"></i>
        <h3 class="fs-6 fw-semibold mb-0">2FA recovery codes</h3>
    </div>
    <p class="text-muted small mb-3">
        Recovery codes let you get back in if you lose your phone. Store them in a password manager.
    </p>

    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-light btn-sm" @click="showRecoveryCodes = ! showRecoveryCodes"
            :aria-expanded="showRecoveryCodes" aria-controls="recovery-codes-section">
            <i class="bi me-1" :class="showRecoveryCodes ? 'bi-eye-slash' : 'bi-eye'"></i>
            <span x-text="showRecoveryCodes ? 'Hide recovery codes' : 'View recovery codes'"></span>
        </button>

        @if (filled($recoveryCodes))
            <button type="button" class="btn btn-light btn-sm" x-show="showRecoveryCodes" wire:click="regenerateRecoveryCodes">
                <i class="bi bi-arrow-repeat me-1"></i> Regenerate codes
            </button>
        @endif
    </div>

    <div x-show="showRecoveryCodes" x-cloak id="recovery-codes-section" class="mt-3">
        @error('recoveryCodes')
            <div class="alert alert-danger small">{{ $message }}</div>
        @enderror

        @if (filled($recoveryCodes))
            <div class="row row-cols-2 g-1 p-3 rounded-3 bg-light font-monospace small" role="list" aria-label="Recovery codes">
                @foreach($recoveryCodes as $code)
                    <div class="col" role="listitem" wire:loading.class="opacity-50">{{ $code }}</div>
                @endforeach
            </div>
            <p class="text-muted small mt-2 mb-0">
                Each code works once and is removed after use. Regenerate them if you run low.
            </p>
        @endif
    </div>
</div>
