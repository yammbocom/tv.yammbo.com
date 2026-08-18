<footer class="bg-black border-t border-zinc-800 mt-20">
    <x-container>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10 py-16">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center space-x-2 mb-4">
                    <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-10 h-10">
                    <span class="text-xl font-bold text-white">Yammbo Tv</span>
                </a>
                <p class="text-sm text-zinc-400 max-w-xs mb-6">
                    {{ __('landing.footer.tagline') }}
                </p>

                {{-- Language switcher --}}
                <div class="flex gap-2">
                    <a href="?lang=es" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ app()->getLocale() === 'es' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white' }} border border-zinc-800">
                        ES
                    </a>
                    <a href="?lang=en" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ app()->getLocale() === 'en' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white' }} border border-zinc-800">
                        EN
                    </a>
                </div>
            </div>

            {{-- Product --}}
            <div>
                <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wide">{{ __('landing.footer.section_product') }}</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="/app" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_app') }}</a></li>
                    <li><a href="#pricing" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_pricing') }}</a></li>
                    <li><a href="/app-tv/download" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_download') }}</a></li>
                    <li><a href="#features" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_features') }}</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wide">{{ __('landing.footer.section_company') }}</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_about') }}</a></li>
                    <li><a href="https://blog.yammbo.com" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_blog') }}</a></li>
                    <li><a href="/app-tv/ayuda" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_help') }}</a></li>
                    <li><a href="mailto:support@yammbo.com" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_contact') }}</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wide">{{ __('landing.footer.section_legal') }}</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="/terms" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_terms') }}</a></li>
                    <li><a href="/privacy" class="text-zinc-400 hover:text-white transition-colors">{{ __('landing.footer.link_privacy') }}</a></li>
                </ul>

                <h3 class="text-sm font-bold text-white mb-4 mt-8 uppercase tracking-wide">{{ __('landing.footer.section_follow') }}</h3>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/yammbo" aria-label="Facebook" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#E50914] border border-white/10 flex items-center justify-center text-zinc-300 hover:text-white transition-all">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/yammbo" aria-label="Instagram" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#E50914] border border-white/10 flex items-center justify-center text-zinc-300 hover:text-white transition-all">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@yammbo" aria-label="TikTok" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#E50914] border border-white/10 flex items-center justify-center text-zinc-300 hover:text-white transition-all">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.84-.1z"/></svg>
                    </a>
                    <a href="https://twitter.com/yammbo" aria-label="X" class="w-9 h-9 rounded-full bg-white/5 hover:bg-[#E50914] border border-white/10 flex items-center justify-center text-zinc-300 hover:text-white transition-all">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Bottom strip --}}
        <div class="border-t border-zinc-800 py-6 text-center">
            <p class="text-xs text-zinc-500">
                {{ __('landing.footer.copyright', ['year' => date('Y')]) }}
            </p>
        </div>
    </x-container>
</footer>
