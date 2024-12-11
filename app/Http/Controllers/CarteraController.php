<?php

namespace App\Http\Controllers;

use App\Models\Acuerdo;
use App\Models\Cartera;
use App\Models\Cuota;
use App\Models\FacturaEncabezadoIcon;
use App\Models\Interes;
use App\Models\Tercero;
use App\Models\User;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;
use Luecano\NumeroALetras\NumeroALetras;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\ListItem;
use stdClass;
use ZipArchive;

class CarteraController extends Controller
{
    public function deudores()
    {
        $terceros = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'SEGUNDO_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO')->has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->take(10)->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cartera.lista', compact('terceros', 'usuario'));
    }

    public function registrosTercero(Tercero $tercero)
    {
		//No lo ignore
        $registros = Cartera::select(DB::raw('CARTERA_GENERICA, AFECTA, FACTURA, FECHA, FECHA_VENCIMIENTO, SALDO_VENCIDO, CUENTA'))->where('TERCERO', $tercero->TERCERO)->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA')->orderBy('FECHA')->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cartera.registros', compact('registros', 'tercero', 'usuario'));
    }

    public function filtrarDeudores(Request $request)
    {
        $c1 = ""; $c2 = "";
        if($request->filled('identificacion')){
            $c1 = $request->input('identificacion');
            $terceros = Tercero::has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->where('NRO_IDENTIFICACION', $c1)->paginate(20);
        }elseif ($request->filled('nombre')) {
            $c2 = $request->input('nombre');
            $terceros = Tercero::has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->where(function($s) use($c2){$s->where('PRIMER_NOMBRE', 'like', '%' . $c2 . '%')->orWhere('PRIMER_APELLIDO', 'like', '%' . $c2 . '%');})->paginate(20);
        }else{
            return redirect('/cartera/listar');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        
        return view('cartera.lista', compact('terceros', 'c1', 'c2', 'usuario'));
    }



    public function buscarMes($tabla, $mes)
    {
        for ($i=0; $i < count($tabla); $i++) { 
            if($tabla[$i]->mes == $mes){
                return $i;
            }
        }

        return 0;
    }

    public function getMes($numero)
    {
        switch ($numero) {
            case 1:
                $mes = "enero";
                break;
            case 2:
                $mes = "febrero";
                break;
            case 3:
                $mes = "marzo";
                break;
            case 4:
                $mes = "abril";
                break;
            case 5:
                $mes = "mayo";
                break;
            case 6:
                $mes = "junio";
                break;
            case 7:
                $mes = "julio";
                break;
            case 8:
                $mes = "agosto";
                break;
            case 9:
                $mes = "septiembre";
                break;
            case 10:
                $mes = "octubre";
                break;
            case 11:
                $mes = "noviembre";
                break;
            case 12:
                $mes = "diciembre";
                break;
        }

        return $mes;
    }

    public function listarAcuerdos()
    {
        $acuerdos = Acuerdo::with(['propietario.tercero'])->orderBy('vencidas', 'desc')->paginate('20');
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.lista', compact('acuerdos', 'usuario'));
    }

    public function nuevoAcuerdo()
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.form', compact('usuario'));
    }

    public function buscarPropietario($identificacion)
    {
        $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'RAZON_SOCIAL')->whereHas('propietario', function($q){$q->has('vehiculos')->orHas('vehiculospri');})->where('NRO_IDENTIFICACION', 'like', $identificacion . '%')->get();
        if(count($propietarios) > 0){
            foreach ($propietarios as $propietario) {
                $tercero = $propietario->TERCERO;
                $propietario->placas = Vehiculo::select('VEHICULO', 'PLACA')->whereHas('propietario',  function($q) use($tercero){$q->where('propietario.TERCERO', $tercero);})->orWhereHas('propietarios',  function($q) use($tercero){$q->where('propietario.TERCERO', $tercero);})->get();
            }           
        }
        return json_encode($propietarios);
    }



    public function cuotasPorAcuerdo($acuerdo)
    {
        $acuerdo = Acuerdo::with('cuotasAll')->find($acuerdo);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.cuotas', compact('acuerdo', 'usuario'));
    }

    public function registrarPago(Request $request)
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        if($request->filled('identificacion')){
            $tercero = Tercero::where('NRO_IDENTIFICACION', $request->input('identificacion'))->first();
            if($tercero != null){
                $ter = $tercero->TERCERO;
                $acuerdos = Acuerdo::with(["cuotasAll"=>function($q){$q->where('estado', '!=', 'Pagada');}])->whereHas('propietario', function($q) use($ter){$q->where('TERCERO', $ter);})->where('estado', '!=', 'Pagado')->get();
                $busq = 1;
            }else{
                return back()->withErrors(["sql" => "La identificación ingresada no corresponde a ningun propietario"]);
            }

            return view('acuerdos.pagar', compact('acuerdos', 'tercero', 'busq', 'usuario'));
            
        }elseif($request->filled('placa')){
            $acuerdos = Acuerdo::with(["cuotasAll"=>function($q){$q->where('estado', '!=', 'Pagada');}])->where('placa', $request->input('placa'))->where('estado', '!=', 'Pagado')->get();
            if(count($acuerdos) > 0){
                $tercero = Tercero::find($acuerdos[0]->propietario_tercero);
                $busq = 1;
            }else{
                return back()->withErrors(["sql" => "No se encontraron acuerdos de pago para la placa ingresada"]);
            }

            return view('acuerdos.pagar', compact('acuerdos', 'tercero', 'busq', 'usuario'));
        }else{
            return view('acuerdos.pagar', compact('usuario'));
        }
    }

    public function pagarCuota(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $cuota = Cuota::with('acuerdo')->find($request->input('cuota'));
        if($cuota != null){
            if($cuota->estado == "Vencida"){
                $cuota->acuerdo->vencidas = $cuota->acuerdo->vencidas - 1;
            }
            $cuota->estado = "Pagada";
            $cuota->fecha_pago = Carbon::now();
            $cuota->save();

            $cuota->acuerdo->pagadas = $cuota->acuerdo->pagadas + 1;
            $cuota->acuerdo->saldo = $cuota->acuerdo->saldo - $cuota->acuerdo->pago_mensual;
            if($cuota->acuerdo->pagadas == $cuota->acuerdo->cuotas){
                $cuota->acuerdo->estado = "Pagado";
                $cuota->acuerdo->saldo = 0;
            }
            $cuota->acuerdo->save();
            $respuesta->estado = "success";
            $respuesta->mensaje = "La cuota ha sido pagada exitosamente";
            $respuesta->direccion = $request->root() . "/acuerdos/registrar_pago?identificacion=" . $request->input('idpropietario');
        }else{
            $respuesta->estado = "error";
            $respuesta->mensaje = "Cuota no encontrada";
        }
        } catch (Exception $ex) {
            $respuesta->estado = "error";
            $respuesta->mensaje = $ex->getMessage();
        }
        
        return json_encode($respuesta);
    }

    public function filtrarAcuerdos(Request $request)
    {
        $propietario = $request->input('propietario');
        $estado = $request->input('estado');
        if(!empty($propietario) && !empty($estado)){
            $acuerdos = Acuerdo::whereHas('propietario', function($q) use($propietario){$q->whereHas('tercero', function($r) use($propietario){$r->where('NRO_IDENTIFICACION', $propietario);});})->where('estado', $estado)->paginate(20)->appends($request->query());
        }elseif(!empty($propietario)){
            $acuerdos = Acuerdo::whereHas('propietario', function($q) use($propietario){$q->whereHas('tercero', function($r) use($propietario){$r->where('NRO_IDENTIFICACION', $propietario);});})->paginate(20)->appends($request->query());
        }elseif(!empty($estado)){
            $acuerdos = Acuerdo::where('estado', $estado)->paginate(20)->appends($request->query());
        }else{
            return redirect('/acuerdos/listar');
        }
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.lista', compact('acuerdos', 'usuario', 'propietario', 'estado'));
    }

    public function iniciarProceso(Acuerdo $acuerdo)
    {
        $acuerdo->estado = "Proceso";
        $acuerdo->fecha_proceso = Carbon::now();
        $acuerdo->save();

        return redirect('/acuerdos/listar');
    }

    public function numeroToOrdinario($numero, $resp = "")
    {
        if($numero == 1){
            if(empty($resp)){
                $resp = "primer";
            }else{
                $resp = $resp . "primero";
            }      
        }elseif($numero == 2){
            $resp = $resp . "segundo";
        }elseif($numero == 3){
            if(empty($resp)){
                $resp = "tercer";
            }else{
                $resp = $resp . "tercero";
            }
        }elseif($numero == 4){
            $resp = $resp . "cuarto";
        }elseif($numero == 5){
            $resp = $resp . "quinto";
        }elseif($numero == 6){
            $resp = $resp . "sexto";
        }elseif($numero == 7){
            $resp = $resp . "séptimo";
        }elseif($numero == 8){
            $resp = $resp . "octavo";
        }elseif($numero == 9){
            $resp = $resp . "noveno";
        }elseif($numero > 9 && $numero < 20){
            $resp = "Décimo " . $this->numeroToOrdinario($numero%10, $resp); 
        }elseif($numero > 20){
            $resp = "Vigésimo " . $this->numeroToOrdinario($numero%20, $resp);
        }
        
        return $resp;
    }

    public function carteraDesdeArchivo()
    {
        $excel = PhpSpreadsheetIOFactory::load(storage_path('facturas.xlsx'));
        $hoja = $excel->setActiveSheetIndex(0);
        $numRows = $hoja->getHighestRow();

        $deuda = 0;
        $intereses = 0;
        $hoy = Carbon::now();
        $tasas = Interes::get();
        $tabla = [];

        for ($i=3; $i < $numRows; $i++) { 
            try {
                $vencimiento = $hoja->getCell('F'.$i)->getFormattedValue();
                if(!empty($vencimiento)){
                    $fecha = Carbon::parse($vencimiento);
                    $fila = new stdClass();
                    $fila->mes = strtoupper($this->getMes($fecha->month)) . " DE " . $fecha->year;
                    $fila->capital = $hoja->getCell('G'.$i)->getCalculatedValue();
                    $fila->concepto = $hoja->getCell('D'.$i)->getValue();
                    $meses = $hoy->diffInMonths($fecha) + 1;
                    $inte = 0;
                    for ($j=0; $j < $meses ; $j++) {
                        $interes = null;
                        foreach ($tasas as $tasa) {
                            if($tasa->year == $fecha->year && $tasa->mes == $fecha->month){
                                $interes = $tasa;
                                break;
                            }
                        }
                        if($interes != null){
                            $intmes = ($fila->capital * $interes->tasa) / 100;
                            $inte = $inte + $intmes;
                            $intereses = $intereses + $intmes; 
                        } 
                        $fecha->addMonth();            
                    }
                    $fila->interes = $inte;
                    $tabla[] = $fila;
                    $deuda = $deuda + $fila->capital;
                }
                
            } catch (InvalidFormatException $ex) {
                
            }
        }

        $dompdf = PDF::loadView('cartera.modeloIntereses', ['cuotas'=>$tabla, 'placa'=> 'BUY-687', 'propietario'=> 'YEIMY ORTEGA DURAN']);
        $dompdf->setPaper('Legal', 'Portrait');
        $nametabla = "Tabla de intereses 1094779758.pdf"; 
        $dompdf->save(storage_path($nametabla));

        return response()->download(storage_path($nametabla), $nametabla, ["Content-Type" => "application/pdf"]);

    }
}
