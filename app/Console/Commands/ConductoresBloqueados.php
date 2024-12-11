<?php

namespace App\Console\Commands;

use App\Models\Cuentac;
use App\Models\Suspension;
use Carbon\Carbon;
use Throwable;
use Illuminate\Console\Command;

class ConductoresBloqueados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conductores:bloqueados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisar las fechas de desbloqueo para conductores';

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
        $cuentas = Cuentac::select('id', 'estado', 'fechabloqueo', 'razon', 'estado')->where('estado', 'Bloqueado')->get();
        $fecha = Carbon::now('-05:00');
        try {
            foreach ($cuentas as $cuenta) {
                if($cuenta->fechabloqueo != null){
                    if($fecha >= $cuenta->fechabloqueo){
                        $cuenta->estado = "No disponible";
                        $cuenta->fechabloqueo = null;
                        $cuenta->razon = null;
                        $cuenta->save();
    
                        $suspension = Suspension::where('cuentasc_id', $cuenta->id)->orderBy('id', 'DESC')->first();
                        if($suspension != null){
                            $suspension->fechadesbloqueo = $fecha;
                            $suspension->save();
                        }
                    }
                }
            }
        } catch (Throwable $e) {

        }
        
        return 0;
        
    }
}
