<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Servicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class IvrController extends Controller
{

    public function consultarDireccion($numero)
    {
        $cliente = Cliente::where('telefono', $numero)->first();

        if ($cliente != null) {
            if ($cliente->direccion != null) {
                return $cliente->direccion;
            }
        }
        return "vacio";
    }

    public function servicio($numero)
    {
        try {
            $cliente = Cliente::where('telefono', $numero)->first();
            if ($cliente != null) {
                $servicio = new Servicio();
                $servicio->pago = "Efectivo";
                $servicio->direccion = $cliente->direccion;
                $servicio->latitud = $cliente->latitud;
                $servicio->longitud = $cliente->longitud;
                $servicio->fecha = Carbon::now('-05:00');
                $servicio->estado = "Pendiente";
                $servicio->asignacion = "Normal";
                $servicio->usuarios = $cliente->nombres;
                $servicio->clientes_id = $cliente->id;
                $servicio->users_id = 112;
                $servicio->save();

                return $servicio->id;
            }
        } catch (Exception $e) {
        }
    }

    public function consultarServicio($servi)
    {
        $servicio = Servicio::select('id', 'estado', 'cuentasc_id')->with(['cuentac' => function ($q) {
            $q->select('id', 'placa', 'estado');
        }])->find($servi);
        if ($servicio != null) {
            if ($servicio->estado == "Asignado" && $servicio->cuentac != null) {
                return $servicio->cuentac->placa;
            } else {
                return "0";
            }
        } else {
            return "error";
        }
    }

    public function noVehiculo($servi)
    {
        $servicio = Servicio::select('id', 'estado')->find($servi);
        if ($servicio != null) {
            $servicio->estado = "No vehiculo";
            $servicio->save();
        }
    }

    public function showFile($filename)
    {
        $file = public_path() . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . $filename . ".pdf";
        $file = File::get($file);
        $response = Response::make($file, 200);
        $response->header('Content-Type', 'application/pdf');

        return $response;
    }
}
