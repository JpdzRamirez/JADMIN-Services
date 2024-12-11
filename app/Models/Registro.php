<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    public $timestamps = false;
    protected $table = 'registros';

    public function servicio(){

        return $this->belongsTo(Servicio::class, 'servicios_id');
    }
}
