@extends('admin.layout')

@section('title', 'Planes')

@section('content')
    <div class="rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#1f1f1f] text-left text-xs uppercase tracking-wide text-[#888]">
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
                    <tr class="border-b border-[#1a1a1a] last:border-0">
                        <td class="px-5 py-3 text-[#888]">{{ $plan->sort_order }}</td>
                        <td class="px-5 py-3 font-medium">{{ $plan->name }}</td>
                        <td class="px-5 py-3 text-[#bdbdbd]">{{ $plan->monthly_price ?? '—' }}</td>
                        <td class="px-5 py-3 text-[#bdbdbd]">{{ $plan->yearly_price ?? '—' }}</td>
                        <td class="px-5 py-3 text-[#888]">{{ $plan->role->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($plan->active)
                                <span class="inline-block rounded-full bg-[#0f3a1c] text-[#5dd87f] px-2 py-0.5 text-xs font-semibold">Activo</span>
                            @else
                                <span class="inline-block rounded-full bg-[#1a1a1a] text-[#888] px-2 py-0.5 text-xs font-semibold">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('panel.plans.edit', $plan) }}" class="text-[#E50914] hover:text-[#FF3B45] text-xs font-semibold">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-6 text-center text-[#888]">Sin planes todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
