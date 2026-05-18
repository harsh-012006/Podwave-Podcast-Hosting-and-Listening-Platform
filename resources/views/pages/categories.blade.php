@extends('layouts.app')
@section('title', 'All Categories — PodWave')

@section('content')
<div class="container py-5">
    <div class="mb-5 text-center">
        <h1 class="fw-black text-white mb-2">Browse Categories</h1>
        <p class="text-muted">Find your next obsession — {{ $categories->count() }} categories to explore</p>
    </div>

    <div class="row g-4">
        @foreach($categories as $category)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ route('browse') }}?category={{ $category->id }}" class="pw-cat-big-card" style="--cat-color: {{ $category->color }}">
                <div class="pw-cat-big-icon">
                    <i class="bi {{ $category->icon }}"></i>
                </div>
                <div class="pw-cat-big-info">
                    <h5 class="text-white fw-bold mb-1">{{ $category->name }}</h5>
                    <div class="text-muted small">{{ number_format($category->podcast_count) }} shows</div>
                    @if($category->description)
                        <p class="text-muted small mt-2 mb-0">{{ Str::limit($category->description, 80) }}</p>
                    @endif
                </div>
                <i class="bi bi-arrow-right-circle-fill pw-cat-big-arrow"></i>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('styles')
<style>
.pw-cat-big-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: var(--pw-surface-2);
    border: 1px solid var(--pw-border);
    border-radius: var(--pw-radius-lg);
    padding: 24px;
    text-decoration: none;
    transition: var(--pw-transition);
    position: relative;
    overflow: hidden;
}
.pw-cat-big-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--cat-color, var(--pw-accent));
}
.pw-cat-big-card:hover {
    border-color: rgba(255,255,255,0.15);
    background: var(--pw-surface-3);
    transform: translateX(6px);
}
.pw-cat-big-icon {
    width: 60px; height: 60px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    background: color-mix(in srgb, var(--cat-color, #8B5CF6) 15%, transparent);
    color: var(--cat-color, var(--pw-accent));
    flex-shrink: 0;
}
.pw-cat-big-info { flex: 1; }
.pw-cat-big-arrow {
    color: var(--cat-color, var(--pw-accent));
    font-size: 1.5rem;
    opacity: 0;
    transition: opacity 0.2s;
    flex-shrink: 0;
}
.pw-cat-big-card:hover .pw-cat-big-arrow { opacity: 1; }
</style>
@endpush
