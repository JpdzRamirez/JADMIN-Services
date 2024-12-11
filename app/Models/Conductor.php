<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    public $timestamps = false;
    protected $table = 'conductor';
    protected $primaryKey = 'CONDUCTOR';


    public function cuentac(){

        return $this->hasOne(Cuentac::class, 'conductor_CONDUCTOR', 'CONDUCTOR');
    }

    public function vehiculos(){

        return $this->belongsToMany(Vehiculo::class, 'vehiculo_conductor', 'CONDUCTOR', 'VEHICULO', 'CONDUCTOR', 'VEHICULO')->withPivot('SW_ACTIVO', 'SW_ACTIVO_NUEVO_CRM');
    }

    public function bloqueadas()
    {
        return $this->belongsToMany(Valera::class, 'listasnegras', 'conductor_CONDUCTOR', 'valeras_id')->withPivot('estado', 'bloqueo', 'razon_bloqueo', 'desbloqueo', 'razon_desbloqueo');
    }

    public function eps()
    {
        return $this->belongsTo(Fondo::class, 'EPS', 'FONDO');
    }

    public function arp()
    {
        return $this->belongsTo(Fondo::class, 'ARP', 'FONDO');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'MUNICIPIO');
    }

    public function pension()
    {
        return $this->belongsTo(Fondo::class, 'PENSION', 'FONDO');
    }
}
