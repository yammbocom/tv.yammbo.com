<?php
    use function Laravel\Folio\{name, render};
    name('home');

    render(function (\Illuminate\View\View $view) {
        if (auth()->check()) {
            return redirect('/app');
        }
        return $view;
    });
?>

<x-layouts.marketing
    :seo="[
        'title'         => __('landing.seo.title'),
        'description'   => __('landing.seo.description'),
        'image'         => asset('/images/yambo-icon.png'),
        'type'          => 'website'
    ]"
>
    {{-- 1. Apertura funcional --}}
    <section class="pt-10 sm:pt-14 fade-in">
        <x-container>
            <div class="max-w-2xl">
                <h1 class="font-display text-[color:var(--color-ink)] text-[length:var(--text-display-s)] leading-[1.1]"
                    style="overflow-wrap: anywhere; min-width: 0;">
                    {{ __('landing.hero.heading_1') }} {{ __('landing.hero.heading_2') }}
                </h1>
                <p class="mt-4 max-w-xl text-[length:var(--text-lg)] text-[color:var(--color-ink-mute)]">
                    {{ __('landing.hero.tagline') }}
                </p>
            </div>
        </x-container>

        <div class="mt-8 sm:mt-10 px-4 sm:px-6 lg:px-10 xl:px-16">
            <figure class="shot">
                <img src="/images/app/spa-discover.webp" width="1600" height="1000"
                     alt="{{ __('landing.shots.discover_alt') }}"
                     class="w-full h-auto">
                <figcaption>{{ __('landing.shots.discover_caption') }}</figcaption>
            </figure>
        </div>
    </section>

    {{-- 2. Secuencia Workbench --}}
    <section class="mt-24 sm:mt-32">
        <x-container>
            <div class="grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] md:items-center md:gap-12">
                <div class="min-w-0">
                    <ul class="space-y-5">
                        @foreach(['f1','f3','f4'] as $key)
                            <li class="min-w-0">
                                <p class="font-display text-[color:var(--color-ink)] text-[length:var(--text-base)] font-semibold">{{ __('landing.features.'.$key.'_title') }}</p>
                                <p class="mt-1 text-[color:var(--color-ink-mute)] text-[length:var(--text-sm)] leading-relaxed">{{ __('landing.features.'.$key.'_body') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <figure class="shot min-w-0">
                    <img src="/images/app/spa-detail.webp" width="1600" height="1000" loading="lazy"
                         alt="{{ __('landing.shots.detail_alt') }}"
                         class="w-full h-auto">
                    <figcaption>{{ __('landing.shots.detail_caption') }}</figcaption>
                </figure>
            </div>
        </x-container>
    </section>

    <section class="mt-20 sm:mt-28">
        <x-container>
            <div class="grid gap-8 md:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] md:items-center md:gap-12">
                <figure class="shot min-w-0 md:order-1">
                    <img src="/images/app/spa-search.webp" width="1600" height="1000" loading="lazy"
                         alt="{{ __('landing.shots.search_alt') }}"
                         class="w-full h-auto">
                    <figcaption>{{ __('landing.shots.search_caption') }}</figcaption>
                </figure>
                <div class="min-w-0 md:order-2">
                    <ul class="space-y-5">
                        @foreach(['f2','f5'] as $key)
                            <li class="min-w-0">
                                <p class="font-display text-[color:var(--color-ink)] text-[length:var(--text-base)] font-semibold">{{ __('landing.features.'.$key.'_title') }}</p>
                                <p class="mt-1 text-[color:var(--color-ink-mute)] text-[length:var(--text-sm)] leading-relaxed">{{ __('landing.features.'.$key.'_body') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-container>
    </section>

    <section class="mt-20 sm:mt-28">
        <x-container>
            <div class="max-w-xl">
                <p class="font-display text-[color:var(--color-ink)] text-[length:var(--text-xl)] font-semibold">
                    {{ __('landing.features.group3_heading') }}
                </p>
            </div>

            {{-- f6/f7/f8 eran una rejilla de tres bloques iguales: el último
                 resto de plantilla que quedaba tras el rediseño. Ahora son
                 anotaciones junto a la captura, como el resto de la página. --}}
            <div class="mt-8 grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] md:items-center md:gap-12">
                <div class="min-w-0">
                    <ul class="space-y-5">
                        @foreach(['f6','f7','f8'] as $key)
                            <li class="min-w-0">
                                <p class="font-display text-[color:var(--color-ink)] text-[length:var(--text-base)] font-semibold">{{ __('landing.features.'.$key.'_title') }}</p>
                                <p class="mt-1 text-[color:var(--color-ink-mute)] text-[length:var(--text-sm)] leading-relaxed">{{ __('landing.features.'.$key.'_body') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <figure class="shot min-w-0">
                    <img src="/images/app/spa-board.webp" width="1600" height="910" loading="lazy"
                         alt="{{ __('landing.shots.board_alt') }}"
                         class="w-full h-auto">
                    <figcaption>{{ __('landing.shots.board_caption') }}</figcaption>
                </figure>
            </div>
        </x-container>
    </section>

    {{-- 3. CTA pegajoso: aparece cuando ya se ha visto el recorrido y se
         retira al llegar a los precios, donde estorbaría.

         Antes colgaba de un IntersectionObserver sobre un <span hidden>: con
         display:none la caja mide 0×0 y no intersecta nunca, así que la barra
         no llegó a aparecerle a nadie. Y el marcador estaba a 98px de #pricing,
         con lo que la ventana útil habría sido de menos de un scroll. --}}
    <div
        x-data="{ show: false, closed: false }"
        x-init="
            const pricingEl = document.getElementById('pricing');
            const update = () => {
                if (closed || !pricingEl) return;
                const pricingTop = pricingEl.getBoundingClientRect().top;
                show = window.scrollY > window.innerHeight && pricingTop > window.innerHeight * 0.9;
            };
            update();
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update, { passive: true });
        "
        x-show="show && !closed"
        x-cloak
        x-transition:enter="transition ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-out" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        style="transition-duration: var(--dur-mid)"
        class="sticky-cta fixed inset-x-0 bottom-0 z-40"
    >
        {{-- En móvil el texto de apoyo se oculta y el botón quedaba pegado a la
             izquierda con la X al otro extremo, descentrado. Ahora el botón se
             centra y la X se ancla al borde sin desplazarlo. --}}
        <x-container class="relative flex items-center justify-center gap-4 py-3 sm:justify-between">
            <p class="hidden sm:block text-[length:var(--text-sm)] text-[color:var(--color-ink-mute)] whitespace-nowrap">{{ __('landing.hero.hint') }}</p>
            <div class="flex items-center gap-3">
                <a href="/pricing" class="btn btn-accent">{{ __('landing.pricing.cta') }}</a>
                <button @click="closed = true; show = false" type="button"
                        aria-label="{{ __('landing.ui.close') }}"
                        class="absolute right-6 sm:static text-[color:var(--color-ink-dim)] hover:text-[color:var(--color-ink)]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </x-container>
    </div>

    {{-- 4. Precios --}}
    <x-container class="py-16 sm:py-24 border-t border-rule">
        <x-marketing.sections.pricing />
    </x-container>

    {{-- 5. FAQ --}}
    <x-container class="py-16 sm:py-24 border-t border-rule">
        <x-marketing.sections.faq />
    </x-container>
</x-layouts.marketing>
