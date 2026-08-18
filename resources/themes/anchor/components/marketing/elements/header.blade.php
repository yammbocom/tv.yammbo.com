<header
    x-data="{
        mobileMenuOpen: false,
        scrolled: false,
        topOffset: '5',
        evaluateScrollPosition(){
            this.scrolled = window.pageYOffset > this.topOffset;
        }
    }"
    x-init="
        window.addEventListener('resize', () => { if(window.innerWidth > 768) mobileMenuOpen = false; });
        $watch('mobileMenuOpen', v => document.body.classList.toggle('overflow-hidden', v));
        evaluateScrollPosition();
        window.addEventListener('scroll', () => evaluateScrollPosition());
    "
    :class="scrolled ? 'bg-black/85 border-b border-zinc-800 backdrop-blur-lg' : 'bg-transparent border-b border-transparent'"
    class="box-content sticky top-0 z-50 w-full h-20 transition-all"
>
    <x-container>
        <div class="flex items-center justify-between h-20 md:space-x-8">
            {{-- Logo --}}
            <div class="flex items-center justify-between w-full md:w-auto">
                <a href="{{ route('home') }}" class="flex items-center space-x-2 font-bold text-white">
                    <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-9 h-9" />
                    <span class="hidden sm:inline text-lg tracking-tight">Yammbo Tv</span>
                </a>

                {{-- Mobile toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-zinc-400 hover:text-white hover:bg-white/5">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-300">
                <a href="#features" class="hover:text-white transition-colors">{{ __('landing.footer.link_features') }}</a>
                <a href="#pricing" class="hover:text-white transition-colors">{{ __('landing.nav.pricing') }}</a>
                <a href="/help" class="hover:text-white transition-colors">{{ __('landing.nav.help') }}</a>
            </nav>

            {{-- Desktop CTAs --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="/app" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#E50914] hover:bg-[#B0070F] transition-colors">
                        {{ __('landing.nav.app') }}
                    </a>
                @else
                    <a href="/auth/login" class="text-sm font-semibold text-zinc-300 hover:text-white transition-colors">
                        {{ __('landing.nav.login') }}
                    </a>
                    <a href="/auth/register" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#E50914] hover:bg-[#B0070F] transition-colors">
                        {{ __('landing.nav.register') }}
                    </a>
                @endauth
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="mobileMenuOpen" x-cloak
             class="md:hidden fixed top-20 left-0 right-0 bottom-0 bg-black border-t border-zinc-800 overflow-y-auto">
            <div class="p-6 space-y-4 text-white">
                <a href="#features" @click="mobileMenuOpen = false" class="block py-3 text-lg border-b border-zinc-800">{{ __('landing.footer.link_features') }}</a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="block py-3 text-lg border-b border-zinc-800">{{ __('landing.nav.pricing') }}</a>
                <a href="/help" @click="mobileMenuOpen = false" class="block py-3 text-lg border-b border-zinc-800">{{ __('landing.nav.help') }}</a>
                <div class="pt-6 flex flex-col gap-3">
                    @auth
                        <a href="/app" class="block text-center px-4 py-3 rounded-lg text-base font-bold text-white bg-[#E50914] hover:bg-[#B0070F]">
                            {{ __('landing.nav.app') }}
                        </a>
                    @else
                        <a href="/auth/login" class="block text-center px-4 py-3 rounded-lg text-base font-bold text-white bg-white/5 border border-zinc-800">
                            {{ __('landing.nav.login') }}
                        </a>
                        <a href="/auth/register" class="block text-center px-4 py-3 rounded-lg text-base font-bold text-white bg-[#E50914] hover:bg-[#B0070F]">
                            {{ __('landing.nav.register') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </x-container>
</header>
