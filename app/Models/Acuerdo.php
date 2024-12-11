<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acuerdo extends Model
{
    public $timestamps = false;
    protected $table = 'acuerdos';
    
    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'propietario_tercero', 'TERCERO');
    }

    public function cuotasAll()
    {
        return $this->hasMany(Cuota::class, 'acuerdos_id');
    }
}
