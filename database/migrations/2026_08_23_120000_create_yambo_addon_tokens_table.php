<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Token por usuario para el manifest premium proxeado (/aio/{token}/manifest.json).
 *
 * Antes el SPA instalaba la URL de AIOStreams tal cual, así que el diálogo
 * "Compartir complemento" la enseñaba entera y con botones de Facebook/X/Reddit:
 * cualquiera podía pegarla en Stremio y tener el premium sin pagar. Con un token
 * por cuenta la URL deja de ser universal y se puede revocar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yambo_addon_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('token', 64)->unique();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('hits')->default(0);
            // {"YYYY-MM-DD": ["<hash ip>", ...]} — sólo el día en curso, para
            // detectar una URL que circula por ahí sin guardar IPs en claro.
            $table->json('ips')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yambo_addon_tokens');
    }
};
