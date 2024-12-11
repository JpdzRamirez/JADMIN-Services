<?php

namespace App\Http\Controllers;

use App\Models\Novedad;
use App\Models\Sancion;
use App\Models\Servicio;
use App\Models\Tiposnovedad;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NovedadController extends Controller
{
    public function index(Servicio $servicio){
        
        $novedades = Novedad::with('tiponovedad')->where('servicios_id', $servicio->id)->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('novedades.lista', compact('novedades', 'usuario', 'servicio'));
        
    }

    public function nuevo(Servicio $servicio){

        $novedad = new Novedad();
        $tipos = Tiposnovedad::get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('novedades.form', ['novedad' => $novedad, 'usuario' => $usuario, 'servicio' => $servicio, 'tipos' => $tipos, 'method' => 'post', 'route' => ['novedades.guardar'] ]);   
    }

    public function guardar(Request $request){

        $novedad = new Novedad();
        $novedad->estado = $request->input('estado');
        $novedad->detalle = $request->input('detalle');
        $novedad->solucion = $request->input('solucion');
        $novedad->servicios_id = $request->input('servicios_id');
        $novedad->tiposnovedad_id = $request->input('tipo');
        $novedad->save();

        $from = "EMAILNOTIFY";
        $to = "EMAILDEST1,EMAILDEST2";
        $subject = "Novedad registrada";
        $message = "Se ha registrado una nueva novedad en el servicio con ID:"  . $novedad->servicios_id . "\n\n" .
                    "Tipo novedad: " . $novedad->tiponovedad->nombre . "\n" .
                    "Detalle: " . $novedad->detalle;
        $headers = "From:" . $from;
        mail($to,$subject,$message, $headers);

        return redirect('novedades/'. $request->input('servicios_id'));
    }
    
    public function editar(Novedad $novedad)
    {
        $tipos = Tiposnovedad::get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('novedades.form', ['novedad' => $novedad, 'usuario' => $usuario, 'tipos' => $tipos, 'method' => 'put', 'route' => ['novedades.actualizar', $novedad->id]]);
    }
    
    public function actualizar(Request $request, $novedad){

        $novedad = Novedad::with(['servicio'=>function($q){$q->select('id', 'fecha')->with('vale.valera.cuentae.agencia');}, 'tiponovedad'])->find($novedad);
        $novedad->estado = $request->input('estado');
        $novedad->detalle = $request->input('detalle');
        $novedad->solucion = $request->input('solucion');
        $novedad->tiposnovedad_id = $request->input('tipo');
        $novedad->save();
        
        if($novedad->solucion != ""){
            if($novedad->servicio->vale != null){
                try{
                    $from = "EMAILNOTIFY";
                    $to = $novedad->servicio->vale->valera->cuentae->agencia->EMAIL;
                    $subject = "JADMIN:Novedad solucionada";
                    $mensaje = '<html><p>Se ha registrado una solución para la novedad presentada en el servicio <b>#' . $novedad->servicio->id . '</b>, realizado en <b>' . $novedad->servicio->fecha . '</b>. A continuación los datos de la novedad: </p> <br>' .
                      '<ul> <li> Tipo de novedad:' . $novedad->tiponovedad->nombre . '</li>'. 
                      '<li> Detalle: ' . $novedad->detalle . '</li>' .
                      '<li> Solución: ' . $novedad->solucion . '</li>'.
                      '</ul></html>'; 
                    $headers = "From:" . $from;
            
                    Mail::send([], [], function ($message) use($to, $subject, $mensaje, $from){
                        $message->from($from, "JADMIN");
                        $message->to($to);
                        $message->subject($subject);
                        $message->setBody($mensaje, 'text/html');
                    });
                    
                }catch(Exception $ex){
                    
                }
            }
        }

        return redirect('novedades/'. $novedad->servicios_id);
    }

    public function nuevotipo(Request $request){

        $tipo = new Tiposnovedad();
        $tipo->nombre = $request->input('nombre');
        $tipo->save();

        return redirect('/novedades/nuevo/' . $request->input('servicios_id'));
    }

    public function nuevasancion(Request $request){

        $sancion = new Sancion();
        $sancion->descripcion = $request->input('descripcion');
        $sancion->unidad = $request->input('unidad');
        $sancion->cantidad = $request->input('cantidad');
        $sancion->save();

        return redirect('conductores/'.$request->input('conductor'));
    }
}
