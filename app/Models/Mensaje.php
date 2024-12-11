<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    public $timestamps = false;
    protected $table = 'mensajes';

    public function cuentac(){

        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }

}
