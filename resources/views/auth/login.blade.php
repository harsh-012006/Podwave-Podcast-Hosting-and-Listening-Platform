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
