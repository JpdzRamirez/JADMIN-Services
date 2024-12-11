<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cartera extends Model
{
    public $timestamps = false;
    protected $table = 'cartera_generica';
    protected $primaryKey = 'CARTERA_GENERICA';

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'TERCERO');
    }
}
