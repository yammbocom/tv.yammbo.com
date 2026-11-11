<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Novedades solo para quien ve un genero (la app manda los generos que mas ve cada perfil).
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tv_news', 'genre')) {
            Schema::table('tv_news', function (Blueprint $t) {
                $t->string('genre', 60)->nullable()->after('audience');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tv_news', 'genre')) {
            Schema::table('tv_news', function (Blueprint $t) {
                $t->dropColumn('genre');
            });
        }
    }
};
