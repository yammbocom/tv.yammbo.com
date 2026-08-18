<section class="relative overflow-hidden">
    <div class="relative p-12 sm:p-16 rounded-3xl bg-gradient-to-br from-[#E50914]/20 via-black to-[#E50914]/10 border border-[#E50914]/30 text-center max-w-4xl mx-auto">
        <div class="absolute inset-0 opacity-30 pointer-events-none"
             style="background: radial-gradient(circle at 50% 50%, rgba(229,9,20,0.35), transparent 60%)"></div>

        <div class="relative">
            <h2 class="text-3xl sm:text-5xl font-bold tracking-tight text-white mb-4">
                {{ __('landing.cta.heading') }}
            </h2>
            <p class="text-lg text-zinc-300 mb-8 max-w-xl mx-auto">
                {{ __('landing.cta.subheading') }}
            </p>
            <a href="/auth/register"
               class="inline-flex items-center justify-center px-10 py-4 rounded-lg bg-[#E50914] hover:bg-[#B0070F] text-white font-bold text-lg transition-colors shadow-lg shadow-[#E50914]/30">
                {{ __('landing.cta.button') }}
            </a>
        </div>
    </div>
</section>
