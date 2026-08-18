<section>
    <div class="text-center max-w-3xl mx-auto mb-16">
        <h2 class="text-4xl sm:text-5xl font-bold tracking-tight text-white mb-4">
            {{ __('landing.features.heading') }}
        </h2>
        <p class="text-lg text-zinc-400">
            {{ __('landing.features.subheading') }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Feature cards --}}
        @php
            $features = [
                ['icon' => 'film', 'key' => 'f1'],
                ['icon' => 'devices', 'key' => 'f2'],
                ['icon' => 'captions', 'key' => 'f3'],
                ['icon' => 'sparkle', 'key' => 'f4'],
                ['icon' => 'cast', 'key' => 'f5'],
                ['icon' => 'no-ad', 'key' => 'f6'],
                ['icon' => 'rocket', 'key' => 'f7'],
                ['icon' => 'heart', 'key' => 'f8'],
            ];
        @endphp

        @foreach($features as $f)
            <div class="relative p-6 rounded-xl bg-white/[0.03] border border-white/10 hover:border-[#E50914]/40 hover:bg-white/[0.05] transition-all group">
                <div class="w-11 h-11 rounded-lg bg-[#E50914]/15 flex items-center justify-center mb-4 text-[#E50914] group-hover:scale-110 transition-transform">
                    @switch($f['icon'])
                        @case('film')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
                            @break
                        @case('devices')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            @break
                        @case('captions')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h2"/><path d="M11 15h6"/><path d="M7 11h10"/></svg>
                            @break
                        @case('sparkle')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.5 5L20 10l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-2L12 3z"/></svg>
                            @break
                        @case('cast')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 16.1A5 5 0 0 1 5.9 20"/><path d="M2 12.05A9 9 0 0 1 9.95 20"/><path d="M2 8V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/><line x1="2" y1="20" x2="2.01" y2="20"/></svg>
                            @break
                        @case('no-ad')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            @break
                        @case('rocket')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                            @break
                        @case('heart')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            @break
                    @endswitch
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">{{ __('landing.features.'.$f['key'].'_title') }}</h3>
                <p class="text-sm leading-relaxed text-zinc-400">{{ __('landing.features.'.$f['key'].'_body') }}</p>
            </div>
        @endforeach
    </div>
</section>
