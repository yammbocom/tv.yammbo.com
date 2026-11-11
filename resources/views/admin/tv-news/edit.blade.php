@extends('admin.layout')

@section('title', 'Editar novedad')

@section('content')
    <p class="mb-6"><a href="{{ route('panel.tv-news.index') }}" class="text-sm text-[#888] hover:text-white">Volver a novedades</a></p>
    <div class="max-w-2xl rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] p-6">
        @include('admin.tv-news._form', ['action' => route('panel.tv-news.update', $news), 'method' => 'PUT', 'submit' => 'Guardar cambios'])
    </div>
@endsection
