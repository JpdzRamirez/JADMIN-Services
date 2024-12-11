<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flota;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Vehiculo_flotas;
use Illuminate\Support\Facades\Auth;

class FlotaController extends Controller
{
    public function index()
    {
        $flotas = Flota::with(['vehiculos'=> function($q){$q->select('VEHICULO');}])->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('flotas.lista', compact('flotas', 'usuario'));
    }

    public function store(Request $request)
    {
        $flota = new Flota();
        $flota->descripcion = $request->input('descripcion');
        $flota->save();

        return redirect('flotas');
    }

    public function vehiculos(Flota $flota)
    {
        $vehiculos = Vehiculo::select('VEHICULO', 'PLACA', 'MODELO', 'PROPIETARIO', 'MARCA')->with(['propietario'=>function($q){$q->select('TERCERO')->with(['tercero'=>function($r){$r->select('TERCERO', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO');}]);}, 'marca'])->whereHas('flotas', function ($s) use ($flota) {
            $s->where('flotas_id', $flota->id);})->get();
        $novehiculos = Vehiculo::select('VEHICULO', 'PLACA', 'MODELO')->whereHas('conductores', function($t){$t->where('SW_ACTIVO_NUEVO_CRM', '1');})->orderBy('PLACA', 'ASC')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('flotas.vehiculos', compact('vehiculos', 'novehiculos', 'flota', 'usuario'));
    }

    public function agregar(Request $request)
    {

        $mayusculas = strtoupper($request->input('placas'));
        $placas = explode(",", $mayusculas);
        $errores = "";

        foreach ($placas as $placa) {
            $vehiculo = Vehiculo::select('VEHICULO', 'PLACA')->where('PLACA', $placa)->first();
            if ($vehiculo != null) {
                $vehflo = Vehiculo_flotas::where('flotas_id', $request->input('flota'))->where('vehiculo_VEHICULO', $vehiculo->VEHICULO)->first();
                if ($vehflo == null) {
                    $vehflo = new Vehiculo_flotas();
                    $vehflo->flotas_id = $request->input('flota');
                    $vehflo->vehiculo_VEHICULO = $vehiculo->VEHICULO;
                    $vehflo->save();
                }else{
                    $errores = $placa . ", " . $errores;
                }
            }else{
                $errores =  $placa . ", " . $errores;
            }
        }

        return redirect('flotas/vehiculos/'. $request->input('flota'))->withErrors(["sql" => $errores]);
    }

    public function actualizarFlota(Flota $flota)
    {
        if($flota->id == "1"){
            $vehiculos = Vehiculo::select('VEHICULO', 'PLACA', 'SW_AIRE')->where('SW_AIRE', 1)->whereHas('conductores', function($q){$q->where('SW_ACTIVO_NUEVO_CRM', '1');})->get();            
            foreach ($vehiculos as $vehiculo) {
                $flotaveh = Vehiculo_flotas::where('flotas_id', $flota->id)->where('vehiculo_VEHICULO', $vehiculo->VEHICULO)->first();
                if($flotaveh == null){
                    $flotaveh = new Vehiculo_flotas();
                    $flotaveh->flotas_id = 1;
                    $flotaveh->vehiculo_VEHICULO = $vehiculo->VEHICULO;
                    $flotaveh->save();
                }               
            }
        } else  if($flota->id == "2"){
            $vehiculos = Vehiculo::select('VEHICULO', 'PLACA', 'SW_BAUL')->where('SW_BAUL', 1)->whereHas('conductores', function($q){$q->where('SW_ACTIVO_NUEVO_CRM', '1');})->get();
            foreach ($vehiculos as $vehiculo) {
                $flotaveh = Vehiculo_flotas::where('flotas_id', $flota->id)->where('vehiculo_VEHICULO', $vehiculo->VEHICULO)->first();
                if($flotaveh == null){
                    $flotaveh = new Vehiculo_flotas();
                    $flotaveh->flotas_id = 2;
                    $flotaveh->vehiculo_VEHICULO = $vehiculo->VEHICULO;
                    $flotaveh->save();
                }               
            }
        }

        return redirect('flotas/vehiculos/'. $flota->id);
    }

    public function removerVehiculo($flota, $vehiculo)
    {
        $flotasveh = Vehiculo_flotas::where('flotas_id', $flota)->where('vehiculo_VEHICULO', $vehiculo)->get();

        foreach ($flotasveh as $flotaveh) {
            $flotaveh->delete();
        }

        return redirect('flotas/vehiculos/'. $flota);
    }

    public function borrarFlota($flota)
    {
        
        $flota = Flota::with(['servicios' => function($q){$q->select('id', 'flotas_id');}])->find($flota);
        
        if (count($flota->servicios) > 0){
            return redirect('flotas')->withErrors(["sql" => "La flota seleccionada posee servicios registrados"]);
            
        }else{
            Vehiculo_flotas::where('flotas_id', $flota->id)->delete();

            $flota->delete();

            return redirect('flotas');
        }
        
    }
}
