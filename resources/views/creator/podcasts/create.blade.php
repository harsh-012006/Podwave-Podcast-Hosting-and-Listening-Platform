@extends('layouts.app')
@section('title', 'Create Podcast — PodWave')

@section('content')
<div class="container py-5" style="max-width:760px;">
    <div class="mb-4">
        <a href="{{ route('creator.podcasts.index') }}" class="pw-link-more">
            <i class="bi bi-chevron-left me-1"></i>Back to Podcasts
        </a>
        <h1 class="fw-bold text-white mt-2 mb-1">Create New Podcast</h1>
        <p class="text-muted">Set up your podcast show. You can add episodes after creation.</p>
    </div>

    <form action="{{ route('creator.podcasts.store') }}" method="POST" enctype="multipart/form-data" data-loading>
        @csrf

        {{-- Thumbnail Upload --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4">Podcast Artwork</h5>
            <div class="d-flex gap-4 align-items-start">
                <div class="pw-upload-zone" id="thumbnailZone" style="width:140px;height:140px;flex-shrink:0;">
                    <img id="thumbnailPreview" src="" class="w-100 h-100 rounded-3" style="object-fit:cover;display:none;" alt="">
                    <div id="thumbnailPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                        <i class="bi bi-image text-muted fs-2 mb-2"></i>
                        <span class="text-muted small">Click to upload</span>
                    </div>
                    <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" class="d-none">
                </div>
                <div>
                    <p class="text-muted small mb-2">Upload your podcast cover art. Recommended: <strong class="text-white">3000×3000px</strong> square JPG or PNG.</p>
                    <p class="text-muted small mb-0">Max file size: 5MB. A high-quality cover helps attract listeners.</p>
                    @error('thumbnail')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4">Podcast Details</h5>

            <div class="mb-3">
                <label class="pw-label">Podcast Title *</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="form-control pw-input @error('title') is-invalid @enderror"
                    placeholder="e.g. The Tech Corner" required maxlength="255">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="pw-label">Description *</label>
                <textarea name="description" rows="5"
                    class="form-control pw-input @error('description') is-invalid @enderror"
                    placeholder="Describe your podcast — what it's about, who it's for, and what listeners can expect." required>{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="pw-label">Category *</label>
                    <select name="category_id" id="categorySelect" class="form-select pw-input @error('category_id') is-invalid @enderror" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="pw-label">Genre</label>
                    <select name="genre_id" id="genreSelect" class="form-select pw-input">
                        <option value="">Select category first</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}"
                                data-category="{{ $genre->category_id }}"
                                {{ old('genre_id') == $genre->id ? 'selected' : '' }}>
                                {{ $genre->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="pw-label">Language *</label>
                    <select name="language" class="form-select pw-input" required>
                        @foreach(['English','Spanish','French','German','Portuguese','Japanese','Korean','Mandarin','Arabic','Hindi','Italian','Dutch','Russian','Polish','Turkish'] as $lang)
                            <option value="{{ $lang }}" {{ old('language','English') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="pw-label">Status</label>
                    <select name="status" class="form-select pw-input">
                        <option value="draft"     {{ old('status') == 'draft'     ? 'selected' : '' }}>Draft (private)</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published (public)</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="pw-label">Tags <span class="text-muted">(comma-separated)</span></label>
                <input type="text" name="tags" value="{{ old('tags') }}"
                    class="form-control pw-input"
                    placeholder="e.g. technology, AI, innovation, startups">
                <div class="form-text text-muted" style="font-size:.78rem;">Add up to 10 tags to help listeners discover your podcast.</div>
            </div>

            <div class="form-check mt-3">
                <input type="checkbox" name="is_explicit" id="isExplicit" class="form-check-input pw-check" value="1"
                    {{ old('is_explicit') ? 'checked' : '' }}>
                <label for="isExplicit" class="form-check-label text-muted small">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    This podcast contains explicit content (strong language, mature themes)
                </label>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-end">
            <a href="{{ route('creator.podcasts.index') }}" class="btn pw-btn-outline px-5">Cancel</a>
            <button type="submit" class="btn pw-btn-primary px-5">
                <i class="bi bi-check-lg me-2"></i>Create Podcast
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.pw-upload-zone {
    border: 2px dashed rgba(255,255,255,0.15);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
    background: var(--pw-surface-2);
}
.pw-upload-zone:hover { border-color: var(--pw-accent); background: rgba(139,92,246,0.08); }
</style>
@endpush

@push('scripts')
<script>
// Thumbnail preview
const zone = document.getElementById('thumbnailZone');
const input = document.getElementById('thumbnailInput');
const preview = document.getElementById('thumbnailPreview');
const placeholder = document.getElementById('thumbnailPlaceholder');

zone.addEventListener('click', () => input.click());
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#8B5CF6'; });
zone.addEventListener('dragleave', () => zone.style.borderColor = '');
zone.addEventListener('drop', e => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) showPreview(file);
});
input.addEventListener('change', () => { if (input.files[0]) showPreview(input.files[0]); });

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// Dynamic genre filter based on category
const catSelect = document.getElementById('categorySelect');
const genreSelect = document.getElementById('genreSelect');
const allGenreOptions = Array.from(genreSelect.querySelectorAll('option'));

catSelect.addEventListener('change', function() {
    const catId = this.value;
    genreSelect.innerHTML = '<option value="">Select a genre (optional)</option>';
    allGenreOptions.forEach(opt => {
        if (opt.dataset.category == catId) {
            genreSelect.appendChild(opt.cloneNode(true));
        }
    });
});

// Init if old value
if (catSelect.value) catSelect.dispatchEvent(new Event('change'));
</script>
@endpush
