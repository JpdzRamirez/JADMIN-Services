<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo_documento extends Model
{
    public $timestamps = false;
    protected $table = 'vehiculo_documento';

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'VEHICULO');
    }

    public function aseguradora()
    {
        return $this->belongsTo(Aseguradora::class, 'ASEGURADORA');
    }
}
