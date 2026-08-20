<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * El tema dejó de ser elegible cuando salió Wave: ya no hay selector ni
     * tabla que consultar, así que el nombre vive aquí y no en la base de datos.
     */
    private const THEME = 'anchor';

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Monta el tema público: las páginas Folio, sus componentes anónimos y el
     * namespace `theme::` que usan las partials.
     *
     * Esto lo hacía DevDojo\Themes\ThemesServiceProvider, que resolvía la
     * carpeta del tema leyendo la tabla `themes`. Esa tabla se fue con el resto
     * del esquema de Wave y el provider envuelve su boot() en un try/catch
     * mudo, así que se quedó componiendo la ruta `resources/themes//pages`, no
     * la encontró y dejó de montar nada sin registrar un solo error: `/`,
     * `/pricing`, `/help`, `/privacy` y `/terms` empezaron a devolver 404.
     */
    public function boot(): void
    {
        $theme = resource_path('themes/'.self::THEME);

        Folio::path($theme.'/pages')->middleware([
            '*' => [
                //
            ],
        ]);

        // El orden importa: `elements` primero para que <x-container> resuelva
        // ahí, y la raíz después para <x-layouts.marketing> y <x-marketing.*>.
        Blade::anonymousComponentPath($theme.'/components/elements');
        Blade::anonymousComponentPath($theme.'/components');

        $this->loadViewsFrom($theme, 'theme');
    }
}
