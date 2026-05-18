<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates episodes table — individual podcast episodes.
     */
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('audio_file'); // stored path
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('duration')->default(0); // seconds
            $table->unsignedInteger('season_number')->nullable();
            $table->unsignedInteger('episode_number')->nullable();
            $table->enum('episode_type', ['full', 'trailer', 'bonus'])->default('full');
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->timestamp('release_date')->nullable();
            $table->boolean('is_explicit')->default(false);
            $table->unsignedBigInteger('play_count')->default(0);
            $table->string('show_notes')->nullable();
            $table->json('chapters')->nullable(); // podcast chapters data
            $table->string('transcript')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['podcast_id', 'status']);
            $table->index(['status', 'release_date']);
            $table->index('play_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
