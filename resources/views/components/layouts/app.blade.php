<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(config('site.description'))
        <meta name="description" content="{{ config('site.description') }}">
    @endif

    <title>{{ $title ?? config('app.name', 'My Website') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --site-bg: {{ config('site.background_color', '#fdfbf7') }};
            --site-accent: {{ config('site.accent_color', '#2c3e50') }};
            --site-font: '{{ config('site.font', 'Instrument Sans') }}', sans-serif;
        }
        body {
            background-color: var(--site-bg) !important;
            font-family: var(--site-font);
        }
    </style>
</head>

<body>
    <!-- Navbar -->


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
