<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App de TV: novedades (aviso al abrir + campana), perfiles por cuenta y anuncios propios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_news', function (Blueprint $t) {
            $t->id();
            $t->string('title', 120);
            $t->text('body');
            $t->string('image_url', 500)->nullable();
            // none | podcasts | podcast (action_value = id MPSP...) | account
            $t->string('action', 20)->default('none');
            $t->string('action_value', 120)->nullable();
            // all | trial | paid | free
            $t->string('audience', 10)->default('all');
            $t->boolean('popup')->default(true);
            $t->boolean('active')->default(true);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->timestamps();
            $t->index(['active', 'starts_at']);
        });

        Schema::create('tv_profiles', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('name', 30);
            $t->string('avatar', 500)->nullable();
            $t->unsignedTinyInteger('color')->default(0);
            $t->boolean('kids')->default(false);
            $t->unsignedTinyInteger('position')->default(0);
            $t->timestamps();
        });

        Schema::create('tv_ads', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->string('video_url', 500);
            $t->string('link_text', 80)->nullable();
            $t->unsignedSmallInteger('skip_after')->default(5);
            // all | trial | paid | free
            $t->string('audience', 10)->default('trial');
            $t->unsignedSmallInteger('weight')->default(1);
            $t->boolean('active')->default(true);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->unsignedInteger('impressions')->default(0);
            $t->unsignedInteger('completions')->default(0);
            $t->unsignedInteger('skips')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_ads');
        Schema::dropIfExists('tv_profiles');
        Schema::dropIfExists('tv_news');
    }
};
