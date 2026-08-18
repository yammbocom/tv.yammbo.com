<?php

/*
 * Config propia de Yammbo Tv. Reemplaza las claves de config/wave.php que
 * usa el código nuestro, para poder retirar Wave sin dejar lecturas huérfanas.
 *
 * Las claves de Stripe se leen con env() aquí, en el fichero de config, que es
 * donde sigue funcionando con `config:cache`; llamar a env() desde un
 * controlador devuelve null cuando la config está cacheada.
 */

return [

    // Rol Spatie que se asigna al crear una cuenta y al cancelar una suscripción.
    'default_user_role' => 'registered',

    'billing_provider' => env('BILLING_PROVIDER', 'stripe'),

    'stripe' => [
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
