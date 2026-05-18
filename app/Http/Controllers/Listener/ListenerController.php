<?php

namespace App\Http\Controllers\Listener;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\Like;
use App\Models\ListeningHistory;
use App\Models\Podcast;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * ListenerController
 * All listener interaction features: dashboard, likes, favorites,
 * subscriptions, comments, history, profile updates.
 */
class ListenerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================================================
    // LISTENER DASHBOARD
    // ============================================================

    public function dashboard(): View
    {
        $user = Auth::user();

        $history = $user->listeningHistory()
            ->with('episode.podcast.creator')
            ->take(10)
            ->get();

        $favorites = $user->favorites()
            ->with('creator', 'category')
            ->latest('favorites.created_at')
            ->take(8)
            ->get();

        $subscriptions = $user->subscriptions()
            ->with(['podcasts' => fn($q) => $q->published()->latest()->take(3)])
            ->take(10)
            ->get();

        // Recommended podcasts: same categories as favorites, or trending
        $recommendedCategories = $user->favorites()->pluck('category_id')->unique()->filter();
        $recommended = Podcast::published()
            ->when($recommendedCategories->isNotEmpty(), function ($q) use ($recommendedCategories) {
                $q->whereIn('category_id', $recommendedCategories);
            })
            ->whereNotIn('id', $user->favorites()->pluck('podcast_id'))
            ->with('creator', 'category')
            ->orderByDesc('total_plays')
            ->take(8)
            ->get();

        $notifications = collect(); // MongoDB doesn't use SQL notifications table

        return view('listener.dashboard', compact(
            'history', 'favorites', 'subscriptions', 'recommended', 'notifications'
        ));
    }

    // ============================================================
    // LIKES
    // ============================================================

    public function toggleLike(Request $request): JsonResponse
    {
        $request->validate([
            'likeable_type' => 'required|in:podcast,episode',
            'likeable_id'   => 'required|integer',
        ]);

        $type  = $request->likeable_type === 'podcast' ? Podcast::class : Episode::class;
        $model = $type::findOrFail($request->likeable_id);
        $user  = Auth::user();

        $existing = Like::where([
            'user_id'       => $user->id,
            'likeable_type' => $type,
            'likeable_id'   => $model->id,
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id'       => $user->id,
                'likeable_type' => $type,
                'likeable_id'   => $model->id,
            ]);
            $liked = true;
        }

        $count = Like::where(['likeable_type' => $type, 'likeable_id' => $model->id])->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    // ============================================================
    // FAVORITES
    // ============================================================

    public function toggleFavorite(Podcast $podcast): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasFavorited($podcast)) {
            $user->favorites()->detach($podcast->id);
            $favorited = false;
            $message   = 'Removed from favorites.';
        } else {
            $user->favorites()->attach($podcast->id);
            $favorited = true;
            $message   = 'Added to favorites!';
        }

        if (request()->expectsJson()) {
            return response()->json(['favorited' => $favorited, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    public function favorites(): View
    {
        $favorites = Auth::user()->favorites()
            ->with(['creator', 'category'])
            ->paginate(16);

        return view('listener.favorites', compact('favorites'));
    }

    // ============================================================
    // SUBSCRIPTIONS
    // ============================================================

    public function toggleSubscription(User $creator): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if ($user->id === $creator->id) {
            return response()->json(['error' => 'Cannot subscribe to yourself.'], 422);
        }

        if ($user->isSubscribedTo($creator)) {
            $user->subscriptions()->detach($creator->id);
            $subscribed = false;
        } else {
            $user->subscriptions()->attach($creator->id);
            $subscribed = true;
        }

        $count = $creator->subscribers()->count();

        if (request()->expectsJson()) {
            return response()->json(['subscribed' => $subscribed, 'count' => $count]);
        }
        return back()->with('success', $subscribed ? 'Subscribed!' : 'Unsubscribed.');
    }

    public function subscriptions(): View
    {
        $subscriptions = Auth::user()->subscriptions()
            ->paginate(16);

        return view('listener.subscriptions', compact('subscriptions'));
    }

    // ============================================================
    // COMMENTS
    // ============================================================

    public function storeComment(Request $request, Episode $episode): RedirectResponse
    {
        $request->validate([
            'body'      => 'required|string|min:2|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create([
            'episode_id' => $episode->id,
            'user_id'    => Auth::id(),
            'parent_id'  => $request->parent_id,
            'body'       => $request->body,
        ]);

        return back()->with('success', 'Comment posted!');
    }

    public function destroyComment(Comment $comment): RedirectResponse
    {
        if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }

    // ============================================================
    // LISTENING HISTORY
    // ============================================================

    public function history(): View
    {
        $history = Auth::user()->listeningHistory()
            ->with('episode.podcast.creator')
            ->paginate(20);

        return view('listener.history', compact('history'));
    }

    public function saveProgress(Request $request): JsonResponse
    {
        $request->validate([
            'episode_id'       => 'required|exists:episodes,id',
            'progress_seconds' => 'required|integer|min:0',
        ]);

        $episode = Episode::findOrFail($request->episode_id);

        ListeningHistory::updateOrCreate(
            ['user_id' => Auth::id(), 'episode_id' => $episode->id],
            [
                'progress_seconds' => $request->progress_seconds,
                'completed'        => $request->progress_seconds >= ($episode->duration * 0.9),
                'listened_at'      => now(),
            ]
        );

        return response()->json(['saved' => true]);
    }

    public function clearHistory(): RedirectResponse
    {
        Auth::user()->listeningHistory()->delete();
        return back()->with('success', 'Listening history cleared.');
    }

    // ============================================================
    // RATINGS
    // ============================================================

    public function ratePodcast(Request $request, Podcast $podcast): JsonResponse
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);

        Rating::updateOrCreate(
            ['user_id' => Auth::id(), 'podcast_id' => $podcast->id],
            ['rating' => $request->rating]
        );

        $podcast->recalculateRating();

        return response()->json([
            'average' => $podcast->fresh()->rating_average,
            'count'   => $podcast->fresh()->rating_count,
        ]);
    }

    // ============================================================
    // PROFILE
    // ============================================================

    public function profile(): View
    {
        return view('listener.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'bio'       => 'nullable|string|max:1000',
            'website'   => 'nullable|url|max:255',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated!');
    }

    // ============================================================
    // NOTIFICATIONS
    // ============================================================

    public function notifications(): View
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        Auth::user()->unreadNotifications->markAsRead();

        return view('listener.notifications', compact('notifications'));
    }
}
