<?php

namespace App\Console\Commands;

use App\Models\Cuentac;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ConductoresDisponibles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conductores:disponibles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inactivar conductores';

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
        $cuentas = Cuentac::select('id', 'estado', 'ultimasesion')->where('estado', 'Libre')->orWhere('estado', 'Ocupado propio')->get();
        $ahora = Carbon::now();
        foreach ($cuentas as $cuenta) {
            if ($cuenta->estado == "Libre") {
                if($ahora->diffInSeconds($cuenta->ultimasesion) >= 60){
                    $cuenta->estado = "No disponible";
                    $cuenta->save();
                } 
            } else {
                if($ahora->diffInMinutes($cuenta->ultimasesion) >= 60){
                    $cuenta->estado = "No disponible";
                    $cuenta->save();
                } 
            }
            
        }
        
        return 0;
    }
}
