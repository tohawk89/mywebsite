<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'My Website') }}</title>

    @include('partials.seo-meta')
    @include('partials.favicon')

    @php
        $bodyFont = config('site.font', 'Instrument Sans');
        $headingFont = config('site.heading_font') ?: $bodyFont;
        $fontFamilies = collect([$bodyFont, $headingFont])->unique()
            ->map(fn ($font) => \Illuminate\Support\Str::slug($font).':400,500,600,700')->implode('|');
    @endphp

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ $fontFamilies }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --site-bg: {{ config('site.background_color', '#fdfbf7') }};
            --site-accent: {{ config('site.accent_color', '#2c3e50') }};
            --site-font: '{{ $bodyFont }}', sans-serif;
            --site-heading-font: '{{ $headingFont }}', sans-serif;
        }
        body {
            background-color: var(--site-bg) !important;
            font-family: var(--site-font);
        }
    </style>

    @include('partials.analytics')
</head>

<body @class([
    'cards-rounded' => config('site.card_corners') === 'rounded',
    'cards-flat' => ! config('site.card_shadow', true),
])>
    <x-site-background />

    @if(config('site.maintenance.enabled') && auth()->user()?->is_admin)
        <div class="site-admin-notice">
            <i class="bi bi-cone-striped me-1"></i>
            Coming soon mode is on. Visitors see the coming soon page; you see the site because you're logged in.
            <a href="{{ route('settings', 'maintenance') }}">Change</a>
        </div>
    @endif

    <!-- Main Content -->
    <main class="container py-4">
        {{ $slot }}
    </main>

    @if(config('site.footer_text'))
        <footer class="text-center py-4 small text-muted border-top">
            {{ config('site.footer_text') }}
        </footer>
    @endif

    <!-- Modals Container -->
    <livewire:nav-modal />

    @stack('scripts')
</body>

</html>
