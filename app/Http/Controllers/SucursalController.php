<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Conductor;
use App\Models\Sucursal;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Mensaje;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SoapClient;
use SoapFault;
use stdClass;

class SucursalController extends Controller
{
    public function index()
    {

        $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('sucursales.lista', compact('sucursales', 'usuario'));
    }

    public function nuevo()
    {

        $sucursal = new Sucursal();
        $estaciones = Tercero::where('RAZON_SOCIAL', 'like' , '%eds%')->orWhere('RAZON_SOCIAL', 'like', '%estacion%')->orWhere('RAZON_SOCIAL', 'like', '%planey%')->orWhere('RAZON_SOCIAL', 'like' , '%e.d.s%')->orderBy('RAZON_SOCIAL', 'ASC')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('sucursales.form', ['sucursal' => $sucursal, 'estaciones' => $estaciones, 'usuario' => $usuario, 'method' => 'post', 'route' => ['sucursales.agregar']]);
    }

    public function editar(Sucursal $sucursal)
    {
        $sucursal = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado', 'usuario');}])->find($sucursal->id);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('sucursales.form', ['sucursal' => $sucursal, 'usuario' => $usuario, 'method' => 'put', 'route' => ['sucursales.actualizar', $sucursal->id]]);
    }

    public function store(Request $request)
    {

        $tercero = Tercero::select('TERCERO', 'NRO_IDENTIFICACION')->where('TERCERO', $request->input('tercero'))->first();
        try {
            $user = new User();
            $user->nombres = $request->input('nombre');
            $user->identificacion = $tercero->NRO_IDENTIFICACION;
            $user->usuario = $request->input('usuario');
            $user->password = Hash::make($request->input('password'));
            $user->estado = $request->input('estado');
            $user->roles_id = 3;
            $user->save();

            $sucursal = new Sucursal();
            $sucursal->saldorecargas = 0;
            $sucursal->saldoventas = 0;
            $sucursal->users_id = $user->id;
            $sucursal->tercero_TERCERO = $tercero->TERCERO;
            $sucursal->save();

            return redirect('sucursales');
        } catch (QueryException $ex) {
            $errorCode = $ex->errorInfo[1];
            if ($errorCode == 1062) {
                return back()->withErrors(['sql' => 'El usuario ingresado ya está en uso']);
            }
        }
    }

    public function update(Request $request, Sucursal $sucursal)
    {

        $sucursal = Sucursal::with('user')->find($sucursal->id);

        $sucursal->user->nombres = $request->input('nombre');
        $sucursal->user->password = Hash::make($request->input('password'));
        $sucursal->user->estado = $request->input('estado');
        $sucursal->user->save();

        return redirect('sucursales');
    }

    public function transacciones($sucursal)
    {

        if ($sucursal != 0) {
            $suc = Sucursal::with('user')->where('id', $sucursal)->first();
            $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->where('sucursales_id', $suc->id)->orderBy('id', 'DESC')->paginate(20);
        } else {
            $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->where('tipo', 'Recarga')->whereNull('sucursales_id')->orderBy('id', 'DESC')->paginate(20);
            $suc = new stdClass();
            $user = new stdClass();
            $user->nombres = "Sucursal Taxsur";
            $suc->id = 0;
            $suc->user = $user;
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.transacciones2', compact('suc', 'transacciones', 'usuario'));
    }

    public function filtrar(Request $request)
    {

        if ($request->filled('id')) {
            $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->where('id', $request->input('id'))->paginate(20)->appends($request->query());
            $filtro = array('ID', $request->input('id'));
        } elseif ($request->filled('empresa')) {
            $empresa = $request->input('empresa');
            $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('tercero', function ($q) use ($empresa) {
                $q->where('RAZON_SOCIAL', 'like', '%' . $empresa . '%');
            })->paginate(20)->appends($request->query());
            $filtro = array('Empresa', $request->input('empresa'));
        } elseif ($request->filled('nombre')) {
            $nombre = $request->input('nombre');
            $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('user', function($q) use($nombre) {$q->where('nombres', 'like', '%' . $nombre  . '%');})->paginate(20)->appends($request->query());
            $filtro = array('Nombre', $request->input('nombre'));
        } elseif ($request->filled('estado')) {
            $estado = $request->input('estado');
            $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('user', function ($q) use ($estado) {$q->where('estado', $estado);
                })->paginate(20)->appends($request->query());
            $filtro = array('Estado', $request->input('estado'));
        } else {
            return redirect('sucursales');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('sucursales.lista', compact('sucursales', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {
        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "ID") {
                $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->where('id', $filtro[1])->get();
            } elseif ($filtro[0] == "Empresa") {
                $empresa = $filtro[1];
                $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('tercero', function ($q) use ($empresa) {
                    $q->where('RAZON_SOCIAL', 'like', '%' . $empresa . '%');
                })->get();
            } elseif ($filtro[0] == "nombre") {
                $nombre = $request->input('nombre');
                $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('user', function($q) use($nombre) {$q->where('nombres', 'like', '%' . $nombre  . '%');})->get();
            } elseif ($filtro[0] == "Estado") {
                $estado = $filtro[1];
                $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->whereHas('user', function ($q) use ($estado) {
                    $q->where('estado', $estado);
                })->get();
            }
        } else {
            $sucursales = Sucursal::with(['tercero'=>function($q){$q->select('TERCERO', 'RAZON_SOCIAL');}, 'user'=>function($r){$r->select('id', 'nombres', 'estado');}])->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:E1");
        $sheet->setCellValue("B1", "Cuentas Corriente de Sucursales");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:D1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Empresa");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Estado");
        $sheet->getStyle("A1:D2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($sucursales as $sucursal) {
            $sheet->setCellValue("A" . $indice, $sucursal->id);
            $sheet->setCellValue("B" . $indice, $sucursal->tercero->RAZON_SOCIAL);
            $sheet->setCellValue("C" . $indice, $sucursal->user->nombres);
            if ($sucursal->user->estado == 1) {
                $sheet->setCellValue("D" . $indice, "Activa");
            } else {
                $sheet->setCellValue("D" . $indice, "Inactiva");
            }
            $indice++;
        }

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Sucursales.xlsx');
        $archivo = file_get_contents('Sucursales.xlsx');
        unlink('Sucursales.xlsx');

        return base64_encode($archivo);
    }

    public function nuevarecarga()
    {

        $transaccion = new Transaccion();

        return view('sucursales.recargas', ['transaccion' => $transaccion, 'method' => 'post', 'route' => ['recargas.nueva']]);
    }

    public function saverecarga(Request $request)
    {

        $conductor = Conductor::with(['cuentac' => function($q){$q->select('id', 'saldo', 'conductor_CONDUCTOR', 'istransacciones');}])->select('CONDUCTOR', 'NUMERO_IDENTIFICACION')->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->first();
        $sucursal = Auth::user()->sucursal;
        $fecha = Carbon::now();
        $caja = Caja::where('sucursales_id', $sucursal->id)->latest('id')->first();

        if($caja != null){
            if($caja->cierre != null){
                $caja = new Caja();
                $caja->apertura = $fecha;
                $caja->sucursales_id = $sucursal->id;
                $caja->totalrecargas = 0;
                $caja->totalventas = 0;
                $caja->save();
            }
        }

        if ($conductor != null) {

            if($conductor->cuentac->istransacciones == 1){
                return back()->withErrors(['sql' => 'Las transacciones para este conductor están temporalmente bloqueadas']);
            }

            $transaccion = new Transaccion();
            $transaccion->tipo = "Recarga";
            $transaccion->tiporecarga = $request->input('tiporecarga');
            $transaccion->fecha = Carbon::now();
            $transaccion->valor = abs($request->input('valor'));
            $transaccion->cuentasc_id = $conductor->cuentac->id;
            $transaccion->sucursales_id = $sucursal->id;
            

            if ($transaccion->tiporecarga == "Ingreso") {
                $tipo = 1;
                $conductor->cuentac->saldo = $conductor->cuentac->saldo + $transaccion->valor;
                $sucursal->saldorecargas = $sucursal->saldorecargas + $transaccion->valor;
                $caja->totalrecargas = $caja->totalrecargas + $transaccion->valor;
            } else {
                $tipo = 3;
                $conductor->cuentac->saldo = $conductor->cuentac->saldo - $transaccion->valor;
                $sucursal->saldorecargas = $sucursal->saldorecargas - $transaccion->valor;
                $caja->totalrecargas = $caja->totalrecargas - $transaccion->valor;
            }

            $ante = Transaccion::where('sucursales_id', $sucursal->id)->where('cuentasc_id', $conductor->cuentac->id)->where('tipo', 'Recarga')->orderBy('id', 'DESC')->first();
            if($ante != null){
                $ahora = Carbon::now('-05:00');
                if($ahora->diffInSeconds($ante->fecha) < 180){
                    return back()->withErrors(['sql' => "Error procesando la solicitud. Intente de nuevo"]);
                }
            }

            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

            try {
                $client = new SoapClient($url, ['exceptions' => true]);
                $result = $client->registrarTicket();
                $parametros = array("ticket"=> $result->registrarTicketReturn, "numeroIdentificacionConductor"=>$conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa"=>$sucursal->tercero->NRO_IDENTIFICACION, "monto"=>$transaccion->valor, "tipo"=>$tipo);
                $peticion = $client->registrarRecarga($parametros);

                if($peticion->registrarRecargaReturn->codigoError != "0000"){
                    return back()->withErrors(['sql' => $peticion->registrarRecargaReturn->mensajeError ]);
                }

                $mensaje = new Mensaje();
                $mensaje->fecha = Carbon::now('-05:00');
                $mensaje->sentido = "Recibido";
                $mensaje->estado = "Pendiente";
                $mensaje->cuentasc_id = $conductor->cuentac->id;
                
                if($transaccion->tiporecarga == "Ingreso"){           
                    $mensaje->texto = "Recarga realizada por valor de: $" . number_format($transaccion->valor);      
                }else{
                    $mensaje->texto = "Egreso realizado por valor de: $" . number_format($transaccion->valor); 
                }  
                $mensaje->save();        

                $transaccion->save();
                $conductor->cuentac->save();
                //$sucursal->save();
                $caja->save();

                return redirect('sucursales/recargas/nueva')->withErrors(['bien' => 'La transacción fue exitosa']);

            } catch (SoapFault $e) {
                return back()->withErrors(['sql' => $e->getMessage()]);
            }  catch(Exception $e){
                return back()->withErrors(['sql' => $e->getMessage()]);
            }        
        } else {
            return back()->withErrors(['sql' => 'No se ha encontrado ningun conductor con la identificación ' . $request->input('identificacion')]);
        }
    }

    public function nuevopago()
    {
        $transaccion = new Transaccion();

        return view('sucursales.pagos', ['transaccion' => $transaccion, 'method' => 'post', 'route' => ['pagos.nuevo']]);
    }

    public function savepago(Request $request)
    {
        $conductor = Conductor::with(['cuentac' => function($q){$q->select('id', 'saldo', 'saldovales', 'password', 'conductor_CONDUCTOR', 'istransacciones');}])->select('CONDUCTOR', 'NUMERO_IDENTIFICACION')->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->first();
        $sucursal = Auth::user()->sucursal;
        $fecha = Carbon::now();
        $caja = Caja::where('sucursales_id', $sucursal->id)->latest('id')->first();

        if($caja != null){
            if($caja->cierre != null){
                $caja = new Caja();
                $caja->apertura = $fecha;
                $caja->sucursales_id = $sucursal->id;
                $caja->totalrecargas = 0;
                $caja->totalventas = 0;
                $caja->save();
            }
        }

        $logFile = fopen("../storage/logToIcon.txt", 'a') or die("Error creando archivo");

        if ($conductor != null) {

            if($conductor->cuentac->istransacciones == 1){
                return back()->withErrors(['sql' => 'Las transacciones para este conductor están temporalmente bloqueadas']);
            }

            if ($conductor->cuentac->password == $request->input('password')) {
                if (intval($conductor->cuentac->saldovales) >= intval($request->input('valor'))) {
                    $transaccion = new Transaccion();
                    $transaccion->tipo = "Venta de Combustible";
                    $transaccion->fecha = Carbon::now();
                    $transaccion->valor = $request->input('valor');
                    $transaccion->cuentasc_id = $conductor->cuentac->id;
                    $transaccion->sucursales_id = $sucursal->id;
                    
                    $conductor->cuentac->saldovales = $conductor->cuentac->saldovales - $transaccion->valor;
                    
                    $sucursal->saldoventas = $sucursal->saldoventas + $transaccion->valor;
                   
                    $caja->totalventas = $caja->totalventas + $transaccion->valor;

                    $ante = Transaccion::where('sucursales_id', $sucursal->id)->where('cuentasc_id', $conductor->cuentac->id)->where('tipo', 'Venta de Combustible')->orderBy('id', 'DESC')->first();
                    if($ante != null){
                        $ahora = Carbon::now();
                        if($ahora->diffInSeconds($ante->fecha) < 180){
                            return back()->withErrors(['sql' => "Error procesando la solicitud. Intente de nuevo"]);
                        }
                    }
                    
                    $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";			

                    try {
						$client = new SoapClient($url, ['exceptions' => true]);
						$result = $client->registrarTicket();
						$parametros = array("ticket"=>$result->registrarTicketReturn, "numeroIdentificacionConductor"=>$conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa"=>$sucursal->tercero->NRO_IDENTIFICACION, "monto"=>$transaccion->valor, "tipo"=>"1");
						$peticion = $client->registrarConsumo($parametros);
						
						fwrite($logFile, "\n".date("d/m/Y H:i:s"). json_encode($parametros) . "-->" . json_encode($peticion->registrarConsumoReturn)) or die("Error escribiendo en el archivo");
						fclose($logFile);
						
						if($peticion->registrarConsumoReturn->codigoError != "0000"){
							return back()->withErrors(['sql' => "Error integración. " . $peticion->registrarConsumoReturn->mensajeError ]);
						}
                        
                        $mensaje = new Mensaje();
                        $mensaje->texto = "Compra de combustible realizada por valor de: $" . number_format($transaccion->valor);
                        $mensaje->fecha = Carbon::now();
                        $mensaje->sentido = "Recibido";
                        $mensaje->estado = "Pendiente";
                        $mensaje->cuentasc_id = $conductor->cuentac->id;

                        $transaccion->save();
                        $conductor->cuentac->save();
                        //$sucursal->save();
                        $mensaje->save();
                        $caja->save();

                        return redirect('sucursales/pagos/nuevo')->withErrors(['bien' => 'La transacción fue exitosa']);
                        
                    } catch (SoapFault $e) {
						fwrite($logFile, "\n".date("d/m/Y H:i:s"). $e->getMessage()) or die("Error escribiendo en el archivo");
						fclose($logFile);
						
                        return back()->withErrors(['sql' => $e->getMessage()]);
                    }
                } else {
                    return back()->withErrors(['sql' => 'Saldo insuficiente para realizar esta compra. El saldo del conductor es: $' . number_format($conductor->cuentac->saldovales)]);
                }
            } else {
                return back()->withErrors(['sql' => 'La transacción falló por problemas de autenticación']);
            }
        } else {
            return back()->withErrors(['sql' => 'No se encontró ningun conductor con la identificación ' . $request->input('identificacion')]);
        }   
    }

    public function cierrecaja()
    {
        $sucursal = Auth::user()->sucursal;
        $fecha = Carbon::now();
        $caja = Caja::where('sucursales_id', $sucursal->id)->whereNull('cierre')->latest('id')->first();
        
        if($caja != null){
            $transacciones = Transaccion::where('sucursales_id', $sucursal->id)->whereBetween('fecha', [$caja->apertura, $fecha])->orderBy('id', 'DESC')->paginate(30);
            $recargas = Transaccion::where('sucursales_id', $sucursal->id)->whereBetween('fecha', [$caja->apertura, $fecha])->where('tipo', 'Recarga')->count();
            $ventas = Transaccion::where('sucursales_id', $sucursal->id)->whereBetween('fecha', [$caja->apertura, $fecha])->where('tipo', 'Venta de Combustible')->count();
            $efectivo = Transaccion::where('sucursales_id', $sucursal->id)->whereBetween('fecha', [$caja->apertura, $fecha])->where('tipo', 'Pago')->count();
            
            return view('sucursales.cierrecaja', compact('sucursal', 'caja', 'transacciones', 'recargas', 'ventas', 'efectivo'));

        }else{

            return redirect('sucursales/recargas/nueva')->withErrors(['sql' => 'No se ha abierto caja']);
        }
    }

    public function cerrarcaja(Request $request)
    {

        $sucursal = Sucursal::with('user')->find($request->input('sucursal'));
        $caja = Caja::find($request->input('caja'));

        if (strcasecmp($sucursal->user->usuario, $request->input('usuario')) == 0) {
            if (Hash::check($request->input('password'), $sucursal->user->password)) {
                if ($caja->cierre == null) {
                    $caja->responsable = $request->input('responsable');
                    $caja->cierre = Carbon::now('-05:00');
                    $caja->save();

                    return redirect('sucursales/recargas/nueva')->withErrors(['bien' => 'El cierre de caja fue exitoso']);
                } else {
                    return redirect('sucursales/recargas/nueva')->withErrors(['sql' => 'La caja del dia de hoy ya se cerró']);
                }
            } else {
                return back()->withErrors(['sql' => 'El cierre de caja falló por problemas de autenticación']);
            }
        } else {
            return back()->withErrors(['sql' => 'El usuario ingresado no corresponde a la sucursal']);
        }
    }

    public function informacion(Sucursal $sucursal)
    {
        $fecha = Carbon::now('-05:00');
        $caja = Caja::where('sucursales_id', $sucursal->id)->whereDate('apertura', $fecha)->first();
        if ($caja == null) {
            $caja = new Caja();
            $caja->apertura = $fecha;
            $caja->sucursales_id = $sucursal->id;
            $caja->totalrecargas = 0;
            $caja->totalventas = 0;
            $caja->save();
        }

        return view('sucursales.informacion', compact('sucursal', 'caja'));
    }

    public function validarinfo(Request $request)
    {

        $sucursal = Sucursal::with('user')->find($request->input('sucursal'));
        $caja = Caja::find($request->input('caja'));

        if ($sucursal->user->usuario == $request->input('usuario')) {
            if (Hash::check($request->input('password'), $sucursal->user->password)) {
                return "Correcto";
            } else {
                return "Falla al autenticar";
            }
        } else {
            return "El usuario ingresado no corresponde a la sucursal";
        }
    }

    public function filtrartrans(Request $request, $sucursal)
    {
        if(empty($request->input('id')) && empty($request->input('fecha')) && empty($request->input('tipo')) && empty($request->input('tiporecarga')) && empty($request->input('conductor')) && empty($request->input('identificacion'))){
            return redirect('/transacciones/sucursal/'. $sucursal);
        }

        $ids = [];
        $filtro = "";
        $c1="";$c2="";$c3="";$c4="";$c5="";$c6="";

        if($sucursal != 0){
            $suc = Sucursal::with('user')->find($sucursal);
        }else{
            $suc = new stdClass();
            $user = new stdClass();
            $user->nombres = "Sucursal Taxsur";
            $suc->id = 0;
            $suc->user = $user;
        }

        if ($request->filled('id')) {
            if ($sucursal != 0) {
                $s1 =  Transaccion::where('sucursales_id', $suc->id)->where('id', $request->input('id'))->pluck('id')->toArray();            
            } else {
                $s1 =  Transaccion::whereNull('sucursales_id')->where('id', $request->input('id'))->pluck('id')->toArray();
            }
            $ids = $s1;
            $c1 = $request->input('id');
            $filtro = $filtro . "ID=" . $request->input('id') . ", ";
        } 
        
        if ($request->filled('fecha')) {
            $dates = explode(" - ", $request->input('fecha'));
            if ($sucursal != 0) {
                if(empty($ids)){
                    $s2 = Transaccion::where('sucursales_id', $suc->id)->whereBetween('fecha', $dates)->pluck('id')->toArray();
                }else{
                    $s2 = Transaccion::where('sucursales_id', $suc->id)->whereBetween('fecha', $dates)->whereIn('id', $ids)->pluck('id')->toArray();
                }
            } else {
                if(empty($ids)){
                    $s2 = Transaccion::whereNull('sucursales_id')->whereBetween('fecha', $dates)->pluck('id')->toArray();
                }else{
                    $s2 = Transaccion::whereNull('sucursales_id')->whereBetween('fecha', $dates)->whereIn('id', $ids)->pluck('id')->toArray();
                }              
            }
            $ids = $s2;
            $c2 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        } 
        
        if ($request->filled('tipo')) {
            if ($sucursal != 0) {
                if(empty($ids)){
                    $s3 = Transaccion::where('sucursales_id', $suc->id)->where('tipo', $request->input('tipo'))->pluck('id')->toArray();
                }else{
                    $s3 = Transaccion::where('sucursales_id', $suc->id)->where('tipo', $request->input('tipo'))->whereIn('id', $ids)->pluck('id')->toArray();
                }               
            } else {
                if(empty($ids)){
                    $s3 = Transaccion::whereNull('sucursales_id')->where('tipo', $request->input('tipo'))->pluck('id')->toArray();
                }else{
                    $s3 = Transaccion::whereNull('sucursales_id')->where('tipo', $request->input('tipo'))->whereIn('id', $ids)->pluck('id')->toArray();
                }    
            }
            $ids = $s3;
            $c3 = $request->input('tipo');
            $filtro = $filtro . "Tipo=" . $request->input('tipo') . ", ";
        } 
        
        if ($request->filled('tiporecarga')) {
            if ($sucursal != 0) {
                if(empty($ids)){
                    $s4 = Transaccion::where('sucursales_id', $suc->id)->where('tiporecarga', $request->input('tiporecarga'))->pluck('id')->toArray();
                }else{
                    $s4 = Transaccion::where('sucursales_id', $suc->id)->where('tiporecarga', $request->input('tiporecarga'))->whereIn('id', $ids)->pluck('id')->toArray();
                }            
            } else {
                if(empty($ids)){
                    $s4 = Transaccion::whereNull('sucursales_id')->where('tiporecarga', $request->input('tiporecarga'))->pluck('id')->toArray();
                }else{
                    $s4 = Transaccion::whereNull('sucursales_id')->where('tiporecarga', $request->input('tiporecarga'))->whereIn('id', $ids)->pluck('id')->toArray();
                }  
            }
            $ids = $s4;
            $c4 = $request->input('tiporecarga');
            $filtro = $filtro . "Tipo recarga=" . $request->input('tiporecarga') . ", ";
        } 
        
        if ($request->filled('conductor')) {
            $conductor = $request->input('conductor');
            if ($sucursal != 0) {
                if(empty($ids)){
                    $s5 = Transaccion::where('sucursales_id', $suc->id)->whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NOMBRE', 'like', '%' . $conductor . '%');});})->pluck('id')->toArray();
                }else{
                    $s5 = Transaccion::where('sucursales_id', $suc->id)->whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NOMBRE', 'like', '%' . $conductor . '%');});})->whereIn('id', $ids)->pluck('id')->toArray();
                }  
            } else {
                if(empty($ids)){
                    $s5 = Transaccion::whereNull('sucursales_id')->whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NOMBRE', 'like', '%' . $conductor . '%');});})->pluck('id')->toArray();
                }else{
                    $s5 = Transaccion::whereNull('sucursales_id')->whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NOMBRE', 'like', '%' . $conductor . '%');});})->whereIn('id', $ids)->pluck('id')->toArray();
                } 
            }
            $ids = $s5;
            $c5 = $request->input('conductor');
            $filtro = $filtro . "Conductor=" . $request->input('conductor') . ", ";
        } 
        
        if ($request->filled('identificacion')) {
            $ide = $request->input('identificacion');
            if ($sucursal != 0) {
                if(empty($ids)){
                    $s6 = Transaccion::where('sucursales_id', $suc->id)->whereHas('cuentac', function($q) use($ide){$q->whereHas('conductor', function($r) use($ide){$r->where('NUMERO_IDENTIFICACION', $ide );});})->pluck('id')->toArray();
                }else{
                    $s6 = Transaccion::where('sucursales_id', $suc->id)->whereHas('cuentac', function($q) use($ide){$q->whereHas('conductor', function($r) use($ide){$r->where('NUMERO_IDENTIFICACION', $ide );});})->whereIn('id', $ids)->pluck('id')->toArray();
                }        
            } else {
                if(empty($ids)){
                    $s6 = Transaccion::whereNull('sucursales_id')->whereHas('cuentac', function($q) use($ide){$q->whereHas('conductor', function($r) use($ide){$r->where('NUMERO_IDENTIFICACION', $ide);});})->pluck('id')->toArray();
                }else{
                    $s6 = Transaccion::whereNull('sucursales_id')->whereHas('cuentac', function($q) use($ide){$q->whereHas('conductor', function($r) use($ide){$r->where('NUMERO_IDENTIFICACION', $ide);});})->whereIn('id', $ids)->pluck('id')->toArray();
                }  
            }
            $ids = $s6;
            $c6 = $request->input('identificacion');
            $filtro = $filtro . "Identificación=" . $request->input('identificacion') . ", ";
        } 

        $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->whereIn('id', $ids)->orderBy('id', 'DESC')->paginate(20)->appends($request->query());
        $idsfiltro = Transaccion::select('id')->whereIn('id', $ids)->pluck('id')->toArray();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cuentas.transacciones2', compact('suc', 'transacciones', 'idsfiltro', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'usuario', 'filtro'));
    }

    public function exportartrans(Request $request, $sucursal)
    {
        if($sucursal != 0){
            $suc = Sucursal::with('user')->find($sucursal);
        }else{
            $suc = new stdClass();
            $user = new stdClass();
            $user->nombres = "Sucursal Taxsur";
            $suc->id = 0;
            $suc->user = $user;
        }

        if ($request->filled('filtro')) {
            $filtro = explode(",", $request->input('filtro'));
            $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->whereIn('id', $filtro)->get();
        } else {
            if($sucursal != 0){
                $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->where('sucursales_id', $suc->id)->get();
            }else{
                $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION');}]);}])->whereNull('sucursales_id')->get();
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("C1:F1");
        $sheet->setCellValue("C1", "Transacciones de Sucursal " . $suc->user->nombres);
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("C1:F1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Tipo transacción");
        $sheet->setCellValue("D2", "Tipo recarga");
        $sheet->setCellValue("E2", "Valor");
        $sheet->setCellValue("F2", "Conductor");
        $sheet->setCellValue("G2", "Identificación");
        $sheet->setCellValue("H2", "Comentarios");
        $sheet->getStyle("A1:H2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($transacciones as $transaccion) {
            $sheet->setCellValue("A" . $indice, $transaccion->id);
            $sheet->setCellValue("B" . $indice, $transaccion->fecha);
            $sheet->setCellValue("C" . $indice, $transaccion->tipo);
            $sheet->setCellValue("D" . $indice, $transaccion->tiporecarga);
            $sheet->setCellValue("E" . $indice, $transaccion->valor);
            if ($transaccion->cuentac != null) {
                $sheet->setCellValue("F" . $indice, $transaccion->cuentac->conductor->NOMBRE);
                $sheet->setCellValue("G" . $indice, $transaccion->cuentac->conductor->NUMERO_IDENTIFICACION);
            } else {
                $sheet->setCellValue("F" . $indice, "");
                $sheet->setCellValue("G" . $indice, "");
            }
            $sheet->setCellValue("H" . $indice, $transaccion->comentarios);
            $indice++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Transacciones.xlsx');
        $archivo = file_get_contents('Transacciones.xlsx');
        unlink('Transacciones.xlsx');

        return base64_encode($archivo);
    }
    
    public function movimientos()
    {
        $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}]);}])->where('sucursales_id', Auth::user()->sucursal->id)->orderBy('id', 'DESC')->paginate(30);

        return view('sucursales.movimientos', compact('transacciones'));
    }

    public function filtrarMovimientos(Request $request)
    {
        $sucursal = Auth::user()->sucursal;
        if(empty($request->input('fecha')) && empty($request->input('tipo')) && empty($request->input('conductor'))){
            return redirect('/sucursal/movimientos');
        }
        
        $ids = [];
        $filtro = "";
        $c1="";$c2="";$c3="";

        if($request->filled('fecha')){
            $dates = explode(" - ", $request->input('fecha'));
            $s1 = Transaccion::whereBetween('fecha', $dates)->where('sucursales_id', $sucursal->id)->pluck('id')->toArray();
            $ids = $s1;
            $c1 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        }
        
        if($request->filled('tipo')){
            if(empty($ids)){
                $s2 = Transaccion::where('tipo', $request->input('tipo'))->where('sucursales_id', $sucursal->id)->pluck('id')->toArray();
            }else{
                $s2 = Transaccion::where('tipo', $request->input('tipo'))->where('sucursales_id', $sucursal->id)->whereIn('id', $ids)->pluck('id')->toArray(); 
            }
            $ids = $s2;    
            $c2 = $request->input('tipo');
            $filtro = $filtro . "Tipo=" . $request->input('tipo') . ", ";
        }

        if ($request->filled('conductor')) {
            $conductor = $request->input('conductor');
            if (empty($ids)) {
                $s3 = Transaccion::whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NUMERO_IDENTIFICACION', $conductor);});})->where('sucursales_id', $sucursal->id)->pluck('id')->toArray();
            } else {
                $s3 = Transaccion::whereHas('cuentac', function($q) use($conductor){$q->whereHas('conductor', function($r) use($conductor){$r->where('NUMERO_IDENTIFICACION', $conductor);});})->where('sucursales_id', $sucursal->id)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            
            $ids = $s3;    
            $c3 = $request->input('conductor');
            $filtro = $filtro . "Conductor=" . $request->input('conductor') . ", ";
        }

        $transacciones = Transaccion::with(['cuentac'=>function($q){$q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){$r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}]);}])->whereIn('id', $ids)->orderBy('id','DESC')->paginate(30)->appends($request->query());
        $exp = implode(",", Transaccion::whereIn('id', $ids)->pluck('id')->toArray());

        //$usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('sucursales.movimientos', compact('transacciones', 'c1', 'c2', 'c3', 'filtro', 'exp'));
    }

    public function historialCajas()
    {
        $sucursal = Auth::user()->sucursal;
        $cajas = Caja::where('sucursales_id', $sucursal->id)->orderBy('id', 'DESC')->paginate(20);

        return view('sucursales.cajas', compact('sucursal', 'cajas'));
    }

    public function filtrarCajas(Request $request)
    {
        $sucursal = Auth::user()->sucursal;
        if(empty($request->input('apertura')) && empty($request->input('cierre'))){
            return redirect('/sucursal/cajas');
        }

        $ids = [];
        $filtro = "";
        $c1="";$c2="";

        if($request->filled('apertura')){
            $s1 = Caja::whereDate('apertura', $request->input('apertura'))->where('sucursales_id', $sucursal->id)->pluck('id')->toArray();
            $ids = $s1;
            $c1 = $request->input('apertura');
            $filtro = $filtro . "Fecha apertura=" . $request->input('apertura') . ", ";
        }

        if($request->filled('cierre')){
            if(empty($ids)){
                $s1 = Caja::whereDate('cierre', $request->input('cierre'))->where('sucursales_id', $sucursal->id)->pluck('id')->toArray();
            }else{
                $s1 = Caja::whereDate('cierre', $request->input('cierre'))->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s1;
            $c2 = $request->input('cierre');
            $filtro = $filtro . "Fecha cierre=" . $request->input('cierre') . ", ";
        }

        $cajas = Caja::whereIn('id', $ids)->orderBy('id', 'DESC')->paginate(20);

        return view('sucursales.cajas', compact('cajas', 'c1', 'c2', 'filtro'));
    }

    public function nuevoEfectivo()
    {
        return view('sucursales.efectivo');
    }

    public function saveEfectivo(Request $request)
    {
        $conductor = Conductor::with(['cuentac' => function($q){$q->select('id', 'saldo', 'saldovales', 'password', 'conductor_CONDUCTOR', 'istransacciones');}])->select('CONDUCTOR', 'NUMERO_IDENTIFICACION')->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->first();
        $fecha = Carbon::now();

        $cobrado = Transaccion::where('cuentasc_id', $conductor->cuentac->id)->where('tipo', 'Pago')->where('fecha', '>', $fecha->format('Y-m-d') . ' 00:00')->sum('valor');
        if($cobrado + $request->input('valor') > 200000){
            return back()->withErrors(['sql' => "No se puede cobrar más de 200 mil por día. Hoy ya ha cobrado $" . $cobrado]);
        } 
        $logFile = fopen("../storage/logToIcon.txt", 'a') or die("Error creando archivo");
        if ($conductor != null) {
            if($conductor->cuentac->istransacciones == 1){
                return back()->withErrors(['sql' => 'Las transacciones para este conductor están temporalmente bloqueadas']);
            }
            if ($conductor->cuentac->password == $request->input('password')) {
                if (intval($conductor->cuentac->saldovales) >= intval($request->input('valor'))) {
                    $transaccion = new Transaccion();
                    $transaccion->tipo = "Pago";
                    $transaccion->fecha = $fecha;
                    $transaccion->comentarios = "Pago realizado en sucursal";
                    $transaccion->valor = $request->input('valor');
                    $transaccion->cuentasc_id = $conductor->cuentac->id;
                    $transaccion->sucursales_id = 2;
                    
                    $conductor->cuentac->saldovales = $conductor->cuentac->saldovales - $transaccion->valor;
                   
                    $ante = Transaccion::where('sucursales_id', 2)->where('cuentasc_id', $conductor->cuentac->id)->where('tipo', 'Pago')->orderBy('id', 'DESC')->first();
                    if($ante != null){
                        $ahora = Carbon::now();
                        if($ahora->diffInSeconds($ante->fecha) < 180){
                            return back()->withErrors(['sql' => "Error procesando la solicitud. Intente de nuevo"]);
                        }
                    }  
                    $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";			

                    try {
						$client = new SoapClient($url, ['exceptions' => true]);
						$result = $client->registrarTicket();
						$parametros = array("ticket"=>$result->registrarTicketReturn, "numeroIdentificacionConductor"=>$conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa"=>"900256369", "monto"=>$transaccion->valor, "tipo"=>"99");
						$peticion = $client->registrarConsumo($parametros);
						
						fwrite($logFile, "\n".date("d/m/Y H:i:s"). json_encode($parametros) . "-->" . json_encode($peticion->registrarConsumoReturn)) or die("Error escribiendo en el archivo");
						fclose($logFile);
						
						if($peticion->registrarConsumoReturn->codigoError != "0000"){
							return back()->withErrors(['sql' => "Error integración. " . $peticion->registrarConsumoReturn->mensajeError]);
						}
                        
                        $mensaje = new Mensaje();
                        $mensaje->texto = "Pago en efectivo realizado por valor de: $" . number_format($transaccion->valor);
                        $mensaje->fecha = Carbon::now();
                        $mensaje->sentido = "Recibido";
                        $mensaje->estado = "Pendiente";
                        $mensaje->cuentasc_id = $conductor->cuentac->id;
  
                        $transaccion->save();
                        //$conductor->cuentac->save();
                        //$sucursal->save();
                        //$mensaje->save();

                        return redirect('sucursales/efectivo/nuevo')->withErrors(['bien' => 'La transacción fue exitosa']);
                        
                    } catch (SoapFault $e) {
						fwrite($logFile, "\n".date("d/m/Y H:i:s"). $e->getMessage()) or die("Error escribiendo en el archivo");
						fclose($logFile);
						
                        return back()->withErrors(['sql' => $e->getMessage()]);
                    }
                } else {
                    return back()->withErrors(['sql' => 'Saldo insuficiente para realizar esta transacción. El saldo del conductor es: $' . number_format($conductor->cuentac->saldovales)]);
                }
            } else {
                return back()->withErrors(['sql' => 'La transacción falló por problemas de autenticación']);
            }
        } else {
            return back()->withErrors(['sql' => 'No se encontró ningun conductor con la identificación ' . $request->input('identificacion')]);
        }   
    }

    public function movimientosEfectivo(Request $request)
    {
        if($request->filled('fecha')){
            $fecha = Carbon::parse($request->input('fecha'));
        }else{
            $fecha = Carbon::now();
        }

        $transacciones = Transaccion::where('sucursales_id', 2)->whereBetween('fecha', [$fecha->format('Y-m-d') . ' 00:00', $fecha->format('Y-m-d') . ' 23:59'])->where('tipo', 'Pago')->paginate(15);

        return view('sucursales.movsEfectivo', compact('transacciones', 'fecha'));
    }
}
