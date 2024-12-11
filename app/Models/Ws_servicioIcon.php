<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ws_servicioIcon extends Model
{
    public $timestamps = false;
    protected $table = 'ws_servicio';
    protected $primaryKey = 'CONSECUTIVO_SERVICIO';
    protected $connection = 'mysql2';

    // public function error(){
    //     return $this->where(Ws_servicio::class, 'CONSECUTIVO_SERVICIO');
    // }
}
