<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValeraFisica extends Model
{
    public $timestamps = false;
    protected $table = 'valerasfisicas';

    
    public function bloqueados()
    {
        return $this->belongsToMany(Conductor::class, 'bloqueados_fisicas', 'valerasfisicas_id', 'conductor_CONDUCTOR')->withPivot('estado', 'bloqueo', 'razon_bloqueo', 'desbloqueo', 'razon_desbloqueo');
    }

    public function cuentae()
    {
        return $this->belongsTo(Cuentae::class, 'cuentase_id');
    }
}
