@extends('layouts.app')
@section('title', 'Edit: ' . $podcast->title)

@section('content')
<div class="container py-5" style="max-width:760px;">
    <div class="mb-4">
        <a href="{{ route('creator.podcasts.index') }}" class="pw-link-more">
            <i class="bi bi-chevron-left me-1"></i>Back to Podcasts
        </a>
        <h1 class="fw-bold text-white mt-2 mb-1">Edit Podcast</h1>
        <p class="text-muted">Updating: <span class="text-accent">{{ $podcast->title }}</span></p>
    </div>

    <form action="{{ route('creator.podcasts.update', $podcast) }}" method="POST" enctype="multipart/form-data" data-loading>
        @csrf @method('PUT')

        {{-- Thumbnail --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4">Podcast Artwork</h5>
            <div class="d-flex gap-4 align-items-start">
                <div class="pw-upload-zone" id="thumbnailZone" style="width:140px;height:140px;flex-shrink:0;">
                    @if($podcast->thumbnail)
                        <img id="thumbnailPreview" src="{{ $podcast->thumbnail_url }}" class="w-100 h-100 rounded-3" style="object-fit:cover;" alt="">
                        <div id="thumbnailPlaceholder" style="display:none;" class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                            <i class="bi bi-image text-muted fs-2 mb-2"></i>
                            <span class="text-muted small">Click to upload</span>
                        </div>
                    @else
                        <img id="thumbnailPreview" src="" class="w-100 h-100 rounded-3" style="object-fit:cover;display:none;" alt="">
                        <div id="thumbnailPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                            <i class="bi bi-image text-muted fs-2 mb-2"></i>
                            <span class="text-muted small">Click to upload</span>
                        </div>
                    @endif
                    <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" class="d-none">
                </div>
                <div>
                    <p class="text-muted small mb-1">Upload a new cover image to replace the current one.</p>
                    <p class="text-muted small mb-0">Recommended: 3000×3000px JPG/PNG · Max 5MB</p>
                    @error('thumbnail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4">Podcast Details</h5>

            <div class="mb-3">
                <label class="pw-label">Podcast Title *</label>
                <input type="text" name="title" value="{{ old('title', $podcast->title) }}"
                    class="form-control pw-input @error('title') is-invalid @enderror" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="pw-label">Description *</label>
                <textarea name="description" rows="5"
                    class="form-control pw-input @error('description') is-invalid @enderror" required>{{ old('description', $podcast->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="pw-label">Category *</label>
                    <select name="category_id" id="categorySelect" class="form-select pw-input" required>
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $podcast->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="pw-label">Genre</label>
                    <select name="genre_id" id="genreSelect" class="form-select pw-input">
                        <option value="">Select genre</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}"
                                data-category="{{ $genre->category_id }}"
                                {{ old('genre_id', $podcast->genre_id) == $genre->id ? 'selected' : '' }}>
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
                            <option value="{{ $lang }}" {{ old('language', $podcast->language) === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="pw-label">Status</label>
                    <select name="status" class="form-select pw-input">
                        <option value="draft"     {{ old('status', $podcast->status) == 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $podcast->status) == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="pw-label">Tags <span class="text-muted">(comma-separated)</span></label>
                <input type="text" name="tags" value="{{ old('tags', $podcast->tags_list) }}" class="form-control pw-input">
            </div>

            <div class="form-check mt-3">
                <input type="checkbox" name="is_explicit" id="isExplicit" class="form-check-input pw-check" value="1"
                    {{ old('is_explicit', $podcast->is_explicit) ? 'checked' : '' }}>
                <label for="isExplicit" class="form-check-label text-muted small">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    Contains explicit content
                </label>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-between">
            <a href="{{ route('podcasts.show', $podcast->slug) }}" class="btn pw-btn-ghost" target="_blank">
                <i class="bi bi-eye me-1"></i>Preview
            </a>
            <div class="d-flex gap-3">
                <a href="{{ route('creator.podcasts.index') }}" class="btn pw-btn-outline px-5">Cancel</a>
                <button type="submit" class="btn pw-btn-primary px-5">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
            </div>
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
.pw-upload-zone:hover { border-color: var(--pw-accent); }
</style>
@endpush

@push('scripts')
<script>
const zone = document.getElementById('thumbnailZone');
const input = document.getElementById('thumbnailInput');
const preview = document.getElementById('thumbnailPreview');
const placeholder = document.getElementById('thumbnailPlaceholder');
zone.addEventListener('click', () => input.click());
input.addEventListener('change', () => {
    if (input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display='block'; if(placeholder) placeholder.style.display='none'; };
        reader.readAsDataURL(input.files[0]);
    }
});

// Genre filter
const catSelect = document.getElementById('categorySelect');
const genreSelect = document.getElementById('genreSelect');
const allOpts = Array.from(genreSelect.querySelectorAll('option'));
const currentGenre = "{{ $podcast->genre_id }}";

catSelect.addEventListener('change', function() {
    const catId = this.value;
    const current = genreSelect.value;
    genreSelect.innerHTML = '<option value="">Select genre (optional)</option>';
    allOpts.forEach(opt => {
        if (opt.dataset.category == catId || !opt.dataset.category) {
            const clone = opt.cloneNode(true);
            if (clone.value === current) clone.selected = true;
            genreSelect.appendChild(clone);
        }
    });
});
</script>
@endpush
