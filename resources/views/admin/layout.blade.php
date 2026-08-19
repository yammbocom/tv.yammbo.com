<!DOCTYPE html>
<html lang="es" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'Panel') — Yammbo Tv</title>
<link rel="icon" href="/images/yambo-icon.png" type="image/png">
@vite(['resources/themes/anchor/assets/css/app.css', 'resources/themes/anchor/assets/js/app.js'])
</head>
<body class="bg-black text-white min-h-screen antialiased">
@php
    // Una sola definición para las dos navegaciones: barra lateral en escritorio
    // y barra inferior en móvil. Los iconos son trazos simples, sin librería.
    $navLinks = [
        ['route' => 'panel.dashboard', 'pattern' => 'panel.dashboard', 'label' => 'Inicio',
         'icon' => 'M3 10.5 12 3l9 7.5M5.5 9.5V20h13V9.5'],
        ['route' => 'panel.users.index', 'pattern' => 'panel.users.*', 'label' => 'Usuarios',
         'icon' => 'M16 19v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V19M9.5 9.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM21 19v-1.5a4 4 0 0 0-3-3.87M16 3.63a4 4 0 0 1 0 7.75'],
        ['route' => 'panel.subscriptions.index', 'pattern' => 'panel.subscriptions.*', 'label' => 'Subs',
         'icon' => 'M3 8.5h18M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7ZM6.5 15h4'],
        ['route' => 'panel.plans.index', 'pattern' => 'panel.plans.*', 'label' => 'Planes',
         'icon' => 'M4 7h16M4 12h16M4 17h9'],
        ['route' => 'panel.push.index', 'pattern' => 'panel.push.*', 'label' => 'Push',
         'icon' => 'M18 8a6 6 0 1 0-12 0c0 6-2 7-2 7h16s-2-1-2-7M13.7 20a1.9 1.9 0 0 1-3.4 0'],
    ];
@endphp

{{-- La navegación cambia de forma según el dispositivo: en escritorio barra
     lateral; en móvil barra inferior fija, que se alcanza con el pulgar. La
     fila horizontal con scroll que había antes obligaba a arrastrar para ver
     las últimas secciones. --}}
<div class="flex flex-col md:flex-row min-h-screen">

    <aside class="w-full md:w-60 md:shrink-0 border-b md:border-b-0 md:border-r border-[#1f1f1f] bg-[#0f0f0f] px-4 py-3 md:p-5 flex flex-col">
        <div class="flex items-center justify-between md:block">
            <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2 md:mb-8">
                <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-7 h-7 md:w-8 md:h-8 rounded">
                <span class="font-bold tracking-tight text-white">Panel</span>
            </a>
            <a href="/auth/logout" class="md:hidden text-xs text-[#888] hover:text-white">Salir</a>
        </div>

        {{-- Barra lateral: solo de md hacia arriba --}}
        <nav class="hidden md:flex md:flex-col gap-1 text-sm md:flex-1">
            @foreach($navLinks as $navLink)
                <a href="{{ route($navLink['route']) }}"
                   @if(request()->routeIs($navLink['pattern'])) aria-current="page" @endif
                   class="rounded-md px-3 py-2 transition-colors {{ request()->routeIs($navLink['pattern']) ? 'bg-[#E50914] text-white' : 'text-[#888] hover:bg-[#1a1a1a] hover:text-white' }}">
                    {{ $navLink['label'] === 'Subs' ? 'Suscripciones' : $navLink['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden md:block mt-6 pt-4 border-t border-[#1f1f1f] text-xs">
            <p class="text-[#666] mb-2 truncate">{{ auth()->user()->email }}</p>
            {{-- El logout lo sigue sirviendo devdojo/auth, que es lo que se mantiene --}}
            <a href="/auth/logout" class="text-[#888] hover:text-white">Cerrar sesión</a>
        </div>
    </aside>

    {{-- Barra inferior: solo en móvil --}}
    <nav class="md:hidden fixed inset-x-0 bottom-0 z-50 grid grid-cols-5 border-t border-[#1f1f1f] bg-[#0f0f0f] pb-[env(safe-area-inset-bottom)]"
         aria-label="Secciones del panel">
        @foreach($navLinks as $navLink)
            @php($activo = request()->routeIs($navLink['pattern']))
            <a href="{{ route($navLink['route']) }}"
               @if($activo) aria-current="page" @endif
               class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] leading-none transition-colors {{ $activo ? 'text-[#E50914]' : 'text-[#888]' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $navLink['icon'] }}"/>
                </svg>
                <span>{{ $navLink['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 max-w-6xl pb-28 md:pb-8">
        @if(session('success'))
            <div class="mb-6 rounded-md border border-[#1f5a35] bg-[#0a2818] px-4 py-3 text-sm text-[#9be4af]">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-md border border-[#5a1d22] bg-[#2a0e10] px-4 py-3 text-sm text-[#ffb3b8]">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-md border border-[#5a1d22] bg-[#2a0e10] px-4 py-3 text-sm text-[#ffb3b8]">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-xl font-bold tracking-tight mb-6">@yield('title', 'Panel')</h1>

        @yield('content')
    </main>
</div>
</body>
</html>
