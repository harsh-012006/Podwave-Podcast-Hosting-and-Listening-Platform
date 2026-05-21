@extends('layouts.app')

@section('title', $episode->title . ' — ' . $episode->podcast->title)

@section('content')
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">

            {{-- Breadcrumb --}}
            <nav class="mb-4" aria-label="breadcrumb">
                <ol class="breadcrumb pw-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('podcasts.show', $episode->podcast->slug) }}">{{ $episode->podcast->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ Str::limit($episode->title, 40) }}</li>
                </ol>
            </nav>

            {{-- Episode Header --}}
            <div class="pw-card mb-4">
                <div class="d-flex gap-4">
                    <img src="{{ $episode->thumbnail_url }}" class="pw-ep-detail-thumb rounded-3 flex-shrink-0" alt="">
                    <div class="flex-grow-1">
                        <div class="d-flex gap-2 mb-2 flex-wrap">
                            <span class="pw-badge-type">{{ $episode->episode_label }}</span>
                            @if($episode->episode_type !== 'full')
                                <span class="pw-badge-type">{{ ucfirst($episode->episode_type) }}</span>
                            @endif
                            @if($episode->is_explicit)
                                <span class="pw-explicit-badge">E</span>
                            @endif
                        </div>
                        <h1 class="pw-ep-detail-title">{{ $episode->title }}</h1>
                        <a href="{{ route('podcasts.show', $episode->podcast->slug) }}" class="pw-ep-podcast-link mb-3 d-block">
                            <i class="bi bi-collection-play-fill me-1"></i>{{ $episode->podcast->title }}
                        </a>
                        <div class="pw-detail-stats mb-3">
                            <span><i class="bi bi-clock"></i> {{ $episode->formatted_duration }}</span>
                            <span><i class="bi bi-play-fill"></i> {{ $episode->formatted_plays }} plays</span>
                            <span><i class="bi bi-chat-fill"></i> {{ $episode->comment_count }} comments</span>
                            <span><i class="bi bi-calendar3"></i> {{ $episode->release_date?->format('M j, Y') }}</span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @auth
                                <button class="btn pw-btn-outline btn-sm" id="epLikeBtn"
                                    data-type="episode" data-id="{{ $episode->id }}"
                                    data-liked="{{ $isLiked ? 'true' : 'false' }}">
                                    <i class="bi {{ $isLiked ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                                    <span id="epLikeCount">{{ $episode->like_count }}</span>
                                </button>
                            @endauth
                            <button class="btn pw-btn-ghost btn-sm"
                                onclick="navigator.clipboard.writeText(window.location.href); showToast('Link copied!')">
                                <i class="bi bi-share-fill me-1"></i>Share
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Video Player --}}
            <div class="pw-video-player-container mb-4" id="inlinePlayerCard">
                <video id="inlineAudio" preload="metadata" controls
                       src="{{ $episode->audio_url }}"
                       data-episode-id="{{ $episode->id }}"
                       style="width: 100%; height: auto; border-radius: 12px; background: #000;">
                </video>
            </div>

            {{-- Description --}}
            <div class="pw-card mb-4">
                <h5 class="text-white fw-semibold mb-3">Episode Description</h5>
                <p class="text-muted" style="white-space: pre-wrap;">{{ $episode->description }}</p>
                @if($episode->show_notes)
                <hr class="border-secondary">
                <h6 class="text-white">Show Notes</h6>
                <p class="text-muted small">{{ $episode->show_notes }}</p>
                @endif
            </div>

            {{-- Previous / Next --}}
            <div class="d-flex gap-3 mb-4">
                @if($prevEpisode)
                <a href="{{ route('episodes.show', $prevEpisode->slug) }}" class="btn pw-btn-outline flex-fill text-start">
                    <div class="text-muted small mb-1"><i class="bi bi-chevron-left"></i> Previous</div>
                    <div class="text-white small">{{ Str::limit($prevEpisode->title, 40) }}</div>
                </a>
                @endif
                @if($nextEpisode)
                <a href="{{ route('episodes.show', $nextEpisode->slug) }}" class="btn pw-btn-outline flex-fill text-end">
                    <div class="text-muted small mb-1">Next <i class="bi bi-chevron-right"></i></div>
                    <div class="text-white small">{{ Str::limit($nextEpisode->title, 40) }}</div>
                </a>
                @endif
            </div>

            {{-- Comments Section --}}
            <div class="pw-card" id="comments">
                <h5 class="text-white fw-semibold mb-4">
                    <i class="bi bi-chat-fill text-accent me-2"></i>
                    Comments <span class="text-muted fw-normal">({{ $episode->comment_count }})</span>
                </h5>

                @auth
                <form action="{{ route('listener.comment.store', $episode->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="d-flex gap-3">
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36" alt="">
                        <div class="flex-grow-1">
                            <textarea name="body" rows="3" class="form-control pw-input mb-2"
                                placeholder="Share your thoughts…" required minlength="2" maxlength="1000"></textarea>
                            <button type="submit" class="btn pw-btn-primary btn-sm">Post Comment</button>
                        </div>
                    </div>
                </form>
                <hr class="border-secondary">
                @endauth

                {{-- Comment List --}}
                @forelse($episode->comments as $comment)
                <div class="pw-comment" id="comment-{{ $comment->id }}">
                    <img src="{{ $comment->user->avatar_url }}" class="pw-comment-avatar rounded-circle" alt="">
                    <div class="pw-comment-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="text-white fw-semibold small">{{ $comment->user->name }}</span>
                            <span class="text-muted" style="font-size:0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted mb-2">{{ $comment->body }}</p>
                        <div class="d-flex gap-3">
                            @auth
                            <button class="pw-comment-action" onclick="toggleReplyForm({{ $comment->id }})">
                                <i class="bi bi-reply-fill"></i> Reply
                            </button>
                            @if(auth()->id() === $comment->user_id || auth()->user()->isAdmin())
                            <form action="{{ route('listener.comment.destroy', $comment->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="pw-comment-action text-danger" onclick="return confirm('Delete this comment?')">
                                    <i class="bi bi-trash-fill"></i> Delete
                                </button>
                            </form>
                            @endif
                            @endauth
                        </div>

                        {{-- Reply form --}}
                        @auth
                        <div id="replyForm-{{ $comment->id }}" style="display:none;" class="mt-3">
                            <form action="{{ route('listener.comment.store', $episode->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <div class="d-flex gap-2">
                                    <textarea name="body" rows="2" class="form-control pw-input" placeholder="Write a reply…" required></textarea>
                                    <button type="submit" class="btn pw-btn-primary btn-sm align-self-start">Reply</button>
                                </div>
                            </form>
                        </div>
                        @endauth

                        {{-- Replies --}}
                        @foreach($comment->replies as $reply)
                        <div class="pw-comment pw-comment-reply mt-3">
                            <img src="{{ $reply->user->avatar_url }}" class="pw-comment-avatar rounded-circle" alt="">
                            <div class="pw-comment-body">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="text-white fw-semibold small">{{ $reply->user->name }}</span>
                                    <span class="text-muted" style="font-size:0.75rem;">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-muted mb-0">{{ $reply->body }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="bi bi-chat-dots fs-2 text-muted"></i>
                    <p class="text-muted mt-2">No comments yet. Be the first!</p>
                </div>
                @endforelse
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="pw-card mb-4">
                <h6 class="text-white fw-semibold mb-3">From this Podcast</h6>
                <a href="{{ route('podcasts.show', $episode->podcast->slug) }}" class="d-flex gap-3 mb-3 text-decoration-none">
                    <img src="{{ $episode->podcast->thumbnail_url }}" class="rounded" width="56" height="56" alt="">
                    <div>
                        <div class="text-white small fw-semibold">{{ $episode->podcast->title }}</div>
                        <div class="text-muted" style="font-size:.8rem;">{{ $episode->podcast->creator->name }}</div>
                        <div class="text-muted" style="font-size:.8rem;">
                            {{ $episode->podcast->episode_count }} episodes
                        </div>
                    </div>
                </a>
                <a href="{{ route('podcasts.show', $episode->podcast->slug) }}" class="btn pw-btn-outline btn-sm w-100">
                    View All Episodes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Inline player logic
const audio = document.getElementById('inlineAudio');
const playBtn = document.getElementById('inlinePlay');
const playIcon = document.getElementById('inlinePlayIcon');
const seekBar = document.getElementById('inlineSeek');
const currentTime = document.getElementById('inlineCurrent');
const durationEl = document.getElementById('inlineDuration');
const episodeId = audio.dataset.episodeId;
let saveTimer;

function formatTime(s) {
    const m = Math.floor(s / 60), sec = Math.floor(s % 60);
    return `${m}:${sec.toString().padStart(2, '0')}`;
}

audio.addEventListener('loadedmetadata', () => {
    seekBar.max = Math.floor(audio.duration);
    durationEl.textContent = formatTime(audio.duration);
    @if($progress > 0)
    audio.currentTime = {{ $progress }};
    @endif
});

audio.addEventListener('timeupdate', () => {
    seekBar.value = Math.floor(audio.currentTime);
    currentTime.textContent = formatTime(audio.currentTime);
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveProgress(Math.floor(audio.currentTime)), 5000);
});

playBtn.addEventListener('click', () => {
    if (audio.paused) { audio.play(); playIcon.className = 'bi bi-pause-circle-fill'; }
    else { audio.pause(); playIcon.className = 'bi bi-play-circle-fill'; }
});

seekBar.addEventListener('input', () => { audio.currentTime = seekBar.value; });
document.getElementById('inlineBack').addEventListener('click', () => { audio.currentTime = Math.max(0, audio.currentTime - 15); });
document.getElementById('inlineFwd').addEventListener('click', () => { audio.currentTime = Math.min(audio.duration, audio.currentTime + 30); });
document.getElementById('inlineVolume').addEventListener('input', e => { audio.volume = e.target.value; });
document.getElementById('inlineSpeed').addEventListener('change', e => { audio.playbackRate = parseFloat(e.target.value); });

// Resume button
const resumeBtn = document.getElementById('resumeBtn');
if (resumeBtn) {
    resumeBtn.addEventListener('click', () => {
        audio.currentTime = parseInt(resumeBtn.dataset.seconds);
        audio.play();
        playIcon.className = 'bi bi-pause-circle-fill';
        resumeBtn.closest('.pw-resume-bar').remove();
    });
}

// Save progress to server
function saveProgress(seconds) {
    @auth
    fetch('/listener/progress', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ episode_id: episodeId, progress_seconds: seconds })
    });
    @endauth
}

// Like button
const likeBtn = document.getElementById('epLikeBtn');
if (likeBtn) {
    likeBtn.addEventListener('click', function() {
        fetch('/listener/like', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json', 'Accept': 'application/json'
            },
            body: JSON.stringify({ likeable_type: 'episode', likeable_id: this.dataset.id })
        })
        .then(r => r.json())
        .then(data => {
            this.querySelector('i').className = data.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
            document.getElementById('epLikeCount').textContent = data.count;
        });
    });
}

function toggleReplyForm(id) {
    const form = document.getElementById(`replyForm-${id}`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
