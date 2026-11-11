<?php

/*
 * Configuración del addon CO-MX (com.yammbo.comx).
 *
 * data_dir apunta al directorio donde viven las bases SQLite del repo
 * https://github.com/patitogit/CO-MX/ (descargadas con download-comx-data.sh).
 * No se sirven por web: solo las lee ComxAddonController con PDO.
 */

return [
    // Ruta absoluta al directorio con los .db de CO-MX.
    'data_dir' => env('COMX_DATA_DIR', '/home/tv/co-mx-work'),

    // Fuentes expuestas como catálogos. Cada una: fichero de películas, de
    // series y nombre de la columna PK de la tabla `series` (difiere por fuente).
    'sources' => [
        'cuevana' => [
            'movies' => ['cuevana_peliculas.db'],
            'series' => ['cuevana_series.db'],
            'series_col' => 'id_tmdb',
        ],
        'pelispedia' => [
            'movies' => ['pelispedia_peliculas.db'],
            'series' => ['pelispedia_series.db'],
            'series_col' => 'tmdb_id',
        ],
        'pelisjuanita' => [
            'movies' => ['pelisjuanita_peliculas.db'],
            'series' => ['pelisjuanita_series.db'],
            'series_col' => 'id_tmdb',
        ],
    ],
];
