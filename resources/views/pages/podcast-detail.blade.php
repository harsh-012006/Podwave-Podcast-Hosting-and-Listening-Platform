@extends('layouts.app')

@section('title', $podcast->title . ' — PodWave')
@section('meta_description', Str::limit($podcast->description, 160))

@section('content')
<div class="pw-detail-hero" style="--thumb: url('{{ $podcast->thumbnail_url }}')">
    <div class="pw-detail-hero-blur"></div>
    <div class="container pw-detail-hero-content">
        <div class="row align-items-end g-4">
            <div class="col-auto">
                <img src="{{ $podcast->thumbnail_url }}" class="pw-detail-cover shadow" alt="{{ $podcast->title }}">
            </div>
            <div class="col">
                <div class="text-uppercase small fw-semibold mb-1" style="color: {{ $podcast->category?->color ?? '#8B5CF6' }}">
                    <i class="bi {{ $podcast->category?->icon ?? 'bi-mic' }}"></i>
                    {{ $podcast->category?->name ?? 'Podcast' }}
                    @if($podcast->genre) · {{ $podcast->genre->name }} @endif
                </div>
                <h1 class="pw-detail-title">{{ $podcast->title }}</h1>
                <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}" class="pw-detail-creator d-inline-flex align-items-center gap-2 mb-3">
                    <img src="{{ $podcast->creator->avatar_url }}" class="rounded-circle" width="28" height="28" alt="">
                    {{ $podcast->creator->name }}
                </a>

                <div class="pw-detail-stats mb-4">
                    <span><i class="bi bi-play-circle-fill"></i> {{ number_format($podcast->total_plays) }} plays</span>
                    <span><i class="bi bi-collection-play-fill"></i> {{ $podcast->episodes_count }} episodes</span>
                    <span><i class="bi bi-people-fill"></i> {{ number_format($podcast->creator->subscriber_count) }} subscribers</span>
                    @if($podcast->rating_average > 0)
                    <span>
                        @for($s=1; $s<=5; $s++)
                            <i class="bi bi-star{{ $s <= round($podcast->rating_average) ? '-fill text-warning' : ' text-muted' }}"></i>
                        @endfor
                        {{ number_format($podcast->rating_average, 1) }} ({{ $podcast->rating_count }})
                    </span>
                    @endif
                    @if($podcast->language)
                        <span><i class="bi bi-translate"></i> {{ $podcast->language }}</span>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @auth
                        {{-- Subscribe Button --}}
                        <button class="btn pw-btn-primary" id="subscribeBtn"
                            data-creator-id="{{ $podcast->creator->id }}"
                            data-subscribed="{{ $isSubscribed ? 'true' : 'false' }}">
                            <i class="bi {{ $isSubscribed ? 'bi-bell-fill' : 'bi-bell' }}"></i>
                            <span>{{ $isSubscribed ? 'Subscribed' : 'Subscribe' }}</span>
                        </button>

                        {{-- Favorite Button --}}
                        <button class="btn pw-btn-outline" id="favoriteBtn"
                            data-podcast-id="{{ $podcast->id }}"
                            data-favorited="{{ $isFavorited ? 'true' : 'false' }}">
                            <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                            <span>{{ $isFavorited ? 'Saved' : 'Save' }}</span>
                        </button>

                        {{-- Like Button --}}
                        <button class="btn pw-btn-outline" id="likeBtn"
                            data-type="podcast" data-id="{{ $podcast->id }}"
                            data-liked="{{ $isLiked ? 'true' : 'false' }}">
                            <i class="bi {{ $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                            <span id="likeCount">{{ $podcast->likes()->count() }}</span>
                        </button>

                        {{-- Share --}}
                        <button class="btn pw-btn-ghost" onclick="navigator.clipboard.writeText(window.location.href); showToast('Link copied!')">
                            <i class="bi bi-share-fill"></i> Share
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn pw-btn-primary">
                            <i class="bi bi-bell"></i> Subscribe
                        </a>
                        <a href="{{ route('login') }}" class="btn pw-btn-outline">
                            <i class="bi bi-heart"></i> Save
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">

            {{-- Description --}}
            <div class="pw-card mb-4">
                <h5 class="fw-semibold text-white mb-3">About this Podcast</h5>
                <p class="text-muted">{{ $podcast->description }}</p>
                @if($podcast->tags && count($podcast->tags))
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($podcast->tags as $tag)
                        <a href="{{ route('browse') }}?search={{ urlencode($tag) }}" class="pw-tag">{{ $tag }}</a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Star Rating --}}
            @auth
            <div class="pw-card mb-4">
                <h6 class="text-white mb-3">Rate this Podcast</h6>
                <div class="pw-star-rating" data-podcast-id="{{ $podcast->id }}">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star pw-star {{ $userRating && $i <= $userRating ? 'active' : '' }}"
                           data-value="{{ $i }}"></i>
                    @endfor
                    <span class="ms-2 text-muted small" id="ratingText">
                        {{ $userRating ? 'Your rating: ' . $userRating . '/5' : 'Click to rate' }}
                    </span>
                </div>
            </div>
            @endauth

            {{-- Episodes List --}}
            <div class="pw-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-semibold text-white mb-0">
                        Episodes <span class="text-muted fw-normal">({{ $podcast->episodes_count }})</span>
                    </h5>
                </div>

                @forelse($episodes as $episode)
                <div class="pw-episode-item {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
                    <div class="d-flex gap-3 py-3">
                        <div class="flex-shrink-0">
                            <button class="pw-play-btn-lg"
                                data-audio="{{ $episode->audio_url }}"
                                data-title="{{ $episode->title }}"
                                data-podcast="{{ $podcast->title }}"
                                data-thumbnail="{{ $episode->thumbnail_url }}"
                                data-episode-id="{{ $episode->id }}"
                                title="Play episode">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <span class="pw-ep-number">{{ $episode->episode_label }}</span>
                                    <a href="{{ route('episodes.show', $episode->slug) }}" class="pw-ep-item-title d-block">
                                        {{ $episode->title }}
                                    </a>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="text-muted small">{{ $episode->formatted_duration }}</div>
                                    <div class="text-muted small">{{ $episode->release_date?->format('M j, Y') }}</div>
                                </div>
                            </div>
                            <p class="text-muted small mt-1 mb-2">{{ Str::limit($episode->description, 140) }}</p>
                            <div class="d-flex gap-3 align-items-center">
                                <span class="text-muted small"><i class="bi bi-play-fill"></i> {{ $episode->formatted_plays }}</span>
                                <span class="text-muted small"><i class="bi bi-chat-fill"></i> {{ $episode->comment_count }}</span>
                                @if($episode->episode_type !== 'full')
                                    <span class="pw-badge-type">{{ ucfirst($episode->episode_type) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="bi bi-collection-play fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No episodes published yet.</p>
                </div>
                @endforelse

                {{-- Pagination --}}
                @if($episodes->hasPages())
                <div class="mt-4">{{ $episodes->links('pagination.custom') }}</div>
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- Creator card --}}
            <div class="pw-card mb-4">
                <h6 class="text-white fw-semibold mb-3">About the Creator</h6>
                <div class="d-flex gap-3 mb-3">
                    <img src="{{ $podcast->creator->avatar_url }}" class="rounded-circle" width="60" height="60" alt="">
                    <div>
                        <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}"
                           class="text-white fw-semibold d-block text-decoration-none">
                            {{ $podcast->creator->name }}
                        </a>
                        <span class="text-muted small">
                            {{ number_format($podcast->creator->subscriber_count) }} subscribers
                        </span>
                    </div>
                </div>
                @if($podcast->creator->bio)
                    <p class="text-muted small">{{ Str::limit($podcast->creator->bio, 150) }}</p>
                @endif
                <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}"
                   class="btn pw-btn-outline btn-sm w-100">
                    View Profile
                </a>
            </div>

            {{-- Related Podcasts --}}
            @if($related->count())
            <div class="pw-card">
                <h6 class="text-white fw-semibold mb-3">You Might Also Like</h6>
                @foreach($related as $rel)
                <a href="{{ route('podcasts.show', $rel->slug) }}" class="pw-related-item">
                    <img src="{{ $rel->thumbnail_url }}" class="pw-related-thumb rounded" alt="">
                    <div>
                        <div class="pw-related-title">{{ Str::limit($rel->title, 35) }}</div>
                        <div class="pw-related-meta">{{ $rel->creator->name }}</div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Subscribe toggle
const subscribeBtn = document.getElementById('subscribeBtn');
if (subscribeBtn) {
    subscribeBtn.addEventListener('click', function() {
        const creatorId = this.dataset.creatorId;
        const isSubscribed = this.dataset.subscribed === 'true';
        fetch(`/listener/subscribe/${creatorId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            this.dataset.subscribed = data.subscribed ? 'true' : 'false';
            this.querySelector('i').className = data.subscribed ? 'bi bi-bell-fill' : 'bi bi-bell';
            this.querySelector('span').textContent = data.subscribed ? 'Subscribed' : 'Subscribe';
        });
    });
}

// Favorite toggle
const favoriteBtn = document.getElementById('favoriteBtn');
if (favoriteBtn) {
    favoriteBtn.addEventListener('click', function() {
        const podcastId = this.dataset.podcastId;
        fetch(`/listener/favorite/${podcastId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            this.dataset.favorited = data.favorited ? 'true' : 'false';
            const icon = this.querySelector('i');
            icon.className = data.favorited ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
            this.querySelector('span').textContent = data.favorited ? 'Saved' : 'Save';
        });
    });
}

// Star rating
document.querySelectorAll('.pw-star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.value;
        const podcastId = this.closest('.pw-star-rating').dataset.podcastId;
        fetch(`/listener/rate/${podcastId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json', 'Accept': 'application/json'
            },
            body: JSON.stringify({ rating })
        })
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.pw-star').forEach((s, i) => {
                s.className = i < rating ? 'bi bi-star-fill pw-star active' : 'bi bi-star pw-star';
            });
            document.getElementById('ratingText').textContent = `Your rating: ${rating}/5`;
        });
    });
    star.addEventListener('mouseover', function() {
        const val = this.dataset.value;
        document.querySelectorAll('.pw-star').forEach((s, i) => {
            s.style.color = i < val ? '#F59E0B' : '';
        });
    });
    star.addEventListener('mouseout', function() {
        document.querySelectorAll('.pw-star').forEach(s => s.style.color = '');
    });
});
</script>
@endpush
