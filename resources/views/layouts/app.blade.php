<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PodWave') — Stream. Create. Discover.</title>
    <meta name="description" content="@yield('meta_description', 'PodWave — The ultimate podcast platform for creators and listeners.')">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Custom PodWave CSS -->
    <link href="{{ asset('css/podwave.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="pw-body">

    {{-- TOP NAVIGATION --}}
    <nav class="navbar pw-navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">

            {{-- Logo --}}
            <a class="navbar-brand pw-logo" href="{{ route('home') }}">
                <i class="bi bi-soundwave"></i>
                <span>Pod<span class="text-accent">Wave</span></span>
            </a>

            {{-- Search Bar (desktop) --}}
            <div class="pw-search-wrapper d-none d-lg-flex mx-4 flex-grow-1" style="max-width:420px;">
                <div class="pw-search-box w-100 position-relative">
                    <i class="bi bi-search pw-search-icon"></i>
                    <input type="text" id="globalSearch" class="form-control pw-search-input"
                           placeholder="Search podcasts, episodes, creators…"
                           autocomplete="off">
                    <div id="searchDropdown" class="pw-search-dropdown d-none"></div>
                </div>
            </div>

            {{-- Nav Links --}}
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <i class="bi bi-list text-white fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link pw-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-fill"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pw-nav-link {{ request()->routeIs('browse') ? 'active' : '' }}" href="{{ route('browse') }}">
                            <i class="bi bi-compass-fill"></i> Browse
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pw-nav-link {{ request()->routeIs('trending') ? 'active' : '' }}" href="{{ route('trending') }}">
                            <i class="bi bi-fire"></i> Trending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pw-nav-link {{ request()->routeIs('categories') ? 'active' : '' }}" href="{{ route('categories') }}">
                            <i class="bi bi-grid-fill"></i> Categories
                        </a>
                    </li>

                    @auth
                        {{-- Notifications --}}
                        <li class="nav-item">
                            <a class="nav-link pw-nav-link position-relative" href="{{ route('listener.notifications') }}">
                                <i class="bi bi-bell-fill"></i>
                                @if(auth()->user()->unreadNotifications->count())
                                    <span class="pw-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                                @endif
                            </a>
                        </li>

                        {{-- User Dropdown --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 pw-user-toggle" href="#" data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->avatar_url }}" class="pw-avatar-sm rounded-circle" alt="avatar">
                                <span class="d-none d-lg-inline">{{ Str::limit(auth()->user()->name, 14) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end pw-dropdown">
                                <li class="px-3 py-2">
                                    <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
                                    <small class="text-muted text-capitalize">{{ auth()->user()->role }}</small>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <a class="dropdown-item pw-dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                                @if(auth()->user()->isCreator())
                                    <li>
                                        <a class="dropdown-item pw-dropdown-item" href="{{ route('creator.podcasts.index') }}">
                                            <i class="bi bi-mic-fill"></i> My Podcasts
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->isListener())
                                    <li>
                                        <a class="dropdown-item pw-dropdown-item" href="{{ route('listener.favorites') }}">
                                            <i class="bi bi-heart-fill"></i> Favorites
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item pw-dropdown-item" href="{{ route('listener.history') }}">
                                            <i class="bi bi-clock-history"></i> History
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item pw-dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-shield-fill"></i> Admin Panel
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item pw-dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right"></i> Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn pw-btn-outline btn-sm" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn pw-btn-primary btn-sm" href="{{ route('register') }}">Get Started</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="pw-main">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="pw-toast-container">
                <div class="pw-toast pw-toast-success show" id="flashToast">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="pw-toast-close">&times;</button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="pw-toast-container">
                <div class="pw-toast pw-toast-error show" id="flashToastErr">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="pw-toast-close">&times;</button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- PERSISTENT AUDIO PLAYER --}}
    @auth
    <div class="pw-player" id="audioPlayer" style="display:none;">
        <div class="pw-player-inner">
            {{-- Track Info --}}
            <div class="pw-player-info">
                <img src="" id="playerThumbnail" class="pw-player-thumb rounded" alt="">
                <div class="pw-player-meta">
                    <div class="pw-player-title" id="playerTitle">—</div>
                    <div class="pw-player-podcast" id="playerPodcast">—</div>
                </div>
                <button class="pw-player-fav-btn" id="playerFavBtn" title="Like">
                    <i class="bi bi-heart"></i>
                </button>
            </div>

            {{-- Controls --}}
            <div class="pw-player-controls">
                <button class="pw-ctrl-btn" id="playerPrev" title="Previous"><i class="bi bi-skip-start-fill"></i></button>
                <button class="pw-ctrl-btn pw-ctrl-skip" id="playerBackward" title="Back 15s"><i class="bi bi-skip-backward-fill"></i></button>
                <button class="pw-ctrl-btn pw-ctrl-play" id="playerPlayPause" title="Play/Pause">
                    <i class="bi bi-play-circle-fill" id="playerPlayIcon"></i>
                </button>
                <button class="pw-ctrl-btn pw-ctrl-skip" id="playerForward" title="Forward 30s"><i class="bi bi-skip-forward-fill"></i></button>
                <button class="pw-ctrl-btn" id="playerNext" title="Next"><i class="bi bi-skip-end-fill"></i></button>

                {{-- Progress --}}
                <div class="pw-player-progress-wrap">
                    <span class="pw-time" id="playerCurrentTime">0:00</span>
                    <input type="range" id="playerSeek" class="pw-progress-bar" value="0" min="0" step="1">
                    <span class="pw-time" id="playerDuration">0:00</span>
                </div>

                {{-- Volume --}}
                <div class="pw-player-volume">
                    <i class="bi bi-volume-up-fill" id="volumeIcon"></i>
                    <input type="range" id="volumeSlider" class="pw-volume-slider" min="0" max="1" step="0.05" value="0.8">
                </div>

                <button class="pw-ctrl-btn" id="playerSpeed" title="Playback speed">1×</button>
            </div>

            {{-- Close --}}
            <button class="pw-player-close" id="playerClose"><i class="bi bi-x-lg"></i></button>
        </div>

        {{-- Hidden audio element --}}
        <audio id="audioElement" preload="metadata"></audio>
    </div>
    @endauth

    {{-- FOOTER --}}
    <footer class="pw-footer @auth pw-footer-padded @endauth">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="pw-logo text-decoration-none d-flex align-items-center gap-2 mb-3" href="{{ route('home') }}">
                        <i class="bi bi-soundwave fs-3 text-accent"></i>
                        <span class="fw-black fs-4 text-white">Pod<span class="text-accent">Wave</span></span>
                    </a>
                    <p class="text-muted small">The ultimate podcast platform for creators and listeners. Stream thousands of shows, create your own, and connect with communities worldwide.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-muted pw-social-link"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-muted pw-social-link"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-muted pw-social-link"><i class="bi bi-youtube fs-5"></i></a>
                        <a href="#" class="text-muted pw-social-link"><i class="bi bi-discord fs-5"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Discover</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('browse') }}" class="pw-footer-link">Browse</a></li>
                        <li><a href="{{ route('trending') }}" class="pw-footer-link">Trending</a></li>
                        <li><a href="{{ route('categories') }}" class="pw-footer-link">Categories</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Create</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('register') }}" class="pw-footer-link">Start Podcasting</a></li>
                        <li><a href="#" class="pw-footer-link">Creator Guide</a></li>
                        <li><a href="#" class="pw-footer-link">Analytics</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('about') }}" class="pw-footer-link">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="pw-footer-link">Contact</a></li>
                        <li><a href="#" class="pw-footer-link">Careers</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="pw-footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="pw-footer-link">Terms of Service</a></li>
                        <li><a href="#" class="pw-footer-link">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary mt-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                <p class="text-muted small mb-0">&copy; {{ date('Y') }} PodWave. All rights reserved.</p>
                <p class="text-muted small mb-0">Built with <i class="bi bi-heart-fill text-accent"></i> using Laravel 11</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- PodWave JS -->
    <script src="{{ asset('js/podwave.js') }}"></script>

    @stack('scripts')

    {{-- Auto-dismiss toasts --}}
    <script>
        setTimeout(() => {
            document.querySelectorAll('.pw-toast').forEach(t => t.remove());
        }, 5000);
    </script>
</body>
</html>
