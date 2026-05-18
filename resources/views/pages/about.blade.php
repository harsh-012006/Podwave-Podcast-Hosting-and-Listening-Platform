@extends('layouts.app')
@section('title', 'About PodWave — Podcast Hosting & Listening Platform')

@section('content')

{{-- Hero --}}
<section class="pw-section" style="background: linear-gradient(135deg, rgba(139,92,246,0.1), transparent);">
    <div class="container text-center" style="max-width:700px;">
        <div class="pw-hero-badge mb-4">
            <i class="bi bi-soundwave"></i> About PodWave
        </div>
        <h1 class="fw-black text-white mb-3" style="font-size:3rem;letter-spacing:-2px;">
            The Home of Great <span class="text-accent">Podcasting</span>
        </h1>
        <p class="text-muted fs-5 mb-5" style="line-height:1.7;">
            PodWave is built for storytellers, educators, comedians, and every voice in between.
            We give creators the tools to grow — and listeners the platform to discover what they'll love.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="{{ route('browse') }}" class="btn pw-btn-primary btn-lg px-5">Start Listening</a>
            <a href="{{ route('register') }}" class="btn pw-btn-outline btn-lg px-5">Start Creating</a>
        </div>
    </div>
</section>

{{-- Mission --}}
<section class="pw-section pw-section-dark">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold text-white mb-3">Our Mission</h2>
                <p class="text-muted mb-3" style="line-height:1.8;">
                    We believe every voice deserves to be heard. PodWave was founded with a simple idea:
                    make it effortless for creators to share their ideas with the world, and for listeners
                    to find content that genuinely matters to them.
                </p>
                <p class="text-muted" style="line-height:1.8;">
                    From the solo bedroom podcaster to the professional production studio, we provide
                    the same powerful platform — because great content shouldn't require a huge budget.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    @foreach([
                        ['icon'=>'bi-mic-fill',        'color'=>'#8B5CF6', 'title'=>'For Creators',  'desc'=>'Intuitive upload tools, episode management, and detailed analytics.'],
                        ['icon'=>'bi-headphones',      'color'=>'#6366F1', 'title'=>'For Listeners', 'desc'=>'Discover, subscribe, and listen across any device — no interruptions.'],
                        ['icon'=>'bi-shield-check-fill','color'=>'#10B981','title'=>'Safe Platform',  'desc'=>'Strong content moderation and community guidelines protect everyone.'],
                        ['icon'=>'bi-graph-up-arrow',  'color'=>'#F59E0B', 'title'=>'Grow Together','desc'=>'Revenue tools, subscriber insights, and community features.'],
                    ] as $item)
                    <div class="col-6">
                        <div class="pw-card">
                            <i class="bi {{ $item['icon'] }} fs-3 mb-3 d-block" style="color: {{ $item['color'] }}"></i>
                            <h6 class="text-white fw-bold mb-1">{{ $item['title'] }}</h6>
                            <p class="text-muted small mb-0">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="pw-section">
    <div class="container text-center">
        <h2 class="fw-bold text-white mb-2">PodWave by the Numbers</h2>
        <p class="text-muted mb-5">Growing every day with creators and listeners worldwide</p>
        <div class="row g-4">
            @foreach([
                ['num'=>'50K+',  'label'=>'Active Listeners',    'icon'=>'bi-headphones'],
                ['num'=>'8K+',   'label'=>'Podcast Creators',    'icon'=>'bi-mic-fill'],
                ['num'=>'120K+', 'label'=>'Episodes Published',  'icon'=>'bi-play-circle-fill'],
                ['num'=>'2M+',   'label'=>'Total Plays',         'icon'=>'bi-soundwave'],
            ] as $stat)
            <div class="col-6 col-md-3">
                <div class="pw-card text-center">
                    <i class="bi {{ $stat['icon'] }} text-accent fs-2 d-block mb-2"></i>
                    <div style="font-size:2rem;font-weight:900;color:#fff;letter-spacing:-1px;">{{ $stat['num'] }}</div>
                    <div class="text-muted small">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Team --}}
<section class="pw-section pw-section-dark">
    <div class="container text-center">
        <h2 class="fw-bold text-white mb-2">Built by Podcast Fans</h2>
        <p class="text-muted mb-5">A small team obsessed with audio content and creator tools</p>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['name'=>'Arjun Mehta',    'role'=>'Founder & CEO',         'emoji'=>'🎙️'],
                ['name'=>'Priya Sharma',   'role'=>'Head of Product',        'emoji'=>'🎧'],
                ['name'=>'Rohan Kapoor',   'role'=>'Lead Engineer',          'emoji'=>'⚙️'],
                ['name'=>'Neha Gupta',     'role'=>'Creator Partnerships',   'emoji'=>'🤝'],
            ] as $member)
            <div class="col-6 col-md-3">
                <div class="pw-card text-center">
                    <div style="font-size:3rem;margin-bottom:12px;">{{ $member['emoji'] }}</div>
                    <div class="text-white fw-bold">{{ $member['name'] }}</div>
                    <div class="text-muted small">{{ $member['role'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
@guest
<section class="pw-cta-section">
    <div class="container">
        <div class="pw-cta-card text-center">
            <div class="pw-cta-icon mb-4"><i class="bi bi-rocket-takeoff-fill"></i></div>
            <h2 class="fw-black text-white mb-2">Ready to Join PodWave?</h2>
            <p class="text-muted mb-4">Free forever for listeners. Start your podcast for free today.</p>
            <a href="{{ route('register') }}" class="btn pw-btn-primary btn-lg px-5">
                <i class="bi bi-person-plus-fill me-2"></i>Create Free Account
            </a>
        </div>
    </div>
</section>
@endguest

@endsection
