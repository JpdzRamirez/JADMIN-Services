<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Contrato_vale_ruta extends Model
{
    public $timestamps = false;
    protected $table = 'contrato_vale_ruta';

    use Compoships;
    public function contratovale()
    {
        return $this->belongsTo(Contrato_vale::class, 'CONTRATO_VALE', 'SECUENCIA', 'CONTRATO_VALE', 'SECUENCIA');
    }
}
