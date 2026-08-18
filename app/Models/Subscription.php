<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Suscripciones. Absorbido de Wave\Subscription para poder retirar el paquete:
 * apunta a la misma tabla `subscriptions`, así que convive con la clase vieja
 * mientras dure la migración.
 */
class Subscription extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'billable_type',
        'billable_id',
        'plan_id',
        'vendor_slug',
        'vendor_product_id',
        'vendor_transaction_id',
        'vendor_customer_id',
        'vendor_subscription_id',
        'cycle',
        'status',
        'seats',
        'trial_ends_at',
        'ends_at',
        'last_payment_at',
        'next_payment_at',
        'cancel_url',
        'update_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'next_payment_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billable_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();

        $this->user->syncRoles([]);
        $this->user->assignRole(config('yammbo.default_user_role', 'registered'));
    }
}
