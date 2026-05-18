@extends('layouts.app')
@section('title', $creator->name . ' — PodWave Creator')

@section('content')

{{-- Creator Hero --}}
<div class="pw-detail-hero">
    <div class="pw-detail-hero-blur" style="background: url('{{ $creator->avatar_url }}') center/cover; filter:blur(60px) brightness(0.12);position:absolute;inset:0;"></div>
    <div class="container pw-detail-hero-content py-5">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-end gap-4">
            <img src="{{ $creator->avatar_url }}" class="rounded-circle"
                 style="width:120px;height:120px;object-fit:cover;border:4px solid var(--pw-accent);flex-shrink:0;"
                 alt="{{ $creator->name }}">
            <div class="flex-grow-1">
                <div class="text-muted small mb-1"><i class="bi bi-mic-fill text-accent me-1"></i>Podcast Creator</div>
                <h1 class="fw-black text-white mb-2" style="font-size:2.5rem;letter-spacing:-1px;">{{ $creator->name }}</h1>
                @if($creator->bio)
                    <p class="text-muted mb-3" style="max-width:600px;">{{ $creator->bio }}</p>
                @endif
                <div class="pw-detail-stats mb-4">
                    <span><i class="bi bi-collection-play-fill"></i> {{ $podcasts->total() }} podcasts</span>
                    <span><i class="bi bi-play-fill"></i> {{ number_format($totalPlays) }} plays</span>
                    <span><i class="bi bi-people-fill"></i> {{ number_format($creator->subscriber_count) }} subscribers</span>
                    <span><i class="bi bi-mic-fill"></i> {{ number_format($episodeCount) }} episodes</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @auth
                        @if(auth()->id() !== $creator->id)
                        <button class="btn pw-btn-primary" id="subscribeBtn"
                            data-creator-id="{{ $creator->id }}"
                            data-subscribed="{{ $isSubscribed ? 'true':'false' }}">
                            <i class="bi {{ $isSubscribed ? 'bi-bell-fill':'bi-bell' }}"></i>
                            <span id="subscribeBtnText">{{ $isSubscribed ? 'Subscribed':'Subscribe' }}</span>
                            <span class="ms-1" id="subCount">({{ number_format($creator->subscriber_count) }})</span>
                        </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn pw-btn-primary">
                            <i class="bi bi-bell me-2"></i>Subscribe
                        </a>
                    @endauth

                    @if($creator->website)
                        <a href="{{ $creator->website }}" target="_blank" rel="noopener" class="btn pw-btn-outline">
                            <i class="bi bi-globe me-1"></i>Website
                        </a>
                    @endif
                    @if($creator->twitter)
                        <a href="https://twitter.com/{{ $creator->twitter }}" target="_blank" class="btn pw-btn-ghost">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                    @endif
                    @if($creator->instagram)
                        <a href="https://instagram.com/{{ $creator->instagram }}" target="_blank" class="btn pw-btn-ghost">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Podcasts Grid --}}
<div class="container py-5">
    <h2 class="fw-bold text-white mb-4">Podcasts by {{ $creator->name }}</h2>

    @if($podcasts->count())
    <div class="row g-4">
        @foreach($podcasts as $podcast)
        <div class="col-6 col-md-4 col-lg-3">
            @include('components.podcast-card', ['podcast' => $podcast])
        </div>
        @endforeach
    </div>

    @if($podcasts->hasPages())
    <div class="mt-5">{{ $podcasts->links() }}</div>
    @endif

    @else
    <div class="pw-card text-center py-5">
        <i class="bi bi-mic fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No Podcasts Yet</h5>
        <p class="text-muted mb-0">{{ $creator->name }} hasn't published any podcasts yet. Check back soon!</p>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
const subscribeBtn = document.getElementById('subscribeBtn');
if (subscribeBtn) {
    subscribeBtn.addEventListener('click', async function() {
        const creatorId = this.dataset.creatorId;
        const res = await fetch(`/listener/subscribe/${creatorId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const data = await res.json();
        this.dataset.subscribed = data.subscribed ? 'true' : 'false';
        this.querySelector('i').className = data.subscribed ? 'bi bi-bell-fill' : 'bi bi-bell';
        document.getElementById('subscribeBtnText').textContent = data.subscribed ? 'Subscribed' : 'Subscribe';
        document.getElementById('subCount').textContent = `(${data.count.toLocaleString()})`;
        showToast(data.subscribed ? 'Subscribed!' : 'Unsubscribed.');
    });
}
</script>
@endpush
