<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    public $timestamps = false;
    protected $table = 'propietario';
    protected $primaryKey = 'TERCERO';

    public function vehiculos(){

        return $this->belongsToMany(Vehiculo::class, 'vehiculo_otro_propietario', 'TERCERO', 'VEHICULO', 'TERCERO', 'VEHICULO');
    }

    public function tercero(){

        return $this->belongsTo(Tercero::class, 'TERCERO', 'TERCERO');
    }

    public function vehiculospri(){

        return $this->hasMany(Vehiculo::class, 'PROPIETARIO', 'TERCERO');
    }
}
