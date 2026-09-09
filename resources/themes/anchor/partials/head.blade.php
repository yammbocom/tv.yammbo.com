@php
    if(isset($seo)){
        $seo = (is_array($seo)) ? ((object)$seo) : $seo;
    }
@endphp
@if(isset($seo->title))
    <title>{{ $seo->title }}</title>
@else
    <title>{{ setting('site.title', 'Laravel Wave') . ' - ' . setting('site.description', 'The Software as a Service Starter Kit built with Laravel') }}</title>
@endif

<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge"> <!-- † -->
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('/') }}">

<x-favicon></x-favicon>

{{-- Social Share Open Graph Meta Tags --}}
@if(isset($seo->title) && isset($seo->description) && isset($seo->image))
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:url" content="{{ Request::url() }}">
    <meta property="og:image" content="{{ $seo->image }}">
    <meta property="og:type" content="@if(isset($seo->type)){{ $seo->type }}@else{{ 'article' }}@endif">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:site_name" content="{{ setting('site.title') }}">

    <meta itemprop="name" content="{{ $seo->title }}">
    <meta itemprop="description" content="{{ $seo->description }}">
    <meta itemprop="image" content="{{ $seo->image }}">

    @if(isset($seo->image_w) && isset($seo->image_h))
        <meta property="og:image:width" content="{{ $seo->image_w }}">
        <meta property="og:image:height" content="{{ $seo->image_h }}">
    @endif
@endif

{{-- El sitio no debe aparecer en buscadores: se accede por enlace directo.
     robots.txt solo "pide" no rastrear; el que de verdad evita que se indexe
     es este noindex (y la cabecera X-Robots-Tag del .htaccess). --}}
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex, nofollow">

{{-- yambo-seo-extra: canonical, hreflang, twitter card y datos estructurados.
     Sin canonical, las mismas paginas servidas con ?lang= o con/sin barra final
     se ven como duplicados. El hreflang le dice a Google que hay version en
     ingles y en espanol de la MISMA pagina, no dos paginas distintas. --}}
@php
    $yamboUrl  = url()->current();
    $yamboEs   = $yamboUrl.(request()->getQueryString() ? '?'.request()->getQueryString() : '');
    $yamboBase = rtrim(config('app.url') ?: 'https://tv.yammbo.com', '/');
    $yamboPath = '/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/');
    $yamboCanonical = $yamboBase.($yamboPath === '/' ? '' : $yamboPath);
@endphp
<link rel="canonical" href="{{ $yamboCanonical }}">
<link rel="alternate" hreflang="es" href="{{ $yamboCanonical }}{{ $yamboPath === '/' ? '/' : '' }}?lang=es">
<link rel="alternate" hreflang="en" href="{{ $yamboCanonical }}{{ $yamboPath === '/' ? '/' : '' }}?lang=en">
<link rel="alternate" hreflang="x-default" href="{{ $yamboCanonical }}">

<meta name="twitter:card" content="summary_large_image">
@if(isset($seo->title))
    <meta name="twitter:title" content="{{ $seo->title }}">
@endif
@if(isset($seo->description))
    <meta name="twitter:description" content="{{ $seo->description }}">
@endif
@if(isset($seo->image))
    <meta name="twitter:image" content="{{ $seo->image }}">
@endif



@if(isset($seo->description))
    <meta name="description" content="{{ $seo->description }}">
@endif

<link rel="preload" as="font" type="font/woff2" href="/fonts/archivo-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/instrument-sans-latin.woff2" crossorigin>

@livewireStyles
@vite(['resources/themes/anchor/assets/css/app.css', 'resources/themes/anchor/assets/js/app.js'])
