<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiario extends Model
{
    public $timestamps = false;
    protected $table = 'beneficiarios';

    public function valera()
    {
        return $this->belongsTo(Valera::class, 'valeras_id');
    }
}
