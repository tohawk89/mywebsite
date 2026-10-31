{{--
    A tag that filters the home feed. Without JavaScript it's a plain ?tag= link;
    on the feed it filters in place and doesn't trigger the card's own click (modal, lightbox).
--}}
@props(['slug'])

<a href="{{ route('home', ['tag' => $slug]) }}" {{ $attributes->class('tag-link') }} x-data
    x-on:click.prevent.stop="Livewire.dispatch('filter-tag', { tag: @js($slug) }); window.scrollTo({ top: 0, behavior: 'smooth' })">{{ $slot }}</a>
