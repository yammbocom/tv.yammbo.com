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
{{-- En móvil se apila: la barra pasa a cabecera y la navegación a fila con scroll.
     Con `w-60` fija se comía 240px de un ancho de 360 y el contenido no cabía. --}}
<div class="flex flex-col md:flex-row min-h-screen">

    <aside class="w-full md:w-60 md:shrink-0 border-b md:border-b-0 md:border-r border-[#1f1f1f] bg-[#0f0f0f] px-4 py-3 md:p-5 flex flex-col">
        <div class="flex items-center justify-between md:block">
            <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2 md:mb-8">
                <img src="/images/yambo-icon.png" alt="Yammbo Tv" class="w-7 h-7 md:w-8 md:h-8 rounded">
                <span class="font-bold tracking-tight text-white">Panel</span>
            </a>
            {{-- En móvil el pie de la barra no se ve, así que la salida sube aquí --}}
            <a href="/auth/logout" class="md:hidden text-xs text-[#888] hover:text-white">Salir</a>
        </div>

        <nav class="flex md:flex-col gap-1 text-sm md:flex-1 mt-3 md:mt-0 -mx-4 px-4 md:mx-0 md:px-0 overflow-x-auto md:overflow-visible">
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
                   class="shrink-0 rounded-md px-3 py-2 transition-colors {{ request()->routeIs($navLink['pattern']) ? 'bg-[#E50914] text-white' : 'text-[#888] hover:bg-[#1a1a1a] hover:text-white' }}">
                    {{ $navLink['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden md:block mt-6 pt-4 border-t border-[#1f1f1f] text-xs">
            <p class="text-[#666] mb-2 truncate">{{ auth()->user()->email }}</p>
            {{-- El logout lo sigue sirviendo devdojo/auth, que es lo que se mantiene --}}
            <a href="/auth/logout" class="text-[#888] hover:text-white">Cerrar sesión</a>
        </div>
    </aside>

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 max-w-6xl">
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
