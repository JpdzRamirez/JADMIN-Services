<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    public $timestamps = false;
    protected $table = 'seguimientos';

    public function servicio(){

        return $this->belongsTo(Servicio::class, 'servicios_id');
    }
}
