<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'rutas';

    public function pasajeros()
    {
        return $this->belongsToMany(Pasajero::class, 'pasajerosxruta', 'rutas_id', 'pasajeros_id')->withPivot('novedadesmaj_id', 'observaciones')->using(Pasajerosxruta::class);
    }

    public function servicioRuta()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id');
    }

    public function programacion()
    {
        return $this->belongsTo(Programacion::class, 'programaciones_id');
    }
}
