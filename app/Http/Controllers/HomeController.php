<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * HomeController
 * Handles public-facing pages: home, about, contact, browse, search.
 */
class HomeController extends Controller
{
    /**
     * Landing homepage with hero, trending, featured, categories.
     */
    public function index(): View
    {
        $trending = Podcast::published()
            ->with(['creator', 'category'])
            ->orderByDesc('total_plays')
            ->take(8)
            ->get();

        $featured = Podcast::published()
            ->featured()
            ->with(['creator', 'category'])
            ->take(6)
            ->get();

        $categories = Category::active()
            ->take(8)
            ->get();

        $latestEpisodes = Episode::published()
            ->with(['podcast.creator', 'podcast.category'])
            ->latest('release_date')
            ->take(6)
            ->get();

        $topCreators = User::where('role', 'creator')
            ->orderByDesc('subscriber_count')
            ->take(6)
            ->get();

        $totalPodcasts  = Podcast::published()->count();
        $totalCreators  = User::where('role', 'creator')->count();
        $totalEpisodes  = Episode::published()->count();

        return view('pages.home', compact(
            'trending', 'featured', 'categories',
            'latestEpisodes', 'topCreators',
            'totalPodcasts', 'totalCreators', 'totalEpisodes'
        ));
    }

    /**
     * Browse all podcasts with filtering and sorting.
     */
    public function browse(Request $request): View
    {
        $query = Podcast::published()->with(['creator', 'category', 'genre']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('genre')) {
            $query->where('genre_id', $request->genre);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        match ($request->get('sort', 'trending')) {
            'latest'  => $query->latest(),
            'popular' => $query->orderByDesc('total_plays'),
            'rating'  => $query->orderByDesc('rating_average'),
            default   => $query->orderByDesc('total_plays'),
        };

        $podcasts   = $query->paginate(16)->withQueryString();
        $categories = Category::active()->get();
        $genres     = collect();

        if ($request->filled('category')) {
            $genres = \App\Models\Genre::active()
                ->where('category_id', $request->category)
                ->get();
        }

        return view('pages.browse', compact('podcasts', 'categories', 'genres'));
    }

    /**
     * AJAX live search endpoint.
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');

        if (strlen($term) < 2) {
            return response()->json(['podcasts' => [], 'episodes' => []]);
        }

        $podcasts = Podcast::published()
            ->search($term)
            ->with('creator')
            ->take(5)
            ->get(['id', 'title', 'slug', 'thumbnail', 'user_id']);

        $episodes = Episode::published()
            ->where('title', 'like', "%{$term}%")
            ->with('podcast')
            ->take(5)
            ->get(['id', 'title', 'slug', 'podcast_id', 'thumbnail']);

        $creators = User::where('role', 'creator')
            ->where('name', 'like', "%{$term}%")
            ->take(3)
            ->get(['id', 'name', 'username', 'avatar']);

        return response()->json([
            'podcasts' => $podcasts->map(fn($p) => [
                'id'        => $p->id,
                'title'     => $p->title,
                'url'       => route('podcasts.show', $p->slug),
                'thumbnail' => $p->thumbnail_url,
                'creator'   => $p->creator->name,
            ]),
            'episodes' => $episodes->map(fn($e) => [
                'id'      => $e->id,
                'title'   => $e->title,
                'url'     => route('episodes.show', $e->slug),
                'podcast' => $e->podcast->title,
            ]),
            'creators' => $creators->map(fn($c) => [
                'id'     => $c->id,
                'name'   => $c->name,
                'url'    => route('creators.show', $c->username ?? $c->id),
                'avatar' => $c->avatar_url,
            ]),
        ]);
    }

    /**
     * Podcast detail page.
     */
    public function showPodcast(string $slug): View
    {
        $podcast = Podcast::where('slug', $slug)
            ->with(['creator', 'category', 'genre', 'ratings'])
            ->firstOrFail();

        $episodes = $podcast->episodes()->published()->paginate(15);

        $isLiked      = false;
        $isFavorited  = false;
        $isSubscribed = false;
        $userRating   = null;

        if (auth()->check()) {
            $user = auth()->user();
            $isLiked      = $podcast->isLikedBy($user);
            $isFavorited  = $user->hasFavorited($podcast);
            $isSubscribed = $user->isSubscribedTo($podcast->creator);
            $userRating   = \App\Models\Rating::where('user_id', $user->id)
                ->where('podcast_id', $podcast->id)->value('rating');
        }

        // Related podcasts (same category)
        $related = Podcast::published()
            ->where('category_id', $podcast->category_id)
            ->where('id', '!=', $podcast->id)
            ->take(4)
            ->get();

        return view('pages.podcast-detail', compact(
            'podcast', 'episodes', 'isLiked', 'isFavorited',
            'isSubscribed', 'userRating', 'related'
        ));
    }

    /**
     * Episode detail with player and comments.
     */
    public function showEpisode(string $slug): View
    {
        $episode = Episode::where('slug', $slug)
            ->with(['podcast.creator', 'comments.user', 'comments.replies.user'])
            ->firstOrFail();

        // Increment play count (simple approach — can be AJAX in production)
        $episode->incrementPlays();

        // Track listening history if logged in
        if (auth()->check()) {
            $episode->trackProgress(auth()->id(), 0);
        }

        $isLiked  = false;
        $progress = 0;

        if (auth()->check()) {
            $user     = auth()->user();
            $isLiked  = $episode->isLikedBy($user);
            $history  = \App\Models\ListeningHistory::where('user_id', $user->id)
                ->where('episode_id', $episode->id)->first();
            $progress = $history ? $history->progress_seconds : 0;
        }

        // Next/previous episodes
        $nextEpisode = Episode::where('podcast_id', $episode->podcast_id)
            ->where('episode_number', '>', $episode->episode_number ?? 0)
            ->published()
            ->first();
        $prevEpisode = Episode::where('podcast_id', $episode->podcast_id)
            ->where('episode_number', '<', $episode->episode_number ?? 0)
            ->published()
            ->latest('episode_number')
            ->first();

        return view('pages.episode-detail', compact(
            'episode', 'isLiked', 'progress', 'nextEpisode', 'prevEpisode'
        ));
    }

    /**
     * Creator public profile.
     */
    public function showCreator(string $username): View
    {
        $creator = User::where('username', $username)
            ->orWhere('id', $username)
            ->where('role', 'creator')
            ->firstOrFail();

        $podcasts = $creator->podcasts()
            ->published()
            ->paginate(12);

        $isSubscribed = auth()->check()
            ? auth()->user()->isSubscribedTo($creator)
            : false;

        $totalPlays   = $creator->podcasts()->sum('total_plays');
        $episodeCount = Episode::whereIn('podcast_id', $creator->podcasts()->pluck('id'))->count();

        return view('pages.creator-profile', compact(
            'creator', 'podcasts', 'isSubscribed', 'totalPlays', 'episodeCount'
        ));
    }

    /**
     * Categories listing page.
     */
    public function categories(): View
    {
        $categories = Category::active()->get();
        return view('pages.categories', compact('categories'));
    }

    /**
     * About page.
     */
    public function about(): View
    {
        return view('pages.about');
    }

    /**
     * Contact page.
     */
    public function contact(): View
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function contactSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Thank you! Your message has been sent. We\'ll get back to you within 24 hours.');
    }

    /**
     * Dashboard redirect based on role.
     */
    public function dashboard(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isCreator()) {
            return redirect()->route('creator.dashboard');
        }
        return redirect()->route('listener.dashboard');
    }

    /**
     * Trending podcasts page.
     */
    public function trending(): View
    {
        $podcasts = Podcast::published()
            ->with(['creator', 'category'])
            ->orderByDesc('total_plays')
            ->paginate(20);

        return view('pages.trending', compact('podcasts'));
    }
}
