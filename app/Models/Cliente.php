<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    public $timestamps = false;
    protected $table = 'clientes';

    public function servicios(){

        return $this->hasMany(Servicio::class, 'clientes_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(CalificacionCli::class, 'clientes_id');
    }
}
