<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Creator\CreatorPodcastController;
use App\Http\Controllers\Listener\ListenerController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PodWave Web Routes
|--------------------------------------------------------------------------
*/

// ============================================================
// PUBLIC ROUTES
// ============================================================

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/browse', [HomeController::class, 'browse'])->name('browse');
Route::get('/trending', [HomeController::class, 'trending'])->name('trending');
Route::get('/categories', [HomeController::class, 'categories'])->name('categories');
Route::get('/search', [HomeController::class, 'search'])->name('search'); // AJAX endpoint

// Podcast & Episode public pages
Route::get('/podcasts/{slug}', [HomeController::class, 'showPodcast'])->name('podcasts.show');
Route::get('/episodes/{slug}', [HomeController::class, 'showEpisode'])->name('episodes.show');
Route::get('/creators/{username}', [HomeController::class, 'showCreator'])->name('creators.show');

// ============================================================
// AUTHENTICATION ROUTES
// ============================================================

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ============================================================
// OAUTH ROUTES (GOOGLE LOGIN)
// ============================================================

Route::get('/auth/google', [OAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [OAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// ============================================================
// DASHBOARD REDIRECT
// ============================================================

Route::middleware('auth')->get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

// ============================================================
// LISTENER ROUTES
// ============================================================

Route::middleware('auth')->prefix('listener')->name('listener.')->group(function () {
    Route::get('/dashboard', [ListenerController::class, 'dashboard'])->name('dashboard');
    Route::get('/favorites', [ListenerController::class, 'favorites'])->name('favorites');
    Route::get('/subscriptions', [ListenerController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/history', [ListenerController::class, 'history'])->name('history');
    Route::delete('/history', [ListenerController::class, 'clearHistory'])->name('history.clear');
    Route::get('/notifications', [ListenerController::class, 'notifications'])->name('notifications');
    Route::get('/profile', [ListenerController::class, 'profile'])->name('profile');
    Route::put('/profile', [ListenerController::class, 'updateProfile'])->name('profile.update');

    // Interactions (AJAX-friendly)
    Route::post('/like', [ListenerController::class, 'toggleLike'])->name('like');
    Route::post('/favorite/{podcast}', [ListenerController::class, 'toggleFavorite'])->name('favorite');
    Route::post('/subscribe/{creator}', [ListenerController::class, 'toggleSubscription'])->name('subscribe');
    Route::post('/progress', [ListenerController::class, 'saveProgress'])->name('progress');
    Route::post('/rate/{podcast}', [ListenerController::class, 'ratePodcast'])->name('rate');

    // Comments
    Route::post('/episodes/{episode}/comments', [ListenerController::class, 'storeComment'])->name('comment.store');
    Route::delete('/comments/{comment}', [ListenerController::class, 'destroyComment'])->name('comment.destroy');
});

// ============================================================
// CREATOR ROUTES
// ============================================================

Route::middleware(['auth', 'role:creator,admin'])->prefix('creator')->name('creator.')->group(function () {
    Route::get('/dashboard', [CreatorPodcastController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CreatorPodcastController::class, 'profile'])->name('profile');
    Route::put('/profile', [CreatorPodcastController::class, 'updateProfile'])->name('profile.update');

    // Podcasts
    Route::get('/podcasts', [CreatorPodcastController::class, 'indexPodcasts'])->name('podcasts.index');
    Route::get('/podcasts/create', [CreatorPodcastController::class, 'createPodcast'])->name('podcasts.create');
    Route::post('/podcasts', [CreatorPodcastController::class, 'storePodcast'])->name('podcasts.store');
    Route::get('/podcasts/{podcast}/edit', [CreatorPodcastController::class, 'editPodcast'])->name('podcasts.edit');
    Route::put('/podcasts/{podcast}', [CreatorPodcastController::class, 'updatePodcast'])->name('podcasts.update');
    Route::delete('/podcasts/{podcast}', [CreatorPodcastController::class, 'destroyPodcast'])->name('podcasts.destroy');
    Route::get('/podcasts/{podcast}/stats', [CreatorPodcastController::class, 'showPodcastStats'])->name('podcasts.stats');

    // Episodes (nested under podcast)
    Route::get('/podcasts/{podcast}/episodes', [CreatorPodcastController::class, 'indexEpisodes'])->name('podcasts.episodes');
    Route::get('/podcasts/{podcast}/episodes/create', [CreatorPodcastController::class, 'createEpisode'])->name('episodes.create');
    Route::post('/podcasts/{podcast}/episodes', [CreatorPodcastController::class, 'storeEpisode'])->name('episodes.store');
    Route::get('/podcasts/{podcast}/episodes/{episode}/edit', [CreatorPodcastController::class, 'editEpisode'])->name('episodes.edit');
    Route::put('/podcasts/{podcast}/episodes/{episode}', [CreatorPodcastController::class, 'updateEpisode'])->name('episodes.update');
    Route::delete('/podcasts/{podcast}/episodes/{episode}', [CreatorPodcastController::class, 'destroyEpisode'])->name('episodes.destroy');
});

// ============================================================
// ADMIN ROUTES
// ============================================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{user}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');

    // Podcasts
    Route::get('/podcasts', [AdminController::class, 'podcasts'])->name('podcasts');
    Route::post('/podcasts/{podcast}/feature', [AdminController::class, 'featurePodcast'])->name('podcasts.feature');
    Route::post('/podcasts/{podcast}/suspend', [AdminController::class, 'suspendPodcast'])->name('podcasts.suspend');
    Route::delete('/podcasts/{podcast}', [AdminController::class, 'destroyPodcast'])->name('podcasts.destroy');

    // Categories
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    // Genres
    Route::get('/genres', [AdminController::class, 'genres'])->name('genres');
    Route::post('/genres', [AdminController::class, 'storeGenre'])->name('genres.store');
    Route::delete('/genres/{genre}', [AdminController::class, 'destroyGenre'])->name('genres.destroy');

    // Comments
    Route::get('/comments', [AdminController::class, 'comments'])->name('comments');
    Route::delete('/comments/{comment}', [AdminController::class, 'destroyComment'])->name('comments.destroy');
    Route::post('/comments/{comment}/approve', [AdminController::class, 'approveComment'])->name('comments.approve');

    // Contact Messages
    Route::get('/messages', [AdminController::class, 'messages'])->name('messages');
    Route::get('/messages/{contactMessage}', [AdminController::class, 'showMessage'])->name('messages.show');
    Route::delete('/messages/{contactMessage}', [AdminController::class, 'destroyMessage'])->name('messages.destroy');
});
