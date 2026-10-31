@props([
    'title',
    'description' => null,
])

<div class="text-center mb-4">
    <h1 class="h3 fw-bold mb-1" style="color: #1a1a1a;">{{ $title }}</h1>
    @if($description)
        <p class="text-muted small mb-0">{{ $description }}</p>
    @endif
</div>
