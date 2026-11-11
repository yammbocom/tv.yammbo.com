<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvNews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Novedades de la app de TV: aviso al abrir la app y lista de la campana. */
class TvNewsController extends Controller
{
    public const ACTIONS = [
        'none' => 'Ninguna (solo informar)',
        'podcasts' => 'Abrir Podcasts',
        'podcast' => 'Abrir un podcast concreto',
        'account' => 'Abrir Mi cuenta',
    ];

    public function index(): View
    {
        $items = TvNews::orderByDesc('id')->take(100)->get();

        return view('admin.tv-news.index', ['items' => $items, 'news' => new TvNews(['audience' => 'all', 'popup' => true, 'active' => true, 'action' => 'none']), 'genres' => self::genres()]);
    }

    public function edit(TvNews $news): View
    {
        return view('admin.tv-news.edit', ['news' => $news, 'genres' => self::genres()]);
    }

    public function store(Request $request): RedirectResponse
    {
        TvNews::create($this->data($request));
        $this->flush();

        return redirect()->route('panel.tv-news.index')->with('success', 'Novedad publicada.');
    }

    public function update(Request $request, TvNews $news): RedirectResponse
    {
        $news->update($this->data($request));
        $this->flush();

        return redirect()->route('panel.tv-news.index')->with('success', 'Novedad guardada.');
    }

    public function toggle(TvNews $news): RedirectResponse
    {
        $news->update(['active' => ! $news->active]);
        $this->flush();

        return back()->with('success', $news->active ? 'Novedad activada.' : 'Novedad ocultada.');
    }

    public function destroy(TvNews $news): RedirectResponse
    {
        $news->delete();
        $this->flush();

        return redirect()->route('panel.tv-news.index')->with('success', 'Novedad eliminada.');
    }

    private function data(Request $request): array
    {
        $v = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:600',
            'image_url' => 'nullable|url|max:500',
            'action' => 'required|in:'.implode(',', array_keys(self::ACTIONS)),
            'action_value' => ['nullable', 'required_if:action,podcast', 'regex:/^MPSP[\w\-]{8,80}$/'],
            'audience' => 'required|in:all,trial,paid,free',
            'genre' => 'nullable|string|max:60',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ], [
            'action_value.required_if' => 'Para abrir un podcast concreto hace falta su identificador (empieza por MPSP).',
            'action_value.regex' => 'El identificador del podcast empieza por MPSP.',
            'ends_at.after' => 'La fecha de fin tiene que ser posterior a la de inicio.',
        ]);
        $v['popup'] = $request->boolean('popup');
        $v['active'] = $request->boolean('active');
        if ($v['action'] !== 'podcast') {
            $v['action_value'] = null;
        }

        return $v;
    }

    /** Generos que ven los usuarios (los manda la app), para elegir sin adivinar como se escriben. */
    public static function genres(): array
    {
        return array_values(array_map(fn ($g) => $g['name'], Cache::get('tv_seen_genres', [])));
    }

    private function flush(): void
    {
        foreach (['all', 'trial', 'paid', 'free'] as $s) {
            Cache::forget('tv_news_v1_'.$s);
        }
    }
}
