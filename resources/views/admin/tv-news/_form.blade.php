{{-- Formulario de novedad (crear y editar). Recibe $news, $action y $method. --}}
<style>
    .tvf-grid{display:grid;gap:1.25rem}
    @media (min-width:768px){.tvf-2{grid-template-columns:1fr 1fr}}
    .tvf-check{display:flex;align-items:center;gap:.6rem;font-size:.875rem;color:#d4d4d4}
    .tvf-check input{width:1rem;height:1rem;accent-color:#E50914}
    .tvf-help{font-size:.75rem;color:#666;margin-top:.25rem}
</style>
<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div>
        <label for="title" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Título</label>
        <input type="text" id="title" name="title" value="{{ old('title', $news->title) }}" required maxlength="120"
               class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
    </div>

    <div>
        <label for="body" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Mensaje</label>
        <textarea id="body" name="body" rows="3" required maxlength="600"
                  class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">{{ old('body', $news->body) }}</textarea>
    </div>

    <div>
        <label for="image_url" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Imagen (opcional)</label>
        <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $news->image_url) }}" placeholder="https://..."
               class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        <p class="tvf-help">Horizontal (16:9) se ve mejor. Por ejemplo, el fondo de una película de TMDB.</p>
    </div>

    <div class="tvf-grid tvf-2">
        <div>
            <label for="action" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Botón</label>
            <select id="action" name="action"
                    class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                @foreach(\App\Http\Controllers\Admin\TvNewsController::ACTIONS as $k => $label)
                    <option value="{{ $k }}" @selected(old('action', $news->action) === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="action_value" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Podcast (solo si abre uno concreto)</label>
            <input type="text" id="action_value" name="action_value" value="{{ old('action_value', $news->action_value) }}" placeholder="MPSP..."
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        </div>
    </div>

    <div class="tvf-grid tvf-2">
        <div>
            <label for="audience" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Quién la ve</label>
            <select id="audience" name="audience"
                    class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                @foreach(\App\Support\TvAudience::LABELS as $k => $label)
                    <option value="{{ $k }}" @selected(old('audience', $news->audience) === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="genre" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Solo a quien le guste (opcional)</label>
            <input type="text" id="genre" name="genre" list="tv-genres" maxlength="60" value="{{ old('genre', $news->genre) }}" placeholder="Por ejemplo: Acción"
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            <datalist id="tv-genres">
                @foreach($genres ?? [] as $g)
                    <option value="{{ $g }}">
                @endforeach
            </datalist>
            <p class="mt-1 text-[11px] text-[#666]">Vacío = todos. Con un género, solo la reciben los perfiles que más ven ese género.</p>
        </div>
    </div>

    <div class="tvf-grid tvf-2">
        <div class="flex flex-col gap-3 justify-end">
            <label class="tvf-check"><input type="checkbox" name="popup" value="1" @checked(old('popup', $news->popup))> Mostrar como aviso al abrir la app</label>
            <label class="tvf-check"><input type="checkbox" name="active" value="1" @checked(old('active', $news->active))> Publicada</label>
        </div>
    </div>

    <div class="tvf-grid tvf-2">
        <div>
            <label for="starts_at" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Desde (opcional)</label>
            <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $news->starts_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        </div>
        <div>
            <label for="ends_at" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Hasta (opcional)</label>
            <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $news->ends_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        </div>
    </div>

    <div class="pt-2">
        <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">{{ $submit }}</button>
    </div>
</form>
