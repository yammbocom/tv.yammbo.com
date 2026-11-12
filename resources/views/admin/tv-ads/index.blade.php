@extends('admin.layout')

@section('title', 'Anuncios de la app de TV')

@section('content')
    <p class="text-sm text-[#a3a3a3] mb-6 max-w-2xl">
        Un video corto antes de empezar una película o un capítulo. Nunca en TV en vivo ni en tráileres o podcasts.
        Cada televisor ve como mucho un anuncio cada tantos minutos (ajuste de abajo).
    </p>

    <div class="max-w-2xl rounded-lg border border-[#262626] bg-[#0a0a0a] p-6 mb-8">
        <h2 class="text-sm font-semibold text-[#a3a3a3] mb-4">Ajustes</h2>
        <form method="POST" action="{{ route('panel.tv-ads.settings') }}" class="space-y-5">
            @csrf
            <label style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;color:#d4d4d4">
                <input type="checkbox" name="enabled" value="1" style="width:1rem;height:1rem;accent-color:#E50914" @checked($settings['enabled'])>
                Anuncios activados
            </label>
            <div>
                <label for="gap_minutes" class="block text-xs font-medium text-[#a3a3a3] mb-1.5">Minutos mínimos entre anuncios en un mismo televisor</label>
                <input type="number" id="gap_minutes" name="gap_minutes" min="0" max="1440" value="{{ $settings['gap_minutes'] }}" required
                       class="w-full rounded-md bg-[#0a0a0a] border border-[#404040] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]" style="max-width:10rem">
            </div>
            <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">Guardar ajustes</button>
        </form>
    </div>

    <div class="max-w-2xl rounded-lg border border-[#262626] bg-[#0a0a0a] p-6 mb-8">
        <h2 class="text-sm font-semibold text-[#a3a3a3] mb-4">Nuevo anuncio</h2>
        @include('admin.tv-ads._form', ['action' => route('panel.tv-ads.store'), 'method' => 'POST', 'submit' => 'Crear anuncio'])
    </div>

    <h2 class="text-sm font-semibold text-[#a3a3a3] mb-3">Anuncios</h2>
    <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#262626] text-left text-xs font-medium text-[#a3a3a3]">
                    <th class="px-5 py-3">Anuncio</th>
                    <th class="px-5 py-3">Quién</th>
                    <th class="px-5 py-3">Vistos</th>
                    <th class="px-5 py-3">Completos</th>
                    <th class="px-5 py-3">Omitidos</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($ads as $a)
                    <tr class="border-b border-[#262626] last:border-0">
                        <td class="px-5 py-3">
                            <div>{{ Str::limit($a->name, 40) }}</div>
                            <a href="{{ $a->video_url }}" target="_blank" rel="noopener" class="text-[#a3a3a3] text-xs hover:text-white">ver video</a>
                        </td>
                        <td class="px-5 py-3 text-[#a3a3a3] whitespace-nowrap">{{ \App\Support\TvAudience::LABELS[$a->audience] ?? $a->audience }}</td>
                        <td class="px-5 py-3">{{ number_format($a->impressions) }}</td>
                        <td class="px-5 py-3">{{ number_format($a->completions) }}</td>
                        <td class="px-5 py-3">{{ number_format($a->skips) }}</td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if(! $a->active)
                                <span class="text-[#a3a3a3]">Pausado</span>
                            @elseif($a->ends_at && $a->ends_at->isPast())
                                <span class="text-[#a3a3a3]">Terminado</span>
                            @elseif($a->starts_at && $a->starts_at->isFuture())
                                <span class="text-[#a3a3a3]">Programado</span>
                            @else
                                <span class="text-white">Activo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('panel.tv-ads.edit', $a) }}" class="text-[#a3a3a3] hover:text-white mr-3">Editar</a>
                            <form method="POST" action="{{ route('panel.tv-ads.toggle', $a) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-[#a3a3a3] hover:text-white mr-3">{{ $a->active ? 'Pausar' : 'Activar' }}</button>
                            </form>
                            <form method="POST" action="{{ route('panel.tv-ads.destroy', $a) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar este anuncio y su video?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[#FF4D55] hover:text-white">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-6 text-center text-[#a3a3a3]">Todavía no hay anuncios. Mientras no haya ninguno activo, nadie ve anuncios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
