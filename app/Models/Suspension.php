<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    public $timestamps = false;
    protected $table = 'suspensiones';

    public function operador1()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function operador2()
    {
        return $this->belongsTo(User::class, 'users2_id');
    }

    public function sancion()
    {
        return $this->belongsTo(Sancion::class, 'sanciones_id');
    }
}
