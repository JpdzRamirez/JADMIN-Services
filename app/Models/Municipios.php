<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipios extends Model
{
    use HasFactory;

    public function departamento(){
        return $this->belongsTo(Departamentos::class, 'departamento_id');
    }
}
