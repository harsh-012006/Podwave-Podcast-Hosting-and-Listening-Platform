@extends('layouts.app')
@section('title', 'Episodes — ' . $podcast->title)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('creator.podcasts.index') }}" class="pw-link-more mb-2 d-block">
                <i class="bi bi-chevron-left me-1"></i>Back to Podcasts
            </a>
            <h1 class="fw-bold text-white mb-1">Episodes</h1>
            <p class="text-muted mb-0">
                <i class="bi bi-collection-play-fill text-accent me-1"></i>{{ $podcast->title }}
            </p>
        </div>
        <a href="{{ route('creator.episodes.create', $podcast) }}" class="btn pw-btn-primary">
            <i class="bi bi-cloud-upload-fill me-2"></i>Upload Episode
        </a>
    </div>

    @if($episodes->count())
    <div class="pw-card p-0">
        @foreach($episodes as $episode)
        <div class="d-flex align-items-center gap-3 p-3 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
            <img src="{{ $episode->thumbnail_url }}" class="rounded-2 flex-shrink-0" width="60" height="60" style="object-fit:cover;" alt="">
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="pw-badge-type">{{ $episode->episode_label }}</span>
                    <span class="pw-status-badge pw-status-{{ $episode->status }}">{{ ucfirst($episode->status) }}</span>
                    @if($episode->is_explicit) <span class="pw-explicit-badge" style="position:relative;top:0;left:0;">E</span> @endif
                </div>
                <a href="{{ route('episodes.show', $episode->slug) }}" class="text-white fw-semibold d-block text-truncate text-decoration-none">
                    {{ $episode->title }}
                </a>
                <div class="d-flex gap-3 mt-1" style="font-size:.75rem;color:var(--pw-text-muted);">
                    <span><i class="bi bi-clock"></i> {{ $episode->formatted_duration }}</span>
                    <span><i class="bi bi-play-fill"></i> {{ number_format($episode->play_count) }}</span>
                    <span><i class="bi bi-calendar3"></i> {{ $episode->release_date?->format('M j, Y') ?? 'No date' }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="{{ route('creator.episodes.edit', [$podcast, $episode]) }}" class="pw-icon-btn" title="Edit">
                    <i class="bi bi-pencil-fill"></i>
                </a>
                <form action="{{ route('creator.episodes.destroy', [$podcast, $episode]) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="pw-icon-btn border-0" style="background:rgba(239,68,68,0.1);color:#FCA5A5;"
                        data-confirm="Delete '{{ $episode->title }}'?" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $episodes->links() }}</div>
    @else
    <div class="pw-card text-center py-5">
        <i class="bi bi-play-circle fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No Episodes Yet</h5>
        <p class="text-muted mb-4">Upload your first episode to get started.</p>
        <a href="{{ route('creator.episodes.create', $podcast) }}" class="btn pw-btn-primary px-5">
            <i class="bi bi-cloud-upload-fill me-2"></i>Upload First Episode
        </a>
    </div>
    @endif
</div>
@endsection
