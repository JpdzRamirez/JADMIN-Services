<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    public $timestamps = false;
    protected $table = 'novedades';

    public function servicio(){

        return $this->belongsTo(Servicio::class, 'servicios_id');
    }

    public function tiponovedad(){

        return $this->belongsTo(Tiposnovedad::class, 'tiposnovedad_id');
    }
}
