<?php

namespace App\Console\Commands;

use App\Models\Acuerdo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CuotasMensajes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuotas:mensajes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mensajes de aviso cuotas';

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
        $acuerdos = Acuerdo::with(['propietario.tercero' , 'cuotasAll'=>function($q){$q->where('estado', 'Vigente')->first();}])->whereHas('cuotasAll', function($q){$q->where('estado', 'Vigente');})->get();
        foreach ($acuerdos as $acuerdo) {
            if($hoy->diffInDays($acuerdo->cuotasAll[0]->fecha_vencimiento) == 5){
                $texto = "Hola " . $acuerdo->propietario->tercero->PRIMER_NOMBRE . ", En JADMIN queremos recordarte que la fecha límite de pago para su cuota " . $acuerdo->cuotasAll[0]->numero . " de " .
                $acuerdo->cuotas . " es " . $acuerdo->cuotasAll[0]->fecha_vencimiento . " .Vence en 5 días. Feliz día.";

                //$this->enviarSMS($acuerdo->celular, $texto);
            }elseif($hoy->diffInDays($acuerdo->cuotasAll[0]->fecha_vencimiento) == 1){
                $texto = "Hola " . $acuerdo->propietario->tercero->PRIMER_NOMBRE . ", En JADMIN queremos recordarte que la fecha límite de pago para su cuota " . $acuerdo->cuotasAll[0]->numero . " de " .
                $acuerdo->cuotas . " es mañana " . $acuerdo->cuotasAll[0]->fecha_vencimiento  . ". Feliz día.";
                
               //$this->enviarSMS($acuerdo->celular, $texto);
            }
        }
        
        return 0;
    }

    public function enviarSMS($numero, $texto)
    {
        
        $connection = fopen('https://portal.bulkgate.com/api/1.0/simple/transactional', 'r', false,
        stream_context_create(['http' => [
            'method' => 'POST',
            'header' => [
                'Content-type: application/json'
            ],
            'content' => json_encode([
                'application_id' => '22484',
                'application_token' => '8f1lnuLJwXRwXFzGnwbqbGw8p1WtQZ39U2lqwYLJG46pNLo6Ct',
                'unicode' => '0',
                'number' => '57' . $numero,
                'text' => $texto
            ]),
            'ignore_errors' => true
            ]])
        );

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "SMSAcuerdos.txt", 'a') or die("Error creando archivo");

        if($connection)
        {
            //$response = json_decode(stream_get_contents($connection));        
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). stream_get_contents($connection). " Número: " . $numero) or die("Error escribiendo en el archivo");
            fclose($connection);
        }else{
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). "Falla conexión: " . $numero) or die("Error escribiendo en el archivo");
        }
        fclose($logFile);
    }
}
