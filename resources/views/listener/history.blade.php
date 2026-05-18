@extends('layouts.app')
@section('title', 'Listening History — PodWave')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold text-white mb-1"><i class="bi bi-clock-history text-accent me-2"></i>Listening History</h1>
            <p class="text-muted mb-0">Episodes you've listened to recently</p>
        </div>
        @if($history->count())
        <form action="{{ route('listener.history.clear') }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"
                data-confirm="Clear all listening history?">
                <i class="bi bi-trash-fill me-1"></i>Clear History
            </button>
        </form>
        @endif
    </div>

    @if($history->count())
    <div class="pw-card p-0">
        @foreach($history as $item)
        <div class="d-flex align-items-center gap-3 p-3 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
            <img src="{{ $item->episode->thumbnail_url }}" class="rounded-2 flex-shrink-0" width="60" height="60" style="object-fit:cover;" alt="">
            <div class="flex-grow-1 min-w-0">
                <a href="{{ route('podcasts.show', $item->episode->podcast->slug) }}" class="text-muted small d-block text-decoration-none mb-1">
                    {{ $item->episode->podcast->title }}
                </a>
                <a href="{{ route('episodes.show', $item->episode->slug) }}" class="text-white fw-semibold d-block text-truncate text-decoration-none mb-2">
                    {{ $item->episode->title }}
                </a>
                <div style="background:rgba(255,255,255,0.08);border-radius:3px;height:4px;overflow:hidden;margin-bottom:6px;">
                    <div style="width:{{ $item->progress_percent }}%;background:var(--pw-accent);height:100%;border-radius:3px;"></div>
                </div>
                <div class="d-flex gap-3 align-items-center" style="font-size:.75rem;color:var(--pw-text-muted);">
                    <span>{{ $item->progress_percent }}% listened</span>
                    <span>{{ formatTime($item->progress_seconds) }} / {{ $item->episode->formatted_duration }}</span>
                    <span>{{ $item->listened_at->diffForHumans() }}</span>
                    @if($item->completed)
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Completed</span>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0">
                <button class="pw-play-btn-sm"
                    data-audio="{{ $item->episode->audio_url }}"
                    data-title="{{ $item->episode->title }}"
                    data-podcast="{{ $item->episode->podcast->title }}"
                    data-thumbnail="{{ $item->episode->thumbnail_url }}"
                    data-episode-id="{{ $item->episode->id }}"
                    title="{{ $item->completed ? 'Play again' : 'Resume' }}">
                    <i class="bi bi-play-fill"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $history->links() }}</div>
    @else
    <div class="pw-card text-center py-5">
        <i class="bi bi-clock-history fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No History Yet</h5>
        <p class="text-muted mb-4">Episodes you listen to will appear here.</p>
        <a href="{{ route('browse') }}" class="btn pw-btn-primary px-5">Start Listening</a>
    </div>
    @endif
</div>
@endsection

@php
function formatTime($s) {
    $m = intdiv($s, 60);
    $sec = $s % 60;
    return "{$m}:" . str_pad($sec, 2, '0', STR_PAD_LEFT);
}
@endphp
