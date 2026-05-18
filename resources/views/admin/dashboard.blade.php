@extends('layouts.admin')
@section('title', 'Admin Dashboard — PodWave')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Platform overview for PodWave</p>
    </div>
    <span class="text-muted small"><i class="bi bi-clock"></i> {{ now()->format('l, M j Y') }}</span>
</div>

{{-- Stats Grid --}}
<div class="row g-4 mb-5">
    @php
    $statCards = [
        ['label'=>'Total Users',     'value'=>number_format($stats['total_users']),    'icon'=>'bi-people-fill',         'color'=>'#6366F1', 'sub'=>$stats['banned_users'].' banned'],
        ['label'=>'Creators',        'value'=>number_format($stats['total_creators']), 'icon'=>'bi-mic-fill',             'color'=>'#8B5CF6', 'sub'=>'Active podcast creators'],
        ['label'=>'Podcasts',        'value'=>number_format($stats['total_podcasts']), 'icon'=>'bi-collection-play-fill', 'color'=>'#10B981', 'sub'=>$stats['published'].' published'],
        ['label'=>'Episodes',        'value'=>number_format($stats['total_episodes']), 'icon'=>'bi-play-circle-fill',     'color'=>'#3B82F6', 'sub'=>'Total episodes uploaded'],
        ['label'=>'Total Plays',     'value'=>number_format($stats['total_plays']),    'icon'=>'bi-headphones',           'color'=>'#F59E0B', 'sub'=>'All-time plays'],
        ['label'=>'Comments',        'value'=>number_format($stats['total_comments']), 'icon'=>'bi-chat-fill',            'color'=>'#EC4899', 'sub'=>$stats['flagged'].' flagged'],
        ['label'=>'Unread Messages', 'value'=>number_format($stats['messages']),       'icon'=>'bi-envelope-fill',        'color'=>'#EF4444', 'sub'=>'Contact form messages'],
        ['label'=>'Listeners',       'value'=>number_format($stats['total_listeners']),'icon'=>'bi-person-fill',          'color'=>'#06B6D4', 'sub'=>'Registered listeners'],
    ];
    @endphp

    @foreach($statCards as $card)
    <div class="col-6 col-md-4 col-xl-3">
        <div class="pw-admin-stat-card" style="--stat-color: {{ $card['color'] }}">
            <div class="pw-admin-stat-icon">
                <i class="bi {{ $card['icon'] }}"></i>
            </div>
            <div class="pw-admin-stat-body">
                <div class="pw-admin-stat-value">{{ $card['value'] }}</div>
                <div class="pw-admin-stat-label">{{ $card['label'] }}</div>
                <div class="pw-admin-stat-sub">{{ $card['sub'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    {{-- User Growth Chart --}}
    <div class="col-lg-8">
        <div class="pw-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-semibold mb-0">User Growth (Last 6 Months)</h5>
            </div>
            <canvas id="userGrowthChart" height="100"></canvas>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="col-lg-4">
        <div class="pw-card h-100">
            <h5 class="text-white fw-semibold mb-4">Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('admin.users') }}" class="btn pw-btn-outline text-start">
                    <i class="bi bi-people-fill me-2"></i>Manage Users
                    @if($stats['banned_users']) <span class="badge bg-danger ms-auto">{{ $stats['banned_users'] }} banned</span> @endif
                </a>
                <a href="{{ route('admin.podcasts') }}" class="btn pw-btn-outline text-start">
                    <i class="bi bi-collection-play-fill me-2"></i>Manage Podcasts
                </a>
                <a href="{{ route('admin.comments') }}?flagged=1" class="btn pw-btn-outline text-start">
                    <i class="bi bi-flag-fill me-2"></i>Flagged Comments
                    @if($stats['flagged']) <span class="badge bg-danger ms-auto">{{ $stats['flagged'] }}</span> @endif
                </a>
                <a href="{{ route('admin.messages') }}" class="btn pw-btn-outline text-start">
                    <i class="bi bi-envelope-fill me-2"></i>Contact Messages
                    @if($stats['messages']) <span class="badge bg-warning text-dark ms-auto">{{ $stats['messages'] }}</span> @endif
                </a>
                <a href="{{ route('admin.categories') }}" class="btn pw-btn-outline text-start">
                    <i class="bi bi-grid-fill me-2"></i>Categories & Genres
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Users --}}
    <div class="col-lg-6">
        <div class="pw-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-semibold mb-0">Recent Users</h5>
                <a href="{{ route('admin.users') }}" class="pw-link-more">View All</a>
            </div>
            <div class="table-responsive">
                <table class="pw-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatar_url }}" class="rounded-circle" width="32" height="32" alt="">
                                    <div>
                                        <div class="text-white small fw-semibold">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="pw-badge-role pw-role-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                            <td><span class="text-muted small">{{ $user->created_at->diffForHumans() }}</span></td>
                            <td>
                                @if($user->is_banned)
                                    <span class="badge bg-danger">Banned</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Podcasts --}}
    <div class="col-lg-6">
        <div class="pw-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-semibold mb-0">Top Podcasts</h5>
                <a href="{{ route('admin.podcasts') }}" class="pw-link-more">View All</a>
            </div>
            @foreach($topPodcasts as $i => $podcast)
            <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom border-secondary' : '' }}">
                <span class="text-muted fw-bold" style="width:20px;">{{ $i+1 }}</span>
                <img src="{{ $podcast->thumbnail_url }}" class="rounded" width="44" height="44" alt="">
                <div class="flex-grow-1 min-w-0">
                    <a href="{{ route('podcasts.show', $podcast->slug) }}" class="text-white small fw-semibold text-decoration-none d-block text-truncate">
                        {{ $podcast->title }}
                    </a>
                    <span class="text-muted" style="font-size:.75rem;">{{ $podcast->creator->name }}</span>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="text-white small">{{ number_format($podcast->total_plays) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">plays</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($userGrowth, 'month')) !!},
        datasets: [{
            label: 'New Users',
            data: {!! json_encode(array_column($userGrowth, 'count')) !!},
            backgroundColor: 'rgba(139, 92, 246, 0.7)',
            borderColor: '#8B5CF6',
            borderWidth: 1,
            borderRadius: 6,
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
