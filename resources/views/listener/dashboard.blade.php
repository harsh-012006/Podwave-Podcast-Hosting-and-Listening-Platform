@extends('layouts.app')
@section('title', 'My Dashboard — PodWave')

@section('content')
<div class="container py-5">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-4 mb-5">
        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="72" height="72" style="object-fit:cover;border:3px solid var(--pw-accent);" alt="">
        <div>
            <h1 class="fw-bold text-white mb-1">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-muted mb-0">Your personal podcast hub</p>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('browse') }}" class="btn pw-btn-primary">
                <i class="bi bi-compass-fill me-2"></i>Discover More
            </a>
        </div>
    </div>

    {{-- Quick nav --}}
    <div class="d-flex gap-3 mb-5 overflow-auto pb-2">
        @foreach([
            ['href'=>route('listener.history'),       'icon'=>'bi-clock-history',       'label'=>'History'],
            ['href'=>route('listener.favorites'),      'icon'=>'bi-heart-fill',           'label'=>'Favorites'],
            ['href'=>route('listener.subscriptions'), 'icon'=>'bi-person-check-fill',    'label'=>'Subscriptions'],
            ['href'=>route('listener.notifications'), 'icon'=>'bi-bell-fill',            'label'=>'Notifications'],
            ['href'=>route('listener.profile'),        'icon'=>'bi-person-circle',        'label'=>'Profile'],
        ] as $nav)
        <a href="{{ $nav['href'] }}" class="btn pw-btn-outline btn-sm px-4 flex-shrink-0">
            <i class="bi {{ $nav['icon'] }} me-2"></i>{{ $nav['label'] }}
        </a>
        @endforeach
    </div>

    <div class="row g-4">

        {{-- Continue Listening --}}
        @if($history->count())
        <div class="col-12">
            <div class="pw-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white fw-semibold mb-0">
                        <i class="bi bi-play-circle-fill text-accent me-2"></i>Continue Listening
                    </h5>
                    <a href="{{ route('listener.history') }}" class="pw-link-more">View All</a>
                </div>
                <div class="row g-3">
                    @foreach($history->take(4) as $item)
                    <div class="col-12 col-md-6">
                        <div class="pw-episode-row position-relative">
                            <img src="{{ $item->episode->thumbnail_url }}" class="pw-ep-thumb rounded" alt="">
                            <div class="pw-ep-info flex-grow-1">
                                <a href="{{ route('podcasts.show', $item->episode->podcast->slug) }}" class="pw-ep-show">
                                    {{ $item->episode->podcast->title }}
                                </a>
                                <a href="{{ route('episodes.show', $item->episode->slug) }}" class="pw-ep-title">
                                    {{ Str::limit($item->episode->title, 50) }}
                                </a>
                                {{-- Progress bar --}}
                                <div class="mt-2" style="background:rgba(255,255,255,0.1);border-radius:2px;height:3px;overflow:hidden;">
                                    <div style="width:{{ $item->progress_percent }}%;background:var(--pw-accent);height:100%;border-radius:2px;"></div>
                                </div>
                                <div class="pw-ep-meta mt-1">
                                    <span>{{ $item->progress_percent }}% complete</span>
                                    <span>{{ $item->listened_at->diffForHumans() }}</span>
                                    @if($item->completed)
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Finished</span>
                                    @endif
                                </div>
                            </div>
                            <button class="pw-play-btn-sm flex-shrink-0"
                                data-audio="{{ $item->episode->audio_url }}"
                                data-title="{{ $item->episode->title }}"
                                data-podcast="{{ $item->episode->podcast->title }}"
                                data-thumbnail="{{ $item->episode->thumbnail_url }}"
                                data-episode-id="{{ $item->episode->id }}"
                                title="Resume">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Subscribed Creators --}}
        @if($subscriptions->count())
        <div class="col-lg-6">
            <div class="pw-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white fw-semibold mb-0">
                        <i class="bi bi-people-fill text-accent me-2"></i>Following
                    </h5>
                    <a href="{{ route('listener.subscriptions') }}" class="pw-link-more">View All</a>
                </div>
                @foreach($subscriptions->take(6) as $creator)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
                    <img src="{{ $creator->avatar_url }}" class="rounded-circle" width="44" height="44" style="object-fit:cover;" alt="">
                    <div class="flex-grow-1 min-w-0">
                        <a href="{{ route('creators.show', $creator->username ?? $creator->id) }}"
                           class="text-white fw-semibold small d-block text-decoration-none text-truncate">
                            {{ $creator->name }}
                        </a>
                        <span class="text-muted" style="font-size:.75rem;">
                            {{ $creator->podcasts_count }} podcasts
                        </span>
                    </div>
                    <a href="{{ route('creators.show', $creator->username ?? $creator->id) }}" class="pw-icon-btn flex-shrink-0">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Saved Podcasts --}}
        @if($favorites->count())
        <div class="col-lg-6">
            <div class="pw-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white fw-semibold mb-0">
                        <i class="bi bi-heart-fill text-accent me-2"></i>Saved
                    </h5>
                    <a href="{{ route('listener.favorites') }}" class="pw-link-more">View All</a>
                </div>
                <div class="row g-2">
                    @foreach($favorites->take(4) as $podcast)
                    <div class="col-6">
                        <a href="{{ route('podcasts.show', $podcast->slug) }}" class="d-flex gap-2 align-items-center text-decoration-none p-2 rounded-3"
                           style="background:var(--pw-surface-3);transition:background 0.2s;"
                           onmouseover="this.style.background='rgba(139,92,246,0.1)'"
                           onmouseout="this.style.background='var(--pw-surface-3)'">
                            <img src="{{ $podcast->thumbnail_url }}" class="rounded-2 flex-shrink-0" width="44" height="44" style="object-fit:cover;" alt="">
                            <div class="min-w-0">
                                <div class="text-white fw-semibold small text-truncate">{{ $podcast->title }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $podcast->creator->name }}</div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Recommended --}}
        @if($recommended->count())
        <div class="col-12">
            <div class="pw-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white fw-semibold mb-0">
                        <i class="bi bi-magic text-accent me-2"></i>Recommended For You
                    </h5>
                    <a href="{{ route('browse') }}" class="pw-link-more">Browse All</a>
                </div>
                <div class="row g-3">
                    @foreach($recommended->take(4) as $podcast)
                    <div class="col-6 col-md-3">
                        @include('components.podcast-card', ['podcast' => $podcast])
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Empty state if nothing --}}
        @if($history->isEmpty() && $favorites->isEmpty() && $subscriptions->isEmpty())
        <div class="col-12">
            <div class="pw-card text-center py-5">
                <i class="bi bi-headphones fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-white mb-2">Your Library is Empty</h5>
                <p class="text-muted mb-4">Start listening to podcasts to build your personal library.</p>
                <a href="{{ route('browse') }}" class="btn pw-btn-primary px-5">
                    <i class="bi bi-compass-fill me-2"></i>Discover Podcasts
                </a>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
