<?php

/*
 * Catálogo de complementos curado de Yammbo Tv.
 *
 * Sustituye a la pestaña "Comunidad" de Stremio, que servía 95 complementos
 * desde v3-cinemeta.strem.io: lista que no controlamos, en inglés, con logos
 * repartidos por 53 hosts de terceros (imgur, herokuapp, reddit, workers.dev...)
 * que veían la IP de cada usuario nuestro, y que empujaba a instalar la
 * fontanería de la competencia dentro de un producto de pago.
 *
 * Criterio para entrar aquí:
 *  - Útil para ver cine y series, no para depurar debrid ni medir la IP.
 *  - Sin `configurationRequired`: nada que mandar al usuario a rellenar en una
 *    web ajena.
 *  - Sin torrents ni cuentas de terceros.
 *  - Descripción propia en español e inglés; el texto del autor suele venir
 *    sólo en inglés y a veces con enlaces de donación.
 *
 * `slug` sólo se usa para servir el logo desde nuestro dominio
 * (/addon-logo/{slug}), así el navegador del usuario no pega a i.imgur.com.
 *
 * Antes de añadir uno, comprobar que responde 200 de verdad. La lista de
 * Stremio sigue anunciando complementos muertos: al montar esto, "IMDb Ratings"
 * daba 404 (su app de Railway ya no existe) y "Marvel" daba 503 (servicio de
 * Render suspendido), y los dos seguían apareciendo instalables en Comunidad.
 */

return [

    'catalog_id' => 'yammbo',

    'addons' => [

        [
            'slug' => 'streaming-catalogs',
            'url' => 'https://7a82163c306e-stremio-netflix-catalog-addon.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Catálogos de plataformas', 'en' => 'Streaming Catalogs'],
            'description' => [
                'es' => 'Qué es tendencia ahora mismo en Netflix, HBO Max, Disney+, Apple TV+ y Prime Video.',
                'en' => 'What is trending right now on Netflix, HBO Max, Disney+, Apple TV+ and Prime Video.',
            ],
            'logo' => 'https://play-lh.googleusercontent.com/TBRwjS_qfJCSj1m7zZB93FnpJM5fSpMA_wUlFDLxWAb45T9RmwBvQd5cWR5viJJOhkI',
        ],

        [
            'slug' => 'tu-subtitulo',
            'url' => 'https://58196d6c26cf-stremio-tusubtitulo.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Subtítulos en español (series)', 'en' => 'Spanish subtitles (series)'],
            'description' => [
                'es' => 'Subtítulos de series en español de España y de Latinoamérica, de TuSubtitulo.',
                'en' => 'Series subtitles in European and Latin American Spanish, from TuSubtitulo.',
            ],
            'logo' => 'https://58196d6c26cf-stremio-tusubtitulo.baby-beamup.club/logo',
        ],

        [
            'slug' => 'subtis',
            'url' => 'https://stremio.fly.dev/manifest.json',
            'name' => ['es' => 'Subtítulos en español (películas)', 'en' => 'Spanish subtitles (movies)'],
            'description' => [
                'es' => 'Subtítulos de películas en español, buscados por el archivo que estás viendo.',
                'en' => 'Spanish movie subtitles, matched against the file you are watching.',
            ],
            'logo' => 'https://yelhsmnvfyyjuamxbobs.supabase.co/storage/v1/object/public/assets//stremio.png',
        ],

        [
            'slug' => 'anime-kitsu',
            'url' => 'https://anime-kitsu.strem.fun/manifest.json',
            'name' => ['es' => 'Anime', 'en' => 'Anime'],
            'description' => [
                'es' => 'Catálogo de anime con temporadas, episodios y títulos en japonés e inglés.',
                'en' => 'Anime catalog with seasons, episodes and both Japanese and English titles.',
            ],
            'logo' => 'https://i.imgur.com/7N6XGoO.png',
        ],

        [
            'slug' => 'tmdb',
            'url' => 'https://94c8cb9f702d-tmdb-addon.baby-beamup.club/N4IgTgDgJgRgsgUygSwIYBUCeEEGcQBcoEA9rgC4JiHlgCuCANCADYkDmJhAZqi7kxAxUAYwDWUMCQg8+AgL7Ny00hSq4xCTIRAglKspTC4AwiQB23ZO0LFDVLDh2qjAWigJedFuT0gKmCwIOgC2JB5g5n7kABZ0ITDmqMgsAEKoUOwIAApkyOTIFjrKEK5g1jG+SnEJSSnpmQgAysgAXsEE-iF8LNE1iclpLHTUBLz8CIqsqObsdKhZOniucAAafhRgCKghyLOEANoAuswiqOR8HPgEB6DIUMUhsAB0LOd4VSDk2B0gYQBuyGCzCSIV+ABl3hQAAQAJQQQVQAnwzFwMRIAHcAJLmAASJDBNHok0YdwenXITxgryhn2+Tk6AnKeD8oIhtLhCO2yI26OxeIJHVoDEUZMeL1oCHMKH2Sh+oRIgOBIDZOnQW2lexsqL5OPxhIIwpJYopVOeks1sq+8sZVCBKJVO1+6qlMu1-l1AoNRtFIHu4upJWiNr+iqBrKdOlyEG8qGoOsxesFRJFpL95K+ZqDcoZ-jtLJBkc60dj8Y9ia9QuJvv9ppemG2ZfpvwB4cLBpAAE1G7yK-qq6mTZn6z2c78mfaIx3u3He-z+ynjemAzTZvNFmOFUqp+y1wtlWi+8nDdW07Xh9S3nuN9bcxOC46O5DrwfPQuTyKTiAzhQzHRzL4BAAKwJhiACCWSwucWo4gA4lKWzXF8xJ+FKqAwEEEEIFBBT7J04wCHOWE4VqADq+QxFiVIkXhIAEQetDICI5DwuwhTmAAYikRg6PRfgoGxFwsPCiICFxPhULIEzyEAA/manifest.json',
            'name' => ['es' => 'Fichas ampliadas (TMDB)', 'en' => 'Rich metadata (TMDB)'],
            'description' => [
                'es' => 'Sinopsis, reparto y carátulas traducidas al idioma de la app, de The Movie Database.',
                'en' => 'Synopsis, cast and artwork translated to the app language, from The Movie Database.',
            ],
            'logo' => 'https://94c8cb9f702d-tmdb-addon.baby-beamup.club/logo.png',
        ],

        [
            'slug' => 'tmdb-collections',
            'url' => 'https://61ab9c85a149-tmdb-collections.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Saga completa', 'en' => 'Movie collections'],
            'description' => [
                'es' => 'Agrupa las películas de una misma saga y te deja verlas en orden.',
                'en' => 'Groups the movies of a saga together so you can watch them in order.',
            ],
            'logo' => 'https://github.com/youchi1/tmdb-collections/raw/main/Images/logo.png',
        ],

        [
            'slug' => 'imdb-catalogs',
            'url' => 'https://1fe84bc728af-imdb-catalogs.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Listas de IMDb', 'en' => 'IMDb lists'],
            'description' => [
                'es' => 'El Top 250 y las listas más populares de IMDb, como filas del inicio.',
                'en' => 'The Top 250 and the most popular IMDb lists, as rows on the home screen.',
            ],
            'logo' => 'https://londonfeministfilmfestival.files.wordpress.com/2017/05/imdb-logo.png',
        ],

        [
            'slug' => 'rotten-tomatoes',
            'url' => 'https://7a82163c306e-rottentomatoes.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Rotten Tomatoes', 'en' => 'Rotten Tomatoes'],
            'description' => [
                'es' => 'Las listas de Certified Fresh: lo mejor valorado por la crítica para ver en casa.',
                'en' => 'The Certified Fresh lists: the best rated by critics to watch at home.',
            ],
            'logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/45/Rotten_Tomatoes_alternative_logo.svg',
        ],

        [
            'slug' => 'watch-next',
            'url' => 'https://099757617587-watch-next.baby-beamup.club/manifest.json',
            'name' => ['es' => 'Y ahora qué veo', 'en' => 'Watch next'],
            'description' => [
                'es' => 'Sugiere títulos parecidos justo debajo de lo que estás mirando.',
                'en' => 'Suggests similar titles right below whatever you are looking at.',
            ],
            'logo' => 'https://myth-115.github.io/Dummy1/logo.png',
        ],

        [
            'slug' => 'mubi',
            'url' => 'https://mubi2stremio.adiba.ro/manifest.json',
            'name' => ['es' => 'Cine de autor (MUBI)', 'en' => 'Arthouse cinema (MUBI)'],
            'description' => [
                'es' => 'La película del día y el archivo de MUBI, para salir de las novedades de siempre.',
                'en' => 'MUBI film of the day and archive, to get out of the usual new releases.',
            ],
            'logo' => 'https://mubi2stremio.adiba.ro/img/mubi2stremio.png',
        ],

        [
            'slug' => 'aftercredits',
            'url' => 'https://aftercredits.almosteffective.com/manifest.json',
            'name' => ['es' => '¿Hay escena post-créditos?', 'en' => 'Is there a post-credits scene?'],
            'description' => [
                'es' => 'Te dice si merece la pena quedarse hasta el final de los créditos.',
                'en' => 'Tells you whether it is worth staying until the end of the credits.',
            ],
            'logo' => null,
        ],

    ],
];
