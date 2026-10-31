{{-- Browser tab / home screen icon: the one uploaded in Site settings, or the bundled defaults. --}}
@if($siteIcon = config('site.icon_url'))
    <link rel="icon" href="{{ $siteIcon }}">
    <link rel="apple-touch-icon" href="{{ $siteIcon }}">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif
