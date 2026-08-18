<section>
    <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-4xl sm:text-5xl font-bold tracking-tight text-white mb-4">
            {{ __('landing.faq.heading') }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto space-y-3">
        @foreach(['q1','q2','q3','q4','q5'] as $i => $key)
            <details class="group p-5 rounded-xl bg-white/[0.03] border border-white/10 hover:border-white/20 transition-colors" {{ $i === 0 ? 'open' : '' }}>
                <summary class="flex justify-between items-center cursor-pointer list-none">
                    <h3 class="text-base font-semibold text-white pr-4">{{ __('landing.faq.'.$key.'_q') }}</h3>
                    <svg class="w-5 h-5 text-zinc-400 group-open:rotate-180 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="mt-3 text-sm leading-relaxed text-zinc-400">
                    {{ __('landing.faq.'.$key.'_a') }}
                </p>
            </details>
        @endforeach
    </div>
</section>
