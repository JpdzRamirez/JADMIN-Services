<?php

namespace App\Http\Controllers;

use App\Models\Cancelacion;
use App\Models\Destino;
use App\Models\Punto;
use App\Models\Servicio;
use App\Models\CuentaC;
use App\Models\Conductor;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use stdClass;

class SisorgController extends Controller
{

    public function puntos(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $puntos = Punto::select('id as idPunto', 'nombre', 'direccion', 'municipio')->get();

            $respuesta->estado = "OK";
            $respuesta->mensaje = $puntos;
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }

    public function destinos(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $destinos = Destino::select('id as idDestino', 'barrios', 'precio')->where('puntos_id', $request->input('idPunto'))->get();

            if (count($destinos) == 0) {
                throw new Exception("El ID de punto enviado no posee ningún destino registrado");
            }

            $respuesta->estado = "OK";
            $respuesta->mensaje = $destinos;
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }

    public function recibirServicio(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $servicio = new Servicio();
            $servicio->fecha = Carbon::now();
            $servicio->fechaprogramada = $request->input('fechaProgramada');
            $servicio->pago = "Vale electrónico";
            $destino = Destino::with('punto')->find($request->input('idDestino'));
            if ($request->input('sentido') == 0) {
                $servicio->direccion = $destino->punto->direccion;
                $servicio->latitud = $destino->punto->latitud;
                $servicio->longitud = $destino->punto->longitud;
            } else {
                $servicio->direccion = $request->input('direccion') . ", " . $destino->punto->municipio;
                $ubicacion = $this->obtenerUbicacion($servicio->direccion);
                $servicio->latitud = $ubicacion->lat;
                $servicio->longitud = $ubicacion->lng;
            }
            $servicio->usuarios = $request->input('usuarios');
            $servicio->save();

            $respuesta->estado = "OK";
            $respuesta->mensaje = $servicio->id;
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }

    public function obtenerUbicacion($direccion)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($direccion) . "&language=ES&key=apikey");
        $json = json_decode(curl_exec($ch));
        if (count($json->results) > 0) {
            return $json->results[0]->geometry->location;
        } else {
            throw new Exception("No se encontró la ubicación de la dirección ingresada");
        }
    }

    public function consultarServicio(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $servicio = Servicio::select('id', 'fecha', 'estado', 'direccion', 'adicional', 'pago', 'usuarios', 'fechaprogramada', 'placa', 'valor', 'cuentasc_id')->find($request->input('idServicio'));

            $cuentasc_id = $servicio->cuentasc_id;
            $valorConductorConductor = Cuentac::where('id', $cuentasc_id)->value('conductor_CONDUCTOR');
            $nombreConductor = Conductor::where('CONDUCTOR', $valorConductorConductor)->value('NOMBRE');

            if ($servicio == null) {
                throw new Exception("No se encontró el ID del servicio ingresado");
            }

            $respuesta->estado = "OK";
            $respuesta->mensaje = [
                "id" => $servicio->id,
                "fecha" => $servicio->fecha,
                "estado" => $servicio->estado,
                "direccion" => $servicio->direccion,
                "adicional" => $servicio->adicional,
                "pago" => $servicio->pago,
                "usuarios" => $servicio->usuarios,
                "fechaprogramada" => $servicio->fechaprogramada,
                "placa" => $servicio->placa,
                "valor" => $servicio->valor,
                "nombreConductor" => $nombreConductor,
            ];
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }

    /*function notificarFecha(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $servicio = Servicio::find($request->input('idServicio'));
            if ($servicio != null) {
                $servicio->fechaprogramada = $request->input('fechaProgramada');
                $servicio->save();
            } else {
                throw new Exception("No se encontró el ID del servicio ingresado");
            }
            $respuesta->estado = "OK";
            $respuesta->mensaje = $servicio;
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }*/

    public function cancelarServicio(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $servicio = Servicio::select('id', 'estado', 'cuentasc_id')->with(['vale', 'cuentac' => function ($q) {
                $q->select('id', 'estado');
            }])->find($request->input('idServicio'));
            if ($servicio->estado == "Asignado" || $servicio->estado == "En curso") {
                $servicio->estado = "Cancelado";
                $servicio->save();

                if ($servicio->vale != null) {
                    if ($servicio->vale->centrocosto != null) {
                        $servicio->vale->estado = "Asignado";
                    } else {
                        $servicio->vale->estado = "Libre";
                    }
                    $servicio->vale->servicios_id = null;
                    $servicio->vale->save();
                }
                $cancelacion = Cancelacion::where('servicios_id', $servicio->id)->first();
                if ($cancelacion == null) {
                    $cancelacion = new Cancelacion();
                }

                $cancelacion->razon = $request->input('motivo');
                $cancelacion->fecha = Carbon::now();
                $cancelacion->servicios_id = $servicio->id;
                $cancelacion->save();

                if ($servicio->cuentac != null) {
                    if ($servicio->cuentac->estado != "Bloqueado") {
                        $servicio->cuentac->estado = "Libre";
                        $servicio->cuentac->save();
                    }
                }

                $respuesta->estado = "OK";
                $respuesta->mensaje = "Servicio cancelado";
            } else {
                throw new Exception("El servicio se encuentra en un estado que no es posible cancelarlo");
            }
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }

    public function editarServicio(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $servicio = Servicio::find($request->input('idServicio'));
            if ($servicio != null) {
                if($request->filled('fechaProgramada')){
                    $servicio->fechaprogramada = $request->input('fechaProgramada');
                }
                
                if($request->filled('idDestino')){
                    $destino = Destino::with('punto')->find($request->input('idDestino'));
                    if ($request->input('sentido') == 0) {
                        $servicio->direccion = $destino->punto->direccion;
                        $servicio->latitud = $destino->punto->latitud;
                        $servicio->longitud = $destino->punto->longitud;
                    } else {
                        $servicio->direccion = $request->input('direccion') . ", " . $destino->punto->municipio;
                        $ubicacion = $this->obtenerUbicacion($servicio->direccion);
                        $servicio->latitud = $ubicacion->lat;
                        $servicio->longitud = $ubicacion->lng;
                    }
                }
                
                if($request->filled('usuarios')){
                    $servicio->usuarios = $request->input('usuarios');
                }
                
                $servicio->save();
            } else {
                throw new Exception("No se encontró el ID del servicio ingresado");
            }
            $respuesta->estado = "OK";
            $respuesta->mensaje = $servicio;
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = $ex->getMessage();

            return response(json_encode($respuesta), 500, ["Content-Type" => "application/json"]);
        }

        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    }
}
