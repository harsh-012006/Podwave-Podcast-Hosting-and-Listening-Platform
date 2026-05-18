<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all engagement tables: subscriptions, likes, favorites,
     * comments, listening history, ratings, notifications.
     */
    public function up(): void
    {
        // -------------------------------------------------------
        // Subscriptions: listeners subscribe to podcast creators
        // -------------------------------------------------------
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscriber_id', 'creator_id']);
        });

        // -------------------------------------------------------
        // Polymorphic Likes — for both podcasts and episodes
        // -------------------------------------------------------
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('likeable'); // likeable_id, likeable_type
            $table->timestamps();
            $table->unique(['user_id', 'likeable_id', 'likeable_type']);
        });

        // -------------------------------------------------------
        // Favorites — listeners save podcasts
        // -------------------------------------------------------
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'podcast_id']);
        });

        // -------------------------------------------------------
        // Comments — on episodes, with threading support
        // -------------------------------------------------------
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->index(['episode_id', 'parent_id']);
        });

        // -------------------------------------------------------
        // Listening History — per-user playback tracking
        // -------------------------------------------------------
        Schema::create('listening_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('progress_seconds')->default(0); // how far they listened
            $table->boolean('completed')->default(false);
            $table->timestamp('listened_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'episode_id']);
        });

        // -------------------------------------------------------
        // Ratings — per-user podcast ratings (1-5 stars)
        // -------------------------------------------------------
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->timestamps();
            $table->unique(['user_id', 'podcast_id']);
        });

        // -------------------------------------------------------
        // Notifications — database notification system
        // -------------------------------------------------------
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // notification class name
            $table->morphs('notifiable'); // notifiable_id, notifiable_type
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // Contact Messages — from contact page
        // -------------------------------------------------------
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // Cache & Queue tables
        // -------------------------------------------------------
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('listening_history');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('subscriptions');
    }
};
