<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    public $timestamps = false;
    protected $table = 'vehiculo';
    protected $primaryKey = 'VEHICULO';

    public function propietarios(){

        return $this->belongsToMany(Propietario::class, 'vehiculo_otro_propietario', 'VEHICULO', 'TERCERO');
    }

    public function conductores(){
        
        return $this->belongsToMany(Conductor::class, 'vehiculo_conductor', 'VEHICULO', 'CONDUCTOR', 'VEHICULO', 'CONDUCTOR')->withPivot('SW_ACTIVO_NUEVO_CRM');
    }

    public function propietario(){

        return $this->belongsTo(Propietario::class, 'PROPIETARIO', 'TERCERO');
    }

    public function marca(){

        return $this->belongsTo(Marca::class, 'MARCA', 'MARCA');
    }

    public function flotas()
    {
        return $this->belongsToMany(Flota::class, 'vehiculo_flotas', 'vehiculo_VEHICULO', 'flotas_id', 'VEHICULO', 'id');
    }

    public function documentos()
    {
        return $this->hasMany(Vehiculo_documento::class, 'VEHICULO');
    }

    public function calificaciones()
    {
        return $this->hasMany(CalificacionVeh::class, 'vehiculo_VEHICULO', 'VEHICULO');
    }

}
