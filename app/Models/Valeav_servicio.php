<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valeav_servicio extends Model
{
    public $timestamps = false;
    protected $table = 'valeav_servicios';

    public function valeav()
    {
        return $this->belongsTo(Valeav::class, 'valesav_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id');
    }
}
