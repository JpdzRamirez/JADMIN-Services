<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductorIcon extends Model
{
    public $timestamps = false;
    protected $table = 'conductor';
    protected $primaryKey = 'CONDUCTOR';
    protected $connection = 'mysql2';


    public function cuentac(){

        return $this->hasOne(Cuentac::class, 'conductor_CONDUCTOR', 'CONDUCTOR');
    }

    public function vehiculos(){

        return $this->belongsToMany(Vehiculo::class, 'vehiculo_conductor', 'CONDUCTOR', 'VEHICULO', 'CONDUCTOR', 'VEHICULO')->withPivot('SW_ACTIVO_NUEVO_CRM');
    }

    public function bloqueadas()
    {
        return $this->belongsToMany(Valera::class, 'listasnegras', 'conductor_CONDUCTOR', 'valeras_id')->withPivot('estado', 'bloqueo', 'desbloqueo');;
    }
}
