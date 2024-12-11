<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vale extends Model
{
    public $timestamps = false;
    protected $table = 'vales';

    public function servicio(){

        return $this->belongsTo(Servicio::class, 'servicios_id');
    }

    public function valera(){

        return $this->belongsTo(Valera::class, 'valeras_id');
    }

    public function valeServicio()
    {
        return $this->hasMany(Vale_servicio::class, 'vales_id');
    }
}
