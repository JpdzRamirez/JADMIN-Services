<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Contrato_vale extends Model
{
    public $timestamps = false;
    protected $table = 'contrato_vale';
    

    public function tercero(){

        return $this->belongsTo(Tercero::class, 'TERCERO', 'TERCERO');
    }

    public function tarifa()
    {
        return $this->belongsTo(Unidad::class, 'TARIFA_COBRO', 'UNIDAD');
    }

    use Compoships;
    public function rutas(){

        return $this->hasMany(Contrato_vale_ruta::class, 'CONTRATO_VALE', 'SECUENCIA', 'CONTRATO_VALE', 'SECUENCIA');
    }



}
