/**
 * PodWave — Main JavaScript
 * Handles: global audio player, live search, AJAX likes/favorites/subscriptions,
 * toast notifications, lazy loading, progress saving.
 */

'use strict';

/* ================================================================
   UTILITIES
   ================================================================ */

/** Get CSRF token from meta tag */
function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/** Show a toast notification */
function showToast(message, type = 'success') {
  const existing = document.querySelector('.pw-toast-container');
  if (existing) existing.remove();

  const container = document.createElement('div');
  container.className = 'pw-toast-container';

  const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
  container.innerHTML = `
    <div class="pw-toast pw-toast-${type} show">
      <i class="bi ${icon}"></i>
      <span>${message}</span>
      <button onclick="this.parentElement.remove()" class="pw-toast-close">&times;</button>
    </div>
  `;
  document.body.appendChild(container);
  setTimeout(() => container.remove(), 4000);
}

/** Format seconds as M:SS or H:MM:SS */
function formatTime(seconds) {
  if (!seconds || isNaN(seconds)) return '0:00';
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = Math.floor(seconds % 60);
  if (h > 0) return `${h}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
  return `${m}:${s.toString().padStart(2,'0')}`;
}

/** Debounce function */
function debounce(fn, delay) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

/* ================================================================
   GLOBAL AUDIO PLAYER
   ================================================================ */

const Player = (() => {
  const bar       = document.getElementById('audioPlayer');
  const audio     = document.getElementById('audioElement');
  const playIcon  = document.getElementById('playerPlayIcon');
  const playBtn   = document.getElementById('playerPlayPause');
  const seekBar   = document.getElementById('playerSeek');
  const currTime  = document.getElementById('playerCurrentTime');
  const durTime   = document.getElementById('playerDuration');
  const title     = document.getElementById('playerTitle');
  const podcast   = document.getElementById('playerPodcast');
  const thumbnail = document.getElementById('playerThumbnail');
  const volSlider = document.getElementById('volumeSlider');
  const volIcon   = document.getElementById('volumeIcon');
  const speedBtn  = document.getElementById('playerSpeed');
  const closeBtn  = document.getElementById('playerClose');
  const prevBtn   = document.getElementById('playerPrev');
  const nextBtn   = document.getElementById('playerNext');
  const fwdBtn    = document.getElementById('playerForward');
  const bwdBtn    = document.getElementById('playerBackward');

  if (!bar) return {};

  let currentEpisodeId = null;
  let saveTimer = null;
  const speeds = [1, 1.25, 1.5, 1.75, 2, 0.75];
  let speedIdx = 0;

  // Play buttons in page: .pw-play-btn-sm and .pw-play-btn-lg
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-audio]');
    if (!btn) return;

    const audioSrc    = btn.dataset.audio;
    const epTitle     = btn.dataset.title || 'Episode';
    const podTitle    = btn.dataset.podcast || '';
    const thumb       = btn.dataset.thumbnail || '';
    const episodeId   = btn.dataset.episodeId || null;

    if (!audioSrc) {
      showToast('Audio file not available for this episode.', 'error');
      return;
    }

    play(audioSrc, epTitle, podTitle, thumb, episodeId);
  });

  function play(src, epTitle, podTitle, thumb, episodeId) {
    audio.src = src;
    title.textContent   = epTitle;
    podcast.textContent = podTitle;
    thumbnail.src       = thumb;
    currentEpisodeId    = episodeId;

    bar.style.display = 'block';
    document.body.style.paddingBottom = '88px';

    audio.play().then(() => {
      playIcon.className = 'bi bi-pause-circle-fill';
    }).catch(err => {
      console.warn('Audio play failed:', err);
      showToast('Unable to play audio. Check your connection.', 'error');
    });
  }

  // Play/pause toggle
  playBtn?.addEventListener('click', () => {
    if (audio.paused) {
      audio.play();
      playIcon.className = 'bi bi-pause-circle-fill';
    } else {
      audio.pause();
      playIcon.className = 'bi bi-play-circle-fill';
    }
  });

  // Progress tracking
  audio?.addEventListener('timeupdate', () => {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    seekBar.value = pct;
    currTime.textContent = formatTime(audio.currentTime);

    // Save progress every 10 seconds
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveProgress(), 10000);
  });

  audio?.addEventListener('loadedmetadata', () => {
    durTime.textContent = formatTime(audio.duration);
    seekBar.max = 100;
  });

  audio?.addEventListener('ended', () => {
    playIcon.className = 'bi bi-play-circle-fill';
    saveProgress();
  });

  // Seek
  seekBar?.addEventListener('input', () => {
    if (audio.duration) {
      audio.currentTime = (seekBar.value / 100) * audio.duration;
    }
  });

  // Volume
  volSlider?.addEventListener('input', () => {
    audio.volume = volSlider.value;
    volIcon.className = audio.volume === 0 ? 'bi bi-volume-mute-fill'
                     : audio.volume < 0.5  ? 'bi bi-volume-down-fill'
                     : 'bi bi-volume-up-fill';
  });

  // Speed
  speedBtn?.addEventListener('click', () => {
    speedIdx = (speedIdx + 1) % speeds.length;
    audio.playbackRate = speeds[speedIdx];
    speedBtn.textContent = `${speeds[speedIdx]}×`;
  });

  // Skip buttons
  bwdBtn?.addEventListener('click', () => { audio.currentTime = Math.max(0, audio.currentTime - 15); });
  fwdBtn?.addEventListener('click', () => { audio.currentTime = Math.min(audio.duration || 999, audio.currentTime + 30); });

  // Close player
  closeBtn?.addEventListener('click', () => {
    audio.pause();
    bar.style.display = 'none';
    document.body.style.paddingBottom = '';
    saveProgress();
  });

  // Keyboard controls
  document.addEventListener('keydown', (e) => {
    if (e.target.matches('input, textarea, select')) return;
    if (!bar || bar.style.display === 'none') return;

    if (e.code === 'Space') {
      e.preventDefault();
      playBtn.click();
    } else if (e.code === 'ArrowLeft') {
      audio.currentTime = Math.max(0, audio.currentTime - 10);
    } else if (e.code === 'ArrowRight') {
      audio.currentTime = Math.min(audio.duration || 999, audio.currentTime + 10);
    }
  });

  // Save listening progress to server
  async function saveProgress() {
    if (!currentEpisodeId || !audio.currentTime) return;
    try {
      await fetch('/listener/progress', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrf(),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          episode_id: currentEpisodeId,
          progress_seconds: Math.floor(audio.currentTime),
        }),
      });
    } catch(e) { /* silent */ }
  }

  return { play };
})();

/* ================================================================
   LIVE SEARCH
   ================================================================ */

const SearchWidget = (() => {
  const input    = document.getElementById('globalSearch');
  const dropdown = document.getElementById('searchDropdown');

  if (!input || !dropdown) return {};

  const search = debounce(async (term) => {
    if (term.length < 2) {
      dropdown.classList.add('d-none');
      return;
    }

    try {
      const res  = await fetch(`/search?q=${encodeURIComponent(term)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();
      render(data);
    } catch(e) {
      dropdown.classList.add('d-none');
    }
  }, 300);

  function render({ podcasts = [], episodes = [], creators = [] }) {
    if (!podcasts.length && !episodes.length && !creators.length) {
      dropdown.innerHTML = `<div class="pw-search-item text-muted" style="cursor:default;">No results found</div>`;
      dropdown.classList.remove('d-none');
      return;
    }

    let html = '';

    if (podcasts.length) {
      html += `<div class="pw-search-section-title">Podcasts</div>`;
      podcasts.forEach(p => {
        html += `
          <a href="${p.url}" class="pw-search-item">
            <img src="${p.thumbnail}" class="pw-search-thumb" alt="">
            <div>
              <div class="pw-search-item-title">${escape(p.title)}</div>
              <div class="pw-search-item-sub">${escape(p.creator)}</div>
            </div>
          </a>`;
      });
    }

    if (episodes.length) {
      html += `<div class="pw-search-section-title">Episodes</div>`;
      episodes.forEach(e => {
        html += `
          <a href="${e.url}" class="pw-search-item">
            <i class="bi bi-play-circle text-muted" style="font-size:1.5rem;width:38px;text-align:center;"></i>
            <div>
              <div class="pw-search-item-title">${escape(e.title)}</div>
              <div class="pw-search-item-sub">${escape(e.podcast)}</div>
            </div>
          </a>`;
      });
    }

    if (creators.length) {
      html += `<div class="pw-search-section-title">Creators</div>`;
      creators.forEach(c => {
        html += `
          <a href="${c.url}" class="pw-search-item">
            <img src="${c.avatar}" class="pw-search-thumb" style="border-radius:50%;" alt="">
            <div>
              <div class="pw-search-item-title">${escape(c.name)}</div>
              <div class="pw-search-item-sub">Creator</div>
            </div>
          </a>`;
      });
    }

    dropdown.innerHTML = html;
    dropdown.classList.remove('d-none');
  }

  function escape(str) {
    return String(str).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[m]);
  }

  input.addEventListener('input', (e) => search(e.target.value.trim()));
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') dropdown.classList.add('d-none');
  });

  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('d-none');
    }
  });
})();

/* ================================================================
   LIKE BUTTON (polymorphic)
   ================================================================ */

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('#likeBtn');
  if (!btn) return;

  const type   = btn.dataset.type;
  const id     = btn.dataset.id;
  const isLiked = btn.dataset.liked === 'true';

  try {
    const res  = await fetch('/listener/like', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': getCsrf(),
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ likeable_type: type, likeable_id: id }),
    });
    const data = await res.json();

    btn.dataset.liked = data.liked ? 'true' : 'false';
    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = data.liked
        ? 'bi bi-hand-thumbs-up-fill text-accent'
        : 'bi bi-hand-thumbs-up';
    }
    const countEl = document.getElementById('likeCount');
    if (countEl) countEl.textContent = data.count;

    showToast(data.liked ? 'Added to liked!' : 'Removed from liked.');
  } catch(err) {
    showToast('Please log in to like.', 'error');
  }
});

/* ================================================================
   IMAGE LAZY LOADING
   ================================================================ */

if ('IntersectionObserver' in window) {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }
        obs.unobserve(img);
      }
    });
  }, { rootMargin: '200px' });

  document.querySelectorAll('img[data-src]').forEach(img => obs.observe(img));
}

/* ================================================================
   CONFIRM DIALOGS (replace native confirm with nicer UX)
   ================================================================ */

document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;

  const message = btn.dataset.confirm || 'Are you sure?';
  if (!confirm(message)) e.preventDefault();
});

/* ================================================================
   FORM SUBMIT LOADING STATE
   ================================================================ */

document.querySelectorAll('form[data-loading]').forEach(form => {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Loading…`;
    }
  });
});

/* ================================================================
   BOOTSTRAP TOOLTIPS INIT
   ================================================================ */

document.querySelectorAll('[title]').forEach(el => {
  try { new bootstrap.Tooltip(el, { trigger: 'hover', delay: { show: 500, hide: 100 } }); }
  catch(e) {}
});

/* ================================================================
   AUTO-DISMISS ALERTS
   ================================================================ */

setTimeout(() => {
  document.querySelectorAll('.pw-toast').forEach(t => {
    t.style.opacity = '0';
    t.style.transition = 'opacity 0.5s';
    setTimeout(() => t.remove(), 500);
  });
}, 5000);

/* ================================================================
   MOBILE SEARCH TOGGLE
   ================================================================ */

const mobileSearchToggle = document.getElementById('mobileSearchToggle');
const mobileSearchBar    = document.getElementById('mobileSearchBar');

mobileSearchToggle?.addEventListener('click', () => {
  mobileSearchBar?.classList.toggle('d-none');
  mobileSearchBar?.querySelector('input')?.focus();
});

/* ================================================================
   SMOOTH SCROLL FOR ANCHOR LINKS
   ================================================================ */

document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', (e) => {
    const target = document.querySelector(link.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

/* ================================================================
   INFINITE SCROLL (for browse pages with .pw-infinite-container)
   ================================================================ */

const infiniteContainer = document.querySelector('.pw-infinite-container');
const infiniteLoader    = document.querySelector('.pw-infinite-loader');
const nextPageLink      = document.querySelector('.pw-next-page-url');

if (infiniteContainer && nextPageLink) {
  let loading = false;
  let nextUrl = nextPageLink.dataset.url;

  const scrollObs = new IntersectionObserver(async (entries) => {
    if (!entries[0].isIntersecting || loading || !nextUrl) return;
    loading = true;
    if (infiniteLoader) infiniteLoader.classList.remove('d-none');

    try {
      const res  = await fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const html = await res.text();
      const parser   = new DOMParser();
      const doc      = parser.parseFromString(html, 'text/html');
      const newItems = doc.querySelectorAll('.pw-infinite-item');
      const newNext  = doc.querySelector('.pw-next-page-url');

      newItems.forEach(item => {
        item.style.opacity = '0';
        infiniteContainer.appendChild(item);
        requestAnimationFrame(() => {
          item.style.transition = 'opacity 0.4s';
          item.style.opacity = '1';
        });
      });

      nextUrl = newNext?.dataset.url || null;
    } catch(err) {
      console.warn('Infinite scroll error:', err);
    } finally {
      loading = false;
      if (infiniteLoader) infiniteLoader.classList.add('d-none');
    }
  }, { threshold: 0.1 });

  if (infiniteLoader) scrollObs.observe(infiniteLoader);
}

/* ================================================================
   WAVEFORM ANIMATION (decorative, for episode player pages)
   ================================================================ */

function animateWaveform(canvas) {
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width = canvas.offsetWidth;
  const H = canvas.height = 60;
  const bars = 60;
  let frame;

  function draw() {
    ctx.clearRect(0, 0, W, H);
    const barW = W / bars;
    for (let i = 0; i < bars; i++) {
      const h = Math.random() * H * 0.8 + H * 0.1;
      const alpha = 0.4 + Math.random() * 0.6;
      ctx.fillStyle = `rgba(139,92,246,${alpha})`;
      ctx.beginPath();
      ctx.roundRect(i * barW + 1, (H - h) / 2, barW - 2, h, 2);
      ctx.fill();
    }
    frame = requestAnimationFrame(draw);
  }

  return {
    start: () => draw(),
    stop: () => cancelAnimationFrame(frame),
  };
}

// Start waveform if audio is playing on episode page
const inlineAudio = document.getElementById('inlineAudio');
const waveCanvas  = document.getElementById('waveformCanvas');

if (inlineAudio && waveCanvas) {
  const waveform = animateWaveform(waveCanvas);
  inlineAudio.addEventListener('play',  () => waveform.start());
  inlineAudio.addEventListener('pause', () => waveform.stop());
  inlineAudio.addEventListener('ended', () => waveform.stop());
}
