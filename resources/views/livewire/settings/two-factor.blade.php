<div style="max-width: 760px;">
    <div class="admin-page-header">
        <div>
            <h1>Two-factor authentication</h1>
            <p>Add a second step to logging in with an authenticator app.</p>
        </div>
    </div>

    <div class="admin-card mb-4" wire:cloak>
        <div class="admin-card-header d-flex align-items-center justify-content-between">
            <h2>Status</h2>
            @if ($twoFactorEnabled)
                <span class="admin-badge is-published">Enabled</span>
            @else
                <span class="admin-badge is-draft">Disabled</span>
            @endif
        </div>
        <div class="admin-card-body">
            @if ($twoFactorEnabled)
                <p class="mb-3">
                    When you log in, you'll be asked for a code from the authenticator app on your phone.
                </p>

                <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />

                <button type="button" class="btn btn-outline-danger mt-3" wire:click="disable">
                    <i class="bi bi-shield-x me-1"></i> Disable 2FA
                </button>
            @else
                <p class="text-muted mb-3">
                    When enabled, you'll be asked for a code from an authenticator app (like 1Password, Google
                    Authenticator or Authy) each time you log in.
                </p>

                <button type="button" class="btn btn-primary" wire:click="enable">
                    <i class="bi bi-shield-check me-1"></i> Enable 2FA
                </button>
            @endif
        </div>
    </div>

    {{-- Setup dialog, driven by Livewire state rather than Bootstrap's modal JS --}}
    @if ($showModal)
        <div class="modal d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="twoFactorTitle"
            x-data @keydown.escape.window="$wire.closeModal()">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 0.75rem;">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title fs-5 fw-semibold" id="twoFactorTitle">{{ $this->modalConfig['title'] }}</h2>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">{{ $this->modalConfig['description'] }}</p>

                        @if ($showVerificationStep)
                            <label for="two-factor-code" class="form-label">Authentication code</label>
                            <input type="text" id="two-factor-code" wire:model="code" inputmode="numeric"
                                autocomplete="one-time-code" maxlength="6" placeholder="123456" autofocus
                                class="form-control form-control-lg text-center font-monospace" style="letter-spacing: 0.4em;">
                            @error('code') <span class="text-danger small">{{ $message }}</span> @enderror

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-light flex-fill" wire:click="resetVerification">Back</button>
                                <button type="button" class="btn btn-primary flex-fill" wire:click="confirmTwoFactor">Confirm</button>
                            </div>
                        @else
                            @error('setupData')
                                <div class="alert alert-danger small">{{ $message }}</div>
                            @enderror

                            <div class="d-flex justify-content-center my-3">
                                <div class="border rounded-3 p-3 bg-white d-flex align-items-center justify-content-center"
                                    style="width: 15rem; height: 15rem;">
                                    @empty($qrCodeSvg)
                                        <div class="spinner-border text-secondary" role="status">
                                            <span class="visually-hidden">Loading…</span>
                                        </div>
                                    @else
                                        <div class="two-factor-qr w-100 h-100">{!! $qrCodeSvg !!}</div>
                                    @endempty
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary w-100" wire:click="showVerificationIfNecessary"
                                @disabled($errors->has('setupData'))>
                                {{ $this->modalConfig['buttonText'] }}
                            </button>

                            <div class="text-center text-muted small my-3">or enter the setup key manually</div>

                            <div class="input-group" x-data="{ copied: false }">
                                <input type="text" readonly class="form-control font-monospace" value="{{ $manualSetupKey }}"
                                    aria-label="Setup key">
                                <button type="button" class="btn btn-light"
                                    @click="navigator.clipboard.writeText(@js($manualSetupKey)).then(() => { copied = true; setTimeout(() => copied = false, 1500) })">
                                    <i class="bi" :class="copied ? 'bi-check-lg text-success' : 'bi-copy'"></i>
                                    <span class="visually-hidden">Copy setup key</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
