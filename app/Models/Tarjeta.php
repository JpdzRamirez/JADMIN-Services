<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    public $timestamps = false;
    protected $table = 'tarjetas';
    use HasFactory;

    public function cliente(){

        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

}
