<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Agencia_tercero extends Model
{
    public $timestamps = false;
    protected $table = 'agencia_tercero';

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'TERCERO', 'TERCERO');
    }

    use Compoships;
    public function cuentae()
    {
        return $this->hasOne(Cuentae::class, ['agencia_tercero_TERCERO', 'agencia_tercero_CODIGO'], ['TERCERO', 'CODIGO']);
    }
}
