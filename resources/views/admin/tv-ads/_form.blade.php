{{-- Formulario de anuncio (crear y editar). Recibe $ad, $action, $method y $submit. --}}
<style>
    .tvf-grid{display:grid;gap:1.25rem}
    @media (min-width:768px){.tvf-2{grid-template-columns:1fr 1fr}.tvf-3{grid-template-columns:1fr 1fr 1fr}}
    .tvf-check{display:flex;align-items:center;gap:.6rem;font-size:.875rem;color:#d4d4d4}
    .tvf-check input{width:1rem;height:1rem;accent-color:#E50914}
    .tvf-help{font-size:.75rem;color:#666;margin-top:.25rem}
    .tvf-file{width:100%;font-size:.875rem;color:#bdbdbd}
    .tvf-file::file-selector-button{margin-right:.75rem;border:1px solid #333;background:#1A1A1A;color:#fff;border-radius:.375rem;padding:.4rem .8rem;cursor:pointer}
</style>
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div>
        <label for="name" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Nombre (solo para ti)</label>
        <input type="text" id="name" name="name" value="{{ old('name', $ad->name) }}" required maxlength="80"
               class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
    </div>

    <div>
        <label for="video" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Video MP4</label>
        <input type="file" id="video" name="video" accept="video/mp4" class="tvf-file">
        <p class="tvf-help">Hasta 96 MB. Recomendado: 1080p o 720p, H.264, entre 10 y 30 segundos.
            @if($ad->video_url) Actual: <a href="{{ $ad->video_url }}" target="_blank" rel="noopener" class="text-[#bdbdbd] hover:text-white">ver video</a>. Sube otro solo si quieres cambiarlo. @endif</p>
    </div>

    <div>
        <label for="video_url" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">O URL del video (en vez de subirlo)</label>
        <input type="url" id="video_url" name="video_url" value="{{ old('video_url', str_starts_with((string) $ad->video_url, 'http') ? $ad->video_url : '') }}" placeholder="https://..."
               class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
    </div>

    <div>
        <label for="link_text" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Texto en pantalla (opcional)</label>
        <input type="text" id="link_text" name="link_text" value="{{ old('link_text', $ad->link_text) }}" maxlength="80" placeholder="Ej.: Pide ya en pedidos.ejemplo.com"
               class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
    </div>

    <div class="tvf-grid tvf-3">
        <div>
            <label for="skip_after" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Omitir tras (s)</label>
            <input type="number" id="skip_after" name="skip_after" min="0" max="60" value="{{ old('skip_after', $ad->skip_after) }}" required
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            <p class="tvf-help">0 = se puede omitir enseguida.</p>
        </div>
        <div>
            <label for="audience" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Quién lo ve</label>
            <select id="audience" name="audience"
                    class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                @foreach(\App\Support\TvAudience::LABELS as $k => $label)
                    <option value="{{ $k }}" @selected(old('audience', $ad->audience) === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="weight" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Peso</label>
            <input type="number" id="weight" name="weight" min="1" max="100" value="{{ old('weight', $ad->weight) }}" required
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            <p class="tvf-help">Con varios anuncios, más peso = sale más veces.</p>
        </div>
    </div>

    <div class="tvf-grid tvf-2">
        <div>
            <label for="starts_at" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Desde (opcional)</label>
            <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $ad->starts_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        </div>
        <div>
            <label for="ends_at" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Hasta (opcional)</label>
            <input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $ad->ends_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
        </div>
    </div>

    <label class="tvf-check"><input type="checkbox" name="active" value="1" @checked(old('active', $ad->active))> Activo</label>

    <div class="pt-2">
        <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">{{ $submit }}</button>
    </div>
</form>
