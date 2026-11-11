@extends('admin.layout')

@section('title', 'Novedades de la app de TV')

@section('content')
    <p class="text-sm text-[#888] mb-6 max-w-2xl">
        Lo que se publique aquí aparece en la campana de la app de TV. Si está marcado como aviso,
        además se muestra una vez al abrir la app (solo la novedad más reciente que esa persona no haya visto).
    </p>

    <div class="max-w-2xl rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] p-6 mb-8">
        <h2 class="text-sm font-semibold text-[#bdbdbd] uppercase tracking-wide mb-4">Nueva novedad</h2>
        @include('admin.tv-news._form', ['action' => route('panel.tv-news.store'), 'method' => 'POST', 'submit' => 'Publicar'])
    </div>

    <h2 class="text-sm font-semibold text-[#bdbdbd] uppercase tracking-wide mb-3">Publicadas</h2>
    <div class="rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#1f1f1f] text-left text-xs uppercase tracking-wide text-[#888]">
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Novedad</th>
                    <th class="px-5 py-3">Quién</th>
                    <th class="px-5 py-3">Aviso</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $n)
                    <tr class="border-b border-[#1a1a1a] last:border-0">
                        <td class="px-5 py-3 text-[#888] whitespace-nowrap">{{ ($n->starts_at ?: $n->created_at)?->format('d/m H:i') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($n->image_url)
                                    <img src="{{ $n->image_url }}" alt="" class="w-24 h-14 object-cover rounded">
                                @endif
                                <div>
                                    <div>{{ Str::limit($n->title, 50) }}</div>
                                    <div class="text-[#888] text-xs">{{ Str::limit($n->body, 70) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-[#bdbdbd] whitespace-nowrap">{{ \App\Support\TvAudience::LABELS[$n->audience] ?? $n->audience }}@if($n->genre)<span class="block text-xs text-[#888]">Le gusta: {{ $n->genre }}</span>@endif</td>
                        <td class="px-5 py-3 text-[#bdbdbd]">{{ $n->popup ? 'Sí' : 'No' }}</td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if(! $n->active)
                                <span class="text-[#888]">Oculta</span>
                            @elseif($n->ends_at && $n->ends_at->isPast())
                                <span class="text-[#888]">Terminada</span>
                            @elseif($n->starts_at && $n->starts_at->isFuture())
                                <span class="text-[#bdbdbd]">Programada</span>
                            @else
                                <span class="text-[#5dd87f]">Visible</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('panel.tv-news.edit', $n) }}" class="text-[#bdbdbd] hover:text-white mr-3">Editar</a>
                            <form method="POST" action="{{ route('panel.tv-news.toggle', $n) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-[#bdbdbd] hover:text-white mr-3">{{ $n->active ? 'Ocultar' : 'Publicar' }}</button>
                            </form>
                            <form method="POST" action="{{ route('panel.tv-news.destroy', $n) }}" class="inline"
                                  onsubmit="return confirm('¿Eliminar esta novedad?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[#ff8b9b] hover:text-white">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-[#888]">Todavía no hay novedades.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
