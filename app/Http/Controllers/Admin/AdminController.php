<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * AdminController
 * Full admin panel: dashboard, user/podcast/category management.
 */
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    // ============================================================
    // DASHBOARD
    // ============================================================

    public function dashboard(): View
    {
        $stats = [
            'total_users'     => User::count(),
            'total_creators'  => User::where('role', 'creator')->count(),
            'total_listeners' => User::where('role', 'listener')->count(),
            'total_podcasts'  => Podcast::count(),
            'published'       => Podcast::where('status', 'published')->count(),
            'total_episodes'  => Episode::count(),
            'total_plays'     => Episode::sum('play_count'),
            'total_comments'  => Comment::count(),
            'flagged'         => Comment::where('is_flagged', true)->count(),
            'messages'        => ContactMessage::where('is_read', false)->count(),
            'banned_users'    => User::where('is_banned', true)->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentPodcasts = Podcast::with('creator')->latest()->take(5)->get();
        $topPodcasts = Podcast::orderByDesc('total_plays')->with('creator')->take(5)->get();

        // New users per month (last 6 months)
        $userGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $userGrowth[] = [
                'month' => $month->format('M'),
                'count' => User::whereMonth('created_at', $month->month)
                               ->whereYear('created_at', $month->year)->count(),
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentPodcasts', 'topPodcasts', 'userGrowth'));
    }

    // ============================================================
    // USER MANAGEMENT
    // ============================================================

    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_banned', $request->status === 'banned');
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user): View
    {
        $podcasts = $user->podcasts()->with('category')->latest()->take(10)->get();
        return view('admin.users.show', compact('user', 'podcasts'));
    }

    public function banUser(Request $request, User $user): RedirectResponse
    {
        $request->validate(['ban_reason' => 'required|string|max:500']);

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot ban an admin user.');
        }

        $user->update([
            'is_banned'  => true,
            'ban_reason' => $request->ban_reason,
            'banned_at'  => now(),
        ]);

        return back()->with('success', "User \"{$user->name}\" has been banned.");
    }

    public function unbanUser(User $user): RedirectResponse
    {
        $user->update([
            'is_banned'  => false,
            'ban_reason' => null,
            'banned_at'  => null,
        ]);

        return back()->with('success', "User \"{$user->name}\" has been unbanned.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot delete an admin user.');
        }
        // Delete avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => 'required|in:admin,creator,listener']);
        $user->update(['role' => $request->role]);
        return back()->with('success', "Role updated to {$request->role}.");
    }

    // ============================================================
    // PODCAST MANAGEMENT
    // ============================================================

    public function podcasts(Request $request): View
    {
        $query = Podcast::with('creator', 'category');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $podcasts   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::active()->get();

        return view('admin.podcasts.index', compact('podcasts', 'categories'));
    }

    public function featurePodcast(Podcast $podcast): RedirectResponse
    {
        $podcast->update(['is_featured' => !$podcast->is_featured]);
        $status = $podcast->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Podcast {$status}.");
    }

    public function suspendPodcast(Podcast $podcast): RedirectResponse
    {
        $podcast->update(['status' => 'suspended']);
        return back()->with('success', 'Podcast suspended.');
    }

    public function destroyPodcast(Podcast $podcast): RedirectResponse
    {
        if ($podcast->thumbnail) {
            Storage::disk('public')->delete($podcast->thumbnail);
        }
        $podcast->delete();
        return redirect()->route('admin.podcasts')->with('success', 'Podcast deleted.');
    }

    // ============================================================
    // CATEGORY MANAGEMENT
    // ============================================================

    public function categories(): View
    {
        $categories = Category::get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:categories,name',
            'icon'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        Category::create([
            'name'  => $request->name,
            'icon'  => $request->icon ?? 'bi-mic-fill',
            'color' => $request->color ?? '#8B5CF6',
        ]);

        return back()->with('success', 'Category created!');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'icon'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($request->only('name', 'icon', 'color', 'is_active'));
        return back()->with('success', 'Category updated!');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ============================================================
    // GENRE MANAGEMENT
    // ============================================================

    public function genres(): View
    {
        $genres     = Genre::with('category')->get();
        $categories = Category::active()->get();
        return view('admin.genres.index', compact('genres', 'categories'));
    }

    public function storeGenre(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Genre::create($request->only('name', 'category_id'));
        return back()->with('success', 'Genre created!');
    }

    public function destroyGenre(Genre $genre): RedirectResponse
    {
        $genre->delete();
        return back()->with('success', 'Genre deleted.');
    }

    // ============================================================
    // COMMENT MODERATION
    // ============================================================

    public function comments(Request $request): View
    {
        $query = Comment::with('user', 'episode.podcast');

        if ($request->filled('flagged')) {
            $query->where('is_flagged', true);
        }

        $comments = $query->latest()->paginate(20)->withQueryString();
        return view('admin.comments.index', compact('comments'));
    }

    public function destroyComment(Comment $comment): RedirectResponse
    {
        $comment->delete();
        return back()->with('success', 'Comment removed.');
    }

    public function approveComment(Comment $comment): RedirectResponse
    {
        $comment->update(['is_flagged' => false, 'is_approved' => true]);
        return back()->with('success', 'Comment approved.');
    }

    // ============================================================
    // CONTACT MESSAGES
    // ============================================================

    public function messages(): View
    {
        $messages = ContactMessage::latest()->paginate(20);
        return view('admin.messages.index', compact('messages'));
    }

    public function showMessage(ContactMessage $contactMessage): View
    {
        $contactMessage->update(['is_read' => true, 'read_at' => now()]);
        return view('admin.messages.show', compact('contactMessage'));
    }

    public function destroyMessage(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();
        return redirect()->route('admin.messages')->with('success', 'Message deleted.');
    }
}
