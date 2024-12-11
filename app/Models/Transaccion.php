<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    public $timestamps = false;
    protected $table = 'transacciones';

    public function cuentac(){

        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }

    public function sucursal(){

        return $this->belongsTo(Sucursal::class, 'sucursales_id');
    }

    public function user(){

        return $this->belongsTo(User::class, 'users_id');
    }
}
