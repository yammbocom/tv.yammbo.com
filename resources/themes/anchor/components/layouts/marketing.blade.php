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
    <script src="https://chat.yammbo.com/widget.js" defer></script>

</body>
</html>
