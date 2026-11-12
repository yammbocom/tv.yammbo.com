@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] p-5">
            <p class="text-xs font-medium text-[#a3a3a3] mb-2">Usuarios</p>
            <p class="text-3xl font-bold">{{ number_format($stats['users']) }}</p>
        </div>
        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] p-5">
            <p class="text-xs font-medium text-[#a3a3a3] mb-2">Suscripciones activas/trial</p>
            <p class="text-3xl font-bold">{{ number_format($stats['active_subscriptions']) }}</p>
        </div>
        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] p-5">
            <p class="text-xs font-medium text-[#a3a3a3] mb-2">Planes activos</p>
            <p class="text-3xl font-bold">{{ number_format($stats['active_plans']) }}</p>
        </div>
        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] p-5">
            <p class="text-xs font-medium text-[#a3a3a3] mb-2">Push enviados</p>
            <p class="text-3xl font-bold">{{ number_format($stats['push_sent']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-hidden">
            <h2 class="px-5 py-3 border-b border-[#262626] text-sm font-semibold text-[#a3a3a3]">Usuarios recientes</h2>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($recentUsers as $recentUser)
                        <tr class="border-b border-[#262626] last:border-0">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ $recentUser->name }}</p>
                                <p class="text-[#a3a3a3] text-xs">{{ $recentUser->email }}</p>
                            </td>
                            <td class="px-5 py-3 text-right text-[#a3a3a3] text-xs whitespace-nowrap">{{ $recentUser->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-3 text-[#a3a3a3]">Sin usuarios todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-hidden">
            <h2 class="px-5 py-3 border-b border-[#262626] text-sm font-semibold text-[#a3a3a3]">Suscripciones recientes</h2>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($recentSubscriptions as $recentSubscription)
                        <tr class="border-b border-[#262626] last:border-0">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ $recentSubscription->user->name ?? $recentSubscription->user->email ?? 'N/A' }}</p>
                                <p class="text-[#a3a3a3] text-xs">{{ $recentSubscription->plan->name ?? 'Sin plan' }} &middot; {{ $recentSubscription->status }}</p>
                            </td>
                            <td class="px-5 py-3 text-right text-[#a3a3a3] text-xs whitespace-nowrap">{{ $recentSubscription->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-3 text-[#a3a3a3]">Sin suscripciones todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
