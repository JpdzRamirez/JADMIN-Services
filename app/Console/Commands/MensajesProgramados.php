<?php

namespace App\Console\Commands;

use App\Models\Mensaje;
use App\Models\Smsprogramado;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MensajesProgramados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mensajes:programados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mensajes programados';

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
        $ahora = Carbon::now();
        $programados = Smsprogramado::where('duracion', '>', $ahora->toDateString())->where('estado', '1')->get();
        foreach ($programados as $programado) {
            if ($ahora > $programado->ultimo) {
                if ($ahora->diffInMinutes($programado->ultimo) >= $programado->intervalo) {
                    $mensaje = new Mensaje();
                    $mensaje->fecha = $ahora;
                    $mensaje->texto = $programado->mensaje;
                    $mensaje->sentido = "Recibido";
                    $mensaje->estado = "Revisado";
                    $mensaje->masivo = true;
                    //$mensaje->cuentasc_id = $cuenta->id;
                    $mensaje->save();
    
                    $programado->ultimo = Carbon::now();
                    $programado->save();
                }
            }
        }

        return 0;
    }
}
