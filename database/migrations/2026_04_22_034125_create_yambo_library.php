<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yambo_library', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('meta_id', 191);
            $table->string('meta_type', 32)->default('movie');
            $table->string('meta_name', 255);
            $table->string('meta_poster', 512)->nullable();
            $table->string('meta_background', 512)->nullable();
            $table->string('meta_year', 16)->nullable();
            $table->float('meta_rating')->nullable();
            $table->unsignedInteger('meta_runtime')->nullable();
            $table->json('meta_genres')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('watched_at')->nullable();
            $table->unsignedInteger('watch_progress')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'meta_id', 'meta_type'], 'yambo_library_unique');
            $table->index(['user_id', 'removed_at'], 'yambo_library_active');
        });

        Schema::create('yambo_library_episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('meta_id', 191); // parent series
            $table->unsignedInteger('season');
            $table->unsignedInteger('episode');
            $table->string('episode_name', 255)->nullable();
            $table->date('air_date')->nullable();
            $table->timestamp('watched_at')->nullable();
            $table->unsignedInteger('watch_progress')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'meta_id', 'season', 'episode'], 'yambo_episode_unique');
            $table->index(['user_id', 'air_date'], 'yambo_episode_calendar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yambo_library_episodes');
        Schema::dropIfExists('yambo_library');
    }
};
