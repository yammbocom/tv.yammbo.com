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
<div class="flex min-h-screen">

    {{-- Barra lateral: mismos 5 puntos de navegación del plan, marcado el activo por routeIs() --}}
    <aside class="w-60 shrink-0 border-r border-[#1f1f1f] bg-[#0f0f0f] p-5 flex flex-col">
        <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2 mb-8">
            <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-8 h-8 rounded">
            <span class="font-bold tracking-tight text-white">Panel</span>
        </a>

        <nav class="space-y-1 text-sm flex-1">
            @php
                $navLinks = [
                    ['route' => 'panel.dashboard', 'pattern' => 'panel.dashboard', 'label' => 'Dashboard'],
                    ['route' => 'panel.users.index', 'pattern' => 'panel.users.*', 'label' => 'Usuarios'],
                    ['route' => 'panel.subscriptions.index', 'pattern' => 'panel.subscriptions.*', 'label' => 'Suscripciones'],
                    ['route' => 'panel.plans.index', 'pattern' => 'panel.plans.*', 'label' => 'Planes'],
                    ['route' => 'panel.push.index', 'pattern' => 'panel.push.*', 'label' => 'Push'],
                ];
            @endphp
            @foreach($navLinks as $navLink)
                <a href="{{ route($navLink['route']) }}"
                   class="block rounded-md px-3 py-2 transition-colors {{ request()->routeIs($navLink['pattern']) ? 'bg-[#E50914] text-white' : 'text-[#888] hover:bg-[#1a1a1a] hover:text-white' }}">
                    {{ $navLink['label'] }}
                </a>
            @endforeach
        </nav>

        <a href="/admin" class="text-xs text-[#666] hover:text-[#888] mt-6">Filament (legado) &rarr;</a>
    </aside>

    <main class="flex-1 p-6 sm:p-8 max-w-6xl">
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
