<?php

/*
 * Yammbo TV Service (el .exe de PC) — qué versión se le ofrece a los clientes
 * que ya están instalados.
 *
 * El servicio consulta /updater/check al arrancar. Solo se actualiza si la
 * versión de aquí es ESTRICTAMENTE MAYOR (semver) que la suya, así que dejar
 * esto igual a la versión publicada equivale a "no hay actualización".
 *
 * 🚨 El checksum NO se escribe a mano: lo calcula el servidor sobre el fichero
 * real (ver AppTv\ServiceUpdaterController). Un sha256 copiado a mano que no
 * cuadre haría que TODOS los clientes descarguen 37 MB y los tiren.
 */

return [

    // Versión que se anuncia. Tiene que existir el instalador de abajo.
    'version' => env('YAMBO_SERVICE_VERSION', '0.1.20'),

    // Instalador por sistema operativo. La clave es lo que Rust reporta en
    // `std::env::consts::OS` ("windows", "macos"), no un nombre bonito.
    // La ruta es relativa a public/.
    'installers' => [
        'windows' => env('YAMBO_SERVICE_WIN_INSTALLER', 'download/YammboTV-Service-Setup-v4.exe'),
    ],

    // Nombre de producto que manda el servicio en ?product=. Si no cuadra, 404.
    'product' => 'yammbo-tv-service',
];
