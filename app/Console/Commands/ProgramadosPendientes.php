<?php

namespace App\Console\Commands;

use App\Models\Cancelacion;
use App\Models\Servicio;
use Carbon\Carbon;
use Throwable;
use Illuminate\Console\Command;

class ProgramadosPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'programados:pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atender programados pendientes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $servicios = Servicio::select('id', 'fechaprogramada', 'estado')->with('vale', 'valeav')->where('estado', "Pendiente")->whereNotNull('fechaprogramada')->get();

        try {
            $fecha = Carbon::now();
            foreach ($servicios as $servicio) {
                if($fecha > $servicio->fechaprogramada){
                    if ($fecha->diffInHours($servicio->fechaprogramada) >= 1) {
                        $servicio->estado = "No vehiculo";
                        $servicio->save();
    
                        if ($servicio->vale != null){
                            if($servicio->vale->centrocosto != null){
                                $servicio->vale->estado = "Asignado";
                            }else{
                                $servicio->vale->estado = "Libre";
                            }
                            $servicio->vale->servicios_id = null;
                            $servicio->vale->save();
                        }elseif ($servicio->valeav != null) {
                            $servicio->valeav->estado = "Libre";
                            $servicio->valeav->servicios_id = null;
                            $servicio->valeav->save();
                        }
                    }
                }
            }
        } catch (Throwable $e) {

        }
        

        $servicios = Servicio::select('id', 'fecha', 'cuentasc_id', 'estado')->with('vale', 'valeav')->where('estado', 'En curso')->orWhere('estado', 'Asignado')->get();
        $fecha = Carbon::now();
        foreach ($servicios as $servicio) {
            if($fecha->diffInDays($servicio->fecha) >= 5){
                $servicio->estado = "Cancelado";
                $servicio->save();

                if ($servicio->vale != null){
                    if($servicio->vale->centrocosto != null){
                        $servicio->vale->estado = "Asignado";
                    }else{
                        $servicio->vale->estado = "Libre";
                    }
                    $servicio->vale->servicios_id = null;
                    $servicio->vale->save();
                }elseif ($servicio->valeav != null) {
                    $servicio->valeav->estado = "Libre";
                    $servicio->valeav->servicios_id = null;
                    $servicio->valeav->save();
                }

                $cancelacion = new Cancelacion();
                $cancelacion->razon = "Cancelado por vencimiento del servicio";
                $cancelacion->fecha = Carbon::now('-05:00');
                $cancelacion->servicios_id = $servicio->id;
                $cancelacion->save();
            }
        }
        
        $servicios = Servicio::select('id', 'fecha', 'estado')->with('vale', 'valeav')->where('estado', 'Pendiente')->whereNull('fechaprogramada')->get();
        $fecha = Carbon::now();
        foreach ($servicios as $servicio) {
            if($fecha->diffInMinutes($servicio->fecha) >= 5){
                $servicio->estado = "No vehiculo";
                $servicio->save();

                if ($servicio->vale != null){
                    if($servicio->vale->centrocosto != null){
                        $servicio->vale->estado = "Asignado";
                    }else{
                        $servicio->vale->estado = "Libre";
                    }
                    $servicio->vale->servicios_id = null;
                    $servicio->vale->save();
                }elseif ($servicio->valeav != null) {
                    $servicio->valeav->estado = "Libre";
                    $servicio->valeav->servicios_id = null;
                    $servicio->valeav->save();
                }
            }
        }
        
        return 0;
    }
}
