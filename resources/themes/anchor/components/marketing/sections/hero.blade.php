<section class="flex relative top-0 flex-col justify-center items-center -mt-24 w-full min-h-screen bg-black lg:min-h-screen overflow-hidden">
    {{-- Background glow --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[800px] h-[800px] rounded-full"
             style="background: radial-gradient(closest-side, rgba(229,9,20,0.25), transparent 70%)"></div>
    </div>

    <div class="relative z-10 flex flex-col flex-1 gap-8 justify-center items-center px-8 pt-32 pb-16 mx-auto w-full max-w-5xl text-center">
        <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-20 h-20 mb-2">

        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-sm text-zinc-300 font-medium">
            <span class="w-2 h-2 rounded-full bg-[#E50914] animate-pulse"></span>
            {{ __('landing.hero.badge') }}
        </div>

        <h1 class="text-5xl font-bold tracking-tight text-center sm:text-6xl md:text-7xl lg:text-[88px] lg:leading-[0.95] text-white text-balance">
            {{ __('landing.hero.heading_1') }}<br>
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#E50914] via-[#FF3B45] to-[#E50914]">{{ __('landing.hero.heading_2') }}</span>
        </h1>

        <p class="mx-auto max-w-2xl text-lg font-normal md:text-xl text-zinc-400 leading-relaxed">
            {{ __('landing.hero.tagline') }}
        </p>

        <div class="flex flex-col sm:flex-row gap-3 mt-4">
            <a href="/auth/register"
               class="inline-flex items-center justify-center px-8 py-3.5 rounded-lg bg-[#E50914] hover:bg-[#B0070F] text-white font-bold text-base transition-colors">
                {{ __('landing.hero.cta_primary') }}
            </a>
            <a href="#pricing"
               class="inline-flex items-center justify-center px-8 py-3.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold text-base transition-colors">
                {{ __('landing.hero.cta_secondary') }}
            </a>
        </div>

        <p class="text-sm text-zinc-500">{{ __('landing.hero.hint') }}</p>
    </div>
</section>
