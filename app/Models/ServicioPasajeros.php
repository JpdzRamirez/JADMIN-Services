<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioPasajeros extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'servicio_pasajeros';
}
