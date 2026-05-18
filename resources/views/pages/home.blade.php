@extends('layouts.app')

@section('title', 'PodWave — Stream, Create & Discover Podcasts')

@section('content')

{{-- ============================================================
     HERO SECTION
     ============================================================ --}}
<section class="pw-hero">
    <div class="pw-hero-bg"></div>
    <div class="pw-hero-overlay"></div>
    <div class="container pw-hero-content text-center">
        <div class="pw-hero-badge mb-3">
            <i class="bi bi-soundwave"></i> {{ number_format($totalPodcasts) }}+ Podcasts Available
        </div>
        <h1 class="pw-hero-title">
            Your Next Obsession<br>
            <span class="text-accent">Starts Here</span>
        </h1>
        <p class="pw-hero-subtitle">
            Stream thousands of podcasts, discover emerging creators,<br class="d-none d-md-block">
            and share your voice with the world.
        </p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
            <a href="{{ route('browse') }}" class="btn pw-btn-primary btn-lg px-5">
                <i class="bi bi-compass-fill me-2"></i>Browse Podcasts
            </a>
            @guest
                <a href="{{ route('register') }}" class="btn pw-btn-outline btn-lg px-5">
                    <i class="bi bi-mic-fill me-2"></i>Start Creating
                </a>
            @endguest
        </div>

        {{-- Stats strip --}}
        <div class="pw-hero-stats mt-5">
            <div class="pw-stat-item">
                <span class="pw-stat-num">{{ number_format($totalPodcasts) }}+</span>
                <span class="pw-stat-label">Podcasts</span>
            </div>
            <div class="pw-stat-divider"></div>
            <div class="pw-stat-item">
                <span class="pw-stat-num">{{ number_format($totalEpisodes) }}+</span>
                <span class="pw-stat-label">Episodes</span>
            </div>
            <div class="pw-stat-divider"></div>
            <div class="pw-stat-item">
                <span class="pw-stat-num">{{ number_format($totalCreators) }}+</span>
                <span class="pw-stat-label">Creators</span>
            </div>
        </div>
    </div>

    {{-- Floating music notes decoration --}}
    <div class="pw-hero-floats">
        <i class="bi bi-music-note pw-float pw-float-1"></i>
        <i class="bi bi-headphones pw-float pw-float-2"></i>
        <i class="bi bi-soundwave pw-float pw-float-3"></i>
        <i class="bi bi-music-note-beamed pw-float pw-float-4"></i>
    </div>
</section>

{{-- ============================================================
     CATEGORIES ROW
     ============================================================ --}}
<section class="pw-section">
    <div class="container">
        <div class="pw-section-header">
            <div>
                <h2 class="pw-section-title">Browse by Category</h2>
                <p class="pw-section-sub">Find exactly what you're in the mood for</p>
            </div>
            <a href="{{ route('categories') }}" class="pw-link-more">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            @foreach($categories as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('browse') }}?category={{ $category->id }}" class="pw-category-card" style="--cat-color: {{ $category->color }};">
                    <div class="pw-cat-icon">
                        <i class="bi {{ $category->icon }}"></i>
                    </div>
                    <div class="pw-cat-info">
                        <span class="pw-cat-name">{{ $category->name }}</span>
                        <span class="pw-cat-count">{{ number_format($category->podcast_count) }} shows</span>
                    </div>
                    <div class="pw-cat-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     TRENDING PODCASTS
     ============================================================ --}}
<section class="pw-section pw-section-dark">
    <div class="container">
        <div class="pw-section-header">
            <div>
                <h2 class="pw-section-title"><i class="bi bi-fire text-accent me-2"></i>Trending Now</h2>
                <p class="pw-section-sub">The most-played podcasts this week</p>
            </div>
            <a href="{{ route('trending') }}" class="pw-link-more">See All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach($trending as $podcast)
            <div class="col-6 col-md-4 col-lg-3">
                @include('components.podcast-card', ['podcast' => $podcast])
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     LATEST EPISODES
     ============================================================ --}}
<section class="pw-section">
    <div class="container">
        <div class="pw-section-header">
            <div>
                <h2 class="pw-section-title"><i class="bi bi-broadcast text-accent me-2"></i>Latest Episodes</h2>
                <p class="pw-section-sub">Fresh out — just dropped</p>
            </div>
            <a href="{{ route('browse') }}" class="pw-link-more">Browse All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            @foreach($latestEpisodes as $episode)
            <div class="col-12 col-md-6">
                <div class="pw-episode-row">
                    <img src="{{ $episode->thumbnail_url }}" class="pw-ep-thumb rounded" alt="">
                    <div class="pw-ep-info flex-grow-1">
                        <a href="{{ route('podcasts.show', $episode->podcast->slug) }}" class="pw-ep-show">
                            {{ $episode->podcast->title }}
                        </a>
                        <a href="{{ route('episodes.show', $episode->slug) }}" class="pw-ep-title">
                            {{ $episode->title }}
                        </a>
                        <div class="pw-ep-meta">
                            <span><i class="bi bi-clock"></i> {{ $episode->formatted_duration }}</span>
                            <span><i class="bi bi-play-fill"></i> {{ $episode->formatted_plays }}</span>
                            <span>{{ $episode->release_date?->diffForHumans() }}</span>
                        </div>
                    </div>
                    <button class="pw-play-btn-sm"
                        data-audio="{{ $episode->audio_url }}"
                        data-title="{{ $episode->title }}"
                        data-podcast="{{ $episode->podcast->title }}"
                        data-thumbnail="{{ $episode->thumbnail_url }}"
                        data-episode-id="{{ $episode->id }}"
                        title="Play">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     FEATURED PODCASTS
     ============================================================ --}}
@if($featured->count())
<section class="pw-section pw-section-dark">
    <div class="container">
        <div class="pw-section-header">
            <div>
                <h2 class="pw-section-title"><i class="bi bi-star-fill text-accent me-2"></i>Editor's Picks</h2>
                <p class="pw-section-sub">Hand-curated shows we love</p>
            </div>
        </div>
        <div class="row g-4">
            @foreach($featured as $podcast)
            <div class="col-12 col-md-6 col-lg-4">
                @include('components.podcast-card-featured', ['podcast' => $podcast])
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     TOP CREATORS
     ============================================================ --}}
<section class="pw-section">
    <div class="container">
        <div class="pw-section-header">
            <div>
                <h2 class="pw-section-title"><i class="bi bi-person-badge-fill text-accent me-2"></i>Top Creators</h2>
                <p class="pw-section-sub">Follow the voices everyone is listening to</p>
            </div>
        </div>
        <div class="row g-3">
            @foreach($topCreators as $creator)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('creators.show', $creator->username ?? $creator->id) }}" class="pw-creator-card">
                    <img src="{{ $creator->avatar_url }}" class="pw-creator-avatar" alt="{{ $creator->name }}">
                    <div class="pw-creator-name">{{ $creator->name }}</div>
                    <div class="pw-creator-subs">
                        <i class="bi bi-people-fill"></i>
                        {{ number_format($creator->subscribers_count) }}
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     CTA BANNER
     ============================================================ --}}
@guest
<section class="pw-cta-section">
    <div class="container">
        <div class="pw-cta-card text-center">
            <div class="pw-cta-icon mb-4"><i class="bi bi-mic-fill"></i></div>
            <h2 class="fw-black text-white mb-2">Ready to Share Your Story?</h2>
            <p class="text-muted mb-4">Join thousands of creators on PodWave. Free to start, powerful to grow.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn pw-btn-primary btn-lg px-5">
                    <i class="bi bi-rocket-takeoff me-2"></i>Start for Free
                </a>
                <a href="{{ route('about') }}" class="btn pw-btn-outline btn-lg px-5">Learn More</a>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection
