<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancelacion extends Model
{
    public $timestamps = false;
    protected $table = 'cancelaciones';

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
