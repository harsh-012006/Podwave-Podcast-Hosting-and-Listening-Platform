<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the podcasts table — represents a podcast channel/show.
     */
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // creator
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->string('language')->default('English');
            $table->json('tags')->nullable(); // array of tags
            $table->enum('status', ['draft', 'published', 'suspended'])->default('draft');
            $table->boolean('is_explicit')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('total_plays')->default(0);
            $table->unsignedBigInteger('total_subscribers')->default(0);
            $table->float('rating_average')->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('rss_feed')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('total_plays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
