<x-layouts.auth title="Two-factor authentication">
    <div x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            toggleInput() {
                this.showRecoveryInput = ! this.showRecoveryInput;
                this.$nextTick(() => (this.showRecoveryInput ? this.$refs.recoveryCode : this.$refs.code).focus());
            },
        }">
        <div x-show="! showRecoveryInput">
            <x-auth.header title="Authentication code" description="Enter the code from your authenticator app." />
        </div>
        <div x-show="showRecoveryInput" x-cloak>
            <x-auth.header title="Recovery code" description="Enter one of your emergency recovery codes." />
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}">
            @csrf

            <div x-show="! showRecoveryInput" class="mb-3">
                <label for="code" class="visually-hidden">Authentication code</label>
                <input type="text" name="code" id="code" x-ref="code" inputmode="numeric" maxlength="6"
                    autocomplete="one-time-code" autofocus placeholder="123456"
                    x-bind:required="! showRecoveryInput"
                    @class(['form-control form-control-lg text-center font-monospace', 'is-invalid' => $errors->has('code')])
                    style="letter-spacing: 0.4em;">
                @error('code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
            </div>

            <div x-show="showRecoveryInput" x-cloak class="mb-3">
                <label for="recovery_code" class="visually-hidden">Recovery code</label>
                <input type="text" name="recovery_code" id="recovery_code" x-ref="recoveryCode"
                    autocomplete="one-time-code" x-bind:required="showRecoveryInput"
                    @class(['form-control text-center font-monospace', 'is-invalid' => $errors->has('recovery_code')])>
                @error('recovery_code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-dark py-2 fw-bold">Continue</button>
            </div>
        </form>

        <p class="text-center small text-muted mt-4 mb-0">
            or
            <button type="button" class="btn btn-link btn-sm p-0 align-baseline text-dark" @click="toggleInput()"
                x-text="showRecoveryInput ? 'log in using an authentication code' : 'log in using a recovery code'">
                log in using a recovery code
            </button>
        </p>
    </div>
</x-layouts.auth>
