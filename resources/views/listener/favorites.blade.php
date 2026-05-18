{{-- resources/views/listener/favorites.blade.php --}}
@extends('layouts.app')
@section('title', 'My Favorites — PodWave')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold text-white mb-1"><i class="bi bi-heart-fill text-accent me-2"></i>My Favorites</h1>
            <p class="text-muted mb-0">Podcasts you've saved for later</p>
        </div>
        <a href="{{ route('browse') }}" class="btn pw-btn-outline">
            <i class="bi bi-compass-fill me-2"></i>Discover More
        </a>
    </div>

    @if($favorites->count())
    <div class="row g-4">
        @foreach($favorites as $podcast)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="position-relative">
                @include('components.podcast-card', ['podcast' => $podcast])
                <form action="{{ route('listener.favorite', $podcast) }}" method="POST" style="position:absolute;top:10px;right:10px;z-index:10;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:rgba(0,0,0,0.7);border:none;color:#EF4444;width:32px;height:32px;border-radius:50%;padding:0;" title="Remove from favorites">
                        <i class="bi bi-heart-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $favorites->links() }}</div>
    @else
    <div class="pw-card text-center py-5">
        <i class="bi bi-heart fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No Favorites Yet</h5>
        <p class="text-muted mb-4">Save podcasts you love to find them easily later.</p>
        <a href="{{ route('browse') }}" class="btn pw-btn-primary px-5">Browse Podcasts</a>
    </div>
    @endif
</div>
@endsection
