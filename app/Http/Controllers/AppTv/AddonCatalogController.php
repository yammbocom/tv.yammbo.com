<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Catálogo de complementos propio, servido en el mismo formato `addon_catalog`
 * que ya consume stremio-core:
 *
 *   GET /addon_catalog/all/yammbo.json  ->  {"addons": [{transportUrl, manifest}, ...]}
 *
 * La lista sale de config/yammbo_addons.php y los logos se sirven por
 * /addon-logo/{slug}, así que abrir la pestaña ya no dispara peticiones a
 * decenas de hosts de terceros con la IP del usuario.
 */
class AddonCatalogController extends Controller
{
    private const MANIFEST_TTL = 6 * 3600;

    private const CATALOG_TTL = 900;

    /** Tope de bytes que aceptamos cachear como logo. */
    private const LOGO_MAX_BYTES = 512 * 1024;

    public function catalog(Request $request, string $type, string $id)
    {
        if ($id !== config('yammbo_addons.catalog_id', 'yammbo')) {
            return $this->cors(response()->json(['err' => 'Catalog not found'], 404));
        }

        $lang = str_starts_with(strtolower((string) $request->header('Accept-Language', 'es')), 'en') ? 'en' : 'es';

        $addons = Cache::remember(
            "yambo:addon-catalog:{$type}:{$lang}",
            self::CATALOG_TTL,
            fn () => $this->build($type, $lang)
        );

        return $this->cors(response()->json(['addons' => $addons], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
            ->header('Cache-Control', 'public, max-age=900');
    }

    private function build(string $type, string $lang): array
    {
        $entries = (array) config('yammbo_addons.addons', []);
        $manifests = $this->manifests($entries);

        $addons = [];
        foreach ($entries as $entry) {
            $manifest = $manifests[$entry['slug']] ?? null;
            if ($manifest === null) {
                continue;
            }

            if ($type !== 'all' && ! in_array($type, (array) ($manifest['types'] ?? []), true)) {
                continue;
            }

            $manifest['name'] = $entry['name'][$lang] ?? $entry['name']['es'] ?? $entry['slug'];
            $manifest['description'] = $entry['description'][$lang] ?? $entry['description']['es'] ?? '';
            $manifest['logo'] = ! empty($entry['logo']) ? url('/addon-logo/'.$entry['slug']) : null;
            unset($manifest['background'], $manifest['stremioAddonsConfig'], $manifest['contactEmail']);

            $addons[] = [
                'transportUrl' => $entry['url'],
                'transportName' => 'http',
                'manifest' => $manifest,
            ];
        }

        return $addons;
    }

    /**
     * Manifests reales, cacheados 6 h y pedidos todos a la vez: en serie eran
     * casi 4 s de espera para quien abriese la pestaña con la caché fría.
     *
     * Si un origen falla se usa la última copia buena en vez de saltarse la
     * fila. Un fetch fallido no es un catálogo vacío, y una lista que encoge
     * sola engaña más que un error.
     *
     * @return array<string, array>  slug => manifest
     */
    private function manifests(array $entries): array
    {
        $out = [];
        $missing = [];

        foreach ($entries as $entry) {
            $cached = Cache::get('yambo:addon-manifest:'.$entry['slug']);
            if (is_array($cached)) {
                $out[$entry['slug']] = $cached;
            } else {
                $missing[] = $entry;
            }
        }

        if ($missing !== []) {
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($entry) => $pool->as($entry['slug'])
                    ->withHeaders(['User-Agent' => 'YammboTv/1.0 (+https://tv.yammbo.com)'])
                    ->timeout(12)
                    ->get($entry['url']),
                $missing
            ));

            foreach ($missing as $entry) {
                $slug = $entry['slug'];
                $res = $responses[$slug] ?? null;

                if ($res instanceof HttpResponse && $res->successful() && is_array($manifest = $res->json())) {
                    Cache::put('yambo:addon-manifest:'.$slug, $manifest, self::MANIFEST_TTL);
                    Cache::forever('yambo:addon-manifest:'.$slug.':lkg', $manifest);
                    $out[$slug] = $manifest;

                    continue;
                }

                $reason = $res instanceof HttpResponse
                    ? 'http '.$res->status()
                    : (string) ($res?->getMessage() ?? 'sin respuesta');

                $lkg = Cache::get('yambo:addon-manifest:'.$slug.':lkg');
                if (is_array($lkg)) {
                    // No reintentar en cada visita mientras el origen esté caído.
                    Cache::put('yambo:addon-manifest:'.$slug, $lkg, 300);
                    $out[$slug] = $lkg;

                    continue;
                }

                // A partir de aquí la fila desaparece del catálogo. Va como
                // error y no como warning a propósito: el .env tiene
                // LOG_LEVEL=error, así que un warning no se escribe en ningún
                // sitio y el complemento se caería de la lista sin dejar
                // rastro. Una lista que encoge sola engaña más que un fallo.
                Log::error('[addon-catalog] complemento fuera del catálogo, no responde', [
                    'slug' => $slug,
                    'url' => $entry['url'],
                    'reason' => $reason,
                ]);
            }
        }

        return $out;
    }

    /**
     * Sirve el logo desde nuestro dominio. Sin esto la pestaña cargaba 79
     * imágenes desde 53 hosts distintos y cada uno veía la IP del usuario.
     */
    public function logo(string $slug)
    {
        $entry = collect((array) config('yammbo_addons.addons', []))
            ->firstWhere('slug', $slug);

        if (! $entry || empty($entry['logo'])) {
            abort(404);
        }

        $cached = Cache::get('yambo:addon-logo:'.$slug);
        if (! is_array($cached)) {
            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; YammboTv/1.0; +https://tv.yammbo.com)',
                ])->timeout(10)->get($entry['logo']);

                if (! $res->successful() || strlen($res->body()) > self::LOGO_MAX_BYTES) {
                    abort(404);
                }

                $cached = [
                    'body' => base64_encode($res->body()),
                    'type' => $res->header('Content-Type') ?: 'image/png',
                ];
                Cache::put('yambo:addon-logo:'.$slug, $cached, 30 * 24 * 3600);
            } catch (\Throwable $e) {
                Log::warning('[addon-catalog] logo no disponible: '.$e->getMessage(), ['slug' => $slug]);
                abort(404);
            }
        }

        return response(base64_decode($cached['body']), 200, [
            'Content-Type' => $cached['type'],
            'Cache-Control' => 'public, max-age=604800',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    private function cors($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*');
    }
}
