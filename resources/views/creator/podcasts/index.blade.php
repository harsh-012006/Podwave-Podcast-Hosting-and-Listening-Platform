@extends('layouts.app')
@section('title', 'My Podcasts — PodWave Creator')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-white mb-1">My Podcasts</h1>
            <p class="text-muted mb-0">Manage your shows and episodes</p>
        </div>
        <a href="{{ route('creator.podcasts.create') }}" class="btn pw-btn-primary">
            <i class="bi bi-plus-lg me-2"></i>New Podcast
        </a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="d-flex gap-3 mb-4 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}"
            class="form-control pw-input" style="max-width:260px;" placeholder="Search podcasts…">
        <select name="status" class="form-select pw-input" style="max-width:160px;">
            <option value="">All Status</option>
            <option value="published" {{ request('status')=='published' ? 'selected':'' }}>Published</option>
            <option value="draft"     {{ request('status')=='draft'     ? 'selected':'' }}>Draft</option>
        </select>
        <button type="submit" class="btn pw-btn-primary btn-sm px-4">Filter</button>
        @if(request()->anyFilled(['search','status']))
            <a href="{{ route('creator.podcasts.index') }}" class="btn pw-btn-outline btn-sm px-4">Clear</a>
        @endif
    </form>

    @forelse($podcasts as $podcast)
    <div class="pw-card mb-3">
        <div class="d-flex gap-4 align-items-start">
            <img src="{{ $podcast->thumbnail_url }}" class="rounded-3 flex-shrink-0" width="90" height="90" style="object-fit:cover;" alt="">
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="pw-status-badge pw-status-{{ $podcast->status }}">{{ ucfirst($podcast->status) }}</span>
                            @if($podcast->is_featured)
                                <span class="pw-status-badge" style="background:rgba(245,158,11,0.15);color:#FCD34D;">
                                    <i class="bi bi-star-fill"></i> Featured
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('podcasts.show', $podcast->slug) }}" class="text-white fw-bold fs-5 text-decoration-none">
                            {{ $podcast->title }}
                        </a>
                        <p class="text-muted small mt-1 mb-0">{{ Str::limit($podcast->description, 120) }}</p>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
                        <a href="{{ route('creator.podcasts.episodes', $podcast) }}" class="btn pw-btn-outline btn-sm">
                            <i class="bi bi-collection-play-fill me-1"></i>Episodes
                        </a>
                        <a href="{{ route('creator.podcasts.stats', $podcast) }}" class="btn pw-btn-outline btn-sm">
                            <i class="bi bi-bar-chart-fill me-1"></i>Stats
                        </a>
                        <a href="{{ route('creator.podcasts.edit', $podcast) }}" class="btn pw-btn-outline btn-sm">
                            <i class="bi bi-pencil-fill me-1"></i>Edit
                        </a>
                        <form action="{{ route('creator.podcasts.destroy', $podcast) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Delete \'{{ $podcast->title }}\'? All episodes will also be deleted.')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="d-flex gap-4 mt-3 flex-wrap">
                    <span class="text-muted small"><i class="bi bi-collection-play-fill text-accent me-1"></i>{{ $podcast->episodes_count }} episodes</span>
                    <span class="text-muted small"><i class="bi bi-play-fill text-accent me-1"></i>{{ number_format($podcast->total_plays) }} plays</span>
                    @if($podcast->category)
                        <span class="text-muted small"><i class="bi bi-grid-fill text-accent me-1"></i>{{ $podcast->category->name }}</span>
                    @endif
                    <span class="text-muted small"><i class="bi bi-calendar3 text-accent me-1"></i>{{ $podcast->created_at->format('M j, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="pw-card text-center py-5">
        <i class="bi bi-mic fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No Podcasts Yet</h5>
        <p class="text-muted mb-4">Create your first podcast show and start uploading episodes.</p>
        <a href="{{ route('creator.podcasts.create') }}" class="btn pw-btn-primary px-5">
            <i class="bi bi-plus-lg me-2"></i>Create First Podcast
        </a>
    </div>
    @endforelse

    @if($podcasts->hasPages())
    <div class="mt-4">{{ $podcasts->links() }}</div>
    @endif
</div>
@endsection
