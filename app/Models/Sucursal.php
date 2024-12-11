<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    public $timestamps = false;
    protected $table = 'sucursales';

    public function transacciones(){

        return $this->hasMany(Transaccion::class, 'sucursales_id');
    }

    public function tercero(){

        return $this->belongsTo(Tercero::class, 'tercero_TERCERO');
    }

    public function user(){

        return $this->belongsTo(User::class, 'users_id');
    }

    public function cajas(){

        return $this->hasMany(Caja::class, 'sucursales_id');
    }

}
