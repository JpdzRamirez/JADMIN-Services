<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Propietario;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\Vale;
use App\Models\Valeav;
use App\Models\Valera;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;
use Exception;
use Illuminate\Support\Facades\DB;

class IconController extends Controller
{
    public function registrarpago(Request $request)
    {
        $response = new stdClass();
        $logFile = fopen("../storage/log.txt", 'a') or die("Error creando archivo");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($request->input())) or die("Error escribiendo en el archivo");

        if (md5("icon84ey3QjT") == $request->input('token')) {
            $conductor = Conductor::select('CONDUCTOR', 'NUMERO_IDENTIFICACION')->with(['cuentac' => function ($q) {
                $q->select('id', 'estado', 'saldovales', 'conductor_CONDUCTOR');
            }])->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->first();

            if ($conductor != null) {
                if (intval($conductor->cuentac->saldovales) >= intval($request->input('valor'))) {
                    $transaccion = new Transaccion();
                    $transaccion->tipo = "Pago";
                    $transaccion->valor = $request->input('valor');
                    $transaccion->fecha = Carbon::now('-05:00');
                    $transaccion->comentarios = "Pago realizado en ICON";
                    $transaccion->cuentasc_id = $conductor->cuentac->id;
                    $transaccion->save();

                    $conductor->cuentac->saldovales = $conductor->cuentac->saldovales - $transaccion->valor;
                    $conductor->cuentac->save();

                    $response->estado = "Correcto";
                    $response->mensaje = "Pago registrado";
                } else {
                    $transaccion = new Transaccion();
                    $transaccion->tipo = "Pago";
                    $transaccion->valor = $request->input('valor');
                    $transaccion->fecha = Carbon::now('-05:00');
                    $transaccion->comentarios = "Pago realizado en ICON";
                    $transaccion->cuentasc_id = $conductor->cuentac->id;
                    $transaccion->save();

                    $conductor->cuentac->saldovales = 0;
                    $conductor->cuentac->save();

                    $response->estado = "Correcto";
                    $response->mensaje = "Pago registrado";
                }
            } else {
                $response->estado = "Error";
                $response->mensaje = "No se encontró conductor";
            }
        } else {
            $response->estado = "Error";
            $response->mensaje = "Falla en la autenticación";
        }

        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($response)) or die("Error escribiendo en el archivo");
        fclose($logFile);

        return json_encode($response);
    }


    public function registrarcobro(Request $request)
    {
        $response = new stdClass();

        if (md5("icon84ey3QjT") == $request->input('token')) {
            $empresa = Tercero::select('TERCERO', 'NRO_IDENTIFICACION')->with('cuentae')->where('NRO_IDENTIFICACION', $request->input('identificacion'))->first();

            if ($empresa != null) {
                $empresa->cuentae->saldo = $empresa->cuentae->saldo - $request->input('valor');
                $empresa->cuentae->save();

                $response->estado = "Correcto";
                $response->mensaje = "Cobro registrado";
            } else {
                $response->estado = "Error";
                $response->mensaje = "No se encontró empresa";
            }
        } else {
            $response->estado = "Error";
            $response->mensaje = "Falla en la autenticación";
        }

        return json_encode($response);
    }

    public function anularRecarga(Request $request)
    {
        $response = new stdClass();
        $logFile = fopen("../storage/log.txt", 'a') or die("Error creando archivo");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($request->input())) or die("Error escribiendo en el archivo");

        if (md5("icon84ey3QjT") == $request->input('token')) {
            $conductor = Conductor::select('CONDUCTOR', 'NUMERO_IDENTIFICACION')->with(['cuentac' => function ($q) {
                $q->select('id', 'estado', 'saldo', 'conductor_CONDUCTOR');
            }])->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->first();

            if ($conductor != null) {
                $transaccion = new Transaccion();
                $transaccion->tipo = "Recarga";
                $transaccion->tiporecarga = "Egreso";
                $transaccion->valor = $request->input('valor');
                $transaccion->fecha = Carbon::now('-05:00');
                $transaccion->comentarios = "Recarga anulada en ICON";
                $transaccion->cuentasc_id = $conductor->cuentac->id;
                $transaccion->save();

                $conductor->cuentac->saldo = $conductor->cuentac->saldo - $request->input('valor');
                $conductor->cuentac->save();

                $response->estado = "Correcto";
                $response->mensaje = "Recarga anulada";
            } else {
                $response->estado = "Error";
                $response->mensaje = "No se encontró conductor";
            }
        } else {
            $response->estado = "Error";
            $response->mensaje = "Falla en la autenticación";
        }

        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($response)) or die("Error escribiendo en el archivo");
        fclose($logFile);

        return json_encode($response);
    }

    public function editarVale(Request $request)
    {
        if ($request->input('identificacion') == "89010057777") {
            $this->editarAvianca($request);
        } else {
            $response = new stdClass();
            $logFile = fopen("../storage/log.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($request->input())) or die("Error escribiendo en el archivo");

            try {
                $transaccion = new Transaccion();
                $transaccion->tipo = "Ajuste de vale";
                $transaccion->valor = $request->input('descuento');
                $transaccion->fecha = Carbon::now('-05:00');
                if (md5("icon84ey3QjT") == $request->input('token')) {
                    $nit = $request->input('identificacion');
                    $valera = Valera::with(['vales', 'cuentae.agencia'])->where('nombre', $request->input('valera'))->whereHas(
                        'cuentae',
                        function ($q) use ($nit) {
                            $q->whereHas(
                                'agencia',
                                function ($r) use ($nit) {
                                    $r->where('NRO_IDENTIFICACION', $nit);
                                }
                            );
                        }
                    )->first();

                    $encontrado = 0;
                    if ($valera != null) {
                        foreach ($valera->vales as $vale) {
                            if ($vale->codigo == $request->input('codigo')) {
                                $vale = Vale::with(['servicio' => function ($q) {
                                    $q->with(['cuentac' => function ($r) {
                                        $r->select('id', 'estado', 'saldovales');
                                    }]);
                                }])->find($vale->id);
                                if ($vale->servicio != null && $vale->estado == "Usado") {
                                    $vale->servicio->unidades = $request->input('unidades');
                                    $vale->servicio->cobro = "Unidades";
                                    $vale->servicio->valor = $request->input('valor');
                                    $des = abs($request->input('descuento'));
                                    if ($request->input('descuento') < 0) {
                                        $vale->servicio->valorc = $vale->servicio->valorc - $des;
                                    } else {
                                        $vale->servicio->valorc = $vale->servicio->valorc + $des;
                                    }
                                    $vale->servicio->save();

                                    $transaccion->cuentasc_id = $vale->servicio->cuentac->id;
                                    if ($request->input('descuento') < 0) {
                                        $vale->servicio->cuentac->saldovales =  $vale->servicio->cuentac->saldovales - $des;
                                        //$vale->servicio->cuentac->save();
                                    } else {
                                        $vale->servicio->cuentac->saldovales =  $vale->servicio->cuentac->saldovales + $des;
                                    }
                                    $vale->servicio->cuentac->save();

                                    $encontrado = 1;
                                } else {
                                    $response->estado = "Error";
                                    $response->mensaje = "El vale no ha sido usado";

                                    return json_encode($response);
                                }
                                break;
                            }
                        }

                        if ($encontrado == 1) {
                            $response->estado = "Correcto";
                            $response->mensaje = "Vale editado";
                            $transaccion->comentarios = "Empresa: " . $valera->cuentae->agencia->NOMBRE . ", Valera: " . $valera->nombre . ", Vale: " . $request->input('codigo') . " editado en ICON";
                            if ($request->input('descuento') < 0) {
                                $transaccion->save();
                            }
                        } else {
                            $response->estado = "Error";
                            $response->mensaje = "El vale no fue encontrado";

                            return json_encode($response);
                        }
                    } else {
                        $response->estado = "Error";
                        $response->mensaje = "No se encuentra la valera";
                    }
                } else {
                    $response->estado = "Error";
                    $response->mensaje = "Falla en la autenticación";
                }
            } catch (Exception $e) {
                $response->estado = "Error";
                $response->mensaje = $e->getMessage();
            }

            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($response)) or die("Error escribiendo en el archivo");
            fclose($logFile);

            return json_encode($response);
        }
    }

    public function editarAvianca(Request $request)
    {
        $response = new stdClass();
        $logFile = fopen("../storage/log.txt", 'a') or die("Error creando archivo");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($request->input())) or die("Error escribiendo en el archivo");

        try {
            $transaccion = new Transaccion();
            $transaccion->tipo = "Ajuste de vale";
            $transaccion->valor = $request->input('descuento');
            $transaccion->fecha = Carbon::now('-05:00');
            if (md5("icon84ey3QjT") == $request->input('token')) {
                $nit = $request->input('identificacion');
                $valera = Valera::with(['valesav', 'cuentae.agencia'])->where('nombre', $request->input('valera'))->whereHas(
                    'cuentae',
                    function ($q) use ($nit) {
                        $q->whereHas(
                            'agencia',
                            function ($r) use ($nit) {
                                $r->where('NRO_IDENTIFICACION', $nit);
                            }
                        );
                    }
                )->first();
                $encontrado = 0;
                if ($valera != null) {
                    foreach ($valera->valesav as $vale) {
                        if ($vale->codigo == $request->input('codigo')) {
                            $vale = Valeav::with(['servicio' => function ($q) {
                                $q->with(['cuentac' => function ($r) {
                                    $r->select('id', 'estado', 'saldovales');
                                }]);
                            }])->find($vale->id);
                            if ($vale->servicio != null && $vale->estado == "Usado") {
                                $vale->servicio->valor = $request->input('valor');
                                $des = abs($request->input('descuento'));
                                if ($request->input('descuento') < 0) {
                                    $vale->servicio->valorc = $vale->servicio->valorc - $des;
                                } else {
                                    $vale->servicio->valorc = $vale->servicio->valorc + $des;
                                }
                                $vale->servicio->save();

                                $transaccion->cuentasc_id = $vale->servicio->cuentac->id;
                                //$vale->servicio->cuentac->saldovales =  $vale->servicio->cuentac->saldovales - $request->input('descuento');
                                if ($request->input('descuento') < 0) {
                                    $vale->servicio->cuentac->saldovales =  $vale->servicio->cuentac->saldovales - $des;
                                    $vale->servicio->cuentac->save();
                                } else {
                                    $vale->servicio->cuentac->saldovales =  $vale->servicio->cuentac->saldovales + $des;
                                }
                                //$vale->servicio->cuentac->save();

                                $encontrado = 1;
                            } else {
                                $response->estado = "Error";
                                $response->mensaje = "El vale no ha sido usado";

                                return json_encode($response);
                            }
                            break;
                        }
                    }

                    if ($encontrado == 1) {
                        $response->estado = "Correcto";
                        $response->mensaje = "Vale editado";
                        $transaccion->comentarios = "Empresa: " . $valera->cuentae->agencia->NOMBRE . ", Valera: " . $valera->nombre . ", Vale: " . $request->input('codigo') . " editado en ICON";
                        if ($request->input('descuento') < 0) {
                            $transaccion->save();
                        }
                    } else {
                        $response->estado = "Error";
                        $response->mensaje = "El vale no fue encontrado";

                        return json_encode($response);
                    }
                } else {
                    $response->estado = "Error";
                    $response->mensaje = "No se encuentra la valera";
                }
            } else {
                $response->estado = "Error";
                $response->mensaje = "Falla en la autenticación";
            }
        } catch (Exception $e) {
            $response->estado = "Error";
            $response->mensaje = $e->getMessage();
        }

        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($response)) or die("Error escribiendo en el archivo");
        fclose($logFile);

        return json_encode($response);
    }

    public function token(Request $request)
    {
        $response = new stdClass();

        if ($request->input('user') == "icon") {

            if ($request->input('password') == "84ey3QjT") {
                $response->estado = "Correcto";
                $response->mensaje = md5("icon84ey3QjT");
            } else {
                $response->estado = "Error";
                $response->mensaje = "Credenciales incorrectas";
            }
        } else {
            $response->estado = "Error";
            $response->mensaje = "Credenciales incorrectas";
        }

        return json_encode($response);
    }

    public function cahorsConductor(Request $request)
    {
        if ($request->input('key') == '97215612') {
            $hoy = Carbon::now();
            $conductor = Conductor::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR');
            }])->where('NUMERO_IDENTIFICACION', $request->input('cedula'))->first();
            $transacciones = Transaccion::where('fecha', '>', $hoy->subMonths(3)->format('Y-m-d'))->where('cuentasc_id', $conductor->cuentac->id)->where('tipo', 'Servicio con vale')->sum('valor');

            $vinculacion = new stdClass();
            $vinculacion->fecha = $conductor->FECHA_INGRESO;
            $vinculacion->promedio = $transacciones / 3;

            return json_encode($vinculacion);
        } else {
            return abort(400);
        }
    }

    public function cahorsPropietario(Request $request)
    {
        if ($request->input('key') == '97215612') {
            $cedula = $request->input('cedula');
            $propietario = Propietario::with('vehiculospri', 'vehiculos')->whereHas('tercero', function ($q) use ($cedula) {
                $q->where('NRO_IDENTIFICACION', $cedula);
            })->first();
            $vinculacion = new stdClass();
            $vinculacion->placas = [];

            if (count($propietario->vehiculospri) > 0) {
                foreach ($propietario->vehiculospri as $vehiculo) {
                    $vinculacion->placas[] = (object)["placa" => $vehiculo->PLACA, "fecha" => $vehiculo->FECHA_VINCULACION];
                }
            }

            if (count($propietario->vehiculos) > 0) {
                foreach ($propietario->vehiculos as $vehiculo) {
                    $vinculacion->placas[] = (object)["placa" => $vehiculo->PLACA, "fecha" => $vehiculo->FECHA_VINCULACION];
                }
            }

            return json_encode($vinculacion);
        } else {
            return abort(400);
        }
    }

    public function placasPropietario(Request $request)
    {
        if ($request->input('key') == '97215612') {
            $placas = [];
            $tercero = Tercero::where('NRO_IDENTIFICACION', $request->input('identificacion'))->first();
            if($tercero != null){
                $placas = Vehiculo::select('VEHICULO', 'PLACA')->where('PROPIETARIO', $tercero->TERCERO)->get()->toArray();
                $otros = Vehiculo::select('VEHICULO', 'PLACA')->whereHas('propietarios', function ($q) use ($tercero) {
                    $q->where('PROPIETARIO', $tercero->TERCERO);
                })->get()->toArray();
                foreach ($otros as $otro) {
                    $placas[] = $otro;
                }
            }
            return json_encode($placas);
        } else {
            return abort(400);
        }
    }

    public function logsFinalizar(Request $request)
    {
        $valera = $request->input('valera');
        $codigo = $request->input('codigo');
        try {
            $logs = DB::connection('mysql2')->table('ws_servicio')->where("DATOS", "LIKE", "%|" . $valera . "|" . $codigo . "|%")->get();
            if (count($logs) > 0) {
                $resp = [];
                foreach ($logs as $log) {
                    $obj = new stdClass();
                    $obj->codigoError = utf8_encode($log->CODIGO_ERROR);
                    $obj->mensajeError = utf8_encode($log->MENSAJE_ERROR);
                    $obj->datos = utf8_encode($log->DATOS);
                    $obj->fechaGrabado = utf8_encode($log->FECHA_GRABADO);
                    $resp[] = $obj;
                }
                return response()->json($resp);
            } else {
                return 'SQl : ' . '%|' . $valera . '|' . $codigo . '|%';
            }
        } catch (Exception $ex) {
            return "Excepción: " . $ex->getMessage();
        }
    }

    public function borrarLogs(Request $request)
    {
        $valera = $request->input('valera');
        $codigo = $request->input('codigo');
        try {
            DB::connection('mysql2')->table('ws_servicio')->where("DATOS", "LIKE", "%|" . $valera . "|" . $codigo . "|%")->where('CODIGO_ERROR', '!=', '0000')->delete();
            //Ws_servicioIcon::where('DATOS', 'like', '%|' . $valera . '|' . $codigo . '|%')->where('CODIGO_ERROR', '!=', '0000')->delete();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return "OK";
    }
}
