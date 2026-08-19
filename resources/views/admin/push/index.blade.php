@extends('admin.layout')

@section('title', 'Push')

@section('content')
    <div class="max-w-xl rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] p-6 mb-8">
        <form method="POST" action="{{ route('panel.push.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="topic" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Topic FCM</label>
                <input type="text" id="topic" name="topic" value="{{ old('topic', 'yammbo_news') }}" required
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                <p class="text-xs text-[#666] mt-1">Default: yammbo_news (todos los devices que abrieron la APK v29+)</p>
            </div>

            <div>
                <label for="title" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Título</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="120"
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div>
                <label for="body" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Mensaje</label>
                <textarea id="body" name="body" rows="3" required maxlength="500"
                          class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">{{ old('body') }}</textarea>
            </div>

            <div>
                <label for="click_url" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">URL al tocar (opcional)</label>
                <input type="url" id="click_url" name="click_url" value="{{ old('click_url') }}" placeholder="https://tv.yammbo.com/..."
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div>
                <label for="image_url" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Imagen (opcional)</label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://tv.yammbo.com/images/..."
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div class="pt-2">
                <button type="submit"
                        onclick="return confirm('Vas a enviar el push. Llegará a TODOS los devices subscritos al topic. ¿Continuar?');"
                        class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">Enviar push</button>
            </div>
        </form>
    </div>

    <h2 class="text-sm font-semibold text-[#bdbdbd] uppercase tracking-wide mb-3">Últimos 30 envíos</h2>
    <div class="rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1f1f1f] text-left text-xs uppercase tracking-wide text-[#888]">
                    <th class="px-5 py-3">Enviado</th>
                    <th class="px-5 py-3">Topic</th>
                    <th class="px-5 py-3">Título</th>
                    <th class="px-5 py-3">Mensaje</th>
                    <th class="px-5 py-3">OK</th>
                    <th class="px-5 py-3">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b border-[#1a1a1a] last:border-0">
                        <td class="px-5 py-3 text-[#888] whitespace-nowrap">{{ $log->created_at?->format('d/m H:i') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block rounded-full bg-[#1a1a1a] border border-[#333] px-2 py-0.5 text-xs text-[#bdbdbd]">{{ $log->topic }}</span>
                        </td>
                        <td class="px-5 py-3">{{ Str::limit($log->title, 40) }}</td>
                        <td class="px-5 py-3 text-[#bdbdbd]">{{ Str::limit($log->body, 60) }}</td>
                        <td class="px-5 py-3">
                            @if($log->success)
                                <span class="text-[#5dd87f]">&#10003;</span>
                            @else
                                <span class="text-[#ff8b9b]">&#10007;</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-[#ff8b9b]">{{ $log->error ? Str::limit($log->error, 30) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-[#888]">Sin envíos todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
