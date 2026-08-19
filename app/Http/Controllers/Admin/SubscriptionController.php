<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $subscriptions = Subscription::with(['user', 'plan'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions', 'status'));
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        // NO se usa Subscription::cancel(): ese es el handler del webhook
        // `customer.subscription.deleted`, o sea, lo que corre cuando Stripe ya
        // dio por terminado el cobro, y por eso degrada el rol en el acto.
        // Aquí se replica lo que hace el propio usuario en
        // /mi-suscripcion/cancelar: marcar la baja conservando el rol y
        // `ends_at`, porque lo pagado sigue disfrutándose hasta esa fecha.
        //
        // Sin esto, cancelar la suscripción del único administrador (que es la
        // única de pago que hay) le quitaba el rol `admin` y cerraba de golpe
        // /panel y /admin, sin forma de recuperarlo salvo tocando la base.
        if ($subscription->user === null) {
            // El usuario está borrado: cancel() reventaría al sincronizar sus
            // roles, después de haber guardado ya el cambio de estado.
            return Redirect::route('panel.subscriptions.index')
                ->with('error', 'La suscripción #'.$subscription->id.' pertenece a un usuario eliminado.');
        }

        $subscription->status = 'cancelled';
        $subscription->save();

        $subscription->user->clearUserCache();

        return Redirect::route('panel.subscriptions.index')
            ->with('success', 'Suscripción #'.$subscription->id.' marcada como cancelada. El acceso se mantiene hasta la fecha de fin. Ojo: esto NO detiene el cobro en Stripe.');
    }
}
