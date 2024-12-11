<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    public $timestamps = false;
    protected $table = 'calificaciones';

    public function cuentac(){

        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }
}
