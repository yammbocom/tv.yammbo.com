<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppTv\TvAdsController as AppAds;
use App\Http\Controllers\Controller;
use App\Models\TvAd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Anuncios propios de la app de TV (video antes de una pelicula o capitulo). */
class TvAdsController extends Controller
{
    public function index(): View
    {
        return view('admin.tv-ads.index', [
            'ads' => TvAd::orderByDesc('id')->get(),
            'settings' => AppAds::settings(),
            'ad' => new TvAd(['skip_after' => 5, 'audience' => 'trial', 'weight' => 1, 'active' => true]),
        ]);
    }

    public function edit(TvAd $ad): View
    {
        return view('admin.tv-ads.edit', ['ad' => $ad]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request, true);
        TvAd::create($data);
        $this->flush();

        return redirect()->route('panel.tv-ads.index')->with('success', 'Anuncio creado.');
    }

    public function update(Request $request, TvAd $ad): RedirectResponse
    {
        $data = $this->data($request, false);
        $old = $ad->video_url;
        $ad->update($data);
        if ($old !== $ad->video_url) {
            $this->deleteLocal($old);
        }
        $this->flush();

        return redirect()->route('panel.tv-ads.index')->with('success', 'Anuncio guardado.');
    }

    public function toggle(TvAd $ad): RedirectResponse
    {
        $ad->update(['active' => ! $ad->active]);
        $this->flush();

        return back()->with('success', $ad->active ? 'Anuncio activado.' : 'Anuncio pausado.');
    }

    public function destroy(TvAd $ad): RedirectResponse
    {
        $this->deleteLocal($ad->video_url);
        $ad->delete();
        $this->flush();

        return redirect()->route('panel.tv-ads.index')->with('success', 'Anuncio eliminado.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $v = $request->validate(['gap_minutes' => 'required|integer|min:0|max:1440']);
        $json = ['enabled' => $request->boolean('enabled'), 'gap_minutes' => (int) $v['gap_minutes']];
        file_put_contents(storage_path('app/tv-ads.json'), json_encode($json, JSON_PRETTY_PRINT));

        return back()->with('success', 'Ajustes de anuncios guardados.');
    }

    private function data(Request $request, bool $creating): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:80',
            'video' => 'nullable|file|mimetypes:video/mp4|max:98304',
            'video_url' => 'nullable|url|starts_with:https://|max:500',
            'link_text' => 'nullable|string|max:80',
            'skip_after' => 'required|integer|min:0|max:60',
            'audience' => 'required|in:all,trial,paid,free',
            'weight' => 'required|integer|min:1|max:100',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ], [
            'video.mimetypes' => 'El video tiene que ser MP4 (H.264).',
            'video.max' => 'El video no puede pasar de 96 MB.',
            'video_url.starts_with' => 'La URL del video tiene que empezar por https://.',
        ]);

        $url = $v['video_url'] ?? null;
        if ($request->hasFile('video')) {
            $dir = public_path('ads');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $name = Str::slug(Str::limit($v['name'], 40, '')).'-'.now()->format('YmdHis').'.mp4';
            $request->file('video')->move($dir, $name);
            $url = '/ads/'.$name;
        }
        if (! $url && $creating) {
            throw \Illuminate\Validation\ValidationException::withMessages(['video' => 'Sube un video MP4 o pega su URL.']);
        }

        $out = [
            'name' => $v['name'],
            'link_text' => $v['link_text'] ?? null,
            'skip_after' => (int) $v['skip_after'],
            'audience' => $v['audience'],
            'weight' => (int) $v['weight'],
            'starts_at' => $v['starts_at'] ?? null,
            'ends_at' => $v['ends_at'] ?? null,
            'active' => $request->boolean('active'),
        ];
        if ($url) {
            $out['video_url'] = $url;
        }

        return $out;
    }

    private function deleteLocal(?string $url): void
    {
        if ($url && preg_match('#^/ads/[\w\-]+\.mp4$#', $url)) {
            @unlink(public_path(ltrim($url, '/')));
        }
    }

    private function flush(): void
    {
        foreach (['all', 'trial', 'paid', 'free'] as $s) {
            Cache::forget('tv_ads_v1_'.$s);
        }
    }
}
