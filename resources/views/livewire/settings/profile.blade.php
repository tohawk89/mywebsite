<div style="max-width: 760px;">
    <div class="admin-page-header">
        <div>
            <h1>Profile</h1>
            <p>Your name and the email you log in with.</p>
        </div>
    </div>

    <form wire:submit="updateProfileInformation" class="admin-card mb-4">
        <div class="admin-card-body">
            <x-form.input name="name" label="Name" required autofocus autocomplete="name" />
            <x-form.input name="email" type="email" label="Email" required autocomplete="email" wrapper="mb-0" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="alert alert-warning small mt-3 mb-0">
                    Your email address is unverified.
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click.prevent="resendVerificationNotification">
                        Resend the verification email
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <div class="fw-semibold mt-1">A new verification link has been sent to your email address.</div>
                    @endif
                </div>
            @endif
        </div>
        <div class="admin-card-body border-top d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>

    <livewire:settings.delete-user-form />
</div>
