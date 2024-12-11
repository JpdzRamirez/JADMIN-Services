<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    public $timestamps = false;
    protected $table = 'servicios';

    public function cuentac(){

        return $this->belongsTo(Cuentac::class, 'cuentasc_id');
    }

    public function vale(){

        return $this->hasOne(Vale::class, 'servicios_id');
    }

    public function valeav(){

        return $this->hasOne(Valeav::class, 'servicios_id');
    }

    public function cuentascercanas(){

        return $this->belongsToMany(Cuentac::class, 'cercanos', 'servicios_id', 'cuentasc_id');
    }

    public function cliente(){

        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    public function registros(){

        return $this->hasMany(Registro::class, 'servicios_id');
    }

    public function seguimientos(){

        return $this->hasMany(Seguimiento::class, 'servicios_id');
    }

    public function novedades(){

        return $this->hasMany(Novedad::class, 'servicios_id');
    }

    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class, 'servicios_id');
    }

    public function flota()
    {
        return $this->belongsTo(Flota::class, 'flotas_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function vale_servicio()
    {
        return $this->hasOne(Vale_servicio::class, 'servicios_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'users2_id');
    }

    public function operador_asignacion(){
        return $this->belongsTo(User::class, 'users3_id');
    }

    public function valeav_servicio()
    {
        return $this->hasOne(Valeav_servicio::class, 'servicios_id');
    }

    public function usuariosav()
    {
        return $this->belongsToMany(Usuarioav::class, 'servicio_usuariosav', 'servicios_id', 'usuariosav_id');
    }
    
    public function ruta()
    {
        return $this->hasOne(Ruta::class, 'servicios_id');
    }

    public function pasajeros()
    {
        return $this->belongsToMany(Pasajero::class, 'servicio_pasajeros', 'servicios_id', 'pasajeros_id')->withPivot('sub_cuenta', 'affe', 'solicitado', 'autorizado');
    }
    use Compoships;
    public function contratoValeRuta()
    {
        return $this->belongsto(Contrato_vale_ruta::class, ['CONTRATO_VALE', 'SECUENCIA'], ['CONTRATO_VALE', 'SECUENCIA']);
    }
}
