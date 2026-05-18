@extends('layouts.app')
@section('title', 'Trending Podcasts — PodWave')

@section('content')
<div class="container py-5">
    <div class="mb-5">
        <h1 class="fw-bold text-white mb-1">
            <i class="bi bi-fire text-accent me-2"></i>Trending Now
        </h1>
        <p class="text-muted mb-0">The most listened-to podcasts on PodWave right now</p>
    </div>

    @foreach($podcasts as $i => $podcast)
    <div class="pw-card mb-3 {{ $i < 3 ? 'border-accent' : '' }}" style="{{ $i < 3 ? 'border-color:rgba(139,92,246,0.3)' : '' }}">
        <div class="d-flex align-items-center gap-4">
            {{-- Rank --}}
            <div class="text-center flex-shrink-0" style="width:44px;">
                @if($i === 0)
                    <i class="bi bi-trophy-fill" style="color:#F59E0B;font-size:1.5rem;"></i>
                @elseif($i === 1)
                    <i class="bi bi-trophy-fill" style="color:#94A3B8;font-size:1.3rem;"></i>
                @elseif($i === 2)
                    <i class="bi bi-trophy-fill" style="color:#B45309;font-size:1.2rem;"></i>
                @else
                    <span class="text-muted fw-bold fs-5">{{ $i+1 }}</span>
                @endif
            </div>

            {{-- Thumbnail --}}
            <img src="{{ $podcast->thumbnail_url }}" class="rounded-3 flex-shrink-0"
                 width="80" height="80" style="object-fit:cover;" alt="{{ $podcast->title }}">

            {{-- Info --}}
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    @if($podcast->category)
                        <span class="small fw-bold" style="color:{{ $podcast->category->color }}">
                            {{ $podcast->category->name }}
                        </span>
                    @endif
                    @if($podcast->is_featured)
                        <span class="pw-status-badge" style="background:rgba(245,158,11,0.15);color:#FCD34D;font-size:.65rem;">
                            <i class="bi bi-star-fill"></i> Featured
                        </span>
                    @endif
                </div>
                <a href="{{ route('podcasts.show', $podcast->slug) }}" class="text-white fw-bold fs-5 text-decoration-none d-block mb-1 text-truncate">
                    {{ $podcast->title }}
                </a>
                <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}"
                   class="pw-card-creator d-inline-flex mb-2">
                    <img src="{{ $podcast->creator->avatar_url }}" class="pw-card-creator-avatar" alt="">
                    {{ $podcast->creator->name }}
                </a>
                <p class="text-muted small mb-0 d-none d-md-block">{{ Str::limit($podcast->description, 100) }}</p>
            </div>

            {{-- Stats --}}
            <div class="text-end flex-shrink-0 d-none d-md-block">
                <div class="text-white fw-bold fs-5">{{ $podcast->formatted_plays }}</div>
                <div class="text-muted small">total plays</div>
                @if($podcast->rating_average > 0)
                <div class="text-warning small mt-1">
                    <i class="bi bi-star-fill"></i> {{ number_format($podcast->rating_average, 1) }}
                </div>
                @endif
                <div class="text-muted small">{{ $podcast->episodes_count }} eps</div>
            </div>

            {{-- Play button --}}
            <div class="flex-shrink-0">
                <a href="{{ route('podcasts.show', $podcast->slug) }}" class="btn pw-btn-primary btn-sm">
                    <i class="bi bi-play-fill me-1"></i>Listen
                </a>
            </div>
        </div>
    </div>
    @endforeach

    @if($podcasts->hasPages())
    <div class="mt-5 d-flex justify-content-center">{{ $podcasts->links() }}</div>
    @endif
</div>
@endsection
