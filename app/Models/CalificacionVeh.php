<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionVeh extends Model
{
    public $timestamps = false;
    protected $table = 'calificacionesveh';

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_VEHICULO', 'VEHICULO');
    }
}
