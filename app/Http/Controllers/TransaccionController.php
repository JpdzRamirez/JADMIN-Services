<?php

namespace App\Http\Controllers;

use App\Models\Cuentac;
use App\Models\Servicio;
use App\Models\Cancelacion;
use App\Models\Mensaje;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Vale;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SoapClient;
use SoapFault;

class TransaccionController extends Controller
{

    public function cuentarecargas($cuenta)
    {

        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        $recargas = Transaccion::with('sucursal.user')->where('cuentasc_id', $cuenta->id)->where('tipo', 'Recarga')->orderBy('id', 'DESC')->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.recargas', compact('cuenta', 'recargas', 'usuario'));
    }

    public function cuentaconsumos($cuenta)
    {

        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        $transacciones = Transaccion::where('cuentasc_id', $cuenta->id)->where('tipo', 'Consumo')->orderBy('id', 'DESC')->paginate(10);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.consumos', compact('cuenta', 'transacciones', 'usuario'));
    }

    public function cuentatransacciones($cuenta)
    {

        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        $transacciones = Transaccion::with('sucursal.user')->where('cuentasc_id', $cuenta->id)->where(function($q){$q->where('tipo', 'Venta de Combustible')->orWhere('tipo', 'Pago')->orWhere('Tipo', 'Transferencia')->orWhere('tipo', 'Servicio con vale')->orWhere('tipo', 'Ajuste de vale')->orWhere('tipo', 'Ingreso')->orWhere('tipo', 'Egreso');})->orderBy('id', 'DESC')->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.transacciones', compact('cuenta', 'transacciones', 'usuario'));
    }

    public function valesporcuenta(Request $request, $cuenta)
    {
        $orden = $request->input('orden');
        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        $servicios = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->orderBy('id', $orden)->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.vales', compact('cuenta', 'servicios', 'usuario', 'orden'));
    }

    public function store(Request $request, $cuentac)
    {
        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}])->select('id', 'estado', 'fechabloqueo', 'saldo', 'saldovales', 'conductor_CONDUCTOR', 'istransacciones')->find($cuentac);
        
        if($cuenta->istransacciones == 1){
            return back()->withErrors(['sql' => 'Las transacciones para este conductor están temporalmente bloqueadas']);
        }
        
        $transaccion = new Transaccion();
        $transaccion->tipo = $request->input('tipo');
        $transaccion->valor = $request->input('valor');
        $transaccion->fecha = Carbon::now('-05:00');
        $transaccion->comentarios = $request->input('comentarios');
        $transaccion->cuentasc_id = $cuenta->id;
        $transaccion->users_id = Auth::user()->id;
        $transaccion->tiporecarga = $request->input('tiporecarga');

        $logFile = fopen("../storage/logRecargas.txt", 'a') or die("Error creando archivo");

        if ($transaccion->tiporecarga == "Cortesía") {
            $tipo = 4;
            $cuenta->saldo = $cuenta->saldo + $transaccion->valor;
        } else if($transaccion->tiporecarga == "Ingreso") {
            $tipo = 1;
            $cuenta->saldo = $cuenta->saldo + $transaccion->valor;
        }else{
            $tipo = 3;
            $cuenta->saldo = $cuenta->saldo - $transaccion->valor;
        }
        
        $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

        try {
            $client = new SoapClient($url, ["trace" => 1, 'exceptions' => true]);
            $result = $client->registrarTicket();
            $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionConductor" => $cuenta->conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa" => "900886956", "monto" => $transaccion->valor, "tipo" => $tipo);
            $peticion = $client->registrarRecarga($parametros);


            if ($peticion->registrarRecargaReturn->codigoError != "0000") {
                $error = $peticion->registrarRecargaReturn->mensajeError;
                if($error == null){
                    $error = "Error integración: No hay mensaje de error retornado.";
                }
                return redirect()->back()->withErrors(['sql' => $error]);
            }else{
                fwrite($logFile, "\n".date("d/m/Y H:i:s"). json_encode($peticion->registrarRecargaReturn)) or die("Error escribiendo en el archivo");
                fclose($logFile); 
            }
        } catch (SoapFault $e) {
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). " " .  $e->getMessage()) or die("Error escribiendo en el archivo");
            fclose($logFile);
            return redirect()->back()->withErrors(['sql' => $e->getMessage()]);
        } catch (Exception $e){
            fwrite($logFile, "\n".date("d/m/Y H:i:s"). " " .  $e->getMessage()) or die("Error escribiendo en el archivo");
            fclose($logFile);
            return redirect()->back()->withErrors(['sql' => $e->getMessage()]);           
        }
        
        $transaccion->save();
        if ($cuenta->estado == "Bloqueado") {
            $cuenta->fechabloqueo = $cuenta->fechabloqueo;
        }
        $cuenta->save();
        
        if($tipo==4 || $tipo==1){
            $mensaje = new Mensaje();
            $mensaje->texto = "Recarga realizada por valor de: $" . number_format($transaccion->valor);
            $mensaje->fecha = Carbon::now('-05:00');
            $mensaje->sentido = "Recibido";
            $mensaje->estado = "Pendiente";
            $mensaje->cuentasc_id = $cuenta->id;
            $mensaje->save();
        }
        
        return redirect('cuentas_afiliados/filtrar?identificacion=' . $request->input('identificacion'));
    }

    public function editarsaldos(Request $request)
    {

        $cuenta = Cuentac::select('id', 'saldovales', 'saldo')->find($request->input('editcuenta'));
        $cuenta->saldovales = $request->input('saldovales');
        $cuenta->saldo = $request->input('saldo');
        $cuenta->save();

        return redirect('cuentas_afiliados/filtrar?identificacion=' . $request->input('identificacions'));
    }

    public function exportar(Request $request, $cuenta)
    {

        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        $desde = $request->input('desde') . ' 00:00';
        if($request->filled('hasta')){
            $hasta = $request->input('hasta') . ' 23:59';
        }else{
            $hasta = Carbon::now()->format('Y-m-d') . ' 23:59';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $style = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];

        if ($request->input('tipo') == "Recargas") {

            $sheet->mergeCells("B1:F1");
            $sheet->setCellValue("B1", "Historial de Recargas " . $cuenta->conductor->NOMBRE);
            $sheet->getStyle("B1:F1")->applyFromArray($style);

            $sheet->setCellValue("A2", "ID");
            $sheet->setCellValue("B2", "Fecha");
            $sheet->setCellValue("C2", "Tipo recarga");
            $sheet->setCellValue("D2", "Valor");
            $sheet->setCellValue("E2", "Sucursal");
            $sheet->setCellValue("F2", "Agente");
            $sheet->setCellValue("G2", "Comentarios");
            $sheet->getStyle("A1:G2")->getFont()->setBold(true);
            
            $recargas = Transaccion::with('sucursal', 'user')->where('cuentasc_id', $cuenta->id)->where('tipo', 'Recarga') ->whereBetween('fecha', [$desde, $hasta])->orderBy('id', 'DESC')->get();
            $indice = 3;
            foreach ($recargas as $recarga) {
                $sheet->setCellValue("A" . $indice, $recarga->id);
                $sheet->setCellValue("B" . $indice, $recarga->fecha);
                $sheet->setCellValue("C" . $indice, $recarga->tiporecarga);
                $sheet->setCellValue("D" . $indice, $recarga->valor);
                if ($recarga->sucursal != null) {
                    $sheet->setCellValue("E" . $indice, $recarga->sucursal->user->nombres);
                } else {
                    $sheet->setCellValue("E" . $indice, "CRM");
                }
                if ($recarga->user != null) {
                    $sheet->setCellValue("F" . $indice, $recarga->user->identificacion . ", " . $recarga->user->nombres);
                } else {
                    $sheet->setCellValue("F" . $indice, "");
                }
                $sheet->setCellValue("G" . $indice, $recarga->comentarios);
                $indice++;
            }

            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
        } elseif ($request->input('tipo') == "Consumos") {
            $sheet->mergeCells("A1:C1");
            $sheet->setCellValue("A1", "Historial de Consumos " . $cuenta->conductor->NOMBRE);
            $sheet->getStyle("A1:C1")->applyFromArray($style);

            $sheet->setCellValue("A2", "ID");
            $sheet->setCellValue("B2", "Fecha de consumo");
            $sheet->setCellValue("C2", "Valor");
            $sheet->getStyle("A1:C2")->getFont()->setBold(true);

            $consumos = Transaccion::where('cuentasc_id', $cuenta->id)->where('tipo', 'Consumo') ->whereBetween('fecha', [$desde, $hasta])->orderBy('id', 'DESC')->get();
            $indice = 3;
            foreach ($consumos as $consumo) {
                $sheet->setCellValue("A" . $indice, $consumo->id);
                $sheet->setCellValue("B" . $indice, $consumo->fecha);
                $sheet->setCellValue("C" . $indice, "-" . $consumo->valor);
                $indice++;
            }

            foreach (range('A', 'C') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
        } elseif ($request->input('tipo') == "Transacciones") {

            $sheet->mergeCells("B1:E1");
            $sheet->setCellValue("B1", "Historial de Transacciones " . $cuenta->conductor->NOMBRE);
            $sheet->getStyle("B1:E1")->applyFromArray($style);

            $sheet->setCellValue("A2", "ID");
            $sheet->setCellValue("B2", "Fecha");
            $sheet->setCellValue("C2", "Tipo transacción");
            $sheet->setCellValue("D2", "Valor");
            $sheet->setCellValue("E2", "Saldos");
            $sheet->setCellValue("F2", "Sucursal");
            $sheet->setCellValue("G2", "Comentarios");
            $sheet->getStyle("A1:G2")->getFont()->setBold(true);

            $desdeAnteriores = $request->input('desde') . ' 23:59';
            $anteriores = Transaccion::select('tipo', 'valor')->where('cuentasc_id', $cuenta->id)->where('fecha', '<=', $desdeAnteriores)->whereIn('tipo', ['Servicio con vale', 'Pago', 'Ingreso', 'Egreso', 'Venta de combustible', 'Transferencia', 'Ajuste de vale'])->get();
            $saldo = 0;
            foreach ($anteriores as $anterior) {
                if($anterior->tipo == 'Servicio con vale' || $anterior->tipo == 'Ingreso' || $anterior->tipo == 'Ajuste de vale'){
                    $saldo = $saldo + $anterior->valor;
                }else{
                    $saldo = $saldo - $anterior->valor;
                }
            }
            $sheet->setCellValue("E3", $saldo);

            $transacciones = Transaccion::with('sucursal')->where('cuentasc_id', $cuenta->id)->whereBetween('fecha', [$desde, $hasta])->whereIn('tipo', ['Servicio con vale', 'Pago', 'Ingreso', 'Egreso', 'Venta de combustible', 'Transferencia', 'Ajuste de vale'])->orderBy('id', 'ASC')->get();
            $indice = 4;
            foreach ($transacciones as $transaccion) {
                $sheet->setCellValue("A" . $indice, $transaccion->id);
                $sheet->setCellValue("B" . $indice, $transaccion->fecha);
                $sheet->setCellValue("C" . $indice, $transaccion->tipo);
                $sheet->setCellValue("D" . $indice, $transaccion->valor);
                if($transaccion->tipo == 'Servicio con vale' || $transaccion->tipo == 'Ingreso' || $transaccion->tipo == 'Ajuste de vale'){
                    $sheet->setCellValue("E" . $indice, $saldo + $transaccion->valor);
                    $saldo = $saldo + $transaccion->valor;
                }else{
                    $sheet->setCellValue("E" . $indice, $saldo - $transaccion->valor);
                    $saldo = $saldo - $transaccion->valor;
                }
                if ($transaccion->sucursal != null) {
                    $sheet->setCellValue("F" . $indice, $transaccion->sucursal->user->nombres);
                } else {
                    $sheet->setCellValue("F" . $indice, "CRM");
                }
                $sheet->setCellValue("G" . $indice, $transaccion->comentarios);
                $indice++;
            }

            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Historial.xlsx');
        $archivo = file_get_contents('Historial.xlsx');
        unlink('Historial.xlsx');

        return base64_encode($archivo);
    }

    public function filtrarvales(Request $request, $cuenta)
    {
        
        //return json_encode($request->input());
        $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
        
        if(empty($request->input('id')) && empty($request->input('fecha')) && empty($request->input('empresa')) && empty($request->input('valera')) && empty($request->input('codigo')) && empty($request->input('unidades')) && empty($request->input('valor'))){
            return redirect('/vales/'. $cuenta->id);
        }
        
        $ids = [];
        $filtro = "";
        $c1="";$c2="";$c3="";$c4="";$c5="";$c6="";
        $c7="";
        
        if($request->filled('id')){
            $ide = $request->input('id');
            $s1 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($ide){$q->whereHas('vale', function($r) use($ide){$r->where('id', $ide);})->orWhereHas('valeav', function($r) use($ide){$r->where('id', $ide);});})->where('cuentasc_id', $cuenta->id)->pluck('id')->toArray();
            $ids = $s1;
            $c1 = $request->input('id');
            $filtro = $filtro . "ID=" . $request->input('id') . ", ";
        }
        
        if($request->filled('fecha')){
            $fecha = $request->input('fecha');
            if(empty($ids)){
                $s2 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->whereDate('fecha', $fecha)->pluck('id')->toArray();  
            }else{
                $s2 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->whereDate('fecha', $fecha)->whereIn('id', $ids)->pluck('id')->toArray(); 
            }
            $ids = $s2;    
            $c2 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        }
        
        if ($request->filled('empresa')) {
            $empresa = $request->input('empresa');
            if(empty($ids)){
                $s3 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($empresa){$q->whereHas('vale', function($r) use($empresa){$r->whereHas('valera', function($s) use($empresa){$s->whereHas('cuentae', function($t) use($empresa){$t->whereHas('agencia', function($u) use($empresa){$u->where('NOMBRE', 'like', '%' . $empresa . '%');});});});})
                ->orWhereHas('valeav', function($r) use($empresa){$r->whereHas('valera', function($s) use($empresa){$s->whereHas('cuentae', function($t) use($empresa){$t->whereHas('agencia', function($u) use($empresa){$u->where('NOMBRE', 'like', '%' . $empresa . '%');});});});});})->where('cuentasc_id', $cuenta->id)->pluck('id')->toArray();
            }else{
                $s3 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($empresa){$q->whereHas('vale', function($r) use($empresa){$r->whereHas('valera', function($s) use($empresa){$s->whereHas('cuentae', function($t) use($empresa){$t->whereHas('agencia', function($u) use($empresa){$u->where('NOMBRE', 'like', '%' . $empresa . '%');});});});})
                ->orWhereHas('valeav', function($r) use($empresa){$r->whereHas('valera', function($s) use($empresa){$s->whereHas('cuentae', function($t) use($empresa){$t->whereHas('agencia', function($u) use($empresa){$u->where('NOMBRE', 'like', '%' . $empresa . '%');});});});});})->where('cuentasc_id', $cuenta->id)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s3;    
            $c3 = $request->input('empresa');
            $filtro = $filtro . "Empresa=" . $request->input('empresa') . ", ";
        }
        
        if ($request->filled('valera')) {
            $valera = $request->input('valera');
            if(empty($ids)){
                $s4 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($valera){$q->whereHas('vale', function($r) use($valera){$r->whereHas('valera', function($s) use($valera){$s->where('nombre', $valera);});})
                ->orWhereHas('valeav', function($r) use($valera){$r->whereHas('valera', function($s) use($valera){$s->where('nombre', 'like', '%' . $valera . '%');});});})->where('cuentasc_id', $cuenta->id)->pluck('id')->toArray(); 
            }else{
                $s4 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($valera){$q->whereHas('vale', function($r) use($valera){$r->whereHas('valera', function($s) use($valera){$s->where('nombre', $valera);});})
                ->orWhereHas('valeav', function($r) use($valera){$r->whereHas('valera', function($s) use($valera){$s->where('nombre', 'like', '%' . $valera . '%');});});})->where('cuentasc_id', $cuenta->id)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s4;    
            $c4 = $request->input('valera');
            $filtro = $filtro . "Valera=" . $request->input('valera') . ", ";
        }
        
        if($request->filled('codigo')){
            $codigo = $request->input('codigo');
            if(empty($ids)){
                $s5 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($codigo){$q->whereHas('vale', function($r) use($codigo){$r->where('codigo', $codigo);})
                ->orWhereHas('valeav', function($r) use($codigo){$r->where('codigo', $codigo);});})->where('cuentasc_id', $cuenta->id)->pluck('id')->toArray(); 
            }else{
                $s5 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q) use($codigo){$q->whereHas('vale', function($r) use($codigo){$r->where('codigo', $codigo);})
                ->orWhereHas('valeav', function($r) use($codigo){$r->where('codigo', $codigo);});})->where('cuentasc_id', $cuenta->id)->whereIn('id', $ids)->pluck('id')->toArray(); 
            }
            $ids = $s5;    
            $c5 = $request->input('codigo');
            $filtro = $filtro . "Código vale=" . $request->input('codigo') . ", ";
        }
        
        if ($request->filled('unidades')) {
            $unidades = $request->input('unidades');
            if(empty($ids)){
                $s6 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->where('unidades', $unidades)->pluck('id')->toArray();  
            }else{
                $s6 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->where('unidades', $unidades)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s6;    
            $c6 = $request->input('unidades');
            $filtro = $filtro . "Unidades=" . $request->input('unidades') . ", ";
        }
        
        if ($request->filled('valor')) {
            $valor = $request->input('valor');
            if(empty($ids)){
                $s7 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->where('valor', $valor)->pluck('id')->toArray();  
            }else{
                $s7 = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->where(function($q){$q->has('vale')->orHas('valeav');})->where('cuentasc_id', $cuenta->id)->where('valor', $valor)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s7;    
            $c7 = $request->input('valor');
            $filtro = $filtro . "Valor=" . $request->input('valor') . ", ";
        }
        $orden = $request->input('orden');

        $servicios = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')->whereIn('id', $ids)->orderBy('id', $orden)->paginate(10)->appends($request->query());

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.vales', compact('cuenta', 'servicios', 'usuario', 'filtro', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'orden'));
    }

    public function exportarvales(Request $request, $cuenta)
    {
        // Obtener la cuenta y conductor
        $cuenta = Cuentac::with(['conductor' => function($q) {
            $q->select('CONDUCTOR', 'NOMBRE');
        }])->select('id', 'conductor_CONDUCTOR')->where('id', $cuenta)->first();
    
        // Obtener parámetros de la solicitud
        $orden = $request->input('orden', 'DESC');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
    
        // Validar las fechas
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    
        // Obtener los servicios basados en el filtro o fechas
        if ($request->filled('filtro')) {
            $filtro = explode(",", $request->input('filtro'));
            $servicios = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')
                ->whereIn('id', $filtro)
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('id', $orden)
                ->get();
        } else {
            $servicios = Servicio::with('vale.valera.cuentae.agencia', 'valeav.valera')
                ->where(function($q) {
                    $q->has('vale')->orHas('valeav');
                })
                ->where('cuentasc_id', $cuenta->id)
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderBy('id', $orden)
                ->get();
        }
    
        // Crear archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells("B1:F1");
        $sheet->setCellValue("B1", "Vales de " . $cuenta->conductor->NOMBRE);
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:E1")->applyFromArray($style);
    
        // Definir encabezados de la tabla
        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Empresa");
        $sheet->setCellValue("D2", "Valera");
        $sheet->setCellValue("E2", "Código vale");
        $sheet->setCellValue("F2", "Unidades");
        $sheet->setCellValue("G2", "Valor");
        $sheet->getStyle("A1:G2")->getFont()->setBold(true);
    
        // Llenar datos en la tabla
        $indice = 3;
        foreach ($servicios as $servicio) {
            if ($servicio->vale != null) {
                $sheet->setCellValue("A" . $indice, $servicio->vale->id);
                $sheet->setCellValue("B" . $indice, $servicio->fecha);
                $sheet->setCellValue("C" . $indice, $servicio->vale->valera->cuentae->agencia->NOMBRE);
                $sheet->setCellValue("D" . $indice, $servicio->vale->valera->nombre);
                $sheet->setCellValue("E" . $indice, $servicio->vale->codigo);
            } else {
                $sheet->setCellValue("A" . $indice, $servicio->valeav->id);
                $sheet->setCellValue("B" . $indice, $servicio->fecha);
                $sheet->setCellValue("C" . $indice, "AVIANCA");
                $sheet->setCellValue("D" . $indice, $servicio->valeav->valera->nombre);
                $sheet->setCellValue("E" . $indice, $servicio->valeav->codigo);
            }
            $sheet->setCellValue("F" . $indice, $servicio->unidades);
            $sheet->setCellValue("G" . $indice, $servicio->valor);
            $indice++;
        }
    
        // Ajustar tamaño de columnas
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        // Generar archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'Vales_' . $cuenta->conductor->NOMBRE . '.xlsx';
        $writer->save($filename);
        $archivo = file_get_contents($filename);
        unlink($filename);
    
        // Devolver archivo como base64
        return base64_encode($archivo);
    }
    

    public function devolver(Request $request)
    {
        $servicio = Servicio::with(['cuentac'=>function($q){$q->select('id', 'saldo');}, 'cancelacion'])->find($request->input('servicio'));
        $servicio->estado = "Cancelado devuelto";

        $servicio->cancelacion->users_id = Auth::user()->id;
        $servicio->cancelacion->justificacion = $request->input('justificacion');
        $servicio->cancelacion->save();

        $servicio->cuentac->saldo = $servicio->cuentac->saldo + 800;
        $servicio->cuentac->save();

        $servicio->save();

        $mensaje = new Mensaje();
        $mensaje->texto = "Devolución realizada por valor de: $800";
        $mensaje->fecha = Carbon::now();
        $mensaje->sentido = "Recibido";
        $mensaje->estado = "Pendiente";
        $mensaje->cuentasc_id = $servicio->cuentasc_id;
        $mensaje->save();

        return redirect('servicios/finalizados');
    }

    public function devoluciones()
    {
        $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->orderBy('id', 'DESC')->paginate(15);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        
        return view('servicios.devoluciones', compact('devoluciones', 'usuario'));
    }

    public function filtrarDevoluciones(Request $request)
    {
        if ($request->filled('fecha')) {
            $dates = explode(" - ", $request->input('fecha'));
            $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereBetween('fecha', $dates)->orderBy('id', 'DESC')->paginate(30)->appends($request->query());
            $filtro = array('Fecha', $request->input('fecha'));
        } else if ($request->filled('conductor')) {
            $conductor = $request->input('conductor');
            $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereHas('servicio', function ($q) use ($conductor) {
                $q->whereHas('cuentac', function ($r) use ($conductor) {
                    $r->whereHas('conductor', function ($s) use ($conductor) {
                        $s->where('NUMERO_IDENTIFICACION', $conductor);
                    });
                });
            })->orderBy('id', 'DESC')->paginate(30)->appends($request->query());
            $filtro = array('Conductor', $request->input('conductor'));
        } else if ($request->filled('operador')) {
            $operador = $request->input('operador');
            $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereHas('user', function ($q) use ($operador) {
                $q->where('identificacion', $operador);
            })->orderBy('id', 'DESC')->paginate(30)->appends($request->query());
            $filtro = array('Operador', $request->input('operador'));
        } else {
            return redirect('devoluciones');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('servicios.devoluciones', compact('devoluciones', 'usuario', 'filtro'));
    }

    public function exportarDevoluciones(Request $request)
    {
        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "Fecha") {
                $dates = explode(" - ", $filtro[1]);
                $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereBetween('fecha', $dates)->orderBy('id', 'DESC')->get();
            } else if ($filtro[0] == "Conductor") {
                $conductor = $filtro[1];
                $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereHas('servicio', function ($q) use ($conductor) {
                    $q->whereHas('cuentac', function ($r) use ($conductor) {
                        $r->whereHas('conductor', function ($s) use ($conductor) {
                            $s->where('NUMERO_IDENTIFICACION', $conductor);
                        });
                    });
                })->orderBy('id', 'DESC')->get();
            } else if ($filtro[0] == "Operador") {
                $operador = $filtro[1];
                $devoluciones = Cancelacion::with(['servicio'=>function($q){$q->with(['cuentac'=>function($r){$r->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($s){$s->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}]);}, 'user'])->whereNotNull('users_id')->whereHas('user', function ($q) use ($operador) {
                    $q->where('identificacion', $operador);
                })->orderBy('id', 'DESC')->get();
            }
        } else {
            $devoluciones = Cancelacion::whereNotNull('users_id')->orderBy('id', 'DESC')->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:E1");
        $sheet->setCellValue("B1", "Devoluciones");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:E1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Fecha");
        $sheet->setCellValue("B2", "ID Servicio");
        $sheet->setCellValue("C2", "Conductor");
        $sheet->setCellValue("D2", "Justificación conductor");
        $sheet->setCellValue("E2", "Operador");
        $sheet->setCellValue("F2", "Justificación operador");
        $sheet->getStyle("A1:F2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($devoluciones as $devolucion) {
            $sheet->setCellValue("A" . $indice, $devolucion->fecha);
            $sheet->setCellValue("B" . $indice, $devolucion->servicios_id);
            $sheet->setCellValue("C" . $indice, $devolucion->servicio->cuentac->conductor->NUMERO_IDENTIFICACION . ", " . $devolucion->servicio->cuentac->conductor->NOMBRE);
            $sheet->setCellValue("D" . $indice, $devolucion->razon);
            $sheet->setCellValue("E" . $indice, $devolucion->user->identificacion . ", " . $devolucion->user->nombres);
            $sheet->setCellValue("F" . $indice, $devolucion->justificacion);
            $indice++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Devoluciones.xlsx');
        $archivo = file_get_contents('Devoluciones.xlsx');
        unlink('Devoluciones.xlsx');

        return base64_encode($archivo);
    }
    
    public function editarSaldo(Request $request, $cuenta)
    {
        $cuentac = Cuentac::select('id', 'estado', 'fechabloqueo', 'saldovales', 'saldo')->find($cuenta);
        if($request->input('tipotrans') == "Ingreso"){
            $cuentac->saldovales = $cuentac->saldovales + $request->input('valortr');
        }else{
            $cuentac->saldovales = $cuentac->saldovales - $request->input('valortr');
        }

        if($cuentac->estado == "Bloqueado"){
            $cuentac->fechabloqueo = $cuentac->fechabloqueo;
        }

        $cuentac->save();

        $transaccion = new Transaccion();
        $transaccion->tipo = $request->input('tipotrans');
        $transaccion->valor = $request->input('valortr');
        $transaccion->fecha = Carbon::now('-05:00');
        $transaccion->cuentasc_id = $cuentac->id;
        $transaccion->users_id = Auth::user()->id;
        $transaccion->comentarios = $request->input('comentariostr');
        $transaccion->save();

        return redirect('cuentas_afiliados/filtrar?identificacion=' . $request->input('identificaciontr'));
    }
    
    public function movimientos()
    {
        $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}, 'sucursal.user'])->orderBy('id', 'DESC')->paginate(50);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.estadisticas', compact('transacciones', 'usuario'));
    }

    public function filtrarMovimientos(Request $request)
    {
        if(empty($request->input('fecha')) && empty($request->input('tipo')) && empty($request->input('conductor'))){
            return redirect('/movimientos');
        }
        
        $ids = [];
        $filtro = "";
        $c1="";$c2="";$c3="";

        if($request->filled('fecha')){
            $dates = explode(" - ", $request->input('fecha'));
            $s1 = Transaccion::whereBetween('fecha', $dates)->pluck('id')->toArray();
            $ids = $s1;
            $c1 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        }
        
        if($request->filled('tipo')){
            if(empty($ids)){
                $s2 = Transaccion::where('tipo', $request->input('tipo'))->pluck('id')->toArray();
            }else{
                $s2 = Transaccion::where('tipo', $request->input('tipo'))->whereIn('id', $ids)->pluck('id')->toArray(); 
            }
            $ids = $s2;    
            $c2 = $request->input('tipo');
            $filtro = $filtro . "Tipo=" . $request->input('tipo') . ", ";
        }

        if ($request->filled('conductor')) {
            $conductor = $request->input('conductor');
            if (empty($ids)) {
                $s3 = Transaccion::whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NUMERO_IDENTIFICACION', $conductor);});})->pluck('id')->toArray();
            } else {
                $s3 = Transaccion::whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NUMERO_IDENTIFICACION', $conductor);});})->whereIn('id', $ids)->pluck('id')->toArray();
            }
            
            $ids = $s3;    
            $c3 = $request->input('conductor');
            $filtro = $filtro . "Conductor=" . $request->input('conductor') . ", ";
        }

        $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}, 'sucursal.user'])->whereIn('id', $ids)->orderBy('id', 'DESC')->paginate(30)->appends($request->query());
        $exp = implode(",", Transaccion::whereIn('id', $ids)->pluck('id')->toArray());

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.estadisticas', compact('transacciones', 'usuario', 'c1', 'c2', 'c3', 'filtro', 'exp'));
    }

    public function exportarMovimientos(Request $request)
    {

        if ($request->filled('filtro')) {
            $filtro = explode(",", $request->input('filtro'));
            $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}, 'sucursal.user'])->whereIn('id', $filtro)->orderBy('id', 'DESC')->get();
        } else {
            $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}, 'sucursal.user'])->orderBy('id', 'DESC')->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:E1");
        $sheet->setCellValue("B1", "Movimientos");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:E1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Fecha");
        $sheet->setCellValue("B2", "Tipo movimiento");
        $sheet->setCellValue("C2", "Tipo recarga");
        $sheet->setCellValue("D2", "Valor");
        $sheet->setCellValue("E2", "Conductor");
        $sheet->setCellValue("F2", "Sucursal");
        $sheet->getStyle("A1:F2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($transacciones as $transaccion) {
            $sheet->setCellValue("A" . $indice, $transaccion->fecha);
            $sheet->setCellValue("B" . $indice, $transaccion->tipo);
            $sheet->setCellValue("C" . $indice, $transaccion->tiporecarga);
            $sheet->setCellValue("D" . $indice, $transaccion->valor);
            if($transaccion->cuentac != null){
                $sheet->setCellValue("E" . $indice, $transaccion->cuentac->conductor->NUMERO_IDENTIFICACION);
            }else{
                $sheet->setCellValue("E" . $indice, "");
            }
            if($transaccion->sucursal != null){
                $sheet->setCellValue("F" . $indice, $transaccion->sucursal->user->nombres);
            }else{
                $sheet->setCellValue("F" . $indice, "");
            }
            $indice++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Movimientos.xlsx');
        $archivo = file_get_contents('Movimientos.xlsx');
        unlink('Movimientos.xlsx');

        return base64_encode($archivo);
    }

    public function migrar()
    {
        $spreadsheet = IOFactory::load("../storage/recargas.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $numRows = $sheet->getHighestRow();
        for ($i = 2; $i <= $numRows; $i++) {
            $cedula = $sheet->getCell('A' . $i)->getCalculatedValue();
            $cuenta = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}])->select('id', 'saldo', 'saldovales', 'conductor_CONDUCTOR')->whereHas('conductor', function($r) use($cedula){$r->where('NUMERO_IDENTIFICACION', $cedula);})->first();
            $transaccion = new Transaccion();
            $transaccion->tipo = "Recarga";
            $transaccion->valor = $sheet->getCell('B' . $i)->getCalculatedValue();
            $transaccion->fecha = Carbon::now('-05:00');
            $transaccion->comentarios = "SALDO INICIAL CRM 1";
            $transaccion->cuentasc_id = $cuenta->id;
            $transaccion->users_id = 55;
            $transaccion->tiporecarga = "Cortesía";
    
            if ($transaccion->tiporecarga == "Cortesía") {
                $tipo = 4;
                $cuenta->saldo = $cuenta->saldo + $transaccion->valor;
            } else if($transaccion->tiporecarga == "Ingreso") {
                $tipo = 1;
                $cuenta->saldo = $cuenta->saldo + $transaccion->valor;
            }else{
                $tipo = 3;
                $cuenta->saldo = $cuenta->saldo - $transaccion->valor;
            }
    
            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";
    
            try {
                $client = new SoapClient($url, ["trace" => 1, 'exceptions' => true]);
                $result = $client->registrarTicket();
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionConductor" => $cuenta->conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa" => "900886956", "monto" => $transaccion->valor, "tipo" => $tipo);
                $peticion = $client->registrarRecarga($parametros);
    
                if ($peticion->registrarRecargaReturn->codigoError != "0000") {
                    return $peticion->registrarRecargaReturn->mensajeError;
                }
            } catch (SoapFault $e) {
                return $e->getMessage();
            }
            
            $transaccion->save();
            $cuenta->save();
            
            if($tipo==4 || $tipo==1){
                $mensaje = new Mensaje();
                $mensaje->texto = "Recarga realizada por valor de: $" . number_format($transaccion->valor);
                $mensaje->fecha = Carbon::now('-05:00');
                $mensaje->sentido = "Recibido";
                $mensaje->estado = "Pendiente";
                $mensaje->cuentasc_id = $cuenta->id;
                $mensaje->save();
            }
        }

        return 'listo';
    }
}
