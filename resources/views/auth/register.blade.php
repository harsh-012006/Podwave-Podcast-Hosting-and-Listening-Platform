@extends('layouts.app')
@section('title', 'Create Account — PodWave')

@section('content')
<div class="pw-auth-wrapper">
    <div class="pw-auth-card">
        <div class="text-center mb-4">
            <a href="{{ route('home') }}" class="pw-logo text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                <i class="bi bi-soundwave fs-2 text-accent"></i>
                <span class="fw-black fs-3 text-white">Pod<span class="text-accent">Wave</span></span>
            </a>
            <h2 class="text-white fw-bold mb-1">Join PodWave</h2>
            <p class="text-muted">Start listening or create your first podcast</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger pw-alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Role selector --}}
            <div class="mb-4">
                <label class="pw-label mb-2">I want to…</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" name="role" id="roleListener" value="listener" class="d-none" checked>
                        <label for="roleListener" class="pw-role-card">
                            <i class="bi bi-headphones fs-2 mb-2 text-accent"></i>
                            <span class="fw-semibold text-white d-block">Listen</span>
                            <span class="text-muted small">Discover & enjoy podcasts</span>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" name="role" id="roleCreator" value="creator" class="d-none">
                        <label for="roleCreator" class="pw-role-card">
                            <i class="bi bi-mic-fill fs-2 mb-2 text-accent"></i>
                            <span class="fw-semibold text-white d-block">Create</span>
                            <span class="text-muted small">Host your own podcast</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="pw-label">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-control pw-input @error('name') is-invalid @enderror"
                    placeholder="Your full name" required>
            </div>

            <div class="mb-3">
                <label class="pw-label">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control pw-input @error('email') is-invalid @enderror"
                    placeholder="you@example.com" required>
            </div>

            <div class="mb-3">
                <label class="pw-label">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" id="pw"
                        class="form-control pw-input @error('password') is-invalid @enderror"
                        placeholder="Min 8 characters" required>
                    <button type="button" class="pw-pw-toggle" onclick="document.getElementById('pw').type = document.getElementById('pw').type === 'password' ? 'text' : 'password'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="pw-label">Confirm Password</label>
                <input type="password" name="password_confirmation"
                    class="form-control pw-input"
                    placeholder="Repeat your password" required>
            </div>

            <button type="submit" class="btn pw-btn-primary w-100 py-2 mb-3">
                <i class="bi bi-rocket-takeoff me-2"></i>Create Account — It's Free
            </button>

            <p class="text-muted small text-center mb-0">
                By signing up, you agree to our
                <a href="#" class="text-accent text-decoration-none">Terms of Service</a> and
                <a href="#" class="text-accent text-decoration-none">Privacy Policy</a>.
            </p>
        </form>

        <hr class="border-secondary my-4">

        <div class="text-center">
            <p class="text-muted mb-0">
                Already have an account?
                <a href="{{ route('login') }}" class="text-accent text-decoration-none fw-semibold">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Highlight selected role card
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.pw-role-card').forEach(c => c.classList.remove('selected'));
        this.nextElementSibling.classList.add('selected');
    });
});
// Default listener selected
document.getElementById('roleListener').nextElementSibling.classList.add('selected');
</script>
@endpush
