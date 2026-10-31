{{-- Shown to visitors while coming soon mode is on (App\Http\Middleware\ShowComingSoon). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }}</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ \Illuminate\Support\Str::slug(config('site.font', 'Instrument Sans')) }}:400,600,700" rel="stylesheet" />
    @vite(['resources/css/app.scss'])
    <style>
        body {
            background-color: {{ config('site.background_color', '#fdfbf7') }} !important;
            font-family: '{{ config('site.font', 'Instrument Sans') }}', sans-serif;
        }
    </style>
</head>

<body>
    <main class="d-flex min-vh-100 align-items-center justify-content-center p-4 text-center">
        <div style="max-width: 32rem;">
            @if(config('site.icon_url'))
                <img src="{{ config('site.icon_url') }}" alt="" width="64" height="64" class="mb-4 rounded">
            @endif
            <h1 class="fw-bold mb-3">{{ config('app.name') }}</h1>
            <p class="text-muted fs-5 mb-0">
                {{ config('site.maintenance.message') ?: 'Something new is on the way. Check back soon.' }}
            </p>
        </div>
    </main>
</body>

</html>
