<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Contrato_vale;
use App\Models\Cuentac;
use App\Models\Logicon;
use App\Models\Mensaje;
use App\Models\Registro;
use App\Models\Servicio;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Vale;
use App\Models\Vale_servicio;
use App\Models\Vehiculo;
use App\Models\Vehiculo_propietario;
use App\Models\WS_Servicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SoapClient;
use SoapFault;

class AlertaController extends Controller
{
    public function index()
    {

        $alertas = Alerta::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->orderBy('id', 'DESC')->paginate(30);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('alertas.lista', compact('alertas', 'usuario'));
    }

    public function pendientes()
    {

        $alertas = Alerta::select('id', 'placa', 'tipo')->where('estado', 'Pendiente')->whereNull('abierta')->get();
        $mensajes = Mensaje::select('id', 'estado', 'sentido', 'placa', 'texto', 'cuentasc_id')->with(['cuentac' => function ($q) {
            $q->select('id', 'placa', 'conductor_CONDUCTOR');
        }])->where('sentido', 'Enviado')->where('estado', 'Pendiente')->get();

        return json_encode(['alertas' => $alertas, 'mensajes' => $mensajes]);
    }

    public function gestionar(Alerta $alerta)
    {

        $alerta = Alerta::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->where('id', $alerta->id)->first();
        $alerta->abierta = 1;
        $alerta->save();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('alertas.form', ['alerta' => $alerta, 'usuario' => $usuario, 'method' => 'put', 'route' => ['alertas.atender', $alerta->id]]);
    }

    public function atender(Request $request, Alerta $alerta)
    {

        $alerta->estado = "Atendida";
        $alerta->descripcion = $request->input('descripcion');
        $alerta->solucion = $request->input('solucion');
        $alerta->save();

        return redirect('alertas');
    }

    public function filtrar(Request $request)
    {

        if ($request->input('estado') == "sinfiltro") {
            return redirect('alertas');
        } else {
            $alertas = Alerta::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }])->where('estado', $request->input('estado'))->orderBy('id', 'DESC')->paginate(30)->appends($request->query());
            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        }

        return view('alertas.lista', compact('alertas', 'usuario'));
    }

    public function imporServicios(Request $request)
    {

        $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

        if ($request->filled('avianca')) {
            $servicio = Servicio::with(['valeav.valera.cuentae.agencia', 'cuentac.conductor'])->find($request->input('id'));
        } else {
            $servicio = Servicio::with(['vale.valera.cuentae.agencia', 'cuentac.conductor', 'usuariosav'])->find($request->input('id'));
            if (count($servicio->usuariosav) > 0) {
                $centrocosto = $servicio->usuariosav[0]->centrocosto;
            } else {
                $centrocosto = $servicio->valeav->centrocosto;
            }
        }

        if ($servicio->cobro == "Unidades") {
            $soapunidades = $servicio->unidades;
            $soaphoras = "0";
            $soapminutos = "0";
            $soapruta = "";
        } elseif ($servicio->cobro == "Minutos") {
            $soaphoras = floor($servicio->unidades / 60);
            $soapminutos = $servicio->unidades % 60;
            $soapunidades = "0";
            $soapruta = "";
        } else {
            $soapruta = "R." . $request->input('secuencia');
            $soapunidades = "0";
            $soaphoras = "0";
            $soapminutos = "0";
        }

        try {
            $client = new SoapClient($url, ['exceptions' => true]);
            $result = $client->registrarTicket();
            $dia = date_parse_from_format('Y-m-d H:i:s', $servicio->fecha);
            if ($dia["month"] < 10) {
                $dia["month"] = "0" . $dia["month"];
            }
            if ($dia["day"] < 10) {
                $dia["day"] = "0" . $dia["day"];
            }

            if ($request->filled('avianca')) {
                $soapunidades = "0";
                $soaphoras = "0";
                $soapminutos = "0";
                $soapruta = "R." . $servicio->valeav->secuencia;
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $servicio->valeav->valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $servicio->valeav->valera->nombre, "codigoVale" => $servicio->valeav->codigo, "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],  "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"], "usuarioVale" => $servicio->usuarios, "placa" => $servicio->placa, "numeroIdentificacionConductor" => $servicio->cuentac->conductor->NUMERO_IDENTIFICACION, "unidades" => $soapunidades, "horaMovimiento" => $soaphoras, "minutoMovimiento" => $soapminutos, "horaEspera" => "0", "minutoEspera" => "0", "rutas" => $soapruta, "valor" => $servicio->valor, "centroCosto" => $centrocosto, "referenciado" => "");
            } else {
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $servicio->vale->valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $servicio->vale->valera->nombre, "codigoVale" => $servicio->vale->codigo, "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],  "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"], "usuarioVale" => $servicio->usuarios, "placa" => $servicio->placa, "numeroIdentificacionConductor" => $servicio->cuentac->conductor->NUMERO_IDENTIFICACION, "unidades" => $soapunidades, "horaMovimiento" => $soaphoras, "minutoMovimiento" => $soapminutos, "horaEspera" => "0", "minutoEspera" => "0", "rutas" => $soapruta, "valor" => $servicio->valor, "centroCosto" => $servicio->vale->centrocosto, "referenciado" => $servicio->vale->referenciado);
            }
            $peticion = $client->registrarVale($parametros);
            /*if ($peticion->registrarValeReturn->codigoError == "0000") {

                return "hecho";
            } else {
                return "codigo error";                   
            }*/

            return json_encode($peticion->registrarValeReturn);
        } catch (SoapFault $e) {
            return "Error soap";
        } catch (Exception $e) {
            return "excepcion";
        }
    }

    public function ventas(Request $request)
    {
        $ids = [$request->input('id'), $request->input('id2'), $request->input('id3')];
        $resps = "";
        foreach ($ids as $key) {
            $transaccion = Transaccion::with('sucursal.tercero', 'cuentac.conductor')->find($key);

            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

            try {
                $client = new SoapClient($url, ['exceptions' => true]);
                $result = $client->registrarTicket();
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionConductor" => $transaccion->cuentac->conductor->NUMERO_IDENTIFICACION, "numeroIdentificacionEmpresa" => $transaccion->sucursal->tercero->NRO_IDENTIFICACION, "monto" => $transaccion->valor, "tipo" => "1");
                $peticion = $client->registrarConsumo($parametros);

                /*if($peticion->registrarConsumoReturn->codigoError != "0000"){
                    return back()->withErrors(['sql' => "Error integración. " . $peticion->registrarConsumoReturn->mensajeError ]);
                }*/

                $resps = $resps . PHP_EOL . json_encode($peticion->registrarConsumoReturn);
            } catch (SoapFault $e) {
                $resps = $resps . PHP_EOL .  "error soap";
            } catch (Exception $e) {
                $resps = $resps . PHP_EOL .  "escepcion";
            }

            return $resps;
        }
    }

    public function legalizarServicios(Request $request)
    {
        $objPHPExcel = IOFactory::load(public_path() . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . "majorel.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = $request->input('fila');
        $cedula = $sheet->getCell('Q' . $fila)->getCalculatedValue();
        $cuentac = Cuentac::select('id', 'conductor_CONDUCTOR', 'saldo', 'saldovales')->whereHas('conductor', function ($q) use ($cedula) {
            $q->where('NUMERO_IDENTIFICACION', $cedula);
        })->first();

        if ($cuentac != null) {
            try {
                $date = Date::excelToDateTimeObject($sheet->getCell('J' . $fila)->getValue());
                $time = Date::excelToDateTimeObject($sheet->getCell('H' . $fila)->getValue());
                $fec =  $date->format("Y-m-d") . "  "  . $time->format("H:i:s");
                $fecha = Carbon::parse($fec);
                $servicio = new Servicio();
                $servicio->fecha = $fecha;
                $servicio->estado = "En curso";
                $servicio->direccion = $sheet->getCell('D' . $fila)->getCalculatedValue();
                $servicio->pago = "Vale electrónico";
                $servicio->latitud = "7.1192047";
                $servicio->latitud = "-73.1679976";
                $servicio->usuarios = $sheet->getCell('B' . $fila)->getCalculatedValue();
                $servicio->placa = $sheet->getCell('L' . $fila)->getCalculatedValue();
                $servicio->asignacion = "Directo";
                $servicio->cuentasc_id = $cuentac->id;
                $servicio->clientes_id = 90;
                $servicio->users_id = 1;

                $vale = Vale::where('valeras_id', 17)->where('codigo', $sheet->getCell('M' . $fila)->getCalculatedValue())->first();
                if ($vale->estado == "Usado") {
                    return "Vale ya utilizado";
                }
                $servicio->save();

                $vale->estado = "Visado";
                $vale->servicios_id = $servicio->id;
                $vale->save();

                $valeserv = new Vale_servicio();
                $valeserv->vales_id = $vale->id;
                $valeserv->servicios_id = $servicio->id;
                $valeserv->save();

                $registro = new Registro();
                $registro->fecha = $fecha->addSeconds(30);
                $registro->evento = "Acepta";
                $registro->servicios_id = $servicio->id;
                $registro->save();

                $cuentac->saldo = $cuentac->saldo - 600;
                $cuentac->save();

                $transaccion = new Transaccion();
                $transaccion->tipo = "Consumo";
                $transaccion->valor = 600;
                $transaccion->fecha = $fecha;
                $transaccion->cuentasc_id = $cuentac->id;
                $transaccion->save();

                $registro = new Registro();
                $registro->fecha = $fecha->addSeconds(30);
                $registro->evento = "Arribo";
                $registro->servicios_id = $servicio->id;
                $registro->save();

                $registro = new Registro();
                $registro->fecha = $fecha;
                $registro->evento = "Inicio";
                $registro->servicios_id = $servicio->id;
                $registro->save();

                $registro = new Registro();
                $registro->fecha = $fecha->addMinutes(5);
                $registro->evento = "Fin";
                $registro->servicios_id = $servicio->id;

                $servicio = Servicio::with('vale.valera.cuentae.agencia')->find($servicio->id);

                $servicio->unidades = $sheet->getCell('P' . $fila)->getCalculatedValue();
                $servicio->cobro = "Unidades";
                $contrato = Contrato_vale::with('tarifa.unidadvalores')->where('TERCERO', $servicio->vale->valera->cuentae->agencia_tercero_TERCERO)->orderBy('CONTRATO_VALE', 'DESC')->first();
                foreach ($contrato->tarifa->unidadvalores as $valor) {
                    if ($servicio->unidades >= $valor->UNIDAD_INICIO && $servicio->unidades <= $valor->UNIDAD_FIN) {
                        $servicio->valor = $valor->VALOR;
                        break;
                    }
                }

                $soapunidades = $servicio->unidades;
                $soaphoras = "0";
                $soapminutos = "0";
                $soapruta = "";
                $servicio->estado = "Finalizado";

                $vale = $servicio->vale;
                $vale->estado = "Usado";

                $cuota = round(($servicio->valor - ($servicio->valor * 0.07)), 0, PHP_ROUND_HALF_DOWN);
                $uno = $cuota % 100;
                $cuota = $cuota - $uno;
                $cuentac->saldovales = $cuentac->saldovales + $cuota;
                $servicio->valorc = $cuota;

                $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";


                $client = new SoapClient($url, ['exceptions' => true]);
                $result = $client->registrarTicket();
                $dia = date_parse_from_format('Y-m-d H:i:s', $servicio->fecha);
                if ($dia["month"] < 10) {
                    $dia["month"] = "0" . $dia["month"];
                }
                if ($dia["day"] < 10) {
                    $dia["day"] = "0" . $dia["day"];
                }

                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $vale->valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $vale->valera->nombre, "codigoVale" => $vale->codigo, "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],  "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"], "usuarioVale" => $servicio->usuarios, "placa" => $servicio->placa, "numeroIdentificacionConductor" => $cuentac->conductor->NUMERO_IDENTIFICACION, "unidades" => $soapunidades, "horaMovimiento" => $soaphoras, "minutoMovimiento" => $soapminutos, "horaEspera" => "0", "minutoEspera" => "0", "rutas" => $soapruta, "valor" => $servicio->valor, "centroCosto" => $vale->centrocosto, "referenciado" => $vale->referenciado);
                $peticion = $client->registrarVale($parametros);
                if ($peticion->registrarValeReturn->codigoError == "0000") {
                    $transaccion = new Transaccion();
                    $transaccion->tipo = "Servicio con vale";
                    $transaccion->valor = $cuota;
                    $transaccion->fecha = $fecha;
                    $transaccion->cuentasc_id = $cuentac->id;
                    $transaccion->save();

                    $registro->save();
                    $servicio->save();
                    $vale->save();
                    $cuentac->save();

                    return "Valor del servicio: $" . $servicio->valor;
                } else {
                    return json_encode($peticion->registrarValeReturn);
                }
            } catch (SoapFault $e) {
                return "Falla soap";
            } catch (Exception $e) {
                return "Falla excepcion " . $e->getMessage() . " Linea: " . $e->getLine();
            }
        } else {
            return "no conductor";
        }
    }

    public function cumples()
    {
        set_time_limit(0);
        $objPHPExcel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . "nacimientos.xlsx");
        $objPHPExcel->setActiveSheetIndex(2);
        $sheet = $objPHPExcel->getActiveSheet();
        $numRows = $sheet->getHighestRow();
        $hoy = Carbon::now();
        if ($numRows >= 1) {
            for ($i = 2; $i <= $numRows; $i++) {
                $cedula = $sheet->getCell('B' . $i)->getCalculatedValue();
                $tercero = Tercero::with('propietario')->where('NRO_IDENTIFICACION', $cedula)->first();
                if ($tercero != null) {
                    $fechan = Carbon::parse($tercero->propietario->FECHA_NACIMIENTO);

                    if ($hoy->day == $fechan->day && $hoy->month == $fechan->month) {
                        $texto = "!Feliz Cumpleaños¡ " . $tercero->PRIMER_NOMBRE . ", en este día Taxsur desea enviarte un gran saludo de felicitaciones.";
                    } elseif (($hoy->month == $fechan->month && $hoy->day > $fechan->day) || $hoy->month > $fechan->month) {
                        $texto = "Dicen que nunca es tarde para felicitar a alguien, por eso Taxsur quiere enviarte un gran saludo de felicitaciones a ti " . $tercero->PRIMER_NOMBRE  . ", por tu más reciente cumpleaños.";
                    } else {
                        $texto = "no";
                    }
                    if ($texto != "no") {
                        //$this->enviarSMS($texto, $tercero->CELULAR);
                    }
                }
            }
        }

        return "enviados";
    }


    public function enviarSMS($texto, $numero)
    {
        $texto = "Debido al vencimiento de documentos de su vehículo y que hasta la fecha estos no han sido reclamados; informamos que mañana 23 de septiembre estaremos atendiendo de las 6:00 AM en nuestras oficinas";
        $connection = fopen(
            'https://portal.bulkgate.com/api/1.0/simple/transactional',
            'r',
            false,
            stream_context_create(['http' => [
                'method' => 'POST',
                'header' => [
                    'Content-type: application/json'
                ],
                'content' => json_encode([
                    'application_id' => '22484',
                    'application_token' => '8f1lnuLJwXRwXFzGnwbqbGw8p1WtQZ39U2lqwYLJG46pNLo6Ct',
                    'unicode' => '1',
                    'number' => '57' . $numero,
                    'text' => $texto
                ]),
                'ignore_errors' => true
            ]])
        );

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "SMSTarjetas.txt", 'a') or die("Error creando archivo");

        if ($connection) {
            //$response = json_decode(stream_get_contents($connection));        
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . stream_get_contents($connection) . " Número: " . $numero) or die("Error escribiendo en el archivo");
            fclose($connection);
        } else {
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . "Falla conexión: " . $numero) or die("Error escribiendo en el archivo");
        }
    }

    public function getNumero(Request $request)
    {
        if ($request->filled('placa')) {
            $vehiculo = Vehiculo::with('propietario.tercero')->where('PLACA', $request->input('placa'))->first();
            if ($vehiculo != null) {
                if (!empty($vehiculo->propietario->tercero->CELULAR)) {
                    return $vehiculo->propietario->tercero->CELULAR;
                } else {
                    return "no";
                }
            } else {
                return "no";
            }
        } else {
            return "no";
        }
    }

    public function nuevoTramite(Request $request)
    {
        $cuentac = Cuentac::with('conductor')->find($request->input('idtaxista'));
        $solicitud = $request->input('solicitud');
        $placa = strtoupper($request->input('placa'));
        $monto = $request->input('monto');
        $dirigido = $request->input('dirigido');
        $email = $request->input('email');
        $celular = $request->input('celular');
        $vehiculo = Vehiculo::where('PLACA', $placa)->first();
        if ($vehiculo != null) {
            if ($monto > 5000000) {
                return "1-No es posible constatar el monto ingresado";
            } elseif ($monto > 4500000 && $vehiculo->MODELO < 2019) {
                return "1-Vehículos con modelo menor a 2019 no es posible constatar el monto ingresado";
            } elseif ($monto > 4000000 && $vehiculo->MODELO < 2014) {
                return "1-Vehículos con modelo menor a 2014 no es posible constatar el monto ingresado";
            } elseif ($monto > 3000000 && $vehiculo->MODELO < 2008) {
                return "1-Vehículos con modelo menor a 2008 no es posible constatar el monto ingresado";
            }
            try {
                Mail::send('alertas.tramite', compact('cuentac', 'solicitud', 'placa', 'monto', 'dirigido', 'email', 'celular'), function ($message) {
                    $message->from("notificaciones@apptaxcenter.com", "Taxiseguro");
                    $message->to(["vinculaciones@taxsur.com"]);
                    $message->subject("Solicitud de documento desde la aplicación");
                });
            } catch (Exception $ex) {
                return "1-" . $ex->getMessage();
            }
        } else {
            return "1-Placa no corresponde a un vehículo registrado en la empresa";
        }
        return "0-OK";
    }

    public function nuevoTramiteUsuario(Request $request)
    {
        $tercero = Tercero::has('propietario')->where('NRO_IDENTIFICACION', $request->input('cedula'))->first();
        if ($tercero != null) {
            $placa = strtoupper($request->input('placa'));
            $vehiculo = Vehiculo::where('PLACA', $placa)->first();
            if ($vehiculo != null) {
                $otro = Vehiculo_propietario::where('VEHICULO', $vehiculo->VEHICULO)->where('TERCERO', $tercero->TERCERO)->first();
                if ($otro != null || $vehiculo->PROPIETARIO == $tercero->TERCERO) {
                    $solicitud = $request->input('solicitud');
                    $monto = $request->input('monto');
                    $dirigido = $request->input('dirigido');
                    $email = $request->input('email');
                    $celular = $request->input('celular');
                    if ($monto > 5000000) {
                        return "1-No es posible constatar el monto ingresado";
                    } elseif ($monto > 4500000 && $vehiculo->MODELO < 2019) {
                        return "1-Vehículos con modelo menor a 2019 no es posible constatar el monto ingresado";
                    } elseif ($monto > 4000000 && $vehiculo->MODELO < 2014) {
                        return "1-Vehículos con modelo menor a 2014 no es posible constatar el monto ingresado";
                    } elseif ($monto > 3000000 && $vehiculo->MODELO < 2008) {
                        return "1-Vehículos con modelo menor a 2008 no es posible constatar el monto ingresado";
                    }
                    try {
                        Mail::send('alertas.tramite', compact('tercero', 'solicitud', 'placa', 'monto', 'dirigido', 'email', 'celular'), function ($message) {
                            $message->from("notificaciones@apptaxcenter.com", "Taxiseguro");
                            $message->to(["vinculaciones@taxsur.com"]);
                            $message->subject("Solicitud de documento desde la aplicación");
                        });
                    } catch (Exception $ex) {
                        return "1-" . $ex->getMessage();
                    }

                    return "0-OK";
                } else {
                    return "1-Cédula no asociada como propietario de la placa ingresada";
                }
            } else {
                return "1-Placa no corresponde a un vehículo registrado en la empresa";
            }
        } else {
            return "1-Cédula no registrada en la empresa";
        }
    }

    public function logsIcon()
    {

        $logs = Logicon::paginate(15);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('alertas.logicon', compact('logs', 'usuario'));
    }

    public function FinalizadosSinLegalizarICON()
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $cantidad = 0;
        $catch = 0;
        $servicios = Servicio::with([ 
        'cuentac' => function ($q) { $q->select('id', 'conductor_CONDUCTOR')->with(
            ['conductor' => function($r){$r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');}
        ]);},
        'vale' =>function($r){$r->select('id', 'codigo', 'centrocosto', 'referenciado', 'valeras_id', 'servicios_id')->with(
            ['valera' => function($s){$s->select('id', 'nombre', 'cuentase_id')->with(
                ['cuentae' => function($t){$t->select('id', 'agencia_tercero_TERCERO', 'agencia_tercero_CODIGO')->with(
                    ['agencia' => function($u){$u->select('TERCERO', 'CODIGO', 'NRO_IDENTIFICACION');}]
                );}]
            );}]
        );}]
        )->select('id', 'cuentasc_id', 'fecha', 'usuarios', 'placa', 'valor', 'unidades')
        ->where('estado', 'Finalizado')->where('pago', 'Vale electrónico')->where('fecha', '>', '2024-08-10')->where('fecha', '<', '2024-08-21')->where('cobro', 'Ruta')->get();
        $aeropuerto = 0;
        foreach ($servicios as $servicio) {
            $cantidad++;
            $finalizadoICON = -1;
            try {
                $finalizadoICON = DB::table('ws_servicio')
                    ->where("DATOS", "LIKE", $servicio->vale->valera->cuentae->agencia->NRO_IDENTIFICACION . "|%")
                    ->where("DATOS", "LIKE", "%|" . $servicio->vale->codigo . "|%")
                    ->where('CODIGO_ERROR', '=', '0000')->count();
            } catch (\Throwable $th) {
                $finalizadoICON = -1;
                $catch++;
            }

            if ($finalizadoICON == 0) {
                $dia = date_parse_from_format('Y-m-d H:i:s', $servicio->fecha);
                if ($dia["month"] < 10) {
                    $dia["month"] = "0" . $dia["month"];
                }
                if ($dia["day"] < 10) {
                    $dia["day"] = "0" . $dia["day"];
                }

                $soapunidades = "0";
                //$soapunidades = $servicio->unidades;
                $soaphoras = "0";
                $soapminutos = "0";
                $soapruta = "R.";

                $parametros = array(
                    "ticket" => "",
                    "numeroIdentificacionEmpresa" => $servicio->vale->valera->cuentae->agencia->NRO_IDENTIFICACION,
                    "nombreValera" => $servicio->vale->valera->nombre,
                    "codigoVale" => $servicio->vale->codigo,
                    "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],
                    "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"],
                    "usuarioVale" => $servicio->usuarios,
                    "placa" => $servicio->placa,
                    "numeroIdentificacionConductor" => $servicio->cuentac->conductor->NUMERO_IDENTIFICACION,
                    "unidades" => $soapunidades,
                    "horaMovimiento" => $soaphoras,
                    "minutoMovimiento" => $soapminutos,
                    "horaEspera" => "0",
                    "minutoEspera" => "0",
                    "rutas" => $soapruta,
                    "valor" => $servicio->valor,
                    "centroCosto" => $servicio->vale->centrocosto,
                    "referenciado" => $servicio->vale->referenciado,
                    "aeropuerto" => $aeropuerto
                );

                DB::insert('insert into parametros_icon (contenido, servicios_id) values (?, ?)', [json_encode($parametros), $servicio->id]);
            }
        }

        return "Listo: " . $cantidad . "---" . $catch;
    }
}
