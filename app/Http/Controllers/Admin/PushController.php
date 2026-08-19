<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushLog;
use App\Services\FcmPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Envío manual de push a un topic FCM. Réplica de app/Filament/Pages/PushNotifications.php
 * sin Livewire: mismo servicio, mismo log, mismo manejo de errores.
 */
class PushController extends Controller
{
    public function index(): View
    {
        $logs = PushLog::latest()->take(30)->get();

        return view('admin.push.index', compact('logs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:64',
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
            'click_url' => 'nullable|url',
            'image_url' => 'nullable|url',
        ]);

        $service = app(FcmPushService::class);

        try {
            $result = $service->sendToTopic($validated['topic'], [
                'title' => $validated['title'],
                'body' => $validated['body'],
                'click_url' => $validated['click_url'] ?: null,
                'image' => $validated['image_url'] ?: null,
            ]);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'error' => $e->getMessage(), 'response' => []];
        }

        PushLog::create([
            'user_id' => $request->user()?->id,
            'topic' => $validated['topic'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'click_url' => $validated['click_url'] ?: null,
            'image_url' => $validated['image_url'] ?: null,
            'success' => $result['success'],
            'fcm_message_id' => $result['message_id'] ?? null,
            'error' => $result['error'] ?? null,
            'response' => $result['response'] ?? null,
        ]);

        if ($result['success']) {
            return Redirect::route('panel.push.index')
                ->with('success', 'Push enviado. FCM message id: '.($result['message_id'] ?? 'n/a'));
        }

        return Redirect::route('panel.push.index')
            ->with('error', 'Error al enviar push: '.($result['error'] ?? 'Error desconocido'));
    }
}
