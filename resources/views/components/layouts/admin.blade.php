<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }} Admin</title>

    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

@php
    $isAdmin = (bool) auth()->user()?->is_admin;
    $postCounts = $isAdmin ? [
        'all' => \App\Models\Post::count(),
        'draft' => \App\Models\Post::where('is_draft', true)->count(),
        'scheduled' => \App\Models\Post::where('is_draft', false)->where('posted_at', '>', now())->count(),
    ] : [];
    $postStatus = request()->routeIs('posts.index') ? request('status') : null;
@endphp

<body class="admin-body antialiased">
    <div class="admin-shell">
        {{-- Sidebar: static on large screens, slide-in offcanvas below lg --}}
        <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar"
            aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header d-lg-none">
                <span class="fw-semibold" id="adminSidebarLabel">{{ config('app.name') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"
                    aria-label="Close menu"></button>
            </div>

            <div class="admin-sidebar-inner">
                <a href="{{ $isAdmin ? route('dashboard') : route('profile.edit') }}"
                    class="admin-brand d-none d-lg-flex" wire:navigate>
                    <span class="admin-brand-mark"><i class="bi bi-grid-1x2-fill"></i></span>
                    <span class="text-truncate">{{ config('app.name') }}</span>
                </a>

                @if($isAdmin)
                    <a href="{{ route('posts.create') }}" class="btn btn-primary w-100 mb-4" wire:navigate>
                        <i class="bi bi-plus-lg me-1"></i> New post
                    </a>

                    <nav class="admin-nav" aria-label="Admin">
                        <a href="{{ route('dashboard') }}" wire:navigate
                            @class(['nav-link', 'active' => request()->routeIs('dashboard')])>
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('posts.index') }}" wire:navigate
                            @class(['nav-link', 'active' => request()->routeIs('posts.*') && ! $postStatus])>
                            <i class="bi bi-collection"></i> Posts
                            <span class="admin-nav-count">{{ $postCounts['all'] }}</span>
                        </a>
                        <a href="{{ route('posts.index', ['status' => 'draft']) }}" wire:navigate
                            @class(['nav-link nav-link-sub', 'active' => $postStatus === 'draft'])>
                            Drafts <span class="admin-nav-count">{{ $postCounts['draft'] }}</span>
                        </a>
                        <a href="{{ route('posts.index', ['status' => 'scheduled']) }}" wire:navigate
                            @class(['nav-link nav-link-sub', 'active' => $postStatus === 'scheduled'])>
                            Scheduled <span class="admin-nav-count">{{ $postCounts['scheduled'] }}</span>
                        </a>

                        <a href="{{ route('tags.index') }}" wire:navigate
                            @class(['nav-link', 'active' => request()->routeIs('tags.*')])>
                            <i class="bi bi-tags"></i> Tags
                            <span class="admin-nav-count">{{ \App\Models\Tag::count() }}</span>
                        </a>

                        <div class="admin-nav-heading">Site settings</div>
                        @php($settingsSection = request()->routeIs('settings') ? (request()->route('section') ?? 'general') : null)
                        @foreach(\App\Livewire\Admin\SiteSettings::SECTIONS as $key => [$label, $description, $icon])
                            <a href="{{ route('settings', $key) }}" wire:navigate
                                @class(['nav-link', 'active' => $settingsSection === $key])>
                                <i class="bi {{ $icon }}"></i> {{ $label }}
                            </a>
                        @endforeach
                        <a href="{{ route('home') }}" target="_blank" class="nav-link">
                            <i class="bi bi-box-arrow-up-right"></i> View site
                        </a>
                    </nav>
                @endif

                <nav class="admin-nav mt-auto" aria-label="Account">
                    <div class="admin-nav-heading">Account</div>
                    <a href="{{ route('profile.edit') }}" wire:navigate
                        @class(['nav-link', 'active' => request()->routeIs('profile.edit')])>
                        <i class="bi bi-person"></i> Profile
                    </a>
                    <a href="{{ route('user-password.edit') }}" wire:navigate
                        @class(['nav-link', 'active' => request()->routeIs('user-password.edit')])>
                        <i class="bi bi-key"></i> Password
                    </a>
                    @if(\Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <a href="{{ route('two-factor.show') }}" wire:navigate
                            @class(['nav-link', 'active' => request()->routeIs('two-factor.show')])>
                            <i class="bi bi-shield-lock"></i> Two-factor auth
                        </a>
                    @endif
                </nav>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
                    <i class="bi bi-list fs-5"></i>
                </button>

                @if($isAdmin)
                    <form action="{{ route('posts.index') }}" method="GET" class="admin-search" role="search">
                        <i class="bi bi-search"></i>
                        <input type="search" name="search" value="{{ request()->routeIs('posts.index') ? request('search') : '' }}"
                            class="form-control" placeholder="Search posts" aria-label="Search posts">
                    </form>
                @endif

                <div class="dropdown ms-auto">
                    <button class="btn btn-light d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="admin-avatar">{{ auth()->user()->initials() }}</span>
                        <span class="d-none d-sm-inline small fw-medium">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}" wire:navigate>
                            <i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>View site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Log out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <main class="admin-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Toasts: shows the session "status" flash and any Livewire dispatch('notify', message: '...') --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3"
        x-data="{
            toasts: [],
            add(message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, message });
                setTimeout(() => this.remove(id), 4000);
            },
            remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
        }"
        @if(session('status')) x-init="add(@js(session('status')))" @endif
        @notify.window="add($event.detail.message ?? $event.detail)">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast show align-items-center border-0 shadow" role="status" aria-live="polite">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle-fill text-success me-2"></i><span x-text="toast.message"></span>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" aria-label="Close" @click="remove(toast.id)"></button>
                </div>
            </div>
        </template>
    </div>
</body>

</html>
