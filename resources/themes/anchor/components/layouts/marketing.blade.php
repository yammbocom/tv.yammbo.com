<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="overflow-x-clip">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null) ])
</head>
<body x-data class="flex flex-col min-h-screen overflow-x-clip bg-paper text-ink font-body @if($bodyClass ?? false){{ $bodyClass }}@endif" x-cloak>

    <x-marketing.elements.header />

    <main class="flex-grow overflow-x-clip">
        {{ $slot }}
    </main>

    {{-- @livewire('notifications') era el toast de Filament; se fue con el paquete --}}
    @include('theme::partials.footer')
    @include('theme::partials.footer-scripts')
    {{ $javascript ?? '' }}

    {{-- Yammbo chat widget (chat.yammbo.com) — marketing pages only, not /app SPA nor /admin --}}
    {{-- The widget ships the Yammbo blue; tv's brand is red, so its accent tokens are overridden here. --}}
    <style>
        #yammbo-chat-root, #yammbo-chat-root[data-yc-theme] { --yc-accent: #E50914 !important; --yc-accent-hover: #B0070F !important; --yc-accent-text: #FF4D55 !important; --yc-accent-fg: #ffffff !important; }
        #yammbo-chat-root[data-yc-theme="light"] { --yc-accent-text: #B0070F !important; }
    </style>
    <script src="https://chat.yammbo.com/widget.js" defer></script>

</body>
</html>
