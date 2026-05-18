@extends('layouts.app')
@section('title', 'Creator Dashboard — PodWave')

@section('content')
<div class="container py-5">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold text-white mb-1">Creator Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('creator.podcasts.create') }}" class="btn pw-btn-primary">
            <i class="bi bi-plus-lg me-2"></i>New Podcast
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-4 mb-5">
        @php
        $cards = [
            ['val' => number_format($totalPodcasts),   'label' => 'Total Podcasts',    'icon' => 'bi-collection-play-fill', 'color' => '#8B5CF6'],
            ['val' => number_format($totalEpisodes),   'label' => 'Episodes',          'icon' => 'bi-play-circle-fill',     'color' => '#6366F1'],
            ['val' => number_format($totalPlays),      'label' => 'Total Plays',        'icon' => 'bi-headphones',           'color' => '#10B981'],
            ['val' => number_format($totalSubscribers),'label' => 'Subscribers',       'icon' => 'bi-people-fill',          'color' => '#F59E0B'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="col-6 col-md-3">
            <div class="pw-stat-card" style="--stat-color: {{ $card['color'] }}">
                <div class="pw-stat-card-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                <div class="pw-stat-card-val">{{ $card['val'] }}</div>
                <div class="pw-stat-card-label">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Recent Podcasts --}}
        <div class="col-lg-6">
            <div class="pw-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-semibold mb-0">My Podcasts</h5>
                    <a href="{{ route('creator.podcasts.index') }}" class="pw-link-more">View All</a>
                </div>
                @forelse($recentPodcasts as $podcast)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
                    <img src="{{ $podcast->thumbnail_url }}" class="rounded" width="48" height="48" alt="">
                    <div class="flex-grow-1 min-w-0">
                        <a href="{{ route('podcasts.show', $podcast->slug) }}" class="text-white small fw-semibold d-block text-truncate text-decoration-none">
                            {{ $podcast->title }}
                        </a>
                        <span class="text-muted" style="font-size:.75rem;">{{ $podcast->episodes_count }} episodes</span>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <span class="pw-status-badge pw-status-{{ $podcast->status }}">{{ ucfirst($podcast->status) }}</span>
                        <a href="{{ route('creator.podcasts.stats', $podcast) }}" class="pw-icon-btn" title="Stats">
                            <i class="bi bi-bar-chart-fill"></i>
                        </a>
                        <a href="{{ route('creator.podcasts.edit', $podcast) }}" class="pw-icon-btn" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-mic fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No podcasts yet.</p>
                    <a href="{{ route('creator.podcasts.create') }}" class="btn pw-btn-primary btn-sm">Create First Podcast</a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Top Episodes --}}
        <div class="col-lg-6">
            <div class="pw-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white fw-semibold mb-0">Top Performing Episodes</h5>
                </div>
                @forelse($topEpisodes as $i => $episode)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
                    <span class="text-accent fw-bold" style="width:20px;">{{ $i+1 }}</span>
                    <div class="flex-grow-1 min-w-0">
                        <a href="{{ route('episodes.show', $episode->slug) }}" class="text-white small fw-semibold d-block text-truncate text-decoration-none">
                            {{ $episode->title }}
                        </a>
                        <span class="text-muted" style="font-size:.75rem;">{{ $episode->podcast->title }}</span>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-white small"><i class="bi bi-play-fill text-accent"></i> {{ number_format($episode->play_count) }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart-fill fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Upload episodes to see stats.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="col-12">
            <div class="pw-card">
                <h5 class="text-white fw-semibold mb-4">Quick Actions</h5>
                <div class="row g-3">
                    @foreach([
                        ['route' => 'creator.podcasts.create', 'icon' => 'bi-plus-circle-fill',    'label' => 'New Podcast',      'color' => '#8B5CF6'],
                        ['route' => 'creator.podcasts.index',  'icon' => 'bi-collection-play-fill', 'label' => 'Manage Podcasts',  'color' => '#6366F1'],
                        ['route' => 'creator.profile',         'icon' => 'bi-person-fill',          'label' => 'Edit Profile',     'color' => '#10B981'],
                    ] as $action)
                    <div class="col-6 col-md-3">
                        <a href="{{ route($action['route']) }}" class="pw-quick-action" style="--qa-color: {{ $action['color'] }}">
                            <i class="bi {{ $action['icon'] }}"></i>
                            <span>{{ $action['label'] }}</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
