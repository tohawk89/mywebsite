{{-- Search engine and link preview (Open Graph / X) tags, from Site settings. --}}
@php
    $siteName = config('app.name');
    $description = config('site.description');
    $shareImage = config('site.seo.share_image');
    $twitterHandle = config('site.seo.twitter_handle');
@endphp

@if($description)
    <meta name="description" content="{{ $description }}">
@endif

@if(config('site.seo.noindex'))
    <meta name="robots" content="noindex, nofollow">
@endif

@if($verification = config('site.seo.google_verification'))
    <meta name="google-site-verification" content="{{ $verification }}">
@endif

<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title ?? $siteName }}">
<meta property="og:url" content="{{ url()->current() }}">
@if($description)
    <meta property="og:description" content="{{ $description }}">
@endif
@if($shareImage)
    <meta property="og:image" content="{{ $shareImage }}">
@endif

<meta name="twitter:card" content="{{ $shareImage ? 'summary_large_image' : 'summary' }}">
@if($twitterHandle)
    <meta name="twitter:site" content="{{ '@'.$twitterHandle }}">
@endif
