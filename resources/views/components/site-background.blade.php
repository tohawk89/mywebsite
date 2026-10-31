{{--
    The site background layer (see App\Support\SiteBackground). Fixed behind the page,
    or contained in its parent with "preview" (used by the settings page).
--}}
@props([
    'options' => config('site.background', \App\Support\SiteBackground::defaults()),
    'baseColor' => config('site.background_color', '#fdfbf7'),
    'preview' => false,
])

@php
    use App\Support\SiteBackground;
@endphp

<div @class(['site-bg', 'is-preview' => $preview]) aria-hidden="true">
    @if($options['type'] === 'animated')
        <div class="site-bg-layer" style="background-color: {{ $baseColor }};"></div>
        <canvas class="site-bg-canvas" style="opacity: {{ (int) $options['effect_opacity'] / 100 }};"
            data-site-background="{{ json_encode(SiteBackground::effectConfig($options, $baseColor)) }}"></canvas>
    @else
        <div @class(['site-bg-layer', 'is-animated' => $options['type'] === 'gradient' && $options['gradient_animate']])
            style="{{ SiteBackground::layerStyle($options, $baseColor) }}"></div>

        @if($options['type'] === 'image' && (int) $options['image_overlay_opacity'] > 0)
            <div class="site-bg-layer"
                style="background-color: {{ $options['image_overlay_color'] }}; opacity: {{ (int) $options['image_overlay_opacity'] / 100 }};"></div>
        @endif
    @endif
</div>
