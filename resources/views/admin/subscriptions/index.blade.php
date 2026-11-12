@extends('admin.layout')

@section('title', 'Suscripciones')

@section('content')
    @php($statuses = ['' => 'Todos', 'active' => 'Active', 'trialing' => 'Trialing', 'cancelled' => 'Cancelled', 'expired' => 'Expired'])

    <form method="GET" action="{{ route('panel.subscriptions.index') }}" class="mb-6 flex gap-2 items-center">
        <label for="status" class="text-xs font-medium text-[#a3a3a3]">Estado</label>
        <select id="status" name="status" onchange="this.form.submit()"
                class="rounded-md bg-[#0a0a0a] border border-[#404040] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-lg border border-[#262626] bg-[#0a0a0a] overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-[#262626] text-left text-xs font-medium text-[#a3a3a3]">
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
                    <tr class="border-b border-[#262626] last:border-0">
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $subscription->id }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium">{{ $subscription->user->name ?? '—' }}</p>
                            <p class="text-[#a3a3a3] text-xs">{{ $subscription->user->email ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $subscription->plan->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block rounded-full border border-[#404040] px-2 py-0.5 text-xs font-semibold
                                @if($subscription->status === 'active') bg-[#0a0a0a] text-white
                                @elseif($subscription->status === 'trialing') bg-[#171717] text-white
                                @elseif($subscription->status === 'cancelled') bg-[#0a0a0a] text-[#FF4D55]
                                @else bg-[#171717] text-[#a3a3a3]
                                @endif">
                                {{ $subscription->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $subscription->vendor_slug }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3]">{{ $subscription->cycle }}</td>
                        {{-- trial_ends_at/ends_at llegan como string plano a propósito (ver Subscription::casts()); se parsean con Carbon aquí --}}
                        <td class="px-5 py-3 text-[#a3a3a3] whitespace-nowrap">{{ $subscription->trial_ends_at ? \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d/m/Y') : '—' }}</td>
                        <td class="px-5 py-3 text-[#a3a3a3] whitespace-nowrap">{{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('d/m/Y') : '—' }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            {{-- Sin usuario vivo no hay nada que cancelar: el controlador lo rechaza igual --}}
                            @if($subscription->status !== 'cancelled' && $subscription->user)
                                <form method="POST" action="{{ route('panel.subscriptions.cancel', $subscription) }}"
                                      onsubmit="return confirm({{ \Illuminate\Support\Js::from('Cancelar la suscripción #'.$subscription->id.' de '.$subscription->user->email.".\n\n".($subscription->vendor_slug === 'stripe' ? 'Se cancela también en Stripe: no se renueva y el acceso sigue hasta el fin del período pagado.' : 'No tiene cobro en Stripe: el acceso se corta ya.')) }});">
                                    @csrf
                                    <button type="submit" class="text-[#FF4D55] hover:text-white text-xs font-semibold">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-6 text-center text-[#a3a3a3]">Sin resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
