<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flota extends Model
{
    public $timestamps = false;
    protected $table = 'flotas';

    public function vehiculos(){

        return $this->belongsToMany(Vehiculo::class, 'vehiculo_flotas', 'flotas_id', 'vehiculo_VEHICULO', 'id', 'VEHICULO');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'flotas_id');
    }
}
