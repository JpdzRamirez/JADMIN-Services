<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiposnovedad extends Model
{
    public $timestamps = false;
    protected $table = 'tiposnovedad';

    public function novedades(){

        return $this->hasMany(Novedad::class, 'tiposnovedad_id');
    }
}
