<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destino extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'destinos';

    public function punto()
    {
        // Define la relación 'belongsTo' que indica que un destino pertenece a un punto
        return $this->belongsTo(Punto::class,'puntos_id');
    }
}
