@extends('admin.layout')

@section('title', 'Planes')

@section('content')
    <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#262626] text-left text-xs font-medium text-[#a3a3a3]">
                    <th class="px-5 py-3">Orden</th>
                    <th class="px-5 py-3">Nombre</th>
                    <th class="px-5 py-3">Mensual</th>
                    <th class="px-5 py-3">Anual</th>
                    <th class="px-5 py-3">Rol</th>
                    <th class="px-5 py-3">Activo</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr class="border-b border-[#262626] last:border-0">
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $plan->sort_order }}</td>
                        <td class="px-5 py-3 font-medium">{{ $plan->name }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $plan->monthly_price ?? '—' }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $plan->yearly_price ?? '—' }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $plan->role->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($plan->active)
                                <span class="inline-block rounded-full bg-[#0a0a0a] text-white border border-[#404040] px-2 py-0.5 text-xs font-semibold">Activo</span>
                            @else
                                <span class="inline-block rounded-full bg-[#171717] text-[#a3a3a3] border border-[#404040] px-2 py-0.5 text-xs font-semibold">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('panel.plans.edit', $plan) }}" class="text-[#FF4D55] hover:text-white text-xs font-semibold">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-6 text-center text-[#a3a3a3]">Sin planes todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
