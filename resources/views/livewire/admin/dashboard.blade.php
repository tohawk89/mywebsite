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

    {{-- Stat tiles --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['published', 'Published', 'bi-check-circle', route('posts.index', ['status' => 'published'])],
            ['drafts', 'Drafts', 'bi-pencil', route('posts.index', ['status' => 'draft'])],
            ['scheduled', 'Scheduled', 'bi-clock', route('posts.index', ['status' => 'scheduled'])],
            ['this_month', 'Published this month', 'bi-calendar-check', null],
        ] as [$key, $label, $icon, $url])
            <div class="col-6 col-xl-3">
                <a @if($url) href="{{ $url }}" wire:navigate @endif
                    @class(['admin-card admin-stat d-block text-decoration-none text-body', 'is-link' => $url])>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small text-muted">{{ $label }}</span>
                        <i class="bi {{ $icon }} text-muted"></i>
                    </div>
                    <div class="admin-stat-value">{{ number_format($this->stats[$key]) }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        {{-- Continue editing --}}
        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <h2>Continue editing</h2>
                    <a href="{{ route('posts.index', ['status' => 'draft']) }}" class="small text-decoration-none" wire:navigate>All drafts</a>
                </div>
                @forelse($this->drafts as $draft)
                    <a href="{{ route('posts.edit', $draft) }}" class="admin-list-row" wire:navigate>
                        <i class="bi {{ \App\PostTypes\PostTypeRegistry::handler($draft->type)->icon() }} text-muted"></i>
                        <span class="text-truncate">{{ $draft->title ?: 'Untitled' }}</span>
                        <span class="small text-muted ms-auto flex-shrink-0">{{ $draft->updated_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <div class="admin-card-body text-muted small">No drafts. Anything you save as a draft shows up here.</div>
                @endforelse
            </div>
        </div>

        {{-- Coming up --}}
        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <h2>Coming up</h2>
                    <a href="{{ route('posts.index', ['status' => 'scheduled']) }}" class="small text-decoration-none" wire:navigate>All scheduled</a>
                </div>
                @forelse($this->upcoming as $scheduled)
                    <a href="{{ route('posts.edit', $scheduled) }}" class="admin-list-row" wire:navigate>
                        <i class="bi {{ \App\PostTypes\PostTypeRegistry::handler($scheduled->type)->icon() }} text-muted"></i>
                        <span class="text-truncate">{{ $scheduled->title ?: 'Untitled' }}</span>
                        <span class="small text-muted ms-auto flex-shrink-0" title="{{ $scheduled->posted_at->format('M j, Y H:i') }}">
                            {{ $scheduled->posted_at->format('M j, H:i') }}
                        </span>
                    </a>
                @empty
                    <div class="admin-card-body text-muted small">Nothing scheduled. Give a post a future date to schedule it.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Posts per month --}}
        <div class="col-lg-8">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h2>Posts per month</h2>
                    <p>Published posts over the last 12 months.</p>
                </div>
                <div class="admin-card-body">
                    @php($maxMonth = max(1, max(array_column($this->postsPerMonth, 'count'))))
                    <div class="admin-chart" role="img"
                        aria-label="Posts per month: {{ collect($this->postsPerMonth)->map(fn ($m) => $m['month'].' '.$m['count'])->implode(', ') }}">
                        @foreach($this->postsPerMonth as $month)
                            <div class="admin-chart-col" title="{{ $month['month'] }}: {{ $month['count'] }} {{ str('post')->plural($month['count']) }}">
                                <span class="admin-chart-value">{{ $month['count'] ?: '' }}</span>
                                <span @class(['admin-chart-bar', 'is-current' => $loop->last])
                                    style="height: {{ round($month['count'] / $maxMonth * 100) }}%;"></span>
                                <span class="admin-chart-label">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Content mix --}}
        <div class="col-lg-4">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h2>Content mix</h2>
                </div>
                <div class="admin-card-body">
                    @php($totalPosts = max(1, array_sum(array_column($this->typeMix, 'count'))))
                    @forelse($this->typeMix as $row)
                        <a href="{{ route('posts.index', ['type' => $row['type']]) }}" class="admin-mix-row" wire:navigate>
                            <span class="d-flex align-items-center gap-2 small">
                                <i class="bi {{ $row['icon'] }} text-muted"></i>
                                <span class="text-truncate">{{ $row['label'] }}</span>
                                <span class="ms-auto text-muted">{{ $row['count'] }}</span>
                            </span>
                            <span class="admin-mix-track"><span style="width: {{ round($row['count'] / $totalPosts * 100) }}%;"></span></span>
                        </a>
                    @empty
                        <div class="text-muted small">No posts yet.</div>
                    @endforelse

                    @if($this->topTags->isNotEmpty())
                        <div class="admin-nav-heading mx-0 mt-4">Top tags</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($this->topTags as $tag)
                                <span class="admin-badge is-draft">{{ $tag->name }} · {{ $tag->posts_count }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Quick create --}}
        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h2>Quick create</h2>
                </div>
                <div class="admin-card-body">
                    <div class="row row-cols-3 row-cols-sm-4 g-2">
                        @foreach($this->quickCreate as $option)
                            <div class="col">
                                <a href="{{ route('posts.create', ['type' => $option['type']]) }}" class="admin-type-option h-100 text-decoration-none" wire:navigate>
                                    <i class="bi {{ $option['icon'] }}"></i>
                                    <span>{{ $option['label'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Site health --}}
        <div class="col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <h2>Site health</h2>
                    @php($issues = collect($this->healthChecks)->where('ok', false)->count())
                    @if($issues)
                        <span class="admin-badge is-pinned">{{ $issues }} to fix</span>
                    @else
                        <span class="admin-badge is-published">All good</span>
                    @endif
                </div>
                <div class="admin-card-body">
                    @foreach(collect($this->healthChecks)->sortBy('ok') as $check)
                        <div class="d-flex gap-2 small {{ $loop->last ? '' : 'mb-2' }}">
                            <i @class(['bi flex-shrink-0', 'bi-check-circle-fill text-success' => $check['ok'], 'bi-exclamation-triangle-fill text-warning' => ! $check['ok']])></i>
                            <span>
                                {{ $check['message'] }}
                                @if(! empty($check['url']))
                                    <a href="{{ $check['url'] }}" class="text-decoration-none ms-1" wire:navigate>Review</a>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
