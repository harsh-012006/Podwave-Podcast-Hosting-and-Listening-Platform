{{-- Featured Podcast Card Component --}}
<div class="pw-featured-card">
    <div class="pw-featured-img-wrap">
        <img src="{{ $podcast->thumbnail_url }}" class="pw-featured-img" alt="{{ $podcast->title }}" loading="lazy">
        <div class="pw-featured-gradient"></div>
        <div class="pw-featured-badges">
            @if($podcast->is_explicit)
                <span class="pw-explicit-badge">E</span>
            @endif
            <span class="pw-featured-tag"><i class="bi bi-star-fill"></i> Featured</span>
        </div>
    </div>
    <div class="pw-featured-body">
        <div class="pw-card-category" style="color: {{ $podcast->category?->color ?? '#8B5CF6' }}">
            <i class="bi {{ $podcast->category?->icon ?? 'bi-mic-fill' }}"></i>
            {{ $podcast->category?->name ?? 'Podcast' }}
        </div>
        <a href="{{ route('podcasts.show', $podcast->slug) }}" class="pw-featured-title">
            {{ $podcast->title }}
        </a>
        <p class="pw-featured-desc">{{ Str::limit($podcast->description, 90) }}</p>
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}" class="pw-card-creator">
                <img src="{{ $podcast->creator->avatar_url }}" class="pw-card-creator-avatar" alt="">
                {{ $podcast->creator->name }}
            </a>
            <a href="{{ route('podcasts.show', $podcast->slug) }}" class="btn pw-btn-primary btn-sm">
                <i class="bi bi-play-fill me-1"></i> Listen
            </a>
        </div>
    </div>
</div>
