<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — PodWave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/podwave.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="pw-body pw-admin-body">

<div class="d-flex" style="min-height:100vh;">

    {{-- SIDEBAR --}}
    <aside class="pw-admin-sidebar" id="adminSidebar">
        <div class="pw-admin-logo">
            <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center gap-2">
                <i class="bi bi-soundwave text-accent fs-4"></i>
                <span class="fw-black text-white">Pod<span class="text-accent">Wave</span></span>
            </a>
            <span class="pw-admin-label">Admin Panel</span>
        </div>

        <nav class="pw-admin-nav">
            <div class="pw-nav-group-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="pw-nav-group-label mt-3">Content</div>
            <a href="{{ route('admin.podcasts') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.podcasts*') ? 'active' : '' }}">
                <i class="bi bi-collection-play-fill"></i> Podcasts
            </a>
            <a href="{{ route('admin.categories') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Categories
            </a>
            <a href="{{ route('admin.genres') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.genres*') ? 'active' : '' }}">
                <i class="bi bi-tag-fill"></i> Genres
            </a>

            <div class="pw-nav-group-label mt-3">Community</div>
            <a href="{{ route('admin.users') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Users
                @php $banned = \App\Models\User::where('is_banned', true)->count(); @endphp
                @if($banned) <span class="pw-sidebar-badge">{{ $banned }}</span> @endif
            </a>
            <a href="{{ route('admin.comments') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                <i class="bi bi-chat-fill"></i> Comments
                @php $flagged = \App\Models\Comment::where('is_flagged', true)->count(); @endphp
                @if($flagged) <span class="pw-sidebar-badge">{{ $flagged }}</span> @endif
            </a>
            <a href="{{ route('admin.messages') }}" class="pw-admin-nav-item {{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
                <i class="bi bi-envelope-fill"></i> Messages
                @php $msgs = \App\Models\ContactMessage::where('is_read', false)->count(); @endphp
                @if($msgs) <span class="pw-sidebar-badge">{{ $msgs }}</span> @endif
            </a>

            <div class="pw-nav-group-label mt-3">Account</div>
            <a href="{{ route('home') }}" class="pw-admin-nav-item">
                <i class="bi bi-globe"></i> View Site
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="pw-admin-nav-item w-100 text-start border-0 bg-transparent text-danger">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
            </form>
        </nav>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="pw-admin-main flex-grow-1">
        {{-- Top Bar --}}
        <header class="pw-admin-header">
            <button class="btn btn-sm text-white d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-block">{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="34" height="34" alt="">
            </div>
        </header>

        {{-- Flash --}}
        @if(session('success'))
        <div class="pw-toast-container">
            <div class="pw-toast pw-toast-success show">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button onclick="this.parentElement.remove()" class="pw-toast-close">&times;</button>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="pw-toast-container">
            <div class="pw-toast pw-toast-error show">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                <button onclick="this.parentElement.remove()" class="pw-toast-close">&times;</button>
            </div>
        </div>
        @endif

        <div class="pw-admin-content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/podwave.js') }}"></script>
@stack('scripts')
<script>setTimeout(() => { document.querySelectorAll('.pw-toast').forEach(t => t.remove()); }, 5000);</script>
</body>
</html>
