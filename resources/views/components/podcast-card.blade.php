{{-- Podcast Card Component
     Usage: @include('components.podcast-card', ['podcast' => $podcast])
--}}
<div class="pw-podcast-card">
    <a href="{{ route('podcasts.show', $podcast->slug) }}" class="pw-card-link">
        <div class="pw-card-thumb-wrap">
            <img src="{{ $podcast->thumbnail_url }}" class="pw-card-thumb" alt="{{ $podcast->title }}" loading="lazy">
            <div class="pw-card-overlay">
                <div class="pw-card-play-btn">
                    <i class="bi bi-play-fill"></i>
                </div>
            </div>
            @if($podcast->is_explicit)
                <span class="pw-explicit-badge">E</span>
            @endif
            @if($podcast->is_featured)
                <span class="pw-featured-badge"><i class="bi bi-star-fill"></i></span>
            @endif
        </div>
    </a>
    <div class="pw-card-body">
        <div class="pw-card-category" style="color: {{ $podcast->category?->color ?? '#8B5CF6' }}">
            {{ $podcast->category?->name ?? 'Uncategorized' }}
        </div>
        <a href="{{ route('podcasts.show', $podcast->slug) }}" class="pw-card-title">
            {{ Str::limit($podcast->title, 38) }}
        </a>
        <a href="{{ route('creators.show', $podcast->creator->username ?? $podcast->creator->id) }}" class="pw-card-creator">
            <img src="{{ $podcast->creator->avatar_url }}" class="pw-card-creator-avatar" alt="">
            {{ $podcast->creator->name }}
        </a>
        <div class="pw-card-stats">
            <span><i class="bi bi-play-fill"></i> {{ $podcast->formatted_plays }}</span>
            <span><i class="bi bi-collection-play-fill"></i> {{ $podcast->episodes_count ?? $podcast->episode_count }} eps</span>
            @if($podcast->rating_average > 0)
                <span><i class="bi bi-star-fill text-warning"></i> {{ number_format($podcast->rating_average, 1) }}</span>
            @endif
        </div>
    </div>
</div>
