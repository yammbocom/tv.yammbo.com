@extends('admin.layout')

@section('title', 'Suscripciones')

@section('content')
    @php($statuses = ['' => 'Todos', 'active' => 'Active', 'trialing' => 'Trialing', 'cancelled' => 'Cancelled', 'expired' => 'Expired'])

    <form method="GET" action="{{ route('panel.subscriptions.index') }}" class="mb-6 flex gap-2 items-center">
        <label for="status" class="text-xs uppercase tracking-wide text-[#888]">Estado</label>
        <select id="status" name="status" onchange="this.form.submit()"
                class="rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#1f1f1f] text-left text-xs uppercase tracking-wide text-[#888]">
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Plan</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Vendor</th>
                    <th class="px-5 py-3">Ciclo</th>
                    <th class="px-5 py-3">Trial hasta</th>
                    <th class="px-5 py-3">Fin</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr class="border-b border-[#1a1a1a] last:border-0">
                        <td class="px-5 py-3 text-[#888]">{{ $subscription->id }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium">{{ $subscription->user->name ?? '—' }}</p>
                            <p class="text-[#888] text-xs">{{ $subscription->user->email ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-[#bdbdbd]">{{ $subscription->plan->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold
                                @if($subscription->status === 'active') bg-[#0f3a1c] text-[#5dd87f]
                                @elseif($subscription->status === 'trialing') bg-[#1c2a3a] text-[#7fbfff]
                                @elseif($subscription->status === 'cancelled') bg-[#3a0f15] text-[#ff8b9b]
                                @else bg-[#1a1a1a] text-[#bdbdbd]
                                @endif">
                                {{ $subscription->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-[#888]">{{ $subscription->vendor_slug }}</td>
                        <td class="px-5 py-3 text-[#888]">{{ $subscription->cycle }}</td>
                        {{-- trial_ends_at/ends_at llegan como string plano a propósito (ver Subscription::casts()); se parsean con Carbon aquí --}}
                        <td class="px-5 py-3 text-[#888] whitespace-nowrap">{{ $subscription->trial_ends_at ? \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d/m/Y') : '—' }}</td>
                        <td class="px-5 py-3 text-[#888] whitespace-nowrap">{{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('d/m/Y') : '—' }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            {{-- Sin usuario vivo no hay nada que cancelar: el controlador lo rechaza igual --}}
                            @if($subscription->status !== 'cancelled' && $subscription->user)
                                <form method="POST" action="{{ route('panel.subscriptions.cancel', $subscription) }}"
                                      onsubmit="return confirm('Marcar como cancelada la suscripción #{{ $subscription->id }} de {{ $subscription->user->email }}.\n\nMantiene el acceso hasta la fecha de fin y NO detiene el cobro en Stripe.');">
                                    @csrf
                                    <button type="submit" class="text-[#FFB3B8] hover:text-[#ff8b9b] text-xs font-semibold">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-6 text-center text-[#888]">Sin resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
