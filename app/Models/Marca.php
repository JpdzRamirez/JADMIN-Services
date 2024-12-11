<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    public $timestamps = false;
    protected $table = 'marca';

    public function vehiculos(){

        return $this->hasMany(Vehiculo::class, 'MARCA', 'MARCA');
    }
}
