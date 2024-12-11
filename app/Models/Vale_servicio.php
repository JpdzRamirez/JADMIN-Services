<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vale_servicio extends Model
{
    public $timestamps = false;
    protected $table = 'vale_servicios';

    public function vale()
    {
        return $this->belongsTo(Vale::class, 'vales_id');
    }

    public function usuario(){
        return $this->belongsTo(User::class, 'users_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id');
    }
}
