<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tercero;
use App\Models\Agencia_tercero;
use App\Models\Cuentac;
use App\Models\Cliente;
use App\Models\Contrato_vale_ruta;
use App\Models\Cuentae;
use App\Models\Flota;
use App\Models\Pasajero;
use App\Models\Servicio_usuariosav;
use App\Models\ServicioPasajeros;
use App\Models\Usuarioav;
use App\Models\Vale;
use App\Models\Vale_servicio;
use App\Models\Valeav;
use App\Models\Valeav_servicio;
use App\Models\Valera;
use App\Models\ValeraFisica;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ServicioController extends Controller
{
    public function encurso()
    {
        $usera = Auth::user();
        if ($usera->roles_id == 4) {
            $ids = [];
            if ($usera->id == 119) {
                $valeras = Valera::with('cuentae.agencia')->where('cuentase_id', 5)->get();
                $agencia = $valeras[0]->cuentae->agencia;
            } else {
                //$valeras = Valera::where('cuentase_id', $usera->cuentae->id)->get();
                $valeras = Valera::whereHas('cuentae', function ($q) {
                    $q->whereHas('users', function ($r) {
                        $r->where('users.id', Auth::user()->id);
                    });
                })->get();
                if (count($valeras) > 0) {
                    $agencia = $valeras[0]->cuentae->agencia;
                }
            }

            if ($agencia->tercero->TERCERO == 435) {
                foreach ($valeras as $valera) {
                    $idvalera = $valera->id;
                    $ids2 = Servicio::where(function ($s) {
                        $s->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                    })->whereHas(
                        'valeav',
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();

                    $ids = array_merge($ids, $ids2);
                }
            } else {
                foreach ($valeras as $valera) {
                    $idvalera = $valera->id;
                    $ids2 = Servicio::where(function ($s) {
                        $s->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                    })->whereHas(
                        'vale',
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();

                    $ids = array_merge($ids, $ids2);
                }
            }

            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->whereIn('id', $ids)->orderBy('id', 'DESC')->get();

            return view('servicios.encursoempresa', compact('servicios'));
        } else if ($usera->roles_id == 5) {

            $ids = [];
            if ($usera->idtercero != null) {
                $tercero = Tercero::where('TERCERO', $usera->idtercero)->first();
            } else {
                $tercero = Auth::user()->tercero;
            }
            $terce = $tercero->TERCERO;
            $valeras = Valera::wherehas('cuentae', function ($q) use ($terce) {
                $q->where('agencia_tercero_TERCERO', $terce);
            })->get();
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $ids2 = Servicio::where(function ($s) {
                    $s->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->whereHas(
                    'vale',
                    function ($q) use ($idvalera) {
                        $q->whereHas(
                            'valera',
                            function ($r) use ($idvalera) {
                                $r->where('id', $idvalera);
                            }
                        );
                    }
                )->pluck('id')->toArray();

                $ids = array_merge($ids, $ids2);
            }

            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->whereIn('id', $ids)->orderBy('id', 'DESC')->get();

            return view('servicios.encursoempresa', compact('servicios'));
        } else {

            $servicios = Servicio::with([
                'cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                },
                'valeav' => function ($s) {
                    $s->select('id', 'servicios_id');
                },
                'vale_servicio.vale.valera.cuentae.agencia',
                'cliente',
                'ruta'
            ])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->paginate(30);
            $pen = Servicio::where('estado', 'Pendiente')->count();
            $asi = Servicio::where('estado', 'Asignado')->count();
            $cur = Servicio::where('estado', 'En curso')->count();

            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

            return view('servicios.encurso', compact('servicios', 'usuario', 'pen', 'asi', 'cur'));
        }
    }

    public function finalizados()
    {
        $usera = Auth::user();
        if ($usera->roles_id == 4) {
            $ids = [];
            if ($usera->id == 119) {
                $valeras = Valera::with('cuentae.agencia')->where('cuentase_id', 5)->get();
                $agencia = $valeras[0]->cuentae->agencia;
            } else {
                /*$valeras = Valera::where('cuentase_id', $usera->cuentae->id)->get();
                $agencia = $usera->cuentae->agencia;*/
                $valeras = Valera::whereHas('cuentae', function ($q) {
                    $q->whereHas('users', function ($r) {
                        $r->where('users.id', Auth::user()->id);
                    });
                })->get();
                if (count($valeras) > 0) {
                    $agencia = $valeras[0]->cuentae->agencia;
                }
            }

            if ($agencia->tercero->TERCERO == 435) {
                foreach ($valeras as $valera) {
                    $idvalera = $valera->id;
                    $ids2 = Servicio::where('estado', 'Finalizado')->whereHas(
                        'valeav',
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();

                    $ids = array_merge($ids, $ids2);
                }
            } else {
                foreach ($valeras as $valera) {
                    $idvalera = $valera->id;
                    $ids2 = Servicio::where('estado', 'Finalizado')->whereHas(
                        'vale',
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();

                    $ids = array_merge($ids, $ids2);
                }
            }

            if (count($ids) > 150) {
                $ids = array_slice($ids, count($ids) - 150);
            }

            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->whereIn('id', $ids)->latest('id')->paginate(20);

            return view('servicios.finalizadosempresa', compact('servicios'));
        } else if ($usera->roles_id == 5) {
            $ids = [];
            if ($usera->idtercero != null) {
                $tercero = Tercero::where('TERCERO', $usera->idtercero)->first();
            } else {
                $tercero = Auth::user()->tercero;
            }
            $terce = $tercero->TERCERO;
            $valeras = Valera::wherehas('cuentae', function ($q) use ($terce) {
                $q->where('agencia_tercero_TERCERO', $terce);
            })->get();
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $ids2 = Servicio::where('estado', 'Finalizado')->whereHas(
                    'vale',
                    function ($q) use ($idvalera) {
                        $q->whereHas(
                            'valera',
                            function ($r) use ($idvalera) {
                                $r->where('id', $idvalera);
                            }
                        );
                    }
                )->pluck('id')->toArray();

                $ids = array_merge($ids, $ids2);
            }

            if (count($ids) > 150) {
                $ids = array_slice($ids, count($ids) - 150);
            }
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->whereIn('id', $ids)->latest('id')->paginate(20);

            return view('servicios.finalizadosempresa', compact('servicios'));
        } else {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente', 'cancelacion', 'ruta'])->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->latest('id')->paginate(20);
            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

            return view('servicios.finalizados', compact('servicios', 'usuario'));
        }
    }

    public function nuevo($telefono = null)
    {
        $servicio = new Servicio();
        $empresas = Tercero::whereHas('agencias', function ($q) {
            $q->has('cuentae');
        })->orderBy('RAZON_SOCIAL', 'ASC')->get();
        if (count($empresas) > 0) {
            $primera =  $empresas[0]->TERCERO;
            $valeras = Valera::whereHas('cuentae', function ($q) use ($primera) {
                $q->where('agencia_tercero_TERCERO', $primera);
            })->where('estado', 1)->get();
        } else {
            $empresas = [];
            $valeras = [];
        }

        if ($telefono != null) {
            $cliente = Cliente::where('telefono', $telefono)->whereNotNull('latitud')->whereNotNull('longitud')->first();
        } else {
            $cliente = null;
        }

        $fisicas = Agencia_tercero::whereNotNull('NOMBRE')->orderBy('NOMBRE', 'ASC')->get();
        $flotas = Flota::get();

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('servicios.form', ['servicio' => $servicio, 'usuario' => $usuario, 'empresas' => $empresas, 'fisicas' => $fisicas, 'flotas' => $flotas, 'valeras' => $valeras, 'cliente' => $cliente, 'method' => 'post', 'route' => ['servicios.agregar']]);
    }

    public function duplicar($idServicio)
    {
        $servicio = Servicio::with('cliente')->find($idServicio);
        $empresas = Tercero::whereHas('agencias', function ($q) {
            $q->has('cuentae');
        })->get();
        if (count($empresas) > 0) {
            $primera =  $empresas[0]->TERCERO;
            $valeras = Valera::whereHas('cuentae', function ($q) use ($primera) {
                $q->where('agencia_tercero_TERCERO', $primera);
            })->where('estado', 1)->get();
        } else {
            $empresas = [];
            $valeras = [];
        }

        $cliente = $servicio->cliente;

        $fisicas = Agencia_tercero::whereNotNull('NOMBRE')->orderBy('NOMBRE', 'ASC')->get();
        $flotas = Flota::get();

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        if ($servicio->pago == "Vale electrónico") {
            $idServicio = $servicio->id;
            $servicio->vale = Vale::with('valera.cuentae')->whereHas('valeServicio', function ($q) use ($idServicio) {
                $q->where('servicios_id', $idServicio);
            })->latest('id')->first();
        } else if ($servicio->pago == "Vale físico") {
            $consulta = DB::select(DB::raw('SELECT agencia_tercero.NRO_IDENTIFICACION from agencia_tercero, cuentase, valerasfisicas, servicios WHERE servicios.valerasfisicas_id=valerasfisicas.id AND
                valerasfisicas.cuentase_id=cuentase.id AND cuentase.agencia_tercero_TERCERO=agencia_tercero.TERCERO AND cuentase.agencia_tercero_CODIGO=agencia_tercero.CODIGO AND servicios.id=' . $servicio->id));
            if (count($consulta) > 0) {
                $servicio->nroIdentificacion = $consulta[0]->NRO_IDENTIFICACION;
            }
        }
        $servicio->usuarios = str_replace(array("\r", "\n"), "|", $servicio->usuarios);

        return view('servicios.form', ['servicio' => $servicio, 'usuario' => $usuario, 'empresas' => $empresas, 'fisicas' => $fisicas, 'flotas' => $flotas, 'valeras' => $valeras, 'cliente' => $cliente, 'method' => 'post', 'route' => ['servicios.agregar']]);
    }

    public function store(Request $request)
    {
        try {
            $cliente = Cliente::where('telefono', $request->input('telefono'))->first();
            if ($cliente == null) {
                $cliente = new Cliente();
                $cliente->nombres = $request->input('nombres');
                $cliente->telefono = $request->input('telefono');
                $cliente->email = $request->input('email');
                $cliente->latitud = $request->input('latitud');
                $cliente->longitud = $request->input('longitud');
                $cliente->direccion = $request->input("direccion") . ", " . $request->input('municipio');
                if ($request->input('pago') == "Vale electrónico") {
                    $vale = Vale::find($request->input('idvale'));
                    $cliente->agencia = $vale->valera->cuentae->agencia->NRO_IDENTIFICACION;
                }
                $cliente->save();
            } else {
                if ($request->has('dataup')) {
                    $cliente->nombres = $request->input('nombres');
                    $cliente->telefono = $request->input('telefono');
                    $cliente->email = $request->input('email');
                    $cliente->direccion = $request->input("direccion") . ", " . $request->input('municipio');
                    $cliente->latitud = $request->input('latitud');
                    $cliente->longitud = $request->input('longitud');
                    if ($request->input('pago') == "Vale electrónico") {
                        $vale = Vale::find($request->input('idvale'));
                        $cliente->agencia = $vale->valera->cuentae->agencia->NRO_IDENTIFICACION;
                    }
                    $cliente->save();
                }
            }

            if ($request->filled('servicioid')) {
                $servicio = Servicio::find($request->input('servicioid'));
                $fecha = Carbon::now();
                $servicio->fecha = $fecha;
                if ($servicio->asignacion == "Normal" && $request->input('asignacion') == "Directo") {
                    $servicio->reasignado = 1;
                }
            } else {
                $servicio = new Servicio();
                $fecha = Carbon::now();
                $servicio->fecha = $fecha;
            }

            $servicio->direccion = $request->input('direccion') . ", " . $request->input('municipio');
            $servicio->adicional = $request->input('complemento');
            $servicio->asignacion = $request->input('asignacion');
            $servicio->pago = $request->input('pago');
            $servicio->cobro = $request->input('cobro');
            $servicio->observaciones = $request->input('observaciones');
            $servicio->latitud = $request->input('latitud');
            $servicio->longitud = $request->input('longitud');
            $servicio->usuarios = $request->input('usuarios');
            $servicio->contacto = $request->input('contacto');
            if ($request->input('flota') == "0") {
                $servicio->flotas_id = null;
            } else {
                $servicio->flotas_id = $request->input('flota');
            }
            $servicio->clientes_id = $cliente->id;
            $servicio->users_id = Auth::user()->id;

            if ($servicio->asignacion == "Directo") {
                $pla = explode("_", $request->input('placa'));
                if (count($pla) >= 3) {
                    $cuentac = Cuentac::with('conductor')->find($pla[2]);
                    $servicio->placa = $pla[0];
                    $servicio->cuentasc_id = $cuentac->id;
                } else {
                    $servicio = new stdClass();
                    $servicio->respuesta = "Error";
                    $servicio->mensaje = "La información de la placa no es correcta";

                    return json_encode($servicio);
                }
            }

            if ($request->filled('fechaprogramada')) {
                $servicio->fechaprogramada = $request->input('fechaprogramada');
            }

            $servicio->estado = "Pendiente";
            if ($servicio->pago == "Vale electrónico") {
                if ($request->filled('idvale')) {
                    $limite = 0;
                    if ($request->input('avianca') == "1") {
                        $vale = Valeav::find($request->input('idvale'));
                        if ($request->filled('vuelo')) {
                            $vale->vuelo = $request->input('vuelo');
                        }
                        if ($request->filled('voucher')) {
                            $vale->voucher = $request->input('voucher');
                        }
                        if ($request->filled('centrocostoav')) {
                            $vale->centrocosto = $request->input('centrocostoav');
                        }
                        if ($request->filled('fechavianca')) {
                            $servicio->fechavianca = $request->input('fechavianca');
                        }
                        $vale->tipo = $request->input('tipovale');
                        $vale->tiposer = $request->input('tiposer');
                        if ($vale->tiposer == "Recogida") {
                            $servicio->fechareportado = $request->input('fechareportado');
                        }
                        $rutas = $request->input('rutas');
                        $ruta = explode("_", $rutas);
                        $vale->contrato = $ruta[0];
                        $vale->secuencia = $ruta[1];
                        $servicio->estadocobro = 1;
                    } else {
                        $vale = Vale::find($request->input('idvale'));
                    }

                    if ($vale->estado == "Libre" || $vale->estado == "Asignado") {
                        $vale->estado = "Visado";
                    } else {
                        $servicio->estado = "No vehiculo";
                        $servicio->save();
                        $servicio->respuesta = "Error";
                        $servicio->mensaje = "La información del vale no es correcta";

                        return json_encode($servicio);
                    }
                    $vale->servicios_id = null;
                    $servicio->save();
                    do {
                        sleep(1);
                        $vale->servicios_id = $servicio->id;
                        $limite++;
                    } while ($vale->servicios_id == null || $limite == 10);

                    if ($vale->servicios_id == null) {
                        $servicio->estado = "No vehiculo";
                        $servicio->save();
                        //$servicio = new stdClass();
                        $servicio->respuesta = "Error";
                        $servicio->mensaje = "La información del vale no es correcta";

                        return json_encode($servicio);
                    } else {
                        $vale->save();
                        if ($request->input('avianca') == "1") {
                            $valeserv = Valeav_servicio::where('servicios_id', $servicio->id)->where('valesav_id', $vale->id)->first();
                        } else {
                            $valeserv = Vale_servicio::where('servicios_id', $servicio->id)->where('vales_id', $vale->id)->first();
                        }
                        if ($valeserv == null) {
                            if ($request->input('avianca') == "1") {
                                $valeserv = new Valeav_servicio();
                                $valeserv->valesav_id = $vale->id;
                                $valeserv->servicios_id = $servicio->id;
                                $valeserv->save();

                                if ($vale->tipo == "Tierra" || $vale->tipo == "Tripulación") {
                                    $usuav = Usuarioav::where('identificacion', $request->input('usuav1'))->first();
                                    $fecpro = Carbon::parse($servicio->fechaprogramada);

                                    if ($usuav != null) {
                                        $serav1 = new Servicio_usuariosav();
                                        $serav1->servicios_id = $servicio->id;
                                        $serav1->usuariosav_id = $usuav->id;
                                        $serav1->save();

                                        if ($cuentac != null) {
                                            //$this->enviarSMS($usuav->celular, $usuav->nombres, $cuentac->conductor->PRIMER_NOMBRE . " " . $cuentac->conductor->PRIMER_APELLIDO, $servicio->placa, $fecpro->format('Y-m-d g:i A'));
                                        }
                                    }

                                    if ($request->filled('usuav2')) {
                                        $usuav = Usuarioav::where('identificacion', $request->input('usuav2'))->first();
                                        if ($usuav != null) {
                                            $serav1 = new Servicio_usuariosav();
                                            $serav1->servicios_id = $servicio->id;
                                            $serav1->usuariosav_id = $usuav->id;
                                            $serav1->save();

                                            if ($cuentac != null) {
                                                if ($request->filled('horausuav2')) {
                                                    $fecpro = Carbon::parse($request->input('horausuav2'));
                                                } else {
                                                    $fecpro->addMinutes(30);
                                                }
                                                //$this->enviarSMS($usuav->celular, $usuav->nombres, $cuentac->conductor->PRIMER_NOMBRE . " " . $cuentac->conductor->PRIMER_APELLIDO, $servicio->placa, $fecpro->format('Y-m-d g:i A'));
                                            }
                                        }
                                    }

                                    if ($request->filled('usuav3')) {
                                        $usuav = Usuarioav::where('identificacion', $request->input('usuav3'))->first();
                                        if ($usuav != null) {
                                            $serav1 = new Servicio_usuariosav();
                                            $serav1->servicios_id = $servicio->id;
                                            $serav1->usuariosav_id = $usuav->id;
                                            $serav1->save();

                                            if ($cuentac != null) {
                                                if ($request->filled('horausuav3')) {
                                                    $fecpro = Carbon::parse($request->input('horausuav3'));
                                                } else {
                                                    $fecpro->addMinutes(30);
                                                }
                                                //$this->enviarSMS($usuav->celular, $usuav->nombres, $cuentac->conductor->PRIMER_NOMBRE . " " . $cuentac->conductor->PRIMER_APELLIDO, $servicio->placa, $servicio->fechaprogramada);
                                            }
                                        }
                                    }

                                    if ($request->filled('autorizacion')) {
                                        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "autorizaciones.txt", 'a');
                                        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . "Operador: " . Auth::user()->usuario . ", Código de autorización: " . $request->input('autorizacion') . ", Servicio: " . $servicio->id);
                                        fclose($logFile);
                                    }
                                }
                            } else {
                                $valeserv = new Vale_servicio();
                                $valeserv->vales_id = $vale->id;
                                $valeserv->servicios_id = $servicio->id;
                                $valeserv->save();
                            }
                        }

                        if ($request->filled('rutaspetro') && $request->filled('idusuariopetro1')) {
                            $valorRuta = explode("_", $request->input('rutaspetro'));
                            $servicio->CONTRATO_VALE = $valorRuta[0];
                            $servicio->SECUENCIA = $valorRuta[1];
                            $servicio->save();

                            ServicioPasajeros::where('servicios_id', $servicio->id)->delete();

                            $pasajero = Pasajero::find($request->input('idusuariopetro1'));
                            if ($pasajero != null) {
                                $servicioPasajero = new ServicioPasajeros();
                                $servicioPasajero->servicios_id = $servicio->id;
                                $servicioPasajero->pasajeros_id = $pasajero->id;
                                $servicioPasajero->sub_cuenta = $pasajero->sub_cuenta;
                                $servicioPasajero->affe = $pasajero->affe;
                                $servicioPasajero->solicitado = $pasajero->solicitado;
                                $servicioPasajero->autorizado = $pasajero->autorizado;
                                $servicioPasajero->save();
                            }

                            for ($i = 2; $i <= 4; $i++) {
                                if ($request->filled('usuariopetro' . $i)) {
                                    $pasajero = Pasajero::find($request->input('idusuariopetro' . $i));

                                    if ($pasajero != null) {
                                        $servicioPasajero = new ServicioPasajeros();
                                        $servicioPasajero->servicios_id = $servicio->id;
                                        $servicioPasajero->pasajeros_id = $pasajero->id;
                                        $servicioPasajero->sub_cuenta = $pasajero->sub_cuenta;
                                        $servicioPasajero->affe = $pasajero->affe;
                                        $servicioPasajero->solicitado = $pasajero->solicitado;
                                        $servicioPasajero->autorizado = $pasajero->autorizado;
                                        $servicioPasajero->save();
                                    }
                                }
                            }
                        }

                        $servicio->respuesta = "Correcto";
                    }
                } else {
                    $servicio = new stdClass();
                    $servicio->respuesta = "Error";
                    $servicio->mensaje = "La información del vale no es correcta";

                    return json_encode($servicio);
                }
            } else if ($servicio->pago == "Vale físico") {
                $nit = $request->input('empresafisico');
                $cuentae = Cuentae::whereHas('agencia', function ($r) use ($nit) {
                    $r->where('NRO_IDENTIFICACION', $nit);
                })->first();
                if ($cuentae == null) {
                    $agencia = Agencia_tercero::where('NRO_IDENTIFICACION', $nit)->first();
                    $cuentae = new Cuentae();
                    $cuentae->saldo = 0;
                    $cuentae->estado = 1;
                    $cuentae->agencia_tercero_TERCERO = $agencia->TERCERO;
                    $cuentae->agencia_tercero_CODIGO = $agencia->CODIGO;
                    $cuentae->save();
                }
                $fisica = ValeraFisica::where('cuentase_id', $cuentae->id)->first();
                if ($fisica == null) {
                    $fisica = new ValeraFisica();
                    $fisica->cuentase_id = $cuentae->id;
                    $fisica->save();
                }
                $servicio->valerasfisicas_id = $fisica->id;
                $servicio->save();
                $servicio->respuesta = "Correcto";
            } else {
                $servicio->save();
                $servicio->respuesta = "Correcto";
            }

            return json_encode($servicio);
        } catch (Exception $e) {
            $servicio = new stdClass();
            $servicio->respuesta = "Error";
            $servicio->mensaje = "La información del servicio no es correcta, " . $e->getMessage() . " Linea" . $e->getLine();

            return json_encode($servicio);
        }
    }

    public function detalles($servicio)
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $servi = Servicio::has('valeav_servicio')->select('id')->find($servicio);
        if($servi == null){
            $servicio = Servicio::with(['cuentac'=>function($q){
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor'=>function($r){
                    $r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION', 'NOMBRE', 'TELEFONO');
                }])
            ;}, 
            'pasajeros',
            'cliente', 
            'vale_servicio.vale.valera.cuentae.agencia', 
            'registros', 
            'seguimientos', 
            'operador_asignacion',
            'vale_servicio.usuario'
            ])
            ->where('id', $servicio)
            ->first();
            //return json_encode($servicio, JSON_PRETTY_PRINT);  
            return view('servicios.detalles', compact('usuario', 'servicio'));
        } else {
            $servicio = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION', 'NOMBRE', 'TELEFONO');
                }])
                ;
            }, 'cliente', 'valeav', 'valeav_servicio.valeav.valera.cuentae.agencia', 'registros', 'seguimientos', 'operador_asignacion'])
                ->where('id', $servicio)
                ->first();

            if ($servicio->valeav != null) {
                $contrato = Contrato_vale_ruta::where('CONTRATO_VALE', $servicio->valeav->contrato)->where('SECUENCIA', $servicio->valeav->secuencia)->first();
                $ruta = $contrato->ORIGEN . ' --- ' . $contrato->DESTINO;
                /*foreach ($vales as $vale) {
                    $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $vale->contrato)->where('SECUENCIA', $vale->secuencia)->first();
                    $rutas = $rutas . $ruta->ORIGEN . ' --- ' . $ruta->DESTINO . PHP_EOL;
                }*/
            } else {
                $ruta = "";
            }

            return view('servicios.detallesav', compact('usuario', 'servicio', 'ruta'));
        }
    }

    function distancia($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 3)
    {
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat)) * cos(deg2rad($point2_lat)) * cos(deg2rad($point1_long - $point2_long)))));
        switch ($unit) {
            case 'km':
                $distance = $degrees * 111.13384;
                break;
            case 'mi':
                $distance = $degrees * 69.05482;
                break;
            case 'nmi':
                $distance =  $degrees * 59.97662;
        }
        return round($distance, $decimals);
    }

    public function filtrarencurso(Request $request)
    {

        if ($request->filled('id')) {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('id', $request->input('id'))->where(function ($q) {
                $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
            })->get();
            $filtro = array('ID', $request->input('id'));
        } elseif ($request->filled('fecha')) {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->whereDate('fecha', $request->input('fecha'))->where(function ($q) {
                $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
            })->get();
            $filtro = array('Fecha', $request->input('fecha'));
        } elseif ($request->filled('cliente')) {
            $servicios = [];
            $servicios2 = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            foreach ($servicios2 as $servicio) {
                if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                    $servicios[] = $servicio;
                }
            }
            $filtro = array('Cliente', $request->input('cliente'));
        } elseif ($request->filled('telefono')) {
            $servicios = [];
            $servicios2 = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            foreach ($servicios2 as $servicio) {
                if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                    $servicios[] = $servicio;
                }
            }
            $filtro = array('Teléfono', $request->input('telefono'));
        } elseif ($request->filled('direccion')) {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('direccion', 'like', $request->input('direccion'))->where(function ($q) {
                $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
            })->get();
            $filtro = array('Dir. Origen', $request->input('direccion'));
        } elseif ($request->filled('fechaprogramada')) {
            if ($request->input('fechaprogramada') == "sinfiltro") {
                return redirect('servicios/en_curso');
            } elseif ($request->input('fechaprogramada') == "inmediato") {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->whereNull('fechaprogramada')->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            } else {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->whereNotNull('fechaprogramada')->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            }
            $filtro = array('Despacho', $request->input('fechaprogramada'));
        } elseif ($request->filled('pago')) {
            if ($request->input('pago') == "sinfiltro") {
                return redirect('servicios/en_curso');
            } else {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('pago', $request->input('pago'))->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            }
            $filtro = array('Modo pago', $request->input('pago'));
        } elseif ($request->filled('asignacion')) {
            if ($request->input('asignacion') == "sinfiltro") {
                return redirect('servicios/en_curso');
            } else {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('asignacion', $request->input('asignacion'))->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            }
            $filtro = array('Asignación', $request->input('asignacion'));
        } elseif ($request->filled('conductor')) {
            $servicios = [];
            $servicios2 = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            foreach ($servicios2 as $servicio) {
                if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                    $servicios[] = $servicio;
                }
            }
            $filtro = array('Conductor', $request->input('conductor'));
        } elseif ($request->filled('vehiculo')) {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('placa', $request->input('vehiculo'))->where(function ($q) {
                $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
            })->get();
            $filtro = array('Vehiculo', $request->input('vehiculo'));
        } elseif ($request->filled('fuente')) {
            if ($request->input('fuente') == "CRM") {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }])->where('users_id', '<>', 112)->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            } elseif ($request->input('fuente') == "IVR") {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }])->where('users_id', 112)->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            } else {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }])->whereNull('users_id')->where(function ($q) {
                    $q->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso');
                })->get();
            }
            $filtro = array('Fuente', $request->input('fuente'));
        } elseif ($request->filled('estado')) {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('estado', $request->input('estado'))->orderBy('id', 'DESC')->get();
            $filtro = array('Estado', $request->input('estado'));
        } else {
            return redirect('servicios/en_curso');
        }

        $pen = Servicio::where('estado', 'Pendiente')->count();
        $asi = Servicio::where('estado', 'Asignado')->count();
        $cur = Servicio::where('estado', 'En curso')->count();

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('servicios.encurso', compact('servicios', 'usuario', 'filtro', 'pen', 'asi', 'cur'));
    }

    public function filtrarfinalizados(Request $request)
    {
        if (
            empty($request->input('id')) && empty($request->input('fecha')) && empty($request->input('cliente')) && empty($request->input('telefono')) && empty($request->input('direccion')) && empty($request->input('fechaprogramada')) &&
            empty($request->input('pago')) && empty($request->input('asignacion')) && empty($request->input('conductor')) && empty($request->input('vehiculo')) && empty($request->input('fuente')) && empty($request->input('estado'))
        ) {
            return redirect('/servicios/finalizados');
        }

        $ids = [];
        $filtro = "";
        $c1 = "";
        $c2 = "";
        $c3 = "";
        $c4 = "";
        $c5 = "";
        $c6 = "";
        $c7 = "";
        $c8 = "";
        $c9 = "";
        $c10 = "";
        $c11 = "";
        $c12 = "";

        if ($request->filled('id')) {
            $s1 = Servicio::select('id', 'estado')->where('id', $request->input('id'))->where(function ($q) {
                $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
            })->get();
            $ids = $s1->toArray();
            $c1 = $request->input('id');
            $filtro = $filtro . "ID=" . $request->input('id') . ", ";
        }

        if ($request->filled('fecha')) {
            $dates = explode(" - ", $request->input('fecha'));
            if (empty($ids)) {
                $s2 = Servicio::select('id', 'estado', 'fecha')->whereBetween('fecha', $dates)->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id');
            } else {
                $s2 = Servicio::select('id', 'estado', 'fecha')->whereBetween('fecha', $dates)->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->pluck('id');
            }
            $ids = $s2->toArray();
            $c2 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        }

        if ($request->filled('cliente')) {
            $s3 = [];
            if (empty($ids)) {
                $servicios2 = Servicio::select('id', 'estado', 'clientes_id')->with('cliente')->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                        $s3[] = $servicio->id;
                    }
                }
            } else {
                $servicios2 = Servicio::select('id', 'estado', 'clientes_id')->with('cliente')->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                        $s3[] = $servicio->id;
                    }
                }
            }

            $ids = $s3;
            $c3 = $request->input('cliente');
            $filtro = $filtro . "Cliente=" . $request->input('cliente') . ", ";
        }

        if ($request->filled('telefono')) {
            $s4 = [];
            if (empty($ids)) {
                $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                        $s4[] = $servicio->id;
                    }
                }
            } else {
                $servicios2 = Servicio::with('cliente')->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                        $s4[] = $servicio->id;
                    }
                }
            }

            $ids = $s4;
            $c4 = $request->input('telefono');
            $filtro = $filtro . "Teléfono=" . $request->input('telefono') . ", ";
        }

        if ($request->filled('direccion')) {
            if (empty($ids)) {
                $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id')->toArray();
            } else {
                $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s5;
            $c5 = $request->input('direccion');
            $filtro = $filtro . "Dir. Origen=" . $request->input('direccion') . ", ";
        }

        if ($request->filled('fechaprogramada')) {
            if ($request->input('fechaprogramada') == "inmediato") {
                if (empty($ids)) {
                    $s6 = Servicio::whereNull('fechaprogramada')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s6 = Servicio::whereNull('fechaprogramada')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
            } else {
                if (empty($ids)) {
                    $s6 = Servicio::whereNotNull('fechaprogramada')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s6 = Servicio::whereNotNull('fechaprogramada')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s6;
            $c6 = $request->input('fechaprogramada');
            $filtro = $filtro . "Despacho=" . $request->input('fechaprogramada') . ", ";
        }

        if ($request->filled('pago')) {
            if (empty($ids)) {
                $s7 = Servicio::where('pago', $request->input('pago'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id')->toArray();
            } else {
                $s7 = Servicio::where('pago', $request->input('pago'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s7;
            $c7 = $request->input('pago');
            $filtro = $filtro . "Modo pago=" . $request->input('pago') . ", ";
        }

        if ($request->filled('asignacion')) {
            if (empty($ids)) {
                $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id')->toArray();
            } else {
                $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s8;
            $c8 = $request->input('asignacion');
            $filtro = $filtro . "Asignación=" . $request->input('asignacion') . ", ";
        }

        if ($request->filled('conductor')) {
            $s9 = [];
            if (empty($ids)) {
                $servicios2 = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }])->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                foreach ($servicios2 as $servicio) {
                    if ($servicio->cuentac != null) {
                        if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                            $s9[] = $servicio->id;
                        }
                    }
                }
            } else {
                $servicios2 = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }])->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->get();
                foreach ($servicios2 as $servicio) {
                    if ($servicio->cuentac != null) {
                        if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                            $s9[] = $servicio->id;
                        }
                    }
                }
            }
            $ids = $s9;
            $c9 = $request->input('conductor');
            $filtro = $filtro . "Conductor=" . $request->input('conductor') . ", ";
        }

        if ($request->filled('vehiculo')) {
            if (empty($ids)) {
                $s10 = Servicio::where('placa', strval($request->input('vehiculo')))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id')->toArray();
            } else {
                $s10 = Servicio::where('placa', $request->input('vehiculo'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s10;
            $c10 = $request->input('vehiculo');
            $filtro = $filtro . "Vehículo=" . $request->input('vehiculo') . ", ";
        }

        if ($request->filled('fuente')) {
            if (empty($ids)) {
                if ($request->input('fuente') == "CRM") {
                    $s11 = Servicio::where('users_id', '<>', 112)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } elseif ($request->input('fuente') == "IVR") {
                    $s11 = Servicio::where('users_id', 112)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s11 = Servicio::whereNull('users_id')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                }
            } else {
                if ($request->input('fuente') == "CRM") {
                    $s11 = Servicio::where('users_id', '<>', 112)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                } elseif ($request->input('fuente') == "IVR") {
                    $s11 = Servicio::where('users_id', 112)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                } else {
                    $s11 = Servicio::whereNull('users_id')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s11;
            $c11 = $request->input('fuente');
            $filtro = $filtro . "Fuente=" . $request->input('fuente') . ", ";
        }

        if ($request->filled('estado')) {
            if (empty($ids)) {
                $s11 = Servicio::where('estado', $request->input('estado'))->pluck('id')->toArray();
            } else {
                $s11 = Servicio::where('estado', $request->input('estado'))->whereIn('id', $ids)->pluck('id')->toArray();
            }
            $ids = $s11;
            $c12 = $request->input('estado');
            $filtro = $filtro . "Estado=" . $request->input('estado') . ", ";
        }

        if (count($ids) > 200) {
            $ids = array_slice($ids, count($ids) - 200);
        }
        $servicios = Servicio::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }, 'cliente'])->whereIn('id', $ids)->orderBy('id', 'DESC')->paginate(20)->appends($request->query());
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('servicios.finalizados', compact('servicios', 'usuario', 'filtro', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12'));
    }

    public function exportarfinalizados(Request $request)
    {
        $ids = [];
        set_time_limit(0);
        DB::statement("SET in_predicate_conversion_threshold=0");
        if (
            empty($request->input('id')) && empty($request->input('fecha')) && empty($request->input('cliente')) && empty($request->input('telefono')) && empty($request->input('direccion')) && empty($request->input('fechaprogramada')) &&
            empty($request->input('pago')) && empty($request->input('asignacion')) && empty($request->input('conductor')) && empty($request->input('vehiculo')) && empty($request->input('fuente')) && empty($request->input('estado'))
        ) {
            $ids  = Servicio::where(function ($q) {
                $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
            })->pluck('id')->toArray();
        } else {
            if ($request->filled('id')) {
                $s1 = Servicio::where('id', $request->input('id'))->where(function ($q) {
                    $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                })->pluck('id')->toArray();
                $ids = $s1;
            }

            if ($request->filled('fecha')) {
                $dates = explode(" - ", $request->input('fecha'));
                if (empty($ids)) {
                    $s2 = Servicio::whereBetween('fecha', $dates)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s2 = Servicio::whereBetween('fecha', $dates)->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s2;
            }

            if ($request->filled('cliente')) {
                $s3 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                            $s3[] = $servicio->id;
                        }
                    }
                } else {
                    $servicios2 = Servicio::with('cliente')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                            $s3[] = $servicio->id;
                        }
                    }
                }
                $ids = $s3;
            }

            if ($request->filled('telefono')) {
                $s4 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                            $s4[] = $servicio->id;
                        }
                    }
                } else {
                    $servicios2 = Servicio::with('cliente')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                            $s4[] = $servicio->id;
                        }
                    }
                }
                $ids = $s4;
            }

            if ($request->filled('direccion')) {
                if (empty($ids)) {
                    $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s5;
            }

            if ($request->filled('fechaprogramada')) {
                if ($request->input('fechaprogramada') == "inmediato") {
                    if (empty($ids)) {
                        $s6 = Servicio::whereNull('fechaprogramada')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->pluck('id')->toArray();
                    } else {
                        $s6 = Servicio::whereNull('fechaprogramada')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->whereIn('id', $ids)->pluck('id')->toArray();
                    }
                } else {
                    if (empty($ids)) {
                        $s6 = Servicio::whereNotNull('fechaprogramada')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->pluck('id')->toArray();
                    } else {
                        $s6 = Servicio::whereNotNull('fechaprogramada')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->whereIn('id', $ids)->pluck('id')->toArray();
                    }
                }
                $ids = $s6;
            }

            if ($request->filled('pago')) {
                if (empty($ids)) {
                    $s7 = Servicio::where('pago', $request->input('pago'))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s7 = Servicio::where('pago', $request->input('pago'))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s7;
            }

            if ($request->filled('asignacion')) {
                if (empty($ids)) {
                    $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s8;
            }

            if ($request->filled('conductor')) {
                $s9 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }])->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto')->get();
                    foreach ($servicios2 as $servicio) {
                        if ($servicio->cuentac != null) {
                            if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                                $s9[] = $servicio->id;
                            }
                        }
                    }
                } else {
                    $servicios2 = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }])->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if ($servicio->cuentac != null) {
                            if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                                $s9[] = $servicio->id;
                            }
                        }
                    }
                }
                $ids = $s9;
            }

            if ($request->filled('vehiculo')) {
                if (empty($ids)) {
                    $s10 = Servicio::where('placa', strval($request->input('vehiculo')))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->pluck('id')->toArray();
                } else {
                    $s10 = Servicio::where('placa', $request->input('vehiculo'))->where(function ($q) {
                        $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                    })->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s10;
            }

            if ($request->filled('fuente')) {
                if (empty($ids)) {
                    if ($request->input('fuente') == "CRM") {
                        $s11 = Servicio::where('users_id', '<>', 112)->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->pluck('id')->toArray();
                    } elseif ($request->input('fuente') == "IVR") {
                        $s11 = Servicio::where('users_id', 112)->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->pluck('id')->toArray();
                    } else {
                        $s11 = Servicio::whereNull('users_id')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->pluck('id')->toArray();
                    }
                } else {
                    if ($request->input('fuente') == "CRM") {
                        $s11 = Servicio::where('users_id', '<>', 112)->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->whereIn('id', $ids)->pluck('id')->toArray();
                    } elseif ($request->input('fuente') == "IVR") {
                        $s11 = Servicio::where('users_id', 112)->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->whereIn('id', $ids)->pluck('id')->toArray();
                    } else {
                        $s11 = Servicio::whereNull('users_id')->where(function ($q) {
                            $q->where('estado', 'Finalizado')->orWhere('estado', 'Cancelado')->orWhere('estado', 'No vehiculo')->orWhere('estado', 'Cancelado devuelto');
                        })->whereIn('id', $ids)->pluck('id')->toArray();
                    }
                }
                $ids = $s11;
            }

            if ($request->filled('estado')) {
                if (empty($ids)) {
                    $s11 = Servicio::where('estado', $request->input('estado'))->pluck('id')->toArray();
                } else {
                    $s11 = Servicio::where('estado', $request->input('estado'))->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $ids = $s11;
            }
        }

        $servicios = collect([]);
        foreach (array_chunk($ids, 500) as $parte) {
            $servicios = $servicios->concat(Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente', 'operador_asignacion'])->whereIn('id', $parte)->get());
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:X1");
        $sheet->setCellValue("A1", "Servicios Finalizados");
        $style = array('alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER,));
        $sheet->getStyle("A1:X1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Cliente");
        $sheet->setCellValue("D2", "Usuarios");
        $sheet->setCellValue("E2", "Teléfono");
        $sheet->setCellValue("F2", "Dir. Origen");
        $sheet->setCellValue("G2", "Beneficiario");
        $sheet->setCellValue("H2", "Centro de Costo");
        $sheet->setCellValue("I2", "Actividad");
        $sheet->setCellValue("J2", "Destino");
        $sheet->setCellValue("K2", "Despacho");
        $sheet->setCellValue("L2", "Pago");
        $sheet->setCellValue("M2", "Valera");
        $sheet->setCellValue("N2", "Código vale");
        $sheet->setCellValue("O2", "Contraseña vale");
        $sheet->setCellValue("P2", "Unds/Mins/Ruta");
        $sheet->setCellValue("Q2", "Valor servicio");
        $sheet->setCellValue("R2", "Asignación");
        $sheet->setCellValue("S2", "Conductor");
        $sheet->setCellValue("T2", "Vehiculo");
        $sheet->setCellValue("U2", "Observaciones");
        $sheet->setCellValue("V2", "Estado");
        $sheet->setCellValue("W2", "Operador");
        $sheet->setCellValue("X2", "Operador cambio conductor");
        $sheet->getStyle("A1:X2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($servicios as $servicio) {
            $sheet->setCellValue("A" . $indice, $servicio->id);
            $sheet->setCellValue("B" . $indice, $servicio->fecha);
            $sheet->setCellValue("C" . $indice, $servicio->cliente->nombres);
            $sheet->setCellValue("D" . $indice, $servicio->usuarios);
            $sheet->setCellValue("E" . $indice, $servicio->cliente->telefono);
            $sheet->setCellValue("F" . $indice, $servicio->direccion);

            if ($servicio->vale != null) {
                $sheet->setCellValue("G" . $indice, $servicio->vale->beneficiario);
                $sheet->setCellValue("H" . $indice, $servicio->vale->centrocosto);
                $sheet->setCellValue("I" . $indice, $servicio->vale->referenciado);
                $sheet->setCellValue("J" . $indice, $servicio->vale->destino);
            } else {
                $sheet->setCellValue("G" . $indice, "");
                $sheet->setCellValue("H" . $indice, "");
                $sheet->setCellValue("I" . $indice, "");
                $sheet->setCellValue("J" . $indice, "");
            }

            if ($servicio->fechaprogramada == null) {
                $sheet->setCellValue("K" . $indice, "Inmediato");
            } else {
                $sheet->setCellValue("K" . $indice, "Programado");
            }

            $sheet->setCellValue("L" . $indice, $servicio->pago);

            if ($servicio->vale != null) {
                $sheet->setCellValue("M" . $indice, $servicio->vale->valera->nombre);
                $sheet->setCellValue("N" . $indice, $servicio->vale->codigo);
                $sheet->setCellValueExplicit("O" . $indice, strtoupper($servicio->vale->clave), DataType::TYPE_STRING);
                $sheet->setCellValue("P" . $indice, $servicio->unidades . " " . $servicio->cobro);
                $sheet->setCellValue("Q" . $indice, $servicio->valor);
            } else {
                $sheet->setCellValue("M" . $indice, "");
                $sheet->setCellValue("N" . $indice, "");
                $sheet->setCellValue("O" . $indice, "");
                $sheet->setCellValue("P" . $indice, "");
                $sheet->setCellValue("Q" . $indice, "");
            }

            if ($servicio->reasignado == '1') {
                $sheet->setCellValue("R" . $indice, "Normal, Directo");
            } else {
                $sheet->setCellValue("R" . $indice, $servicio->asignacion);
            }

            if ($servicio->cuentac == null) {
                $sheet->setCellValue("S" . $indice, "Sin asignar");
            } else {
                $sheet->setCellValue("S" . $indice, $servicio->cuentac->conductor->NOMBRE);
            }

            if ($servicio->placa == null) {
                $sheet->setCellValue("T" . $indice, "Sin asignar");
            } else {
                $sheet->setCellValue("T" . $indice, $servicio->placa);
            }

            $sheet->setCellValue("U" . $indice, $servicio->observaciones);
            $sheet->setCellValue("V" . $indice, $servicio->estado);
            if ($servicio->operador != null) {
                $sheet->setCellValue("W" . $indice, $servicio->operador->usuario);
            }
            if ($servicio->operador_asignacion != null) {
                $sheet->setCellValue("X" . $indice, $servicio->operador_asignacion->usuario);
            }
            $indice++;
        }

        foreach (range('A', 'X') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Servicios.xlsx');
        $archivo = file_get_contents('Servicios.xlsx');
        unlink('Servicios.xlsx');

        return base64_encode($archivo);
    }

    public function exportarcurso(Request $request)
    {

        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == 'ID') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('id', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Fecha') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->whereDate('fecha', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Cliente') {
                $servicios = [];
                $servicios2 = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->nombres, $filtro[1]) != false) {
                        $servicios[] = $servicio;
                    }
                }
            } elseif ($filtro[0] == 'Teléfono') {
                $servicios = [];
                $servicios2 = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cliente->telefono, $filtro[1]) != false) {
                        $servicios[] = $servicio;
                    }
                }
            } elseif ($filtro[0] == 'Dir. Origen') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('direccion', 'like', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Despacho') {
                if ($filtro[1] == "inmediato") {
                    $servicios = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente'])->whereNull('fechaprogramada')->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
                } else {
                    $servicios = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente'])->whereNotNull('fechaprogramada')->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
                }
            } elseif ($filtro[0] == 'Modo pago') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('pago', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Asignación') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('asignacion', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Conductor') {
                $servicios = [];
                $servicios2 = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
                foreach ($servicios2 as $servicio) {
                    if (stristr($servicio->cuentac->conductor->NOMBRE, $filtro[1]) != false) {
                        $servicios[] = $servicio;
                    }
                }
            } elseif ($filtro[0] == 'Vehiculo') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('placa', $filtro[1])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            } elseif ($filtro[0] == 'Estado') {
                $servicios = Servicio::with(['cuentac' => function ($q) {
                    $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
            }
        } else {
            $servicios = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'cliente'])->where('estado', 'Pendiente')->orWhere('estado', 'Asignado')->orWhere('estado', 'En curso')->orderBy('id', 'DESC')->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("D1:H1");
        $sheet->setCellValue("D1", "Servicios en Curso");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("D1:H1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Cliente");
        $sheet->setCellValue("D2", "Teléfono");
        $sheet->setCellValue("E2", "Dir. Origen");
        $sheet->setCellValue("F2", "Despacho");
        $sheet->setCellValue("G2", "Pago");
        $sheet->setCellValue("H2", "Asignación");
        $sheet->setCellValue("I2", "Conductor");
        $sheet->setCellValue("J2", "Vehiculo");
        $sheet->setCellValue("K2", "Estado");
        $sheet->getStyle("A1:K2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($servicios as $servicio) {
            $sheet->setCellValue("A" . $indice, $servicio->id);
            $sheet->setCellValue("B" . $indice, $servicio->fecha);
            $sheet->setCellValue("C" . $indice, $servicio->cliente->nombres);
            $sheet->setCellValue("D" . $indice, $servicio->cliente->telefono);
            $sheet->setCellValue("E" . $indice, $servicio->direccion);

            if ($servicio->horaprogramada == null) {
                $sheet->setCellValue("F" . $indice, "Inmediato");
            } else {
                $sheet->setCellValue("F" . $indice, $servicio->horaprogramada);
            }

            $sheet->setCellValue("G" . $indice, $servicio->pago);
            $sheet->setCellValue("H" . $indice, $servicio->asignacion);

            if ($servicio->cuentac == null) {
                $sheet->setCellValue("I" . $indice, "Sin asignar");
            } else {
                $sheet->setCellValue("I" . $indice, $servicio->cuentac->conductor->NOMBRE);
            }

            if ($servicio->placa == null) {
                $sheet->setCellValue("J" . $indice, "Sin asignar");
            } else {
                $sheet->setCellValue("J" . $indice, $servicio->placa);
            }

            $sheet->setCellValue("K" . $indice, $servicio->estado);
            $indice++;
        }

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Servicios.xlsx');
        $archivo = file_get_contents('Servicios.xlsx');
        unlink('Servicios.xlsx');

        return base64_encode($archivo);
    }

    public function getconductor(Request $request)
    {
        DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::beginTransaction();
        try {
            $servicio = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }])->find($request->input('id'));
            if ($servicio->estado == "Asignado") {
                $servicio->nombrec = $servicio->cuentac->conductor->NOMBRE;
                DB::commit();

                return json_encode($servicio);
            } else {
                throw new Exception("No asignado");
            }
        } catch (Exception $th) {
            DB::rollBack();

            return json_encode([]);
        }
    }

    public function liberarservicio(Request $request)
    {
        $servicio = Servicio::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->find($request->input('id'));
        if ($servicio->estado == "Pendiente") {
            $servicio->estado = "Libre";
            $servicio->save();
        } else if ($servicio->estado == "Asignado") {
            $servicio->nombrec = $servicio->cuentac->conductor->NOMBRE;
        }

        return json_encode($servicio);
    }

    public function stopservicio(Request $request)
    {
        try {
            $servicio = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }, 'vale', 'valeav'])->find($request->input('id'));
            if ($servicio->estado == "Libre" || $servicio->estado == "Pendiente") {
                $servicio->estado = "No vehiculo";
                $servicio->save();

                if ($servicio->vale != null) {
                    if ($servicio->vale->centrocosto != null) {
                        $servicio->vale->estado = "Asignado";
                    } else {
                        $servicio->vale->estado = "Libre";
                    }
                    $servicio->vale->servicios_id = null;
                    $servicio->vale->save();
                } elseif ($servicio->valeav != null) {
                    $servicio->valeav->estado = "Libre";
                    $servicio->valeav->servicios_id = null;
                    $servicio->valeav->save();
                }
            } else if ($servicio->estado == "Asignado") {
                $servicio->nombrec = $servicio->cuentac->conductor->NOMBRE;
            }

            return json_encode($servicio);
        } catch (QueryException $e) {
            $servicio = Servicio::with(['cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                    $r->select('CONDUCTOR', 'NOMBRE');
                }]);
            }])->find($request->input('id'));
            if ($servicio->estado == "Asignado") {
                $servicio->nombrec = $servicio->cuentac->conductor->NOMBRE;
            }

            return json_encode($servicio);
        }
    }

    public function ajaxCurso()
    {

        $antes = Carbon::now()->subSeconds(10)->format('Y-m-d H:i:s');
        $ahora = Carbon::now();
        $ahoraFormat = $ahora->format('Y-m-d H:i:s');
        $servicios = new stdClass();
        $servicios->pen = Servicio::where('estado', 'Pendiente')->count();
        $servicios->asi = Servicio::where('estado', 'Asignado')->count();
        $servicios->cur = Servicio::where('estado', 'En curso')->count();
        $servicios->cancelados = Servicio::select('id', 'users_id', 'estado', 'fecha')->whereHas('cancelacion', function ($q) use ($antes, $ahoraFormat) {
            $q->whereBetween('fecha', [$antes, $ahoraFormat]);
        })->get();
        //$servicios->ivr = Servicio::select('id', 'users_id', 'estado', 'fecha')->where('users_id', 112)->whereHas('cancelacion', function($q) use($antes, $ahoraFormat){$q->whereBetween('fecha', [$antes, $ahoraFormat]);})->get();
        if (count($servicios->cancelados) > 0) {
        } else {
            //$servicios->electronicos = Servicio::select('id', 'users_id', 'estado', 'fecha')->where('pago', 'Vale electrónico')->whereHas('cancelacion', function($q) use($antes, $ahoraFormat){$q->whereBetween('fecha', [$antes, $ahoraFormat]);})->get();
            $perdidos = [];
            $programados = Servicio::select('id', 'fechaprogramada', 'estado')->where('fechaprogramada', '<', $ahoraFormat)->where('estado', 'Pendiente')->get();
            foreach ($programados as $programado) {
                $fecha = Carbon::parse($programado->fechaprogramada);
                $dif = $fecha->diffInMinutes($ahora);
                if ($dif > 15 && $dif < 17) {
                    $perdidos[] = $programado;
                }
            }
            $servicios->perdidos = $perdidos;
        }

        return json_encode($servicios);
    }

    public function filtrarEmpresa(Request $request)
    {
        if (
            empty($request->input('id')) && empty($request->input('fecha')) && empty($request->input('cliente')) && empty($request->input('telefono')) && empty($request->input('direccion')) && empty($request->input('fechaprogramada')) &&
            empty($request->input('pago')) && empty($request->input('asignacion')) && empty($request->input('conductor')) && empty($request->input('vehiculo'))
        ) {
            return redirect('/servicios/finalizados');
        }

        $svale = "vale";

        if (Auth::user()->roles_id == 4) {
            if (Auth::user()->id == 119) {
                $valeras = Valera::where('cuentase_id', 5)->get();
            } else {
                $cuentae =  Auth::user()->cuentae;
                $valeras = Valera::where('cuentase_id', $cuentae->id)->get();
                if ($cuentae->agencia_tercero_TERCERO == 435) {
                    $svale = "valeav";
                }
            }
        } else {
            if (Auth::user()->idtercero != null) {
                $tercero = Tercero::where('TERCERO', Auth::user()->idtercero)->first();
            } else {
                $tercero = Auth::user()->tercero;
            }
            $terce = $tercero->TERCERO;
            $valeras = Valera::whereHas('cuentae', function ($q) use ($terce) {
                $q->whereHas('agencia', function ($r) use ($terce) {
                    $r->where('agencia_tercero_TERCERO', $terce);
                });
            })->get();
        }

        $ids = [];
        $filtro = "";
        $c1 = "";
        $c2 = "";
        $c3 = "";
        $c4 = "";
        $c5 = "";
        $c6 = "";
        $c7 = "";
        $c8 = "";
        $c9 = "";
        $c10 = "";
        $c11 = "";

        if ($request->filled('id')) {
            $v1 = [];
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $s1 = Servicio::where('estado', 'Finalizado')->whereHas(
                    $svale,
                    function ($q) use ($idvalera) {
                        $q->whereHas(
                            'valera',
                            function ($r) use ($idvalera) {
                                $r->where('id', $idvalera);
                            }
                        );
                    }
                )->where('id', $request->input('id'))->pluck('id')->toArray();
                $v1 = array_merge($v1, $s1);
            }
            $ids = $v1;
            $c1 = $request->input('id');
            $filtro = $filtro . "ID=" . $request->input('id') . ", ";
        }

        if ($request->filled('fecha')) {
            $dates = explode(" - ", $request->input('fecha'));
            $v2 = [];
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if (empty($ids)) {
                    $s2 = Servicio::where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereBetween('fecha', $dates)->pluck('id')->toArray();
                } else {
                    $s2 = Servicio::where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereBetween('fecha', $dates)->whereIn('id', $ids)->pluck('id')->toArray();
                }
                $v2 = array_merge($v2, $s2);
            }
            $ids = $v2;
            $c2 = $request->input('fecha');
            $filtro = $filtro . "Fecha=" . $request->input('fecha') . ", ";
        }

        if ($request->filled('cliente')) {
            $v3 = [];
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $s3 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                            $s3[] = $servicio->id;
                        }
                    }
                } else {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->nombres, $request->input('cliente')) != false) {
                            $s3[] = $servicio->id;
                        }
                    }
                }
                $v3 = array_merge($v3, $s3);
            }
            $ids = $v3;
            $c3 = $request->input('cliente');
            $filtro = $filtro . "Cliente=" . $request->input('cliente') . ", ";
        }

        if ($request->filled('telefono')) {
            $v4 = [];
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $s4 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                            $s4[] = $servicio->id;
                        }
                    }
                } else {
                    $servicios2 = Servicio::with('cliente')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if (stristr($servicio->cliente->telefono, $request->input('telefono')) != false) {
                            $s4[] = $servicio->id;
                        }
                    }
                }
                $v4 = array_merge($v4, $s4);
            }
            $ids = $v4;
            $c4 = $request->input('telefono');
            $filtro = $filtro . "Teléfono=" . $request->input('telefono') . ", ";
        }

        if ($request->filled('direccion')) {
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if (empty($ids)) {
                    $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();
                } else {
                    $s5 = Servicio::where('direccion', 'like', '%' . $request->input('direccion') . '%')->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s5;
            $c5 = $request->input('direccion');
            $filtro = $filtro . "Dir. Origen=" . $request->input('direccion') . ", ";
        }

        if ($request->filled('fechaprogramada')) {
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if ($request->input('fechaprogramada') == "inmediato") {
                    if (empty($ids)) {
                        $s6 = Servicio::whereNull('fechaprogramada')->where('estado', 'Finalizado')->whereHas(
                            $svale,
                            function ($q) use ($idvalera) {
                                $q->whereHas(
                                    'valera',
                                    function ($r) use ($idvalera) {
                                        $r->where('id', $idvalera);
                                    }
                                );
                            }
                        )->pluck('id')->toArray();
                    } else {
                        $s6 = Servicio::whereNull('fechaprogramada')->where('estado', 'Finalizado')->whereHas(
                            $svale,
                            function ($q) use ($idvalera) {
                                $q->whereHas(
                                    'valera',
                                    function ($r) use ($idvalera) {
                                        $r->where('id', $idvalera);
                                    }
                                );
                            }
                        )->whereIn('id', $ids)->pluck('id')->toArray();
                    }
                } else {
                    if (empty($ids)) {
                        $s6 = Servicio::whereNotNull('fechaprogramada')->where('estado', 'Finalizado')->whereHas(
                            $svale,
                            function ($q) use ($idvalera) {
                                $q->whereHas(
                                    'valera',
                                    function ($r) use ($idvalera) {
                                        $r->where('id', $idvalera);
                                    }
                                );
                            }
                        )->pluck('id')->toArray();
                    } else {
                        $s6 = Servicio::whereNotNull('fechaprogramada')->where('estado', 'Finalizado')->whereHas(
                            $svale,
                            function ($q) use ($idvalera) {
                                $q->whereHas(
                                    'valera',
                                    function ($r) use ($idvalera) {
                                        $r->where('id', $idvalera);
                                    }
                                );
                            }
                        )->whereIn('id', $ids)->pluck('id')->toArray();
                    }
                }
            }
            $ids = $s6;
            $c6 = $request->input('fechaprogramada');
            $filtro = $filtro . "Despacho=" . $request->input('fechaprogramada') . ", ";
        }

        /*if($request->filled('pago')){
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if(empty($ids)){
                    $s7 = Servicio::where('pago', $request->input('pago'))->where('estado', 'Finalizado')->whereHas($svale, 
                    function($q) use($idvalera) {$q->whereHas('valera', 
                        function($r) use($idvalera){$r->where('id', $idvalera);});})->pluck('id')->toArray();
                }else{
                    $s7 = Servicio::where('pago', $request->input('pago'))->where('estado', 'Finalizado')->whereHas($svale, 
                    function($q) use($idvalera) {$q->whereHas('valera', 
                        function($r) use($idvalera){$r->where('id', $idvalera);});})->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }          
            $ids = $s7;       
            $c7 = $request->input('pago');
            $filtro = $filtro . "Modo pago=" . $request->input('pago') . ", ";
        }*/

        if ($request->filled('asignacion')) {
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if (empty($ids)) {
                    $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();
                } else {
                    $s8 = Servicio::where('asignacion', $request->input('asignacion'))->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s8;
            $c8 = $request->input('asignacion');
            $filtro = $filtro . "Asignación=" . $request->input('asignacion') . ", ";
        }

        if ($request->filled('conductor')) {
            $v9 = [];
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                $s9 = [];
                if (empty($ids)) {
                    $servicios2 = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }])->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->get();
                    foreach ($servicios2 as $servicio) {
                        if ($servicio->cuentac != null) {
                            if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                                $s9[] = $servicio->id;
                            }
                        }
                    }
                } else {
                    $servicios2 = Servicio::with(['cuentac' => function ($q) {
                        $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                            $r->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }])->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->get();
                    foreach ($servicios2 as $servicio) {
                        if ($servicio->cuentac != null) {
                            if (stristr($servicio->cuentac->conductor->NOMBRE, $request->input('conductor')) != false) {
                                $s9[] = $servicio->id;
                            }
                        }
                    }
                }
                $v9 = array_merge($v9, $s9);
            }
            $ids = $v9;
            $c9 = $request->input('conductor');
            $filtro = $filtro . "Conductor=" . $request->input('conductor') . ", ";
        }

        if ($request->filled('vehiculo')) {
            foreach ($valeras as $valera) {
                $idvalera = $valera->id;
                if (empty($ids)) {
                    $s10 = Servicio::where('placa', $request->input('vehiculo'))->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->pluck('id')->toArray();
                } else {
                    $s10 = Servicio::where('placa', $request->input('vehiculo'))->where('estado', 'Finalizado')->whereHas(
                        $svale,
                        function ($q) use ($idvalera) {
                            $q->whereHas(
                                'valera',
                                function ($r) use ($idvalera) {
                                    $r->where('id', $idvalera);
                                }
                            );
                        }
                    )->whereIn('id', $ids)->pluck('id')->toArray();
                }
            }
            $ids = $s10;
            $c10 = $request->input('vehiculo');
            $filtro = $filtro . "Vehículo=" . $request->input('vehiculo') . ", ";
        }

        $servicios = Servicio::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }, 'cliente'])->whereIn('id', $ids)->orderBy('id', 'DESC')->paginate(20)->appends($request->query());

        return view('servicios.finalizadosempresa', compact('servicios', 'filtro', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10'));
    }


    public function enviarSMS($numero, $nombre, $conductor, $placa, $fecha)
    {
        $texto = "Sr(a) " . strtoupper($nombre) . "; TAXCENTER le informa que el señor " . $conductor . " con el vehículo " . $placa . " será su conductor para el servicio del día " . $fecha;
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
                    'text' => $texto,
                    'sender_id' => 'gOwn',
                    'sender_id_value' => '573183731974'
                ]),
                'ignore_errors' => true
            ]])
        );

        $logFile = fopen(storage_path() . DIRECTORY_SEPARATOR . "SMS.txt", 'a') or die("Error creando archivo");

        if ($connection) {
            //$response = json_decode(stream_get_contents($connection));        
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . stream_get_contents($connection) . " Número: " . $numero) or die("Error escribiendo en el archivo");
            fclose($connection);
        } else {
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . "Falla conexión: " . $numero) or die("Error escribiendo en el archivo");
        }
        fclose($logFile);
    }

    public function actualizarCECOFinalizado(Request $request)
    {
        //Recuperamos el id de la uri el servicio
        $idServicio = $request->route('servicio');
        $idPasajero = $request->route('pasajero');


        $validatedData = $request->validate([
            'sub_cuenta' => 'required|string|max:10',
            'affe' => 'required|string|max:15',
        ]);

        // Recupera los datos validados
        $sub_cuenta = $validatedData['sub_cuenta'];
        $affe = $validatedData['affe'];

        try {
            // obtenemos al objeto pasajero
            $CECO = ServicioPasajeros::where('servicios_id', $idServicio)
                ->where('pasajeros_id', $idPasajero)
                ->first();

            if ($CECO) {
                $CECO->sub_cuenta = $sub_cuenta;
                $CECO->affe = $affe;
                $CECO->save();
                return response()->json([
                    'message' => 'Se ha actualizado el centro de costo exitosamente',
                    'footer' => "Nuevo CECO: Sub_Cuenta:  {$sub_cuenta}, Affe: {$affe}",
                    'sub_cuenta' => $sub_cuenta,
                    'affe' => $affe
                ], 200);
            } else {
                return response()->json(['message' => 'No se encontró registros del pasajero'], 404);
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            return response()->json(['message' => 'Ocurrió un error al actualizar el pasajero', 'error' => $e->getMessage()], 500);
        }
    }
}
