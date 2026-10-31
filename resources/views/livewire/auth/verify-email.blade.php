<x-layouts.auth title="Verify email">
    <x-auth.header title="Verify your email" description="Click the link we just emailed you to verify your address." />

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small rounded-0 border-0" role="status">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="d-grid">
        @csrf
        <button type="submit" class="btn btn-dark py-2 fw-bold">Resend verification email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
        @csrf
        <button type="submit" class="btn btn-link btn-sm text-muted" data-test="logout-button">Log out</button>
    </form>
</x-layouts.auth>
