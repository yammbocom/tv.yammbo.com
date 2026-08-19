<header
    x-data="{ mobileOpen: false }"
    x-init="$watch('mobileOpen', v => document.body.classList.toggle('overflow-hidden', v))"
    class="sticky top-4 z-50 px-4"
>
    {{-- `relative z-50` para que la píldora quede por encima del panel móvil:
         el panel es hijo de este header y va después en el DOM, así que sin
         esto lo tapaba y el botón de cerrar dejaba de verse. --}}
    <div class="nav-pill relative z-50 mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-2.5 sm:px-5">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-display text-[color:var(--color-ink)]">
            <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-7 h-7" width="28" height="28">
            <span class="hidden sm:inline text-[length:var(--text-sm)] font-semibold whitespace-nowrap">Yammbo Tv</span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-6 text-[length:var(--text-sm)] font-body">
            <a href="/pricing" class="link-quiet whitespace-nowrap">{{ __('landing.nav.pricing') }}</a>
            <a href="/help" class="link-quiet whitespace-nowrap">{{ __('landing.nav.help') }}</a>
            @guest
                <a href="/auth/login" class="link-quiet whitespace-nowrap">{{ __('landing.nav.login') }}</a>
            @endguest
        </nav>

        {{-- Desktop accent CTA --}}
        <div class="hidden md:block shrink-0">
            @auth
                <a href="/app" class="btn btn-accent">{{ __('landing.nav.app') }}</a>
            @else
                <a href="/pricing" class="btn btn-accent">{{ __('landing.pricing.cta') }}</a>
            @endauth
        </div>

        {{-- Mobile toggle --}}
        {{-- El aria-label decía "Planes": un lector de pantalla anunciaba el
             botón de menú como si fuera el enlace de precios. --}}
        <button @click="mobileOpen = !mobileOpen" type="button"
                :aria-expanded="mobileOpen ? 'true' : 'false'"
                aria-controls="nav-mobile"
                :aria-label="mobileOpen
                    ? '{{ app()->getLocale() === 'es' ? 'Cerrar menú' : 'Close menu' }}'
                    : '{{ app()->getLocale() === 'es' ? 'Abrir menú' : 'Open menu' }}'"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-full text-[color:var(--color-ink-mute)]">
            <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
            <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Menú móvil a pantalla completa.

         Antes reutilizaba .nav-pill, que lleva border-radius:999px porque está
         pensada para una barra horizontal: en vertical se deformaba en un óvalo
         y dejaba ver la página por las esquinas, con el botón saliéndose de la
         curva. Un panel opaco que cubre la pantalla evita las dos cosas. --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-out" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         style="transition-duration: var(--dur-mid)"
         id="nav-mobile" class="nav-sheet fixed inset-0 z-40 flex flex-col md:hidden">
        <nav class="flex flex-col gap-1 px-6 pt-28 pb-8">
            <a href="/pricing" @click="mobileOpen = false" class="nav-sheet-link">{{ __('landing.nav.pricing') }}</a>
            <a href="/help" @click="mobileOpen = false" class="nav-sheet-link">{{ __('landing.nav.help') }}</a>
            @auth
                <a href="/app" @click="mobileOpen = false" class="btn btn-accent mt-4 justify-center">{{ __('landing.nav.app') }}</a>
            @else
                <a href="/auth/login" @click="mobileOpen = false" class="nav-sheet-link">{{ __('landing.nav.login') }}</a>
                <a href="/pricing" @click="mobileOpen = false" class="btn btn-accent mt-4 justify-center">{{ __('landing.pricing.cta') }}</a>
            @endauth
        </nav>
    </div>
</header>
