<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    public $timestamps = false;
    protected $table = 'unidad';
    protected $primaryKey = 'UNIDAD';

    public function contratos()
    {
        return $this->hasMany(Contrato_vale::class, 'TARIFA_COBRO', 'UNIDAD');
    }

    public function unidadvalores()
    {
        return $this->hasMany(Unidad_valor::class, 'UNIDAD', 'UNIDAD');
    }
}
