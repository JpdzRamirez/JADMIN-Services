<?php

namespace App\Http\Controllers;

use App\Models\Cuentac;
use App\Models\Mensaje;
use App\Models\Smsprogramado;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class MensajeController extends Controller
{
    public function index()
    {

        $mensajes = Mensaje::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($q) {
                $q->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->orderBy('id', 'DESC')->paginate(30);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('mensajes.lista', compact('mensajes', 'usuario'));
    }

    public function store(Request $request)
    {

        if ($request->input('masivo') == 1) {
            $fecha = Carbon::now();
            $mensaje = new Mensaje();
            $mensaje->fecha = $fecha;
            $mensaje->texto = $request->input('texto');
            $mensaje->sentido = "Recibido";
            $mensaje->estado = "Revisado";
            $mensaje->masivo = true;
            $mensaje->save();

            if ($request->filled('intervalo')) {
                $prog = new Smsprogramado();
                if ($request->input('undinter') == "Horas") {
                    $prog->intervalo = $request->input('intervalo') * 60;
                } else {
                    $prog->intervalo = $request->input('intervalo');
                }
                $prog->ultimo = Carbon::now('-05:00');
                $prog->duracion = $fecha->addDays($request->input('duracion'));
                $prog->mensaje = $request->input('texto');
                $prog->estado = 1;
                $prog->save();
            }

            return redirect('/vehiculos/ubicar');
        }
    }

    public function pendientes()
    {

        $mensajes = Mensaje::select('id', 'estado', 'sentido', 'placa', 'texto', 'cuentasc_id')->with(['cuentac' => function ($q) {
            $q->select('id', 'placa', 'conductor_CONDUCTOR');
        }])->where('sentido', 'Enviado')->where('estado', 'Pendiente')->get();

        return json_encode($mensajes);
    }

    public function chat($cuentac)
    {

        $cuentac = Cuentac::with(['conductor' => function ($q) {
            $q->select('CONDUCTOR', 'NOMBRE');
        }])->select('id', 'estado', 'placa', 'conductor_CONDUCTOR')->where('id', $cuentac)->first();
        $mensajes = Mensaje::where('cuentasc_id', $cuentac->id)->orderBy('fecha', 'DESC')->take(100)->get();
        foreach ($mensajes as $mensaje) {
            if ($mensaje->sentido == "Enviado") {
                $mensaje->estado = "Revisado";
                $mensaje->save();
            }
        }

        $mensajes = $mensajes->reverse();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('mensajes.chat', compact('mensajes', 'cuentac', 'usuario'));
    }

    public function chatmensaje(Request $request)
    {

        try {
            $mensaje = new Mensaje();
            $mensaje->fecha = Carbon::now("-05:00");
            $mensaje->texto = $request->input('texto');
            $mensaje->sentido = "Recibido";
            $mensaje->estado = "Pendiente";
            $mensaje->cuentasc_id = $request->input('cuentasc_id');
            $mensaje->save();
        } catch (Exception $e) {
        }

        return json_encode($mensaje);
    }

    public function chatcuenta(Cuentac $cuentac)
    {

        $mensajes = Mensaje::where('cuentasc_id', $cuentac->id)->where('estado', 'Pendiente')->where('sentido', 'Enviado')->get();
        foreach ($mensajes as $mensaje) {
            $mensaje->estado = "Revisado";
            $mensaje->save();
        }

        return json_encode($mensajes);
    }

    public function sincrotaxista(Request $request)
    {        
        $taxista = $request->input('idtaxista');
        $fecha = Carbon::now();
        $formato = $fecha->format('Y-m-d');
        $mensajes = Mensaje::where(function ($q) use ($taxista) {
            $q->where('cuentasc_id', $taxista)->where('sentido', 'Recibido')->where('estado', 'Pendiente');
        })->orWhere(function ($q) use ($formato) {
            $q->where('masivo', 1)->whereDate('fecha', $formato);
        })->get();
        $textos = [];

        foreach ($mensajes as $mensaje) {
            if ($mensaje->masivo == 1) {
                if ($fecha->diffInSeconds($mensaje->fecha) < 50) {
                    $textos[] = $mensaje;
                }
            } else {
                $mensaje->estado = "Revisado";
                $mensaje->save();
                $textos[] = $mensaje;
            }
        }

        if (count($textos) > 0) {
            return json_encode($textos);
        } else {
            return "0";
        }
    }

    public function chattaxista(Request $request)
    {

        $mensaje = new Mensaje();
        $mensaje->fecha = Carbon::now("-05:00");
        $mensaje->texto = $request->input('texto');
        $mensaje->sentido = "Enviado";
        $mensaje->estado = "Pendiente";
        $mensaje->placa = $request->input('placa');
        $mensaje->cuentasc_id = $request->input('idtaxista');
        $mensaje->save();

        return "OK";
    }

    public function chathistorial(Request $request)
    {

        $mensajes = Mensaje::where('cuentasc_id', $request->input('idtaxista'))->orWhere('masivo', 1)->orderBy('fecha', 'DESC')->take(10)->get();
        foreach ($mensajes as $mensaje) {
            if ($mensaje->sentido == "Recibido") {
                $mensaje->estado = "Revisado";
                $mensaje->save();
            }
        }

        return json_encode($mensajes);
    }

    public function filtrarMensajes(Request $request)
    {
        $ids = [];
        $filtro = "";
        $c1 = "";
        $c2 = "";
        $c3 = "";
        $c4 = "";

        if ($request->filled('fecha')) {
            $dates = explode(" - ", $request->input('fecha'));
            if (empty($ids)) {
                $s1 = Mensaje::whereBetween('fecha', $dates)->pluck('id')->toArray();
            } else {
                $s1 = Mensaje::whereBetween('fecha', $dates)->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s1;
            $c1 = $request->input('fecha');
            $filtro = $filtro . 'Fecha=' . $request->input('fecha') . ', ';
        }

        if ($request->filled('texto')) {
            if (empty($ids)) {
                $s2 = Mensaje::where('texto', 'like', '%' . $request->input('texto') . '%')->pluck('id')->toArray();
            } else {
                $s2 = Mensaje::where('texto', 'like', '%' . $request->input('texto') . '%')->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s2;
            $c2 = $request->input('texto');
            $filtro = $filtro . 'Texto=' . $request->input('texto') . ', ';
        }

        if ($request->filled('emisor')) {
            if (empty($ids)) {
                if (strtolower($request->input('emisor')) == "crm") {
                    $s3 = Mensaje::where('sentido', 'Recibido')->pluck('id')->toArray();
                } else {
                    $emisor = $request->input('emisor');
                    $s3 = Mensaje::where('sentido', 'Enviado')->whereHas('cuentac', function ($q) use ($emisor) {
                        $q->whereHas('conductor', function ($r) use ($emisor) {
                            $r->where('NOMBRE', 'like', '%' . $emisor . '%');
                        });
                    })->pluck('id')->toArray();
                }
            } else {
                if (strtolower($request->input('emisor')) == "crm") {
                    $s3 = Mensaje::where('sentido', 'Recibido')->whereIn('id', $ids)->pluck('id')->toArray();
                } else {
                    $emisor = $request->input('emisor');
                    $s3 = Mensaje::where('sentido', 'Enviado')->whereHas('cuentac', function ($q) use ($emisor) {
                        $q->whereHas('conductor', function ($r) use ($emisor) {
                            $r->where('NOMBRE', 'like', '%' . $emisor . '%');
                        });
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s3;
            $c3 = $request->input('emisor');
            $filtro = $filtro . 'Emisor=' . $request->input('emisor') . ', ';
        }

        if ($request->filled('receptor')) {
            if (empty($ids)) {
                if (strtolower($request->input('receptor')) == "crm") {
                    $s4 = Mensaje::where('sentido', 'Enviado')->pluck('id')->toArray();
                } else {
                    $receptor = $request->input('receptor');
                    $s4 = Mensaje::where('sentido', 'Recibido')->whereHas('cuentac', function ($q) use ($receptor) {
                        $q->whereHas('conductor', function ($r) use ($receptor) {
                            $r->where('NOMBRE', 'like', '%' . $receptor . '%');
                        });
                    })->pluck('id')->toArray();
                }
            } else {
                if (strtolower($request->input('receptor')) == "crm") {
                    $s4 = Mensaje::where('sentido', 'Enviado')->whereIn('id', $ids)->pluck('id')->toArray();
                } else {
                    $receptor = $request->input('receptor');
                    $s4 = Mensaje::where('sentido', 'Recibido')->whereHas('cuentac', function ($q) use ($receptor) {
                        $q->whereHas('conductor', function ($r) use ($receptor) {
                            $r->where('NOMBRE', 'like', '%' . $receptor . '%');
                        });
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s4;
            $c4 = $request->input('receptor');
            $filtro = $filtro . 'Receptor=' . $request->input('receptor') . ', ';
        }

        $mensajes = Mensaje::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($q) {
                $q->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->whereIn('id', $ids)->get();

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('mensajes.lista', compact('mensajes', 'usuario', 'filtro', 'c1', 'c2', 'c3', 'c4'));
    }

    public function programados()
    {
        $smsprogramados = Smsprogramado::orderBy('id', 'desc')->paginate(15);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('mensajes.programados', compact('smsprogramados', 'usuario'));
    }

    public function editarSmsProgramado(Request $request)
    {
        $smsprogramado = Smsprogramado::find($request->input('idsms'));
        $smsprogramado->duracion = $request->input('duracion');
        $smsprogramado->intervalo = $request->input('intervalo');
        $smsprogramado->save();

        return redirect('mensajes/programados');
    }

    public function inactivarSmsProgramado($idsms)
    {
        $smsprogramado = Smsprogramado::find($idsms);
        $smsprogramado->estado = 0;
        $smsprogramado->save();

        return redirect('mensajes/programados');
    }

    public function activarSmsProgramado($idsms)
    {
        $smsprogramado = Smsprogramado::find($idsms);
        $smsprogramado->estado = 1;
        $smsprogramado->save();

        return redirect('mensajes/programados');
    }
}
