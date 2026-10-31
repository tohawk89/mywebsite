{{-- Registration is disabled in config/fortify.php; this view is only used if it's turned back on. --}}
<x-layouts.auth title="Create an account">
    <x-auth.header title="Create an account" description="Enter your details to create your account." />
    <x-auth.session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <x-auth.input name="name" label="Name" required autofocus autocomplete="name" />
        <x-auth.input name="email" type="email" label="Email address" required autocomplete="email" placeholder="email@example.com" />
        <x-auth.input name="password" type="password" label="Password" required autocomplete="new-password" />
        <x-auth.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-dark py-2 fw-bold">Create account</button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        Already have an account? <a href="{{ route('login') }}" class="text-dark">Log in</a>
    </p>
</x-layouts.auth>
