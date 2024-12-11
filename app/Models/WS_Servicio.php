<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WS_Servicio extends Model
{
    public $timestamps = false;
    protected $table = 'ws_servicio';
    protected $primaryKey = 'CONSECUTIVO_SERVICIO';
}
