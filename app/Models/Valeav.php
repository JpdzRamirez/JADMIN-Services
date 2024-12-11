<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valeav extends Model
{
    public $timestamps = false;
    protected $table = 'valesav';

    public function servicio(){

        return $this->belongsTo(Servicio::class, 'servicios_id');
    }

    public function valera(){

        return $this->belongsTo(Valera::class, 'valeras_id');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'valeav_servicios', 'valesav_id', 'servicios_id');
    }
}
