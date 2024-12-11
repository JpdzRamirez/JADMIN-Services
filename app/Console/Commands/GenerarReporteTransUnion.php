<?php

namespace App\Console\Commands;

use App\Models\Credito;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\TemplateProcessor;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class GenerarInformeTransunion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'informetransunion:generar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generacion y envio de informe mensual reporte a Transunion';

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
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        
        $fechain = Carbon::now()->subMonth()->startOfMonth();
        $fechafi = Carbon::now()->subMonth()->endOfMonth();
        $rango = [$fechain->format('Y-m-d'), $fechafi->format('Y-m-d')];
    
        try{
            $creditos = Credito::with(['pagos'=>function($q) use($rango){$q->select('id', 'valor', 'fecha', 'creditos_id')->with('cuotas')->whereBetween('fecha', $rango);}, 
                'cuotas'=>function($q){$q->select('id', 'fecha_vencimiento', 'mora', 'estado', 'saldo_interes', 'saldo_mora', 'saldo_capital', 'creditos_id');}, 
                'factura'=>function($q){$q->select('id', 'prefijo', 'numero', 'creditos_id');}, 
                'cliente'=>function($q){$q->select('id', 'nro_identificacion', 'primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre', 'direccion', 'email', 'celular');}])
            //->whereHas('cuotas', function($q) use($rango){$q->whereBetween('fecha_vencimiento', $rango);})
            //->orWhere(function($q) use($fechain){$q->where('estado', 'En cobro')->where('fecha_prestamo', '<', $fechain->format('Y-m-d'));})->get();
            ->where(function($q) use($rango){$q->where('estado', 'Finalizado')->whereHas('pagos', function($r) use($rango){$r->whereBetween('fecha', $rango);});})
            ->orWhere(function($q) use($fechain){$q->where('estado', 'En cobro')->where('fecha_prestamo', '<', $fechain->format('Y-m-d'));})->get();
            
            $excel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "plantillaInformeCentral.xlsx");
            $hoja = $excel->setActiveSheetIndex(0);
            $fila = 2;

            //return "excel cargado";
            foreach ($creditos as $credito) {
                if($credito->tipo != "Seguro de Vida"){
                    $hoja->getCell('A'.$fila)->setValue(1);
                    $hoja->getCell('B'.$fila)->setValue($credito->cliente->nro_identificacion);
                    $hoja->getCell('C'.$fila)->setValue($credito->cliente->primer_apellido . " " . $credito->cliente->segundo_apellido . " " . $credito->cliente->primer_nombre . " " . $credito->cliente->segundo_nombre);
                    $numCuotas = count($credito->cuotas);
                    $mora = 0;
                    $i = 0;
                    $cuotasVencidas = 0;
                    $valorMora = 0;
                    $valorSaldo = 0;
                    $sumaPagos = 0;
                    $encontro = false;                
    
                    foreach ($credito->cuotas as $cuota) {
                        $i++;
                        $fechaVencimiento = Carbon::parse($cuota->fecha_vencimiento);
                        if($fechain <= $fechaVencimiento && $fechafi >= $fechaVencimiento){
                            $encontro = true;
                            $hoja->getCell('E'.$fila)->setValue(str_replace("-", "" ,$cuota->fecha_vencimiento));//Fecha limite de pago
                        }
        
                        if($cuota->estado == "Vencida"){ 
                            $cuotasVencidas++;
                            $valorMora = $valorMora + $cuota->saldo_capital + $cuota->saldo_interes;
    
                            if($mora == 0){
                                $mora = $cuota->mora;
                                $edadMora = $this->calcularMora($mora);
                                $hoja->getCell('L'.$fila)->setValue($edadMora); 
    
                                //Generar carta
                                if($mora > 0 && $mora < 30){
                                    $nombreCompleto = $credito->cliente->primer_nombre . " " . $credito->cliente->segundo_nombre . " " . $credito->cliente->primer_apellido . " " . $credito->cliente->segundo_apellido;
                                    self::crearCarta($nombreCompleto, $credito->cliente->email, $credito->cliente->celular);       
                                }
                            }
                        }
    
                        if($cuota->estado != "Pagada"){
                            $valorSaldo = $valorSaldo + $cuota->saldo_capital + $cuota->saldo_interes;
                        }
                        
                        if($i == $numCuotas){//Se verifica la ultima cuota
                           //Estado de ultima cuota
                            $hoja->getCell('P'.$fila)->setValue(str_replace("-", "", $cuota->fecha_vencimiento));
                            if($cuota->estado == "Pagada"){
                                $hoja->getCell('K'.$fila)->setValue(2);
                                $hoja->getCell('AI'.$fila)->setValue(2);//Estado de contrato
                            }
                            else{
                                $hoja->getCell('K'.$fila)->setValue(1);
                                $hoja->getCell('AI'.$fila)->setValue(1);//Estado de contrato
                            }
    
                            if($encontro == false){
                                $hoja->getCell('E'.$fila)->setValue(str_replace("-", "" ,$cuota->fecha_vencimiento));
                            }
                        } 
                    }
    
                    if($mora == 0){
                        $hoja->getCell('L'.$fila)->setValue(0); 
                    }
    
                    if($credito->antiguo == 1){
                        $hoja->getCell('F'.$fila)->setValue("CF");//Numero de obligacion
                    }else{
                        $numObligacion = "CF" . $credito->factura->numero;
                        $hoja->getCell('F'.$fila)->setValue($numObligacion);//Numero de obligacion
                    }
                    $hoja->getCell('G'.$fila)->setValue(1);//Codigo de sucursal
                    $hoja->getCell('H'.$fila)->setValue("P");//Calidad
                    $hoja->getCell('N'.$fila)->setValue(str_replace("-", "", $fechafi->format('Y-m-d')));//Fecha de corte
                    $hoja->getCell('O'.$fila)->setValue(str_replace("-", "", $credito->fecha_prestamo));//Fecha inicio
                    $hoja->getCell('V'.$fila)->setValue("6");//Periodicidad
                    $hoja->getCell('Z'.$fila)->setValue($cuotasVencidas);//Cuotas en mora
                    $hoja->getCell('AA'.$fila)->setValue(intdiv($credito->monto_total, 1000));//Valor inicial
                    $hoja->getCell('AB'.$fila)->setValue(intdiv($valorMora, 1000));//Valor mora
                    $hoja->getCell('AC'.$fila)->setValue(intdiv($valorSaldo, 1000));//Valor del saldo
                    $hoja->getCell('AD'.$fila)->setValue(intdiv($credito->pago, 1000));//Valor de la cuota
                    $hoja->getCell('AF'.$fila)->setValue(11);//Linea de credito
                    $hoja->getCell('AH'.$fila)->setValue(1);//Tipo de contrato
                    $hoja->getCell('AQ'.$fila)->setValue(2);//Obligacion refinanciada
                    $hoja->getCell('BB'.$fila)->setValue($credito->cliente->direccion);//Direccion casa del tercero
                    $hoja->getCell('CD'.$fila)->setValue($credito->cliente->email);//Correo electronico
                    $hoja->getCell('CE'.$fila)->setValue($credito->cliente->celular);//Numero Celular
                    $sumaPagos = 0;

                    foreach($credito->pagos as $key => $pago){
                        if ($key == 0) {
                            $hoja->getCell('S'.$fila)->setValue($pago->fecha);
                        }

                        foreach ($pago->cuotas as $pagocuota) {
                            $sumaPagos = $sumaPagos + $pagocuota->pivot->interes + $pagocuota->pivot->capital;
                        }
                    }
                    $hoja->getCell('CF'.$fila)->setValue(intdiv($sumaPagos, 1000));//Valor real pagado
                    $fila++;
                }   
            }

            $writer = new Xlsx($excel);
            $writer->save(storage_path('informecentral' . DIRECTORY_SEPARATOR . 'InformeCentral.xlsx'));
            self::crearZip(storage_path('informecentral'), storage_path('informe.zip'));
            self::enviarCorreo(storage_path('informe.zip'));
            self::borrarCartas(storage_path('informecentral' . DIRECTORY_SEPARATOR . 'cartas'));
        }
        catch(Exception $ex){
            $logFile = fopen(storage_path('informecentral') . '/log_error_transunion.txt', 'a');
            fwrite($logFile, "\n".date("d/m/Y H:i:s") . "-" . $ex->getMessage() . "---" . $ex->getLine());
            fclose($logFile);
        }
    }
    
    private function calcularMora($mora){
        switch ($mora) {
            case ($mora >= 0 && $mora <= 29 ):
                return 0;
            case ($mora >= 30 && $mora <= 59):
                return 1;
            case ($mora >= 60 && $mora <= 89):
                return 2;
            case ($mora >= 90 && $mora <= 119):
                return 3;
            case ($mora >= 120 && $mora <= 149):
                return 4;
            case ($mora >= 150 && $mora <= 179):
                return 5;
            case ($mora >= 180 && $mora <= 209):
                return 6;
            case ($mora >= 210 && $mora <= 239):
                return 7;
            case ($mora >= 240 && $mora <= 269):
                return 8;
            case ($mora >= 270 && $mora <= 299):
                return 9;
            case ($mora >= 300 && $mora <= 329):
                return 10;
            case ($mora >= 330 && $mora <= 359):
                return 11;
            case ($mora >= 360 && $mora <= 539):
                return 12;
            case ($mora >= 540 && $mora <= 729):
                return 13;
            case ($mora >= 730):
                return 14;
            default:
                break;
        }
    }

    private function crearCarta($nombre, $correo, $telefono){

        $templateProcessor = new TemplateProcessor(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "plantillaNotificacionReporte.docx");
        $templateProcessor->setValue('nombre', $nombre);
        $templateProcessor->setValue('correo', $correo);
        $templateProcessor->setValue('telefono', $telefono); 
        
        $templateProcessor->saveAs(storage_path('informecentral' . DIRECTORY_SEPARATOR . 'cartas' . DIRECTORY_SEPARATOR .  'Notificacion' . $nombre . '.docx'));    
    }
    
    function crearZip($rootPath, $source)
    {
        $zip = new ZipArchive();
        $zip->open($source, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
        
        foreach ($files as $file)
        {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
        
            if (!$file->isDir())
            {
                $zip->addFile($filePath, $relativePath);
            }else {
                if($relativePath !== false)
                    $zip->addEmptyDir($relativePath);
            }
        }
        $zip->close();
        
    }

    public function borrarCartas($ruta)
    {
        $files = glob($ruta. DIRECTORY_SEPARATOR . "*");
        foreach($files as $file){ 
            if(is_file($file)) {
                unlink($file);
            }
        }
    }

    function enviarCorreo($archivo){
        $fechaActual = Carbon::now()->format('d-m-Y');
        try{
            Mail::send([], [], function ($message) use($archivo, $fechaActual){
                $message->from("notificaciones@apptaxcenter.com", "Cahors");
                $message->to(["gestion@cahors.co", "ingprogramar@taxiseguro.co"]);
                $message->subject("Informe Transunion");
                $message->attach($archivo, ['as' => 'Informe Transunion - ' . $fechaActual . '.zip', 'mime' => 'application/zip']);
            });
        }
        catch(Exception $ex){
            $logFile = fopen(storage_path('informecentral') . '/logCorreoInfTransunion.txt', 'a');
            fwrite($logFile, "\n".date("d/m/Y H:i:s") . "-" . $ex->getMessage() . "---" . $ex->getLine());
            fclose($logFile);
        }
    }
}
