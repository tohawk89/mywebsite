@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success small rounded-0 border-0']) }} role="status">
        {{ $status }}
    </div>
@endif
