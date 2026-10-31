<x-layouts.auth title="Reset password">
    <x-auth.header title="Reset password" description="Enter your new password below." />
    <x-auth.session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <x-auth.input name="email" type="email" label="Email" :value="request('email')" required autocomplete="email" />
        <x-auth.input name="password" type="password" label="Password" required autocomplete="new-password" />
        <x-auth.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-dark py-2 fw-bold" data-test="reset-password-button">Reset password</button>
        </div>
    </form>
</x-layouts.auth>
