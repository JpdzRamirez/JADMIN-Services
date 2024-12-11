<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Cuentae extends Model
{
    public $timestamps = false;
    protected $table = 'cuentase';

    public function valeras(){

        return $this->hasMany(Valera::class, 'cuentase_id');
    }

    public function valeraFisica(){

        return $this->hasOne(ValeraFisica::class, 'cuentase_id');
    }

    /*public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }*/

    public function users()
    {
        return $this->belongsToMany(User::class, 'cuentasexuser', 'cuentase_id', 'users_id');
    }

    use Compoships;
    public function agencia()
    {
        return $this->belongsTo(Agencia_tercero::class, ['agencia_tercero_TERCERO', 'agencia_tercero_CODIGO'], ['TERCERO', 'CODIGO']);
    }
}
