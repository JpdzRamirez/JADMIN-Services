<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad_valor extends Model
{
    public $timestamps = false;
    protected $table = 'unidad_valor';

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'UNIDAD', 'UNIDAD');
    }
}
