<?php

namespace App\Http\Controllers;

use App\Models\Cuentac;
use App\Models\Novedad;
use App\Models\Propietario;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HomeController extends Controller
{
    /*public function index()
    {
        $cuentas = Cuentac::select('id', 'estado')->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio')->count();
        $fecha = Carbon::now();
        $curso = Servicio::select('id', 'estado', 'fecha')->where('estado', 'En curso')->orWhere('estado', 'Asignado')->orWhere('estado', 'Pendiente')->count();
        $finalizados = Servicio::select('id', 'estado', 'fecha')->where('estado', 'Finalizado')->whereDate('fecha', $fecha)->count();
        $cancelados = Servicio::select('id', 'estado', 'fecha')->whereDate('fecha', $fecha)->where(function($q){
            $q->where('estado', 'Cancelado')->orWhere('estado', 'No vehiculo');
        })->count();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();


        return view('dashboard.index', compact('usuario', 'cuentas', 'cancelados', 'curso', 'finalizados'));
    }*/
    
    public function index()
    {
        $cuentas = Cuentac::select('id', 'estado')->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio')->count();
        $fecha = Carbon::now();
        $curso = Servicio::select('id', 'estado', 'fecha')->where('estado', 'En curso')->orWhere('estado', 'Asignado')->orWhere('estado', 'Pendiente')->count();
        $finalizados = Servicio::select('id', 'estado', 'fecha')->where('estado', 'Finalizado')->whereDate('fecha', $fecha)->count();
        $cancelados = Servicio::select('id', 'estado', 'fecha')->whereDate('fecha', $fecha)->where(function($q){
            $q->where('estado', 'Cancelado')->orWhere('estado', 'No vehiculo');
        })->count();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        $valores = [];
        $valores[0]= Servicio::select('id', 'estado', 'pago')->where('pago', 'Efectivo')->where('estado', 'Finalizado')->whereBetween('fecha', [$fecha->year . '-' . $fecha->month . '-01', $fecha->year . '-' . $fecha->month . '-31'])->count();
        $valores[1]= Servicio::select('id', 'estado', 'pago')->where('pago', 'Vale electrónico')->where('estado', 'Finalizado')->whereBetween('fecha', [$fecha->year . '-' . $fecha->month . '-01', $fecha->year . '-' . $fecha->month . '-31'])->count();
        $valores[2]= Servicio::select('id', 'estado', 'pago')->where('pago', 'Vale físico')->where('estado', 'Finalizado')->whereBetween('fecha', [$fecha->year . '-' . $fecha->month . '-01', $fecha->year . '-' . $fecha->month . '-31'])->count();


        $meses = [];
        $vmeses = [];

        for ($i=0; $i < 6; $i++) { 
            $vmeses[$i] = Servicio::select('id', 'estado', 'fecha')->where('estado', 'Finalizado')->whereBetween('fecha', [$fecha->year . '-' . $fecha->month . '-01', $fecha->year . '-' . $fecha->month . '-31'])->count();
            switch ($fecha->format("M")) {
                case 'Jan':
                    $meses[$i] = 'Enero';
                    break;
                case 'Feb':
                    $meses[$i] = 'Febrero';
                    break;
                case 'Mar':
                    $meses[$i] = 'Marzo';
                    break;
                case 'Apr':
                    $meses[$i] = 'Abril';
                    break;
                case 'May':
                    $meses[$i] = 'Mayo';
                    break;
                case 'Jun':
                    $meses[$i] = 'Junio';
                    break;
                case 'Jul':
                    $meses[$i] = 'Julio';
                    break;
                case 'Aug':
                    $meses[$i] = 'Agosto';
                    break;
                case 'Sep':
                    $meses[$i] = 'Septiembre';
                    break;
                case 'Oct':
                    $meses[$i] = 'Octubre';
                    break;
                case 'Nov':
                    $meses[$i] = 'Noviembre';
                    break;
                case 'Dec':
                    $meses[$i] = 'Diciembre';
                    break;
            }
            $fecha->subMonth();
        }

        $meses = array_reverse($meses);
        $vmeses = array_reverse($vmeses);

        $fecha = Carbon::now();

        $tops = Servicio::select(DB::raw('cuentasc_id, count(*) as cantidad'))->where('estado', 'finalizado')->whereBetween('fecha', [$fecha->year . '-' . $fecha->month . '-01', $fecha->year . '-' . $fecha->month . '-31'])->groupBy('cuentasc_id')->orderBy('cantidad', 'DESC')->take(5)->get();

        $cuentasc = [];
        foreach ($tops as $top) {
            $cuenta = Cuentac::select('id', 'foto', 'conductor_CONDUCTOR')->with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}, 'calificaciones'])->find($top->cuentasc_id);
            $cuenta->topservicios = $top->cantidad;
            if(count($cuenta->calificaciones) == 0){
                $cuenta->amarillas = 5;
                $cuenta->grises = 0;
            }else{
                $cuenta->amarillas = round($cuenta->calificaciones->avg('puntaje'));
                $cuenta->grises = 5-$cuenta->amarillas;
            }
            $cuentasc[] = $cuenta;
        }
        
        $novedades = Novedad::with('tiponovedad')->where('estado', 'Abierta')->get();


        return view('dashboard.index', compact('usuario', 'cuentas', 'novedades', 'cancelados', 'curso', 'finalizados', 'valores', 'meses', 'vmeses', 'cuentasc'));
    }
    
    public function vcard()
    {
        $excel = IOFactory::load(storage_path() . "/propietarios.xlsx");
        $vcard = "";
        for ($i=0; $i < 3; $i++) { 
            $hoja = $excel->setActiveSheetIndex($i);
            $filas = $hoja->getHighestRow();

            for ($j=2; $j <= $filas; $j++) { 
                $cedula = $hoja->getCell('A'.$j)->getValue();
                $propietario = Propietario::with('tercero', 'vehiculospri')->whereHas('tercero', function($q) use($cedula){$q->where('NRO_IDENTIFICACION', $cedula);})->first();
                if($propietario != null){
                    $vcard = $vcard . "BEGIN:VCARD\nVERSION:2.1\n";
                    $placa = count($propietario->vehiculospri);
                    if($placa > 0){
                        $placa = $propietario->vehiculospri[0]->PLACA;
                    }else{
                        $placa = '';
                    }
                    $vcard = $vcard . "N:" . $placa . ";" . $propietario->tercero->RAZON_SOCIAL . ";;;\n";
                    $vcard = $vcard  . "FN:" . $propietario->tercero->RAZON_SOCIAL . "\n";
                    $vcard = $vcard .  "TEL;CELL:" . $propietario->tercero->CELULAR . "\n";
                    if($propietario->tercero->TELEFONO != NULL){
                        $fijos = explode(" - ", $propietario->tercero->TELEFONO);
                        $vcard = $vcard .  "TEL;HOME:" . $fijos[0] . "\n";
                    }
                    $vcard = $vcard . "END:VCARD\n";
                }
            }
        }

        file_put_contents("contactos.vcf", $vcard);

        return "Listo";
    }
}
