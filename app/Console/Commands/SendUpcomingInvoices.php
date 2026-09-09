<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use App\Support\YamboMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Avisa de la proxima renovacion 5 dias antes del cobro.
 *
 * Se ejecuta una vez al dia. Guarda una marca por suscripcion para no repetir
 * el aviso del mismo periodo si el comando corre varias veces.
 */
class SendUpcomingInvoices extends Command
{
    protected $signature = 'yambo:avisos-renovacion {--dias=5}';
    protected $description = 'Envia el aviso de proxima renovacion a las suscripciones que se cobran pronto';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');
        $desde = Carbon::now()->startOfDay()->addDays($dias);
        $hasta = (clone $desde)->endOfDay();

        $subs = Subscription::where('billable_type', 'user')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$desde, $hasta])
            ->get();

        $enviados = 0;
        foreach ($subs as $sub) {
            $user = $sub->user;
            if (! $user || ! $user->email) {
                continue;
            }

            // No repetir el aviso de este mismo periodo
            $marca = 'aviso_renov_' . $sub->id . '_' . Carbon::parse($sub->ends_at)->format('Ymd');
            if (DB::table('cache')->where('key', $marca)->exists()) {
                continue;
            }

            $plan = Plan::find($sub->plan_id);
            $ok = YamboMail::upcomingInvoice($user, array_filter([
                'Plan'          => $plan ? $plan->name : null,
                'Ciclo'         => $sub->cycle === 'year' ? 'Anual' : 'Mensual',
                'Se cobrara el' => Carbon::parse($sub->ends_at)->format('d/m/Y'),
            ]));

            if ($ok) {
                cache()->put($marca, 1, now()->addDays(40));
                $enviados++;
            }
        }

        $this->info("Avisos de renovacion enviados: {$enviados}");
        return self::SUCCESS;
    }
}
