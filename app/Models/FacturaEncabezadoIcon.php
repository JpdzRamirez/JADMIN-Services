<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaEncabezadoIcon extends Model
{
    public $timestamps = false;
    protected $table = 'factura_encabezado';
    protected $primaryKey = 'CONSECUTIVO_FACTURA';
    protected $connection = 'mysql2';
}
