@extends('layouts.app')
@section('title', 'Upload Episode — ' . $podcast->title)

@section('content')
<div class="container py-5" style="max-width:760px;">
    <div class="mb-4">
        <a href="{{ route('creator.podcasts.episodes', $podcast) }}" class="pw-link-more">
            <i class="bi bi-chevron-left me-1"></i>Back to Episodes
        </a>
        <h1 class="fw-bold text-white mt-2 mb-1">Upload New Episode</h1>
        <p class="text-muted">Adding episode to: <span class="text-accent">{{ $podcast->title }}</span></p>
    </div>

    <form action="{{ route('creator.episodes.store', $podcast) }}" method="POST" enctype="multipart/form-data" data-loading>
        @csrf

        {{-- Audio Upload --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4"><i class="bi bi-music-note-beamed text-accent me-2"></i>Audio File *</h5>
            <div class="pw-audio-upload-zone" id="audioZone">
                <div id="audioPlaceholder" class="text-center py-4">
                    <i class="bi bi-cloud-upload-fill text-accent fs-1 mb-3 d-block"></i>
                    <h6 class="text-white mb-1">Drop your audio file here</h6>
                    <p class="text-muted small mb-3">or click to browse your files</p>
                    <span class="pw-tag">MP3</span>
                    <span class="pw-tag">WAV</span>
                    <span class="pw-tag">OGG</span>
                    <span class="pw-tag">M4A</span>
                    <p class="text-muted small mt-3 mb-0">Max file size: 100MB</p>
                </div>
                <div id="audioSelected" class="d-flex align-items-center gap-3 py-3 px-2" style="display:none!important;">
                    <div class="pw-play-btn-sm" style="pointer-events:none;"><i class="bi bi-music-note-beamed"></i></div>
                    <div>
                        <div class="text-white small fw-semibold" id="audioFileName">—</div>
                        <div class="text-muted" style="font-size:.75rem;" id="audioFileSize">—</div>
                    </div>
                    <button type="button" class="btn pw-btn-ghost btn-sm ms-auto" id="clearAudio">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <input type="file" name="audio_file" id="audioInput" accept=".mp3,.wav,.ogg,.m4a,audio/*" class="d-none" required>
            </div>
            @error('audio_file') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>

        {{-- Episode Details --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-4">Episode Details</h5>

            <div class="mb-3">
                <label class="pw-label">Episode Title *</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="form-control pw-input @error('title') is-invalid @enderror"
                    placeholder="e.g. The Future of AI with Dr. Jane Smith" required maxlength="255">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="pw-label">Description *</label>
                <textarea name="description" rows="5"
                    class="form-control pw-input @error('description') is-invalid @enderror"
                    placeholder="What's this episode about? Include guest names, main topics, key takeaways…" required>{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="pw-label">Season #</label>
                    <input type="number" name="season_number" value="{{ old('season_number', 1) }}"
                        class="form-control pw-input" min="1" placeholder="1">
                </div>
                <div class="col-md-4">
                    <label class="pw-label">Episode #</label>
                    <input type="number" name="episode_number" value="{{ old('episode_number') }}"
                        class="form-control pw-input" min="1" placeholder="Auto-numbered">
                </div>
                <div class="col-md-4">
                    <label class="pw-label">Episode Type</label>
                    <select name="episode_type" class="form-select pw-input">
                        <option value="full"    {{ old('episode_type','full') == 'full'    ? 'selected':'' }}>Full Episode</option>
                        <option value="trailer" {{ old('episode_type') == 'trailer' ? 'selected':'' }}>Trailer</option>
                        <option value="bonus"   {{ old('episode_type') == 'bonus'   ? 'selected':'' }}>Bonus</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="pw-label">Release Date</label>
                    <input type="datetime-local" name="release_date"
                        value="{{ old('release_date', now()->format('Y-m-d\TH:i')) }}"
                        class="form-control pw-input">
                    <div class="form-text text-muted small">Leave at current time for immediate release.</div>
                </div>
                <div class="col-md-6">
                    <label class="pw-label">Status</label>
                    <select name="status" class="form-select pw-input">
                        <option value="draft"     {{ old('status') == 'draft'     ? 'selected':'' }}>Draft</option>
                        <option value="published" {{ old('status','published') == 'published' ? 'selected':'' }}>Published</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="pw-label">Show Notes <span class="text-muted">(optional)</span></label>
                <textarea name="show_notes" rows="3" class="form-control pw-input"
                    placeholder="Links, resources, sponsors mentioned in this episode…">{{ old('show_notes') }}</textarea>
            </div>

            <div class="form-check mt-3">
                <input type="checkbox" name="is_explicit" id="explicit" class="form-check-input pw-check"
                    {{ old('is_explicit') ? 'checked' : '' }}>
                <label for="explicit" class="form-check-label text-muted small">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    This episode contains explicit content
                </label>
            </div>
        </div>

        {{-- Thumbnail --}}
        <div class="pw-card mb-4">
            <h5 class="text-white fw-semibold mb-3">Episode Thumbnail <span class="text-muted fw-normal">(optional)</span></h5>
            <p class="text-muted small mb-3">If left empty, the podcast cover art will be used.</p>
            <div class="d-flex gap-4 align-items-start">
                <div class="pw-upload-zone" id="epThumbZone" style="width:100px;height:100px;flex-shrink:0;">
                    <img id="epThumbPreview" src="" class="w-100 h-100 rounded-2" style="object-fit:cover;display:none;" alt="">
                    <div id="epThumbPlaceholder" class="d-flex align-items-center justify-content-center h-100">
                        <i class="bi bi-image text-muted fs-3"></i>
                    </div>
                    <input type="file" name="thumbnail" id="epThumbInput" accept="image/*" class="d-none">
                </div>
                <div class="text-muted small">
                    <p class="mb-1">Recommended: <strong class="text-white">3000×3000px</strong> JPG or PNG</p>
                    <p class="mb-0">Max size: 5MB</p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-end">
            <a href="{{ route('creator.podcasts.episodes', $podcast) }}" class="btn pw-btn-outline px-5">Cancel</a>
            <button type="submit" class="btn pw-btn-primary px-5">
                <i class="bi bi-cloud-upload-fill me-2"></i>Upload Episode
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.pw-audio-upload-zone {
    border: 2px dashed rgba(255,255,255,0.12);
    border-radius: 14px;
    background: var(--pw-surface-2);
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}
.pw-audio-upload-zone:hover, .pw-audio-upload-zone.dragover {
    border-color: var(--pw-accent);
    background: rgba(139,92,246,0.07);
}
.pw-upload-zone {
    border: 2px dashed rgba(255,255,255,0.12);
    border-radius: 10px;
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
// Audio file upload
const audioZone  = document.getElementById('audioZone');
const audioInput = document.getElementById('audioInput');
const audioPlaceholder = document.getElementById('audioPlaceholder');
const audioSelected    = document.getElementById('audioSelected');
const audioFileName    = document.getElementById('audioFileName');
const audioFileSize    = document.getElementById('audioFileSize');

audioZone.addEventListener('click', (e) => { if (!e.target.closest('#clearAudio')) audioInput.click(); });
audioZone.addEventListener('dragover', (e) => { e.preventDefault(); audioZone.classList.add('dragover'); });
audioZone.addEventListener('dragleave', () => audioZone.classList.remove('dragover'));
audioZone.addEventListener('drop', (e) => {
    e.preventDefault();
    audioZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) setAudioFile(file);
});
audioInput.addEventListener('change', () => { if (audioInput.files[0]) setAudioFile(audioInput.files[0]); });

function setAudioFile(file) {
    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
    audioFileName.textContent = file.name;
    audioFileSize.textContent = `${sizeMB} MB`;
    audioPlaceholder.style.display = 'none';
    audioSelected.style.setProperty('display', 'flex', 'important');
}

document.getElementById('clearAudio').addEventListener('click', () => {
    audioInput.value = '';
    audioPlaceholder.style.display = 'block';
    audioSelected.style.removeProperty('display');
});

// Episode thumbnail
const epZone  = document.getElementById('epThumbZone');
const epInput = document.getElementById('epThumbInput');
const epPreview = document.getElementById('epThumbPreview');
const epPlaceholder = document.getElementById('epThumbPlaceholder');

epZone.addEventListener('click', () => epInput.click());
epInput.addEventListener('change', () => {
    if (epInput.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            epPreview.src = e.target.result;
            epPreview.style.display = 'block';
            epPlaceholder.style.display = 'none';
        };
        reader.readAsDataURL(epInput.files[0]);
    }
});
</script>
@endpush
