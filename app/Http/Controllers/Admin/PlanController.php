<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::with('role')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function edit(Plan $plan): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();

        return view('admin.plans.edit', compact('plan', 'roles'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'nullable|string|max:32',
            'yearly_price' => 'nullable|string|max:32',
            'monthly_price_id' => 'nullable|string|max:255',
            'yearly_price_id' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'role_id' => 'required|exists:roles,id',
        ]);

        // El checkbox "active" no viaja en el POST cuando está desmarcado.
        $validated['active'] = $request->boolean('active');

        $plan->update($validated);

        // Los planes activos y la búsqueda por nombre se sirven desde caché.
        Plan::clearCache();

        return Redirect::route('panel.plans.index')->with('success', 'Plan actualizado.');
    }
}
