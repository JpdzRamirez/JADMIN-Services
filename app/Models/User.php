<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;
    protected $table = 'users';

    public function rol(){

        return $this->belongsTo(Rol::class, 'roles_id');
    }

    public function modulos(){

        return $this->belongsToMany(Modulo::class, 'permisos', 'users_id', 'modulos_id')->withPivot('ver', 'editar');
    }

    public function sucursal(){

        return $this->hasOne(Sucursal::class, 'users_id');
    }

    public function transacciones(){

        return $this->hasMany(Transaccion::class, 'users_id');
    }

    public function cuentae()
    {
        return $this->hasOne(Cuentae::class, 'users_id');
    }
    
    public function cancelaciones()
    {
        return $this->hasMany(Cancelacion::class, 'users_id');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'users_id');
    }

    public function tercero()
    {
        return $this->hasOne(Tercero::class, 'users_id');
    }

    public function cuentase()
    {
        return $this->belongsToMany(Cuentae::class, 'cuentasexuser', 'users_id', 'cuentase_id');
    }
}

