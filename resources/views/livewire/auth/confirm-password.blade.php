<x-layouts.auth title="Confirm password">
    <x-auth.header title="Confirm password" description="This is a secure area. Confirm your password to continue." />
    <x-auth.session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.confirm.store') }}">
        @csrf
        <x-auth.input name="password" type="password" label="Password" required autofocus autocomplete="current-password" />

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-dark py-2 fw-bold" data-test="confirm-password-button">Confirm</button>
        </div>
    </form>
</x-layouts.auth>
