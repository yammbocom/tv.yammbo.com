<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function edit(User $user): View
    {
        // Solo roles del guard "web": es el único que usa la app.
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Un solo rol por usuario, igual que hace Plan::switchPlans().
        $role = Role::where('guard_name', 'web')->findOrFail($validated['role']);

        // Quitarse a uno mismo el rol admin, siendo el único que lo tiene,
        // cierra /panel y /admin a la vez y solo se arregla por base de datos.
        if ($role->name !== 'admin' && $user->hasRole('admin') && $this->isLastAdmin($user)) {
            return Redirect::route('panel.users.edit', $user)
                ->with('error', 'No se puede quitar el rol admin: es el único administrador que queda.');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$role]);
        $user->clearUserCache();

        return Redirect::route('panel.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return Redirect::route('panel.users.index')
                ->with('error', 'No puedes eliminar tu propia cuenta desde el panel.');
        }

        // El borrado es soft y el listado no muestra los borrados, así que
        // perder al último admin aquí no tendría vuelta atrás desde la interfaz.
        if ($user->hasRole('admin') && $this->isLastAdmin($user)) {
            return Redirect::route('panel.users.index')
                ->with('error', 'No se puede eliminar al único administrador.');
        }

        $user->delete();

        return Redirect::route('panel.users.index')->with('success', 'Usuario eliminado.');
    }

    /**
     * ¿Es el único usuario con rol admin?
     */
    private function isLastAdmin(User $user): bool
    {
        return User::role('admin')->where('id', '!=', $user->id)->doesntExist();
    }
}
