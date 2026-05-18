@extends('layouts.admin')
@section('title', 'Manage Podcasts — PodWave Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="pw-admin-page-title">Podcasts</h1>
        <p class="pw-admin-page-sub">{{ $podcasts->total() }} podcasts on the platform</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="pw-filter-bar mb-4">
    <input type="text" name="search" value="{{ request('search') }}"
        class="form-control pw-filter-input" placeholder="Search by title…">
    <select name="status" class="form-select pw-filter-select" style="width:140px;">
        <option value="">All Status</option>
        <option value="published"  {{ request('status')=='published'  ?'selected':'' }}>Published</option>
        <option value="draft"      {{ request('status')=='draft'      ?'selected':'' }}>Draft</option>
        <option value="suspended"  {{ request('status')=='suspended'  ?'selected':'' }}>Suspended</option>
    </select>
    <select name="category" class="form-select pw-filter-select" style="width:160px;">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn pw-btn-primary btn-sm px-4">Filter</button>
    @if(request()->hasAny(['search','status','category']))
        <a href="{{ route('admin.podcasts') }}" class="btn pw-btn-outline btn-sm px-4">Clear</a>
    @endif
</form>

<div class="pw-card p-0">
    <div class="table-responsive">
        <table class="pw-table">
            <thead>
                <tr>
                    <th>Podcast</th>
                    <th>Creator</th>
                    <th>Category</th>
                    <th>Episodes</th>
                    <th>Plays</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($podcasts as $podcast)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $podcast->thumbnail_url }}" class="rounded-2" width="46" height="46" style="object-fit:cover;" alt="">
                            <div>
                                <a href="{{ route('podcasts.show', $podcast->slug) }}" class="text-white fw-semibold small text-decoration-none d-block" target="_blank">
                                    {{ Str::limit($podcast->title, 36) }}
                                </a>
                                <div class="text-muted" style="font-size:.72rem;">{{ $podcast->created_at->format('M j, Y') }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $podcast->creator) }}" class="text-muted small text-decoration-none d-flex align-items-center gap-2">
                            <img src="{{ $podcast->creator->avatar_url }}" class="rounded-circle" width="24" height="24" alt="">
                            {{ $podcast->creator->name }}
                        </a>
                    </td>
                    <td><span class="text-muted small">{{ $podcast->category?->name ?? '—' }}</span></td>
                    <td><span class="text-white">{{ $podcast->episodes_count }}</span></td>
                    <td><span class="text-white">{{ number_format($podcast->total_plays) }}</span></td>
                    <td>
                        <span class="pw-status-badge pw-status-{{ $podcast->status }}">{{ ucfirst($podcast->status) }}</span>
                    </td>
                    <td>
                        <form action="{{ route('admin.podcasts.feature', $podcast) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="pw-action-btn {{ $podcast->is_featured ? 'pw-action-btn-warning' : 'pw-action-btn-primary' }} border-0">
                                <i class="bi bi-star{{ $podcast->is_featured ? '-fill' : '' }}"></i>
                                {{ $podcast->is_featured ? 'Featured' : 'Feature' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('podcasts.show', $podcast->slug) }}" class="pw-action-btn pw-action-btn-primary" target="_blank">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @if($podcast->status !== 'suspended')
                            <form action="{{ route('admin.podcasts.suspend', $podcast) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="pw-action-btn pw-action-btn-warning border-0">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.podcasts.destroy', $podcast) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="pw-action-btn pw-action-btn-danger border-0"
                                    data-confirm="Delete '{{ $podcast->title }}'? This cannot be undone.">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="pw-empty-state">
                            <i class="bi bi-collection-play"></i>
                            <p>No podcasts found.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($podcasts->hasPages())
<div class="mt-4">{{ $podcasts->withQueryString()->links() }}</div>
@endif
@endsection
