@extends('admin.layout')

@section('title', 'Editar plan')

@section('content')
    <a href="{{ route('panel.plans.index') }}" class="text-xs text-[#888] hover:text-white">&larr; Volver a planes</a>

    <div class="mt-4 max-w-xl rounded-lg border border-[#1f1f1f] bg-[#0f0f0f] p-6">
        <form method="POST" action="{{ route('panel.plans.update', $plan) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $plan->name) }}" required
                       class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
            </div>

            <div>
                <label for="description" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Descripción</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="monthly_price" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Precio mensual</label>
                    <input type="text" id="monthly_price" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price) }}"
                           class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                </div>
                <div>
                    <label for="yearly_price" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Precio anual</label>
                    <input type="text" id="yearly_price" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price) }}"
                           class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="monthly_price_id" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Price ID mensual (Stripe)</label>
                    <input type="text" id="monthly_price_id" name="monthly_price_id" value="{{ old('monthly_price_id', $plan->monthly_price_id) }}"
                           class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                </div>
                <div>
                    <label for="yearly_price_id" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Price ID anual (Stripe)</label>
                    <input type="text" id="yearly_price_id" name="yearly_price_id" value="{{ old('yearly_price_id', $plan->yearly_price_id) }}"
                           class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Orden</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}"
                           class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                </div>
                <div>
                    <label for="role_id" class="block text-xs uppercase tracking-wide text-[#888] mb-1.5">Rol asociado</label>
                    <select id="role_id" name="role_id" required
                            class="w-full rounded-md bg-[#1A1A1A] border border-[#333] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#E50914]">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $plan->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="active" name="active" value="1" @checked(old('active', $plan->active))
                       class="rounded border-[#333] bg-[#1A1A1A] text-[#E50914] focus:ring-[#E50914]">
                <label for="active" class="text-sm text-[#bdbdbd]">Plan activo (visible en /pricing)</label>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-md bg-[#E50914] hover:bg-[#B0070F] px-4 py-2 text-sm font-semibold text-white">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
