{{-- Analytics script from Site settings > Integrations. Public layout only; optionally skipped for logged-in admins. --}}
@php
    $provider = config('site.analytics.provider', 'none');
    $skip = config('site.analytics.exclude_admins') && auth()->user()?->is_admin;
@endphp

@if(! $skip && $provider === 'ga4' && ($gaId = config('site.analytics.ga_id')))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @js($gaId));
    </script>
@elseif(! $skip && $provider === 'plausible' && ($domain = config('site.analytics.plausible_domain')))
    <script defer data-domain="{{ $domain }}" src="https://plausible.io/js/script.js"></script>
@endif
