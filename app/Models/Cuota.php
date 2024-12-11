<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    public $timestamps = false;
    protected $table = 'cuotas';

    public function acuerdo()
    {
        return $this->belongsTo(Acuerdo::class, 'acuerdos_id');
    }
}
