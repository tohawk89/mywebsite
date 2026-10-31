{{-- Bootstrap layout for logged-out pages (password reset, two-factor challenge...). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="auth-body antialiased">
    <div class="d-flex min-vh-100 align-items-center justify-content-center p-3">
        <div class="card border-0 shadow-sm rounded-0 w-100" style="max-width: 400px;">
            <div class="card-body p-4 p-sm-5">
                {{ $slot }}
            </div>
        </div>
    </div>

    {{-- Livewire bundles Alpine, used by the two-factor challenge. --}}
    @livewireScripts
</body>

</html>
