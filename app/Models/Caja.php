<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    public $timestamps = false;
    protected $table = 'cajas';

    public function sucursal(){

        return $this->belongsTo(Sucursal::class, 'sucursales_id');
    }
}
