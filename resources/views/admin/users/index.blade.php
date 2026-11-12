@extends('admin.layout')

@section('title', 'Usuarios')

@section('content')
    <form method="GET" action="{{ route('panel.users.index') }}" class="mb-6 flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o email..."
               class="flex-1 max-w-sm rounded-md bg-[#0a0a0a] border border-[#404040] px-3 py-2 text-sm text-white placeholder-[#a3a3a3] focus:outline-none focus:border-[#E50914]">
        <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">Buscar</button>
        @if($search !== '')
            <a href="{{ route('panel.users.index') }}" class="rounded-md border border-[#404040] px-4 py-2 text-sm text-[#a3a3a3] hover:text-white">Limpiar</a>
        @endif
    </form>

    <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#262626] text-left text-xs font-medium text-[#a3a3a3]">
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Nombre</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Roles</th>
                    <th class="px-5 py-3">Trial hasta</th>
                    <th class="px-5 py-3">Alta</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b border-[#262626] last:border-0">
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $user->id }}</td>
                        <td class="px-5 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            @forelse($user->roles as $role)
                                <span class="inline-block rounded-full bg-[#171717] border border-[#404040] px-2 py-0.5 text-xs text-[#a3a3a3] mr-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-[#a3a3a3] text-xs">&mdash;</span>
                            @endforelse
                        </td>
                        {{-- trial_ends_at llega como string plano a propósito (ver User::casts()); se parsea con Carbon aquí --}}
                        <td class="px-5 py-3 text-[#a3a3a3] whitespace-nowrap">{{ $user->trial_ends_at ? \Carbon\Carbon::parse($user->trial_ends_at)->format('d/m/Y') : '—' }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3] whitespace-nowrap">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('panel.users.edit', $user) }}" class="text-[#FF4D55] hover:text-white text-xs font-semibold">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-6 text-center text-[#a3a3a3]">Sin resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
