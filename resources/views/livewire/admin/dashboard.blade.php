<div>
    <div class="admin-page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, {{ auth()->user()->name }}.</p>
        </div>
        <a href="{{ route('posts.create') }}" class="btn btn-primary" wire:navigate>
            <i class="bi bi-plus-lg me-1"></i> New post
        </a>
    </div>

    {{-- Placeholder until the dashboard is built (phase 2). --}}
    <div class="admin-card">
        <div class="admin-card-body text-center py-5">
            <div class="admin-thumb mx-auto mb-3"><i class="bi bi-speedometer2"></i></div>
            <div class="fw-semibold mb-1">Your dashboard is coming soon</div>
            <div class="text-muted small mb-3">Stats, drafts and scheduled posts will show up here.</div>
            <a href="{{ route('posts.index') }}" class="btn btn-light btn-sm" wire:navigate>Go to posts</a>
        </div>
    </div>
</div>
