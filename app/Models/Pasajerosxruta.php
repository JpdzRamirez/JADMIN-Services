<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Pasajerosxruta extends Pivot
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pasajerosxruta';

    public function novedad()
    {
        return $this->belongsTo(NovedadMajorel::class, 'novedadesmaj_id');
    }
}
