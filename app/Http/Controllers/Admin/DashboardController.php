<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PushLog;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

/**
 * Portada del panel: números de un vistazo + lo último que ha pasado en la app.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'active_subscriptions' => Subscription::whereIn('status', ['active', 'trialing'])->count(),
            'active_plans' => Plan::where('active', true)->count(),
            'push_sent' => PushLog::count(),
        ];

        $recentUsers = User::latest()->take(5)->get();

        $recentSubscriptions = Subscription::with(['user', 'plan'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentSubscriptions'));
    }
}
