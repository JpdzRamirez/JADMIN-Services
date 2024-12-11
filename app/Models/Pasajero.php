<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasajero extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pasajeros';
    
    public function ruta()
    {
        return $this->belongsToMany(Ruta::class, 'pasajerosxruta', 'pasajeros_id', 'rutas_id');
    }
}
