<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('inspire')->hourly();
Schedule::command('subscriptions:cancel-expired')->hourly();
Schedule::command('accounts:process-deletions')->daily();
Schedule::command('activity:clean')->daily();

// Aviso de proxima renovacion: una vez al dia, 5 dias antes del cobro.
Illuminate\Support\Facades\Schedule::command('yambo:avisos-renovacion')->dailyAt('09:00');
