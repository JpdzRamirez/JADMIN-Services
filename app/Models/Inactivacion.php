<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inactivacion extends Model
{
    public $timestamps = false;
    protected $table = 'inactivaciones';

    public function cuentac()
    {
        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }

    public function operador1()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function operador2()
    {
        return $this->belongsTo(User::class, 'users2_id');
    }
}
