<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    public $timestamps = false;
    protected $table = 'modulos';

    public function users(){

        return $this->belongsToMany(User::class, 'permisos', 'modulos_id', 'users_id')->withPivot('ver', 'editar');
    }
}
