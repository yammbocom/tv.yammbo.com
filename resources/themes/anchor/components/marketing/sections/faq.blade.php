<section>
    <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="font-display text-[length:var(--text-xl)] font-semibold text-[color:var(--color-ink)] mb-4">
            {{ __('landing.faq.heading') }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto space-y-3">
        @foreach(['q1','q2','q3','q4','q5'] as $i => $key)
            <details class="group p-5 rounded-xl bg-surface border border-rule transition-colors" {{ $i === 0 ? 'open' : '' }}>
                <summary class="flex justify-between items-center cursor-pointer list-none">
                    <h3 class="font-display text-base font-semibold text-[color:var(--color-ink)] pr-4">{{ __('landing.faq.'.$key.'_q') }}</h3>
                    <svg class="w-5 h-5 text-[color:var(--color-ink-mute)] group-open:rotate-180 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="mt-3 text-sm leading-relaxed text-[color:var(--color-ink-mute)]">
                    {{ __('landing.faq.'.$key.'_a') }}
                </p>
            </details>
        @endforeach
    </div>
</section>
