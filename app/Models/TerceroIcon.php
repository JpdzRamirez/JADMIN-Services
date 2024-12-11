<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerceroIcon extends Model
{
    public $timestamps = false;
    protected $table = 'tercero';
    protected $primaryKey = 'TERCERO';
    protected $connection = 'mysql2';

    public function propietario(){

        return $this->hasOne(Propietario::class, 'TERCERO', 'TERCERO');
    }

    public function contratovale(){

        return $this->hasMany(Contrato_vale::class, 'TERCERO', 'TERCERO');
    }

    public function agencias()
    {
        return $this->hasMany(Agencia_tercero::class, 'TERCERO', 'TERCERO');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function cartera()
    {
        return $this->hasMany(Cartera::class, 'TERCERO');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'MUNICIPIO');
    }
}
