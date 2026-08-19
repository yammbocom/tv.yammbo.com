<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/*
 * Helpers globales propios. Antes los cargaba el WaveServiceProvider desde
 * wave/src/Helpers/globals.php; de aquel fichero solo sobrevive setting(), que
 * es el único que consumía el tema (título y og:site_name en partials/head,
 * Google Analytics en partials/footer-scripts).
 */

if (! function_exists('setting')) {
    /**
     * Lee un ajuste del sitio. La tabla entera se cachea, así que una página
     * con varias llamadas hace una sola consulta.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $settingsCache = null;

        if ($settingsCache === null) {
            $settingsCache = Cache::rememberForever('yambo_settings', function () {
                return Setting::pluck('value', 'key')->toArray();
            });
        }

        return $settingsCache[$key] ?? $default;
    }
}
