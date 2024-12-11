<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuarioav extends Model
{
    public $timestamps = false;
    protected $table = 'usuariosav';

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_usuariosav', 'usuariosav_id', 'servicios_id');
    }
}
