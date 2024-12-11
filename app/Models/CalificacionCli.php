<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionCli extends Model
{
    public $timestamps = false;
    protected $table = 'calificacionescli';

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
