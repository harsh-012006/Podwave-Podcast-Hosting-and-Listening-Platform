@extends('layouts.app')
@section('title', 'Browse Podcasts — PodWave')

@section('content')
<div class="container py-5">

    <div class="mb-5">
        <h1 class="fw-bold text-white mb-1">Browse Podcasts</h1>
        <p class="text-muted mb-0">Explore {{ $podcasts->total() }} podcasts across all categories</p>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('browse') }}" class="row g-3 mb-5" id="filterForm">
        <div class="col-12 col-md-4">
            <div class="position-relative">
                <i class="bi bi-search position-absolute" style="left:14px;top:50%;transform:translateY(-50%);color:var(--pw-text-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control pw-input" style="padding-left:38px;"
                    placeholder="Search podcasts…">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <select name="category" class="form-select pw-input" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($genres->count())
        <div class="col-6 col-md-2">
            <select name="genre" class="form-select pw-input" onchange="this.form.submit()">
                <option value="">All Genres</option>
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                        {{ $genre->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-6 col-md-2">
            <select name="sort" class="form-select pw-input" onchange="this.form.submit()">
                <option value="trending" {{ request('sort','trending') == 'trending' ? 'selected':'' }}>🔥 Trending</option>
                <option value="latest"   {{ request('sort') == 'latest'   ? 'selected':'' }}>🆕 Latest</option>
                <option value="popular"  {{ request('sort') == 'popular'  ? 'selected':'' }}>📈 Most Played</option>
                <option value="rating"   {{ request('sort') == 'rating'   ? 'selected':'' }}>⭐ Top Rated</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn pw-btn-primary flex-fill">Search</button>
            @if(request()->anyFilled(['search','category','genre','sort']))
                <a href="{{ route('browse') }}" class="btn pw-btn-outline px-3" title="Clear filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- Active filters display --}}
    @if(request()->anyFilled(['search','category','genre']))
    <div class="d-flex gap-2 flex-wrap mb-4 align-items-center">
        <span class="text-muted small">Filtering by:</span>
        @if(request('search'))
            <span class="pw-tag">Search: "{{ request('search') }}" <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="ms-1 text-muted">&times;</a></span>
        @endif
        @if(request('category'))
            @php $cat = $categories->find(request('category')); @endphp
            @if($cat)
                <span class="pw-tag">{{ $cat->name }} <a href="{{ request()->fullUrlWithoutQuery(['category','genre']) }}" class="ms-1 text-muted">&times;</a></span>
            @endif
        @endif
    </div>
    @endif

    {{-- Results --}}
    @if($podcasts->count())
    <div class="row g-4">
        @foreach($podcasts as $podcast)
        <div class="col-6 col-md-4 col-lg-3">
            @include('components.podcast-card', ['podcast' => $podcast])
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($podcasts->hasPages())
    <div class="mt-5 d-flex justify-content-center">
        {{ $podcasts->withQueryString()->links() }}
    </div>
    @endif

    @else
    <div class="text-center py-5">
        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-white mb-2">No Podcasts Found</h5>
        <p class="text-muted mb-4">Try adjusting your search filters or browse all categories.</p>
        <a href="{{ route('browse') }}" class="btn pw-btn-primary px-5">Clear Filters</a>
    </div>
    @endif

</div>
@endsection
