<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('topic', 64)->default('yammbo_news');
            $table->string('title');
            $table->text('body');
            $table->string('click_url')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('success')->default(false)->index();
            $table->string('fcm_message_id')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_logs');
    }
};
