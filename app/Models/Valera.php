<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valera extends Model
{
    public $timestamps = false;
    protected $table = 'valeras';

    public function vales(){

        return $this->hasMany(Vale::class, 'valeras_id');
    }

    public function cuentae(){

        return $this->belongsTo(Cuentae::class, 'cuentase_id');
    }

    public function bloqueados()
    {
        return $this->belongsToMany(Conductor::class, 'listasnegras', 'valeras_id', 'conductor_CONDUCTOR')->withPivot('estado', 'bloqueo', 'razon_bloqueo', 'desbloqueo', 'razon_desbloqueo');
    }
    
    public function valesav()
    {
		return $this->hasMany(Valeav::class, 'valeras_id');
	}

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class, 'valeras_id');
    }

}
