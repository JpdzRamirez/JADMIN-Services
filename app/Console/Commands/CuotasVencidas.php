<?php

namespace App\Console\Commands;

use App\Models\Cuota;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CuotasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuotas:vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vencer cuotas';

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
        $hoy = Carbon::now();
        $cuotas = Cuota::with('acuerdo')->whereDate('fecha_vencimiento', $hoy)->get();
        foreach($cuotas as $cuota){
            $cuota->estado = "Vencida";
            $cuota->save();

            $cuota->acuerdo->vencidas = $cuota->acuerdo->vencidas + 1;
            $cuota->acuerdo->save();
        }
        
        return 0;
    }
}
