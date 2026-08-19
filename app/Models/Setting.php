<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ajustes del sitio (título, Analytics, etc.), en la tabla `settings`.
 * Absorbido de Wave\Setting para poder retirar el paquete; se lee a través del
 * helper global setting().
 */
class Setting extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
