@extends('layouts.app')
@section('title', 'Stats: ' . $podcast->title)

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('creator.podcasts.index') }}" class="pw-link-more">
            <i class="bi bi-chevron-left me-1"></i>Back to Podcasts
        </a>
        <h1 class="fw-bold text-white mt-2 mb-1">{{ $podcast->title }}</h1>
        <p class="text-muted">Podcast Analytics</p>
    </div>

    {{-- Top Stats --}}
    <div class="row g-4 mb-5">
        @php $statCards = [
            ['val' => number_format($totalPlays),       'label' => 'Total Plays',    'icon' => 'bi-play-fill',    'color' => '#8B5CF6'],
            ['val' => number_format($totalLikes),       'label' => 'Total Likes',    'icon' => 'bi-heart-fill',   'color' => '#EF4444'],
            ['val' => number_format($totalComments),    'label' => 'Comments',       'icon' => 'bi-chat-fill',    'color' => '#3B82F6'],
            ['val' => number_format($subscribers),      'label' => 'Subscribers',    'icon' => 'bi-people-fill',  'color' => '#10B981'],
        ]; @endphp
        @foreach($statCards as $card)
        <div class="col-6 col-md-3">
            <div class="pw-stat-card" style="--stat-color: {{ $card['color'] }}">
                <div class="pw-stat-card-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                <div class="pw-stat-card-val">{{ $card['val'] }}</div>
                <div class="pw-stat-card-label">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Monthly Chart --}}
        <div class="col-lg-8">
            <div class="pw-card">
                <h5 class="text-white fw-semibold mb-4">Plays Over Time (Last 6 Months)</h5>
                <canvas id="playsChart" height="120"></canvas>
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="col-lg-4">
            <div class="pw-card">
                <h5 class="text-white fw-semibold mb-4">Podcast Info</h5>
                <div class="d-flex gap-3 mb-3">
                    <img src="{{ $podcast->thumbnail_url }}" class="rounded-3" width="70" height="70" style="object-fit:cover;" alt="">
                    <div>
                        <div class="text-white fw-semibold">{{ $podcast->title }}</div>
                        <span class="pw-status-badge pw-status-{{ $podcast->status }} mt-1 d-inline-block">{{ ucfirst($podcast->status) }}</span>
                    </div>
                </div>
                <div class="border-top border-secondary pt-3">
                    @foreach([
                        ['label'=>'Category',        'value'=> $podcast->category?->name ?? '—'],
                        ['label'=>'Language',         'value'=> $podcast->language],
                        ['label'=>'Rating',           'value'=> $podcast->rating_average > 0 ? number_format($podcast->rating_average,1).'/5 ('.$podcast->rating_count.' ratings)' : 'No ratings yet'],
                        ['label'=>'Total Episodes',   'value'=> $podcast->episode_count],
                        ['label'=>'Created',          'value'=> $podcast->created_at->format('M j, Y')],
                    ] as $info)
                    <div class="d-flex justify-content-between py-2 border-bottom border-secondary">
                        <span class="text-muted small">{{ $info['label'] }}</span>
                        <span class="text-white small">{{ $info['value'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 d-grid gap-2">
                    <a href="{{ route('creator.podcasts.episodes', $podcast) }}" class="btn pw-btn-outline btn-sm">Manage Episodes</a>
                    <a href="{{ route('creator.podcasts.edit', $podcast) }}" class="btn pw-btn-primary btn-sm">Edit Podcast</a>
                </div>
            </div>
        </div>

        {{-- Episode Performance Table --}}
        <div class="col-12">
            <div class="pw-card">
                <h5 class="text-white fw-semibold mb-4">Episode Performance</h5>
                <div class="table-responsive">
                    <table class="pw-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Episode</th>
                                <th>Published</th>
                                <th>Duration</th>
                                <th>Plays</th>
                                <th>Likes</th>
                                <th>Comments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($episodes as $ep)
                            <tr>
                                <td><span class="text-muted small">{{ $ep->episode_number ?? '—' }}</span></td>
                                <td>
                                    <a href="{{ route('episodes.show', $ep->slug) }}" class="text-white small fw-semibold text-decoration-none">
                                        {{ Str::limit($ep->title, 45) }}
                                    </a>
                                </td>
                                <td><span class="text-muted small">{{ $ep->release_date?->format('M j, Y') ?? '—' }}</span></td>
                                <td><span class="text-muted small">{{ $ep->formatted_duration }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:60px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden;">
                                            @php $maxPlays = $episodes->max('play_count') ?: 1; @endphp
                                            <div style="width:{{ ($ep->play_count/$maxPlays)*100 }}%;background:var(--pw-accent);height:100%;border-radius:2px;"></div>
                                        </div>
                                        <span class="text-white small">{{ number_format($ep->play_count) }}</span>
                                    </div>
                                </td>
                                <td><span class="text-white small">{{ $ep->likes()->count() }}</span></td>
                                <td><span class="text-white small">{{ $ep->allComments()->count() }}</span></td>
                                <td><span class="pw-status-badge pw-status-{{ $ep->status }}">{{ ucfirst($ep->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('playsChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($monthlyData, 'month')) !!},
        datasets: [{
            label: 'Plays',
            data: {!! json_encode(array_column($monthlyData, 'plays')) !!},
            borderColor: '#8B5CF6',
            backgroundColor: 'rgba(139,92,246,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#8B5CF6',
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#9CA3AF' } } },
        scales: {
            x: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
        }
    }
});
</script>
@endpush
