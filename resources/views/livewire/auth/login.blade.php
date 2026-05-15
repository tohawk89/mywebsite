<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="antialiased">
    <div class="d-flex min-vh-100 align-items-center justify-content-center" style="background-color: #fdfbf7;">
        <div class="card border-0 shadow-sm rounded-0" style="width: 100%; max-width: 400px; background-color: #fff;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1" style="color: #1a1a1a;">Welcome Back</h3>
                    <p class="text-muted small">Please log in to your account.</p>
                </div>

                <x-auth.session-status class="mb-4 text-success small text-center" :status="session('status')" />

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold text-uppercase"
                            style="letter-spacing: 0.5px; font-size: 0.75rem;">Email</label>
                        <input type="email" name="email" id="email" class="form-control rounded-0 p-2"
                            value="{{ old('email') }}" required autofocus autocomplete="username"
                            style="background-color: #fdfbf7; border-color: #e5e5e5;">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-bold text-uppercase mb-0"
                                style="letter-spacing: 0.5px; font-size: 0.75rem;">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-decoration-none small text-muted hover-underline" wire:navigate>Forgot?</a>
                            @endif
                        </div>
                        <input type="password" name="password" id="password" class="form-control rounded-0 p-2" required
                            autocomplete="current-password" style="background-color: #fdfbf7; border-color: #e5e5e5;">
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input rounded-0" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="form-check-label small text-muted">Remember me</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark rounded-0 py-2 fw-bold text-uppercase"
                            style="letter-spacing: 1px;">
                            Log in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>