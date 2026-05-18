@extends('layouts.app')
@section('title', 'Contact Us — PodWave')

@section('content')
<div class="container py-5" style="max-width:900px;">
    <div class="text-center mb-5">
        <div class="pw-hero-badge mb-3"><i class="bi bi-envelope-fill"></i> Get in Touch</div>
        <h1 class="fw-black text-white mb-2">Contact PodWave</h1>
        <p class="text-muted">Have a question, feedback, or partnership inquiry? We'd love to hear from you.</p>
    </div>

    <div class="row g-5">
        <div class="col-lg-5">
            <h5 class="text-white fw-semibold mb-4">Ways to Reach Us</h5>
            @foreach([
                ['icon'=>'bi-envelope-fill',       'color'=>'#8B5CF6', 'title'=>'Email',        'info'=>'hello@podwave.fm',    'sub'=>'We reply within 24 hours'],
                ['icon'=>'bi-twitter-x',            'color'=>'#6366F1', 'title'=>'Twitter / X',  'info'=>'@PodWaveFM',          'sub'=>'For quick questions'],
                ['icon'=>'bi-discord',              'color'=>'#5865F2', 'title'=>'Discord',      'info'=>'discord.gg/podwave',  'sub'=>'Join our community'],
                ['icon'=>'bi-headset',              'color'=>'#10B981', 'title'=>'Creator Support','info'=>'creators@podwave.fm','sub'=>'For hosting-related help'],
            ] as $contact)
            <div class="d-flex gap-3 mb-4">
                <div style="width:44px;height:44px;background:rgba(255,255,255,0.05);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $contact['icon'] }}" style="color:{{ $contact['color'] }};font-size:1.1rem;"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold small">{{ $contact['title'] }}</div>
                    <div class="text-accent small">{{ $contact['info'] }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ $contact['sub'] }}</div>
                </div>
            </div>
            @endforeach

            <div class="pw-card mt-4">
                <h6 class="text-white fw-semibold mb-3">Office Hours</h6>
                <div class="text-muted small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Monday – Friday</span><span class="text-white">9AM – 6PM IST</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Saturday – Sunday</span><span class="text-muted">Closed</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="pw-card">
                <h5 class="text-white fw-semibold mb-4">Send Us a Message</h5>

                @if(session('success'))
                <div class="alert mb-4" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#6EE7B7;border-radius:10px;">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" data-loading>
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="pw-label">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}"
                                class="form-control pw-input @error('name') is-invalid @enderror"
                                placeholder="Your name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="pw-label">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                                class="form-control pw-input @error('email') is-invalid @enderror"
                                placeholder="you@example.com" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="pw-label">Subject *</label>
                        <select name="subject" class="form-select pw-input @error('subject') is-invalid @enderror" required>
                            <option value="">Select a topic…</option>
                            <option value="General Inquiry"       {{ old('subject')=='General Inquiry'       ?'selected':'' }}>General Inquiry</option>
                            <option value="Creator Support"       {{ old('subject')=='Creator Support'       ?'selected':'' }}>Creator Support</option>
                            <option value="Technical Issue"       {{ old('subject')=='Technical Issue'       ?'selected':'' }}>Technical Issue</option>
                            <option value="Report Content"        {{ old('subject')=='Report Content'        ?'selected':'' }}>Report Content</option>
                            <option value="Partnership"           {{ old('subject')=='Partnership'           ?'selected':'' }}>Partnership Inquiry</option>
                            <option value="Press"                 {{ old('subject')=='Press'                 ?'selected':'' }}>Press & Media</option>
                            <option value="Feature Request"       {{ old('subject')=='Feature Request'       ?'selected':'' }}>Feature Request</option>
                            <option value="Account Issue"         {{ old('subject')=='Account Issue'         ?'selected':'' }}>Account Issue</option>
                        </select>
                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="pw-label">Message *</label>
                        <textarea name="message" rows="6"
                            class="form-control pw-input @error('message') is-invalid @enderror"
                            placeholder="Tell us how we can help…" required minlength="10" maxlength="2000">{{ old('message') }}</textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text text-muted small text-end"><span id="charCount">0</span>/2000</div>
                    </div>
                    <button type="submit" class="btn pw-btn-primary w-100 py-2">
                        <i class="bi bi-send-fill me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const msg = document.querySelector('textarea[name="message"]');
const counter = document.getElementById('charCount');
if (msg) {
    msg.addEventListener('input', () => counter.textContent = msg.value.length);
}
</script>
@endpush
