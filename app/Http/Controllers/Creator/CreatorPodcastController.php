<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CreatorPodcastController
 * Handles all podcast management for creator role.
 */
class CreatorPodcastController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:creator,admin']);
    }

    // ============================================================
    // CREATOR DASHBOARD
    // ============================================================

    public function dashboard(): View
    {
        $user = Auth::user();

        $totalPodcasts    = $user->podcasts()->count();
        $publishedPodcasts = $user->podcasts()->where('status', 'published')->count();
        $totalEpisodes    = Episode::whereIn('podcast_id', $user->podcasts()->pluck('id'))->count();
        $totalPlays       = $user->podcasts()->sum('total_plays');
        $totalSubscribers = $user->subscribers()->count();

        $recentPodcasts = $user->podcasts()
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $recentEpisodes = Episode::whereIn('podcast_id', $user->podcasts()->pluck('id'))
            ->with('podcast')
            ->latest()
            ->take(5)
            ->get();

        // Top performing episodes
        $topEpisodes = Episode::whereIn('podcast_id', $user->podcasts()->pluck('id'))
            ->orderByDesc('play_count')
            ->with('podcast')
            ->take(5)
            ->get();

        return view('creator.dashboard', compact(
            'totalPodcasts', 'publishedPodcasts', 'totalEpisodes',
            'totalPlays', 'totalSubscribers',
            'recentPodcasts', 'recentEpisodes', 'topEpisodes'
        ));
    }

    // ============================================================
    // PODCAST CRUD
    // ============================================================

    public function indexPodcasts(Request $request): View
    {
        $query = Auth::user()->podcasts()->with('category');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $podcasts   = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::active()->get();

        return view('creator.podcasts.index', compact('podcasts', 'categories'));
    }

    public function createPodcast(): View
    {
        $categories = Category::active()->get();
        $genres     = Genre::active()->get();
        return view('creator.podcasts.create', compact('categories', 'genres'));
    }

    public function storePodcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'category_id' => 'required|exists:categories,id',
            'genre_id'    => 'nullable|exists:genres,id',
            'language'    => 'required|string|max:50',
            'tags'        => 'nullable|string',
            'is_explicit' => 'boolean',
            'status'      => 'required|in:draft,published',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/podcasts', 'public');
        }

        // Parse tags
        $tags = [];
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags);
        }

        Podcast::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'slug'        => Str::slug($validated['title']) . '-' . Str::random(5),
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'genre_id'    => $validated['genre_id'],
            'language'    => $validated['language'],
            'tags'        => $tags,
            'is_explicit' => $request->boolean('is_explicit'),
            'status'      => $validated['status'],
            'thumbnail'   => $thumbnailPath,
        ]);

        return redirect()->route('creator.podcasts.index')
            ->with('success', 'Podcast "' . $validated['title'] . '" created successfully!');
    }

    public function editPodcast(Podcast $podcast): View
    {
        $this->authorizeCreator($podcast);
        $categories = Category::active()->get();
        $genres     = Genre::active()->get();
        return view('creator.podcasts.edit', compact('podcast', 'categories', 'genres'));
    }

    public function updatePodcast(Request $request, Podcast $podcast): RedirectResponse
    {
        $this->authorizeCreator($podcast);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'category_id' => 'required|exists:categories,id',
            'genre_id'    => 'nullable|exists:genres,id',
            'language'    => 'required|string|max:50',
            'tags'        => 'nullable|string',
            'is_explicit' => 'boolean',
            'status'      => 'required|in:draft,published',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $thumbnailPath = $podcast->thumbnail;
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($podcast->thumbnail) {
                Storage::disk('public')->delete($podcast->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/podcasts', 'public');
        }

        $tags = [];
        if ($request->filled('tags')) {
            $tags = array_filter(array_map('trim', explode(',', $request->tags)));
        }

        $podcast->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'genre_id'    => $validated['genre_id'],
            'language'    => $validated['language'],
            'tags'        => $tags,
            'is_explicit' => $request->boolean('is_explicit'),
            'status'      => $validated['status'],
            'thumbnail'   => $thumbnailPath,
        ]);

        return redirect()->route('creator.podcasts.index')
            ->with('success', 'Podcast updated successfully!');
    }

    public function destroyPodcast(Podcast $podcast): RedirectResponse
    {
        $this->authorizeCreator($podcast);

        // Delete files
        if ($podcast->thumbnail) {
            Storage::disk('public')->delete($podcast->thumbnail);
        }

        // Delete episode audio files
        foreach ($podcast->episodes as $episode) {
            if ($episode->audio_file) {
                Storage::disk('public')->delete($episode->audio_file);
            }
        }

        $podcast->delete();
        return redirect()->route('creator.podcasts.index')
            ->with('success', 'Podcast deleted successfully.');
    }

    public function showPodcastStats(Podcast $podcast): View
    {
        $this->authorizeCreator($podcast);

        $episodes = $podcast->episodes()
            ->orderByDesc('play_count')
            ->get();

        $totalPlays    = $episodes->sum('play_count');
        $totalLikes    = $episodes->sum(fn($e) => $e->likes()->count());
        $totalComments = $episodes->sum(fn($e) => $e->allComments()->count());
        $subscribers   = $podcast->creator->subscribers()->count();

        // Monthly plays (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'plays' => $podcast->episodes()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('play_count'),
            ];
        }

        return view('creator.podcasts.stats', compact(
            'podcast', 'episodes', 'totalPlays',
            'totalLikes', 'totalComments', 'subscribers', 'monthlyData'
        ));
    }

    // ============================================================
    // EPISODE CRUD
    // ============================================================

    public function indexEpisodes(Podcast $podcast): View
    {
        $this->authorizeCreator($podcast);
        $episodes = $podcast->episodes()->latest()->paginate(15);
        return view('creator.episodes.index', compact('podcast', 'episodes'));
    }

    public function createEpisode(Podcast $podcast): View
    {
        $this->authorizeCreator($podcast);
        return view('creator.episodes.create', compact('podcast'));
    }

    public function storeEpisode(Request $request, Podcast $podcast): RedirectResponse
    {
        $this->authorizeCreator($podcast);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string|min:10',
            'audio_file'     => 'required|mimes:mp3,wav,ogg,m4a|max:102400',
            'thumbnail'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'season_number'  => 'nullable|integer|min:1',
            'episode_number' => 'nullable|integer|min:1',
            'episode_type'   => 'required|in:full,trailer,bonus',
            'status'         => 'required|in:draft,published',
            'release_date'   => 'nullable|date',
            'is_explicit'    => 'boolean',
            'show_notes'     => 'nullable|string|max:5000',
        ]);

        // Upload audio
        $audioPath = $request->file('audio_file')->store('audio/episodes', 'public');

        // Get audio duration using getID3 or estimate
        $duration = $this->getAudioDuration($request->file('audio_file')->getRealPath());

        // Upload thumbnail
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/episodes', 'public');
        }

        Episode::create([
            'podcast_id'     => $podcast->id,
            'title'          => $validated['title'],
            'slug'           => Str::slug($validated['title']) . '-' . Str::random(5),
            'description'    => $validated['description'],
            'audio_file'     => $audioPath,
            'thumbnail'      => $thumbnailPath,
            'duration'       => $duration,
            'season_number'  => $validated['season_number'] ?? null,
            'episode_number' => $validated['episode_number'] ?? null,
            'episode_type'   => $validated['episode_type'],
            'status'         => $validated['status'],
            'release_date'   => $validated['release_date'] ?? now(),
            'is_explicit'    => $request->boolean('is_explicit'),
            'show_notes'     => $validated['show_notes'] ?? null,
        ]);

        return redirect()->route('creator.podcasts.episodes', $podcast)
            ->with('success', 'Episode uploaded successfully!');
    }

    public function editEpisode(Podcast $podcast, Episode $episode): View
    {
        $this->authorizeCreator($podcast);
        return view('creator.episodes.edit', compact('podcast', 'episode'));
    }

    public function updateEpisode(Request $request, Podcast $podcast, Episode $episode): RedirectResponse
    {
        $this->authorizeCreator($podcast);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string|min:10',
            'audio_file'     => 'nullable|mimes:mp3,wav,ogg,m4a|max:102400',
            'thumbnail'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'season_number'  => 'nullable|integer|min:1',
            'episode_number' => 'nullable|integer|min:1',
            'episode_type'   => 'required|in:full,trailer,bonus',
            'status'         => 'required|in:draft,published',
            'release_date'   => 'nullable|date',
            'is_explicit'    => 'boolean',
            'show_notes'     => 'nullable|string|max:5000',
        ]);

        $audioPath     = $episode->audio_file;
        $thumbnailPath = $episode->thumbnail;
        $duration      = $episode->duration;

        if ($request->hasFile('audio_file')) {
            Storage::disk('public')->delete($episode->audio_file);
            $audioPath = $request->file('audio_file')->store('audio/episodes', 'public');
            $duration  = $this->getAudioDuration($request->file('audio_file')->getRealPath());
        }

        if ($request->hasFile('thumbnail')) {
            if ($episode->thumbnail) {
                Storage::disk('public')->delete($episode->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/episodes', 'public');
        }

        $episode->update([
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'audio_file'     => $audioPath,
            'thumbnail'      => $thumbnailPath,
            'duration'       => $duration,
            'season_number'  => $validated['season_number'] ?? null,
            'episode_number' => $validated['episode_number'] ?? null,
            'episode_type'   => $validated['episode_type'],
            'status'         => $validated['status'],
            'release_date'   => $validated['release_date'] ?? $episode->release_date,
            'is_explicit'    => $request->boolean('is_explicit'),
            'show_notes'     => $validated['show_notes'] ?? null,
        ]);

        return redirect()->route('creator.podcasts.episodes', $podcast)
            ->with('success', 'Episode updated successfully!');
    }

    public function destroyEpisode(Podcast $podcast, Episode $episode): RedirectResponse
    {
        $this->authorizeCreator($podcast);

        Storage::disk('public')->delete($episode->audio_file);
        if ($episode->thumbnail) {
            Storage::disk('public')->delete($episode->thumbnail);
        }

        $episode->delete();

        return back()->with('success', 'Episode deleted.');
    }

    // ============================================================
    // PROFILE
    // ============================================================

    public function profile(): View
    {
        return view('creator.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'bio'       => 'nullable|string|max:1000',
            'website'   => 'nullable|url|max:255',
            'twitter'   => 'nullable|string|max:100',
            'instagram' => 'nullable|string|max:100',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function authorizeCreator(Podcast $podcast): void
    {
        if ($podcast->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'You do not own this podcast.');
        }
    }

    /**
     * Try to get audio duration using built-in PHP functions.
     * Falls back to 0 if unable to determine.
     */
    private function getAudioDuration(string $filePath): int
    {
        try {
            // Attempt to get duration via ffprobe if available
            $output = shell_exec("ffprobe -v quiet -show_entries format=duration -of csv=p=0 " . escapeshellarg($filePath) . " 2>/dev/null");
            if ($output && is_numeric(trim($output))) {
                return (int) round(trim($output));
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return 0;
    }
}
