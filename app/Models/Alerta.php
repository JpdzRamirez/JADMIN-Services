<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    public $timestamps = false;
    protected $table = 'alertas';

    public function cuentac(){

        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }

}
