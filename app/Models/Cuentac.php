<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuentac extends Model
{
    public $timestamps = false;
    protected $table = 'cuentasc';

    public function conductor(){

        return $this->belongsTo(Conductor::class, 'conductor_CONDUCTOR', 'CONDUCTOR');
    }

    public function transacciones(){

        return $this->hasMany(Transaccion::class, 'cuentasc_id');
    }

    public function servicios(){

        return $this->hasMany(Servicio::class, 'cuentasc_id');
    }

    public function servicioscercanos(){

        return $this->belongsToMany(Servicio::class, 'cercanos', 'cuentasc_id', 'servicios_id');
    }

    public function mensajes(){

        return $this->hasMany(Mensaje::class, 'cuentasc_id');
    }

    public function alertas(){

        return $this->hasMany(Alerta::class, 'cuentasc_id');
    }

    public function calificaciones(){

        return $this->hasMany(Calificacion::class, 'cuentasc_id');
    }

    public function suspensiones()
    {
        return $this->hasMany(Suspension::class, 'cuentasc_id');
    }

    public function inactivaciones()
    {
        return $this->hasMany(Inactivacion::class, 'cuentasc_id');
    }
}
