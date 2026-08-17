<?php

/*
 * Rangos de Cloudflare desde los que llega el tráfico al origen.
 *
 * Sin esto Laravel toma como IP del cliente la del edge de Cloudflare, que
 * cambia en cada petición: el rate limiting por IP nunca llega a dispararse
 * (verificado — 12 intentos de login seguidos pasaban sin un solo 429).
 *
 * Se confía en los rangos concretos y no en '*' para que nadie que alcance el
 * origen por otra vía pueda falsear X-Forwarded-For.
 *
 * Refrescar con: curl https://www.cloudflare.com/ips-v4 y .../ips-v6
 * Última actualización: 2026-08-17
 */

return [
    '173.245.48.0/20',
    '103.21.244.0/22',
    '103.22.200.0/22',
    '103.31.4.0/22',
    '141.101.64.0/18',
    '108.162.192.0/18',
    '190.93.240.0/20',
    '188.114.96.0/20',
    '197.234.240.0/22',
    '198.41.128.0/17',
    '162.158.0.0/15',
    '104.16.0.0/13',
    '104.24.0.0/14',
    '172.64.0.0/13',
    '131.0.72.0/22',
    '2400:cb00::/32',
    '2606:4700::/32',
    '2803:f800::/32',
    '2405:b500::/32',
    '2405:8100::/32',
    '2a06:98c0::/29',
    '2c0f:f248::/32',
];
