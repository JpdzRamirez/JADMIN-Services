<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'programaciones';

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'programaciones_id');
    }
}
