<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\SignupPromo;
use Illuminate\Console\Command;

/**
 * Estado de la promo de alta (ver config/yammbo.php, app/Support/SignupPromo.php)
 * y aplicacion retroactiva a usuarios ya existentes.
 *
 * Sin --backfill-from-id solo informa; con el, recorre esos usuarios y les
 * concede la promo (o explica por que no, en --dry-run).
 */
class SignupPromoCommand extends Command
{
    protected $signature = 'yambo:signup-promo {--backfill-from-id= : Aplica la promo a usuarios con id >= este} {--dry-run}';

    protected $description = 'Estado de la promo de alta y aplicación retroactiva';

    public function handle(): int
    {
        $enabled = SignupPromo::enabled();
        $days = (int) config('yammbo.signup_promo.days', 0);
        $plan = Plan::find((int) config('yammbo.signup_promo.plan_id', 0));

        $this->info('Promo de alta: '.($enabled ? 'activada' : 'desactivada'));
        $this->line('Dias: '.$days);
        $this->line('Plan: '.($plan ? "{$plan->id} ({$plan->name})" : 'no encontrado'));

        $activas = Subscription::where('vendor_slug', 'promo')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();
        $this->line("Suscripciones de promo activas: {$activas}");

        $fromId = $this->option('backfill-from-id');
        if ($fromId === null) {
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->newLine();
        $this->info($dryRun
            ? "Simulando backfill desde id >= {$fromId} (dry-run, no se escribe nada)"
            : "Aplicando backfill desde id >= {$fromId}");

        $concedidos = 0;
        $sinCambios = 0;

        User::where('id', '>=', (int) $fromId)
            ->orderBy('id')
            ->chunk(100, function ($users) use ($dryRun, &$concedidos, &$sinCambios) {
                foreach ($users as $user) {
                    if ($dryRun) {
                        $decision = SignupPromo::plan($user);
                        if ($decision['action'] === 'grant') {
                            $this->line("#{$user->id} {$user->email}: concederia hasta {$decision['until']->format('d/m/Y H:i')}");
                            $concedidos++;
                        } else {
                            $this->line("#{$user->id} {$user->email}: sin cambios ({$decision['reason']})");
                            $sinCambios++;
                        }

                        continue;
                    }

                    $until = SignupPromo::grant($user);
                    if ($until) {
                        $this->line("#{$user->id} {$user->email}: concedido hasta {$until->format('d/m/Y H:i')}");
                        $concedidos++;
                    } else {
                        $this->line("#{$user->id} {$user->email}: sin cambios");
                        $sinCambios++;
                    }
                }
            });

        $this->newLine();
        $this->info("Total: {$concedidos} concedidos, {$sinCambios} sin cambios.");

        return self::SUCCESS;
    }
}
