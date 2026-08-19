<footer class="mt-20 border-t border-rule">
    <x-container class="py-16 sm:py-20">
        <p class="font-display text-[color:var(--color-ink)] text-[length:var(--text-display-s)] max-w-3xl"
           style="overflow-wrap: anywhere; min-width: 0;">
            {{ __('landing.footer.tagline') }}
        </p>

        <div class="mt-10 pt-8 border-t border-rule flex flex-col gap-4 text-[length:var(--text-sm)] font-body sm:flex-row sm:items-center sm:justify-between">
            <nav class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <a href="/privacy" class="link-quiet whitespace-nowrap">{{ __('landing.footer.link_privacy') }}</a>
                <a href="/terms" class="link-quiet whitespace-nowrap">{{ __('landing.footer.link_terms') }}</a>
                <a href="/help" class="link-quiet whitespace-nowrap">{{ __('landing.footer.link_help') }}</a>
                {{-- /app-tv/download es una ruta viva y este era su único enlace
                     público; se quedó fuera al rehacer el footer. --}}
                <a href="/app-tv/download" class="link-quiet whitespace-nowrap">{{ __('landing.footer.link_download') }}</a>
                <a href="mailto:support@yammbo.com" class="link-quiet whitespace-nowrap">support@yammbo.com</a>
            </nav>

            <div class="flex items-center gap-4 text-[color:var(--color-ink-dim)]">
                <div class="flex items-center gap-2">
                    <a href="?lang=es" class="link-quiet whitespace-nowrap {{ app()->getLocale() === 'es' ? 'text-[color:var(--color-ink)]' : '' }}">ES</a>
                    <span>/</span>
                    <a href="?lang=en" class="link-quiet whitespace-nowrap {{ app()->getLocale() === 'en' ? 'text-[color:var(--color-ink)]' : '' }}">EN</a>
                </div>
                <span class="whitespace-nowrap">{{ __('landing.footer.copyright', ['year' => date('Y')]) }}</span>
            </div>
        </div>
    </x-container>
</footer>
