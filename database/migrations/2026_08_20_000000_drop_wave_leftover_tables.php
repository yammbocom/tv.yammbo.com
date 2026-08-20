<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retira las tablas que quedaron sin dueño cuando se desmontó Wave: blog,
 * changelog, páginas, temas, formularios y claves de API. Ningún controlador,
 * modelo ni vista las toca; las únicas referencias vivas eran los seeders del
 * paquete, que se van en el mismo commit.
 *
 * `password_resets` NO entra en la lista aunque también viene de Wave: sigue
 * declarada en config/auth.php y el broker de contraseñas escribe en ella.
 *
 * El orden respeta las claves foráneas (hijas antes que padres). Todas las FK
 * implicadas quedan dentro de este conjunto o apuntan a `users`, que se queda.
 *
 * Volcado previo con datos: /root/restore/db-dumps/tv_yammbo-pre-cleanup-20260820.sql.gz
 */
return new class extends Migration
{
    private array $tablas = [
        'changelog_user',
        'changelogs',
        'form_entries',
        'forms',
        'theme_options',
        'themes',
        'posts',
        'categories',
        'pages',
        'profile_key_values',
        'api_keys',
        'notifications',
        'social_provider_user',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }

    /**
     * Sin vuelta atrás: recrear el esquema de Wave sin el paquete sería
     * inventárselo. Para recuperarlas, restaurar del volcado citado arriba.
     */
    public function down(): void
    {
        //
    }
};
