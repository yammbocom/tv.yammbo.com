@extends('admin.layout')

@section('title', 'Editar usuario')

@section('content')
    <a href="{{ route('panel.users.index') }}" class="text-xs text-[#888] hover:text-white">&larr; Volver a usuarios</a>

    <div class="mt-4 max-w-xl rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] p-6">
        <form method="POST" action="{{ route('panel.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div>
                <label for="email" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div>
                <label for="role" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Rol</label>
                @php($currentRoleId = $user->roles->first()?->id)
                <select id="role" name="role" required
                        class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role', $currentRoleId) == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Acceso real de la app: vive en subscriptions, no en el rol --}}
            <div class="border-t border-[#222] pt-4 mt-1">
                <p class="text-xs uppercase tracking-wide text-[#888] mb-1">Acceso a la app</p>
                <p class="text-xs text-[#666] mb-3">
                    El acceso lo da la suscripción, no el rol. Aquí activas o cancelas el plan a mano.
                </p>
                <div class="mb-3 rounded-md border {{ $hasAccess ? 'border-[#1f5a35] bg-[#0a2818] text-[#9be4af]' : 'border-[#5a1d22] bg-[#2a0e10] text-[#ffb3b8]' }} px-3 py-2 text-sm">
                    @if($hasAccess && $sub)
                        <strong>Con acceso</strong> · {{ optional($sub->plan)->name ?? '—' }}
                        · {{ $sub->vendor_slug === 'stripe' ? 'pago con Stripe' : ($sub->vendor_slug === 'manual' ? 'acceso manual' : ($sub->vendor_slug ?: 'sin origen')) }}
                        · {{ $sub->ends_at ? 'hasta '.\Carbon\Carbon::parse($sub->ends_at)->format('d/m/Y') : 'sin fecha de fin' }}
                    @elseif($hasAccess)
                        <strong>Con acceso</strong> · prueba gratis
                    @else
                        <strong>Sin acceso</strong>
                        @if($sub) · última: {{ optional($sub->plan)->name ?? '—' }} · {{ $sub->status }}@if($sub->ends_at) · terminó {{ \Carbon\Carbon::parse($sub->ends_at)->format('d/m/Y') }}@endif @endif
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="access_plan" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Plan</label>
                        <select id="access_plan" name="access_plan"
                                class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                            <option value="none">Sin cambios</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}{{ optional($sub)->plan_id == $plan->id ? ' (actual)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="access_status" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Estado</label>
                        <select id="access_status" name="access_status"
                                class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                            <option value="active" @selected(optional($sub)->status !== 'cancelled')>Activa</option>
                            <option value="cancelled" @selected(optional($sub)->status === 'cancelled')>Cancelada</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">Guardar cambios</button>
            </div>
        </form>

        {{-- Ni la cuenta propia ni el último admin: perderlos cierra /panel y /admin --}}
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('panel.users.destroy', $user) }}"
              onsubmit="return confirm('¿Eliminar a {{ $user->email }}? Esta acción es un borrado suave, se puede restaurar en base de datos.');"
              class="mt-6 pt-6 border-t border-[#1f1f1f]">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-md border border-[#5A1D22] px-4 py-2 text-sm font-semibold text-[#FFB3B8] hover:bg-[#1A0608]">Eliminar usuario</button>
        </form>
        @endif
    </div>
@endsection
