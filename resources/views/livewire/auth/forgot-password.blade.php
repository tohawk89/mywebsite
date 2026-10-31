<x-layouts.auth title="Forgot password">
    <x-auth.header title="Forgot password" description="Enter your email to receive a password reset link." />
    <x-auth.session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-auth.input name="email" type="email" label="Email address" required autofocus placeholder="email@example.com" />

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-dark py-2 fw-bold" data-test="email-password-reset-link-button">
                Email password reset link
            </button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        Or, return to <a href="{{ route('login') }}" class="text-dark">log in</a>
    </p>
</x-layouts.auth>
