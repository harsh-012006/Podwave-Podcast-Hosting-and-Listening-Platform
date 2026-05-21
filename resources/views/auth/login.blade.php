@extends('layouts.app')
@section('title', 'Sign In — PodWave')

@section('content')
<div class="pw-auth-wrapper">
    <div class="pw-auth-card">
        <div class="text-center mb-4">
            <a href="{{ route('home') }}" class="pw-logo text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                <i class="bi bi-soundwave fs-2 text-accent"></i>
                <span class="fw-black fs-3 text-white">Pod<span class="text-accent">Wave</span></span>
            </a>
            <h2 class="text-white fw-bold mb-1">Welcome back</h2>
            <p class="text-muted">Sign in to your PodWave account</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger pw-alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="pw-label">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control pw-input @error('email') is-invalid @enderror"
                    placeholder="you@example.com" required autofocus>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label class="pw-label">Password</label>
                    <a href="#" class="text-accent small text-decoration-none">Forgot password?</a>
                </div>
                <div class="position-relative">
                    <input type="password" name="password" id="passwordField"
                        class="form-control pw-input @error('password') is-invalid @enderror"
                        placeholder="••••••••" required>
                    <button type="button" class="pw-pw-toggle" onclick="togglePw()">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input pw-check">
                    <label for="remember" class="form-check-label text-muted small">Remember me</label>
                </div>
            </div>
            <button type="submit" class="btn pw-btn-primary w-100 py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="pw-divider mb-3">
            <span class="text-muted">or</span>
        </div>

        <!-- Google Login Button -->
        <a href="{{ route('auth.google') }}" class="btn btn-outline-light w-100 py-2 mb-3 d-flex align-items-center justify-content-center gap-2">
            <svg class="w-5 h-5" width="20" height="20" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#4285F4" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <div class="text-center">
            <p class="text-muted mb-0">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-accent text-decoration-none fw-semibold">Create one free</a>
            </p>
        </div>

        {{-- Demo credentials --}}
        <div class="pw-demo-creds mt-4">
            <p class="text-muted small text-center mb-2">Quick Demo Login:</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-sm pw-demo-btn" onclick="fillCreds('admin@podwave.fm')">Admin</button>
                <button class="btn btn-sm pw-demo-btn" onclick="fillCreds('creator@podwave.fm')">Creator</button>
                <button class="btn btn-sm pw-demo-btn" onclick="fillCreds('listener@podwave.fm')">Listener</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePw() {
    const f = document.getElementById('passwordField');
    const i = document.getElementById('pwIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
function fillCreds(email) {
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('input[name="password"]').value = 'password';
}
</script>
@endpush
