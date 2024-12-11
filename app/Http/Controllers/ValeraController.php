<?php

namespace App\Http\Controllers;

use App\Models\Agencia_tercero;
use App\Models\Beneficiario;
use App\Models\Cancelacion;
use App\Models\Cliente;
use App\Models\Cuentae;
use App\Models\CuentasexUser;
use App\Models\Mensaje;
use App\Models\NovedadMajorel;
use App\Models\Pasajero;
use App\Models\Pasajerosxruta;
use App\Models\Programacion;
use App\Models\Registro;
use App\Models\Ruta;
use App\Models\Servicio;
use App\Models\Tercero;
use App\Models\User;
use App\Models\Vale;
use App\Models\Vale_servicio;
use App\Models\Valeav;
use App\Models\Valera;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use SoapClient;
use SoapFault;
use stdClass;

class ValeraController extends Controller
{
    public function index()
    {
        if (Auth::user()->roles_id == 4) {
            if (Auth::user()->id == 119) {
                $agencia = Agencia_tercero::where('TERCERO', 870)->where('CODIGO', 1768)->first();
                $tercero = $agencia->TERCERO;
                $codigos = [$agencia->CODIGO];
            } else {
                $cuentase = Auth::user()->cuentase;
                $tercero = $cuentase[0]->agencia_tercero_TERCERO;
                $codigos = [];
                foreach ($cuentase as $cuentae) {
                    $codigos[] = $cuentae->agencia_tercero_CODIGO;
                }
                //$agencia = Agencia_tercero::where('TERCERO', Auth::user()->cuentae->agencia_tercero_TERCERO)->where('CODIGO', Auth::user()->cuentae->agencia_tercero_CODIGO)->first();
            }

            /*$ter = $agencia->TERCERO;
            $cod = $agencia->CODIGO;*/

            $valeras = Valera::with('cuentae.agencia')->whereHas('cuentae', function ($q) use ($tercero, $codigos) {
                $q->where('agencia_tercero_TERCERO', $tercero)->whereIn('agencia_tercero_CODIGO', $codigos);
            })->get();

            return view('valeras.empresa', compact('valeras'));
        } else if (Auth::user()->roles_id == 5) {
            if (Auth::user()->idtercero != null) {
                $tercero = Tercero::where('TERCERO', Auth::user()->idtercero)->first();
            } else {
                $tercero = Auth::user()->tercero;
            }
            $terce = $tercero;
            $valeras = Valera::with('cuentae.agencia')->whereHas('cuentae', function ($q) use ($terce) {
                $q->where('agencia_tercero_TERCERO', $terce->TERCERO);
            })->get();

            return view('valeras.empresa', compact('valeras'));
        } else {
            $valeras = Valera::with('cuentae.agencia')->paginate(15);
            $fecha = Carbon::now();
            /*foreach ($valeras as $valera) {
                if ($fecha > $valera->fin || $fecha < $valera->inicio) {
                    $valera->estado = 0;
                    $valera->save();
                } else {
                    $valera->estado = 1;
                    $valera->save();
                }
            }*/
            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

            return view('valeras.lista', compact('valeras', 'usuario'));
        }
    }

    public function nuevo()
    {

        $valera = new Valera();
        $empresas = Tercero::has('contratovale')->where('SW_NUEVO_CRM', "1")->orderBy('RAZON_SOCIAL', 'ASC')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.form', ['valera' => $valera, 'usuario' => $usuario, 'empresas' => $empresas, 'method' => 'post', 'route' => ['valeras.agregar']]);
    }

    public function nuevoerror($error)
    {

        $valera = new Valera();
        $empresas = Tercero::has('contratovale')->orderBy('RAZON_SOCIAL', 'ASC')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.error', ['valera' => $valera, 'error' => $error, 'usuario' => $usuario, 'empresas' => $empresas, 'method' => 'post', 'route' => ['valeras.agregar']]);
    }

    public function store(Request $request)
    {
        $agencia = Agencia_tercero::with('cuentae')->where('TERCERO', $request->input('empresa'))->where('CODIGO', $request->input('agencia'))->first();
        if ($agencia->cuentae == null) {
            $cuentae = new Cuentae();
            $cuentae->saldo = 0;
            $cuentae->estado = 1;
            $cuentae->agencia_tercero_TERCERO = $agencia->TERCERO;
            $cuentae->agencia_tercero_CODIGO =  $agencia->CODIGO;
        } else {
            $cuentae = $agencia->cuentae;
        }

        $user = User::where('identificacion', $agencia->NRO_IDENTIFICACION)->first();
        if ($user == null) {
            $user = new User();
            $user->nombres = $agencia->NOMBRE;
            $user->usuario = $agencia->NRO_IDENTIFICACION;
            $user->identificacion = $agencia->NRO_IDENTIFICACION;
            $user->password = Hash::make($agencia->NRO_IDENTIFICACION);
            $user->estado = 1;
            $user->roles_id = 4;
        }

        $valera = new Valera();
        $valera->nombre = $request->input('nombre');
        $valera->fecha = Carbon::now('-05:00');
        $valera->inicio = $request->input('inicio');
        $valera->fin = $request->input('fin');
        $valera->inferior = $request->input('inferior');
        $valera->superior = $request->input('superior');
        $valera->estado = $request->input('estado');

        $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

        try {
            $client = new SoapClient($url, ['exceptions' => true]);
            $result = $client->registrarTicket();
            $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $agencia->NRO_IDENTIFICACION, "nombreValera" => $valera->nombre, "estadoValera" => $valera->estado, "limiteInferior" => $valera->inferior, "limiteSuperior" => $valera->superior, "fechaInicio" => str_replace("-", "", $valera->inicio), "fechaFin" => str_replace("-", "", $valera->fin));
            $peticion = $client->registrarValera($parametros);

            if ($peticion->registrarValeraReturn->codigoError != "0000") {
                return redirect('valeras/nuevo/' . $peticion->registrarValeraReturn->mensajeError);
            }
        } catch (SoapFault $e) {
            return redirect('valeras/nuevo/' . $e->getMessage());
        } catch (Exception $e) {
            return redirect('valeras/nuevo/' . $e->getMessage());
        }

        $user->save();
        //$cuentae->users_id = $user->id;
        $cuentae->save();

        $CuentasexUser = new CuentasexUser();
        $CuentasexUser->users_id = $user->id;
        $CuentasexUser->cuentase_id = $cuentae->id;
        $CuentasexUser->save();

        $valera->cuentase_id = $cuentae->id;
        $valera->save();

        if ($cuentae->agencia_tercero_TERCERO == 435) {
            for ($i = $valera->inferior; $i <= $valera->superior; $i++) {
                $vale = new Valeav();
                $vale->estado = "Libre";
                $vale->codigo = $i;
                $vale->valeras_id = $valera->id;
                $vale->save();
            }
        } else {
            for ($i = $valera->inferior; $i <= $valera->superior; $i++) {
                $vale = new Vale();
                $vale->estado = "Libre";
                $vale->codigo = $i;
                $vale->clave = $this->genclave(4);
                $vale->valeras_id = $valera->id;
                $vale->save();
            }
        }

        if ($request->filled('emails')) {
            $emails = $request->input('emails');
            $nombres = $request->input('nombres');
            $celulares = $request->input('celulares');
            for ($i = 0; $i < count($emails); $i++) {
                if ($celulares[$i] != null) {
                    $beneficiario = new Beneficiario();
                    $beneficiario->nombre = $nombres[$i];
                    $beneficiario->email = $emails[$i];
                    $beneficiario->celular = $celulares[$i];
                    $beneficiario->valeras_id = $valera->id;
                    $beneficiario->save();
                }
            }
        }

        return redirect('valeras');
    }

    public function editar(Valera $valera)
    {

        $valera = Valera::with('cuentae.agencia', 'beneficiarios')->where('id', $valera->id)->first();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.form', ['valera' => $valera, 'usuario' => $usuario, 'method' => 'put', 'route' => ['valeras.actualizar', $valera->id]]);
    }

    public function actualizar(Request $request, $valera)
    {
        $valera = Valera::with('cuentae.agencia', 'beneficiarios')->where('id', $valera)->first();
        $anterior = $valera->superior;
        $valera->inicio = $request->input('inicio');
        $valera->fin = $request->input('fin');
        $valera->superior = $request->input('superior');
        $valera->estado = $request->input('estado');

        if ($valera->superior >= $anterior) {
            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

            try {
                $client = new SoapClient($url, ["trace" => 1, 'exceptions' => true]);
                $result = $client->registrarTicket();
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $valera->nombre, "estadoValera" => $valera->estado, "limiteInferior" => $valera->inferior, "limiteSuperior" => $valera->superior, "fechaInicio" => str_replace("-", "", $valera->inicio), "fechaFin" => str_replace("-", "", $valera->fin));
                $peticion = $client->registrarValera($parametros);

                if ($peticion->registrarValeraReturn->codigoError != "0000") {
                    return redirect('valeras/nuevo/' . $peticion->registrarValeraReturn->mensajeError);
                }
            } catch (SoapFault $e) {
                return redirect('valeras/nuevo/' . $e->getMessage());
            }
            $valera->save();
            if ($valera->centro != null) {
                $centro = $valera->centro;
            } else {
                $centro = null;
            }

            if ($valera->cuentae->agencia_tercero_TERCERO == 435) {
                for ($i = $anterior + 1; $i <= $valera->superior; $i++) {
                    $vale = new Valeav();
                    $vale->estado = "Libre";
                    $vale->codigo = $i;
                    $vale->valeras_id = $valera->id;
                    $vale->save();
                }
            } else {
                for ($i = $anterior + 1; $i <= $valera->superior; $i++) {
                    $vale = new Vale();
                    $vale->estado = "Libre";
                    $vale->codigo = $i;
                    $vale->centrocosto = $centro;
                    $vale->clave = $this->genclave(4);
                    $vale->valeras_id = $valera->id;
                    $vale->save();
                }
            }
        }
        if ($request->input('cambios') == '1') {
            $valera->beneficiarios()->delete();
            $emails = $request->input('emails');
            $nombres = $request->input('nombres');
            $celulares = $request->input('celulares');
            for ($i = 0; $i < count($emails); $i++) {
                if ($celulares[$i] != null) {
                    $beneficiario = new Beneficiario();
                    $beneficiario->nombre = $nombres[$i];
                    $beneficiario->email = $emails[$i];
                    $beneficiario->celular = $celulares[$i];
                    $beneficiario->valeras_id = $valera->id;
                    $beneficiario->save();
                }
            }
        }

        return redirect('valeras');
    }

    public function asignarvale(Valera $valera, $vale = null)
    {
        $fecha = Carbon::now();
        //if ($fecha > $valera->inicio && $fecha < $valera->fin) {
        if (true) {
            if ($vale == null) {
                $vale = Vale::where('valeras_id', $valera->id)->where('estado', 'Libre')->first();
            } else {
                $vale = Vale::find($vale);
            }

            if ($vale == null) {
                return redirect('/valeras/' . $valera->id . '/vales')
                    ->withErrors(['sql' => 'Todos los vales libres se han agotado']);
            } else {
                $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

                return view('valeras.asignarvale', ['valera' => $valera, 'vale' => $vale, 'fecha' => $fecha, 'usuario' => $usuario, 'method' => 'post', 'route' => ['valeras.guardarvale', $vale->id]]);
            }
        } else {
            return back()
                ->withErrors(['sql' => 'No se puede asignar vales en valeras que no están vigentes']);
        }
    }

    function genclave($longitud)
    {
        $key = '';
        $pattern = '1234567890abcdef';
        $max = strlen($pattern) - 1;
        for ($i = 0; $i < $longitud; $i++) $key .= $pattern{
            mt_rand(0, $max)};
        return $key;
    }

    public function guardarvale(Request $request, Vale $vale)
    {
        if (Auth::user()->id != 119) {
            $vale->beneficiario = $request->input('beneficiario');
            $vale->referenciado = $request->input('referenciado');
            $vale->centrocosto = $request->input('centrocosto');
            $vale->destino = $request->input('destino');
            $vale->estado = "Asignado";
            $vale->fecha = $request->input('fecha');
            $vale->nombreasigna = $request->input('nombreasigna');
            $vale->save();

            if ($request->filled('pasajero')) {
                $pasajero = Pasajero::find($request->input('pasajero'));
                if ($pasajero != null) {
                    $to = $pasajero->email;
                    Mail::send('emails.notificacionPasajero', compact('vale'), function ($message) use ($to) {
                        $message->from("notificaciones@apptaxcenter.com", "Taxcenter");
                        $message->to($to);
                        $message->subject("Asignación de vale");
                    });
                }
            }
        }

        return redirect('valeras/' . $request->input('valeras_id') . '/vales');
    }

    public function vales(Valera $valera)
    {
        $valera = Valera::where('id', $valera->id)->first();
        $vales = Vale::with(['servicio' => function ($q) {
            $q->select('id', 'usuarios', 'unidades', 'cobro', 'valor')->with(['registros' => function ($r) {
                $r->select('id', 'fecha', 'servicios_id');
            }]);
        }])->where('valeras_id', $valera->id)->paginate(50);
        $libres = Vale::where('valeras_id', $valera->id)->where('estado', 'Libre')->count();
        $asignados = Vale::where('valeras_id', $valera->id)->where('estado', 'Asignado')->count();
        $visados = Vale::where('valeras_id', $valera->id)->where('estado', 'Visado')->count();
        $usados = Vale::where('valeras_id', $valera->id)->where('estado', 'Usado')->count();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        if (Auth::user()->roles_id == 4 || Auth::user()->roles_id == 5) {
            return view('valeras.valesempresa', compact('vales', 'valera', 'usuario', 'libres', 'asignados', 'visados', 'usados'));
        } else {
            return view('valeras.vales', compact('vales', 'valera', 'usuario', 'libres', 'asignados', 'visados', 'usados'));
        }
    }

    public function getagencias($tercero)
    {
        $agencias = [];
        $agencias2 = Agencia_tercero::where('TERCERO', $tercero)->get();
        $valeras = Valera::where('agencia_tercero_TERCERO', $tercero)->get();

        foreach ($agencias2 as $agencia) {
            $esta = false;
            foreach ($valeras as $valera) {
                if ($agencia->TERCERO == $valera->agencia_tercero_TERCERO && $agencia->CODIGO == $valera->agencia_tercero_CODIGO) {
                    $esta = true;
                    break;
                }
            }
            if ($esta == false) {
                $agencias[] = $agencia;
            }
        }

        return json_encode($agencias);
    }

    public function valerasxtercero($tercero)
    {
        $valeras = Valera::whereHas('cuentae', function ($q) use ($tercero) {
            $q->where('agencia_tercero_TERCERO', $tercero);
        })->where('estado', 1)
            ->get();

        return json_encode($valeras);
    }

    public function filtrar(Request $request)
    {

        if ($request->input('empresa')) {
            $empresa = $request->input('empresa');
            $valeras = Valera::whereHas('cuentae', function ($q) use ($empresa) {
                $q->whereHas('agencia', function ($r) use ($empresa) {
                    $r->where('NOMBRE', 'like', '%' . $empresa . '%');
                });
            })->paginate(20)->appends($request->query());;
            $filtro = array('Agencia', $request->input('empresa'));
        } elseif ($request->filled('nombre')) {
            $valeras = Valera::where('nombre', 'like', '%' . $request->input('nombre') . '%')->paginate(20)->appends($request->query());;
            $filtro = array('Nombre valera', $request->input('nombre'));
        } elseif ($request->filled('fecha')) {
            $valeras = Valera::whereDate('fecha', $request->input('fecha'))->paginate(20)->appends($request->query());;
            $filtro = array('Fecha creación', $request->input('fecha'));
        } elseif ($request->filled('inferior')) {
            $valeras = Valera::where('inferior', $request->input('inferior'))->paginate(20)->appends($request->query());;
            $filtro = array('Límite inferior', $request->input('inferior'));
        } elseif ($request->filled('superior')) {
            $valeras = Valera::where('superior', $request->input('superior'))->paginate(20)->appends($request->query());;
            $filtro = array('Límite superior', $request->input('superior'));
        } elseif ($request->filled('vigencia')) {
            $valeras = Valera::where('fin', '<=', $request->input('vigencia'))->paginate(20)->appends($request->query());;
            $filtro = array('Vigencia', $request->input('vigencia'));
        } elseif ($request->filled('estado')) {
            $valeras = Valera::where('estado', $request->input('estado'))->paginate(20)->appends($request->query());
            $filtro = array('Estado', $request->input('estado'));
        } else {
            return redirect('valeras');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.lista', compact('valeras', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {

        if ($request->filled('filtro')) {

            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "Agencia") {
                $empresa = $filtro[1];
                $valeras = Valera::whereHas('cuentae', function ($q) use ($empresa) {
                    $q->whereHas('agencia', function ($r) use ($empresa) {
                        $r->where('NOMBRE', 'like', '%' . $empresa . '%');
                    });
                })->get();
            } elseif ($filtro[0] == "Nombre valera") {
                $valeras = Valera::where('nombre', 'like', '%' . $filtro[1] . '%')->get();
            } elseif ($filtro[0] == "Fecha creación") {
                $valeras = Valera::whereDate('fecha', $filtro[1])->get();
            } elseif ($filtro[0] == "Límite inferior") {
                $valeras = Valera::where('inferior', $filtro[1])->get();
            } elseif ($filtro[0] == "Límite superior") {
                $valeras = Valera::where('superior', $filtro[1])->get();
            } elseif ($filtro[0] == "Vigencia") {
                $valeras = Valera::where('fin', '<=', $filtro[1])->get();
            } elseif ($filtro[0] == "Estado") {
                $valeras = Valera::where('estado', $filtro[1])->get();
            }
        } else {
            $valeras = Valera::get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("C1:D1");
        $sheet->setCellValue("C1", "Lista de Valeras");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("C1:D1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Agencia");
        $sheet->setCellValue("B2", "Nombre valera");
        $sheet->setCellValue("C2", "Fecha creación");
        $sheet->setCellValue("D2", "Límite inferior");
        $sheet->setCellValue("E2", "Límite superior");
        $sheet->setCellValue("F2", "Vigencia");
        $sheet->setCellValue("G2", "Estado");
        $sheet->getStyle("A1:G2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($valeras as $valera) {
            $sheet->setCellValue("A" . $indice, $valera->cuentae->agencia->NOMBRE);
            $sheet->setCellValue("B" . $indice, $valera->nombre);
            $sheet->setCellValue("C" . $indice, $valera->fecha);
            $sheet->setCellValue("D" . $indice, $valera->inferior);
            $sheet->setCellValue("E" . $indice, $valera->superior);
            $sheet->setCellValue("F" . $indice, $valera->inicio . "/" . $valera->fin);
            if ($valera->estado == 1) {
                $sheet->setCellValue("G" . $indice, "Activa");
            } else {
                $sheet->setCellValue("G" . $indice, "Inactiva");
            }
            $indice++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Valeras.xlsx');
        $archivo = file_get_contents('Valeras.xlsx');
        unlink('Valeras.xlsx');

        return base64_encode($archivo);
    }

    public function validarvale(Request $request)
    {
        $valera = Valera::with('cuentae')->find($request->input('valera'));
        if ($valera != null) {
            //$hoy =  Carbon::now();
            //if ($hoy >= $valera->inicio && $hoy <= $valera->fin) {
            if (true) {
                if ($valera->cuentae->agencia_tercero_TERCERO == 435) {
                    $vale = Valeav::where('codigo', $request->input('codigo'))->where('valeras_id', $valera->id)->first();
                } else {
                    $vale = Vale::where('codigo', $request->input('codigo'))->where('valeras_id', $valera->id)->first();
                }
                if ($vale != null) {
                    if ($vale->estado == "Asignado" || $vale->estado == "Libre") {
                        return json_encode($vale);
                    } else {
                        return json_encode(array('usadonull' => "0"));
                    }
                } else {
                    return json_encode(array('valenull' => "0"));
                }
            } else {
                return json_encode(array('valeranull' => "0"));
            }
        } else {
            return json_encode(array('valeranull' => "0"));
        }
    }

    public function filtrarvales(Request $request, Valera $valera)
    {
        // Inicializa la consulta base
        $query = Vale::with(['servicio' => function ($q) {
            $q->select('id', 'usuarios', 'unidades', 'cobro', 'valor')
                ->with(['registros' => function ($r) {
                    $r->select('id', 'fecha', 'servicios_id');
                }]);
        }])->where('valeras_id', $valera->id);


        // Obtener conteos de estados
        $libres = Vale::where('valeras_id', $valera->id)->where('estado', 'Libre')->count();
        $asignados = Vale::where('valeras_id', $valera->id)->where('estado', 'Asignado')->count();
        $visados = Vale::where('valeras_id', $valera->id)->where('estado', 'Visado')->count();
        $usados = Vale::where('valeras_id', $valera->id)->where('estado', 'Usado')->count();

        // Aplica filtros según el parámetro presente en la solicitud
        if ($request->filled('codigo')) {
            $query->where('codigo', $request->input('codigo'));
            $filtro = ['Código del vale', $request->input('codigo')];
        } elseif ($request->filled('clave')) {
            $query->where('clave', $request->input('clave'));
            $filtro = ['Contraseña', $request->input('clave')];
        } elseif ($request->filled('beneficiario')) {
            $bene = $request->input('beneficiario');
            $query->whereHas('servicio', function ($q) use ($bene) {
                $q->where('usuarios', 'like', '%' . $bene . '%');
            });
            $filtro = ['Beneficiario', $request->input('beneficiario')];
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
            $filtro = ['Estado', $request->input('estado')];
        } elseif ($request->filled('fecha_inicio')) {
            $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
            $query->whereHas('servicio.registros', function ($query) use ($fechaInicio) {
                $query->whereDate('fecha', $fechaInicio);
            });
            $filtro = ['FechaInicio', $fechaInicio->toDateString()];
        } elseif ($request->filled('fecha_fin')) {
            $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
            $query->whereHas('servicio.registros', function ($query) use ($fechaFin) {
                $query->whereDate('fecha', $fechaFin);
            });
            $filtro = ['FechaFin', $fechaFin->toDateString()];
        } else {
            // Si no hay filtros aplicados, mostrar todos los registros
            return redirect('valeras/' . $valera->id . '/vales');
        }

        $horaActual = now()->format('H:i');

        // Ejecutar la consulta y paginar resultados
        $vales = $query->paginate(50)->appends($request->query());

        // Obtener el usuario actual
        $usuario = User::with('modulos')->findOrFail(Auth::user()->id);

        // Determinar la vista a renderizar
        $view = Auth::user()->roles_id == 4 || Auth::user()->roles_id == 5
            ? 'valeras.valesempresa'
            : 'valeras.vales';

        return view($view, compact('vales', 'valera', 'usuario', 'filtro', 'libres', 'asignados', 'visados', 'usados', 'horaActual'));
    }


    public function exportarvales(Request $request, Valera $valera)
    {
        $desde = $request->input('desde') . ' 00:00';
        if ($request->filled('hasta')) {
            $hasta = $request->input('hasta') . ' 23:59';
        } else {
            $hasta = Carbon::now()->format('Y-m-d') . ' 23:59';
        }

        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));
            if ($filtro[0] == "Código del vale") {
                $vales = Vale::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente' => function ($t) {
                        $t->select('id', 'nombres', 'telefono');
                    }, 'operador', 'registros']);
                }])->where('valeras_id', $valera->id)->whereHas('servicio', function ($q) use ($desde, $hasta) {
                    $q->whereBetween('fecha', [$desde, $hasta]);
                })->where('codigo', $filtro[1])->get();
            } elseif ($filtro[0] == "Contraseña") {
                $vales = Vale::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente' => function ($t) {
                        $t->select('id', 'nombres', 'telefono');
                    }, 'operador', 'registros']);
                }])->where('valeras_id', $valera->id)->whereHas('servicio', function ($q) use ($desde, $hasta) {
                    $q->whereBetween('fecha', [$desde, $hasta]);
                })->where('clave', $filtro[1])->get();
            } elseif ($filtro[0] == "Estado") {
                $vales = Vale::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente' => function ($t) {
                        $t->select('id', 'nombres', 'telefono');
                    }, 'operador', 'registros']);
                }])->where('valeras_id', $valera->id)->whereHas('servicio', function ($q) use ($desde, $hasta) {
                    $q->whereBetween('fecha', [$desde, $hasta]);
                })->where('estado', $filtro[1])->get();
            }
        } else {
            $vales = Vale::with(['servicio' => function ($q) {
                $q->with(['cuentac' => function ($r) {
                    $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                        $s->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente' => function ($t) {
                    $t->select('id', 'nombres', 'telefono');
                }, 'operador', 'registros']);
            }])->where('valeras_id', $valera->id)->whereHas('servicio', function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha', [$desde, $hasta]);
            })->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:AB1");
        $sheet->setCellValue("A1", "Vales de " . $valera->nombre);
        $style = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $sheet->getStyle("A1:AB1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID Servicio");
        $sheet->setCellValue("B2", "Fecha de Solicitud");
        $sheet->setCellValue("C2", "Fecha Tomado");
        $sheet->setCellValue("D2", "Fecha Arribo");
        $sheet->setCellValue("E2", "Fecha Inicio");
        $sheet->setCellValue("F2", "Fecha Fin");
        $sheet->setCellValue("G2", "Cliente");
        $sheet->setCellValue("H2", "Usuarios");
        $sheet->setCellValue("I2", "Teléfono");
        $sheet->setCellValue("J2", "Dir. Origen");
        $sheet->setCellValue("K2", "Beneficiario");
        $sheet->setCellValue("L2", "Centro de Costo");
        $sheet->setCellValue("M2", "Actividad");
        $sheet->setCellValue("N2", "Destino");
        $sheet->setCellValue("O2", "Despacho");
        $sheet->setCellValue("P2", "Pago");
        $sheet->setCellValue("Q2", "Valera");
        $sheet->setCellValue("R2", "Código vale");
        $sheet->setCellValue("S2", "Contraseña vale");
        $sheet->setCellValue("T2", "Unds/Mins/Ruta");
        $sheet->setCellValue("U2", "Valor servicio");
        $sheet->setCellValue("V2", "Asignación");
        $sheet->setCellValue("W2", "Conductor");
        $sheet->setCellValue("X2", "Vehiculo");
        $sheet->setCellValue("Y2", "Observaciones");
        $sheet->setCellValue("Z2", "Estado");
        $sheet->setCellValue("AA2", "Nombre Operador");
        $sheet->setCellValue("AB2", "Usuario Operador");
        $sheet->setCellValue("AC2", "Nombre quien asigna"); //Carlos
        $sheet->setCellValue("AD2", "Recargo aeropuerto");

        $sheet->getStyle("A1:AD2")->getFont()->setBold(true);
        $indice = 3;
        foreach ($vales as $vale) {
            $sheet->setCellValue("A" . $indice, $vale->servicio->id);
            $sheet->setCellValue("B" . $indice, $vale->servicio->fecha);
            if (count($vale->servicio->registros) > 0) {
                $sheet->setCellValue("C" . $indice, $vale->servicio->registros[0]->fecha);
            } else {
                $sheet->setCellValue("C" . $indice, "");
            }
            if (count($vale->servicio->registros) > 1) {
                $sheet->setCellValue("D" . $indice, $vale->servicio->registros[1]->fecha);
            } else {
                $sheet->setCellValue("D" . $indice, "");
            }
            if (count($vale->servicio->registros) > 2) {
                $sheet->setCellValue("E" . $indice, $vale->servicio->registros[2]->fecha);
            } else {
                $sheet->setCellValue("E" . $indice, "");
            }
            if (count($vale->servicio->registros) > 3) {
                $sheet->setCellValue("F" . $indice, $vale->servicio->registros[3]->fecha);
            } else {
                $sheet->setCellValue("F" . $indice, "");
            }
            $sheet->setCellValue("G" . $indice, $vale->servicio->cliente->nombres);
            $sheet->setCellValue("H" . $indice, $vale->servicio->usuarios);
            $sheet->setCellValue("I" . $indice, $vale->servicio->cliente->telefono);
            $sheet->setCellValue("J" . $indice, $vale->servicio->direccion);
            $sheet->setCellValue("K" . $indice, $vale->beneficiario);
            $sheet->setCellValue("L" . $indice, $vale->centrocosto);
            $sheet->setCellValue("M" . $indice, $vale->referenciado);
            $sheet->setCellValue("N" . $indice, $vale->destino);
            if ($vale->servicio->fechaprogramada == null) {
                $sheet->setCellValue("O" . $indice, "Inmediato");
            } else {
                $sheet->setCellValue("O" . $indice, "Programado");
            }
            $sheet->setCellValue("P" . $indice, $vale->servicio->pago);
            $sheet->setCellValue("Q" . $indice, $valera->nombre);
            $sheet->setCellValue("R" . $indice, $vale->codigo);

            if (Auth::user()->roles_id == 1) {
                $sheet->setCellValueExplicit("S" . $indice, strtoupper($vale->clave), DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue("S" . $indice, "");
            }
            $sheet->setCellValue("T" . $indice, $vale->servicio->unidades . " " . $vale->servicio->cobro);
            $sheet->setCellValue("U" . $indice, $vale->servicio->valor);
            $sheet->setCellValue("V" . $indice, $vale->servicio->asignacion);
            if ($vale->servicio->cuentac != null) {
                $sheet->setCellValue("W" . $indice, $vale->servicio->cuentac->conductor->NOMBRE);
            } else {
                $sheet->setCellValue("W" . $indice, "");
            }
            $sheet->setCellValue("X" . $indice, $vale->servicio->placa);
            $sheet->setCellValue("Y" . $indice, $vale->servicio->observaciones);
            $sheet->setCellValue("Z" . $indice, $vale->servicio->estado);
            if ($vale->servicio->operador != null) {
                $sheet->setCellValue("AA" . $indice, $vale->servicio->operador->nombres);
                $sheet->setCellValue("AB" . $indice, $vale->servicio->operador->usuario);
            } else {
                $sheet->setCellValue("AA" . $indice, "");
                $sheet->setCellValue("AB" . $indice, "");
            }
            $sheet->setCellValue("AC" . $indice, $vale->nombreasigna); //Carlos
            if ($vale->servicio->cobro == "Unidades" && $vale->servicio->estadocobro == 1) {
                $sheet->setCellValue("AD" . $indice, "Si");
            } else {
                $sheet->setCellValue("AD" . $indice, "No");
            }

            $indice++;
        }

        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Vales.xlsx');
        $archivo = file_get_contents('Vales.xlsx');
        unlink('Vales.xlsx');

        return base64_encode($archivo);
    }

    public function editarvale($valera, $vale)
    {
        $valera = Valera::with('cuentae.agencia')->find($valera);
        $vale = Vale::find($vale);
        $route = ['vales.editar', $valera->id, $vale->id];
        $method = 'put';

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $fecha = $vale->fecha;
        return view('valeras.updatevale', compact('valera', 'vale', 'usuario', 'route', 'method', 'fecha'));
    }

    public function updatevale(Request $request, Valera $valera, Vale $vale)
    {
        if (Auth::user()->id != 119) {
            if ($request->filled('estado')) {
                $vale->estado = $request->input('estado');
                if ($vale->estado == "Libre") {
                    $vale->beneficiario = null;
                    $vale->referenciado = null;
                    $vale->centrocosto = null;
                    $vale->destino = null;
                } else {
                    $vale->beneficiario = $request->input('beneficiario');
                    $vale->referenciado = $request->input('referenciado');
                    $vale->centrocosto = $request->input('centrocosto');
                    $vale->destino = $request->input('destino');
                }
                $vale->save();
            }
        }

        return redirect('valeras/' . $valera->id . '/vales');
    }

    public function descargarplantilla(Valera $valera)
    {
        $vales = Vale::where('valeras_id', $valera->id)->whereIn('estado', ['Libre', 'Asignado'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A1", "Plantilla de asignación de vales " . $valera->nombre);
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:F1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Código del vale");
        $sheet->setCellValue("B2", "Contraseña");
        $sheet->setCellValue("C2", "Beneficiario");
        $sheet->setCellValue("D2", "Centro de costo");
        $sheet->setCellValue("E2", "Actividad a realizar");
        $sheet->setCellValue("F2", "Dirección destino");
        $sheet->setCellValue("G2", "Identificación");
        $sheet->setCellValue("H2", "Fecha Asignación Vale");
        $sheet->getStyle("A1:H2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($vales as $vale) {
            $sheet->setCellValue("A" . $indice, $vale->codigo);
            $sheet->setCellValueExplicit("B" . $indice, strtoupper($vale->clave), DataType::TYPE_STRING);
            $sheet->setCellValue("C" . $indice, $vale->beneficiario);
            $sheet->setCellValue("D" . $indice, $vale->centrocosto);
            if ($valera->centro != null) {
                $sheet->setCellValue("D" . $indice, $valera->centro);
            }
            $sheet->setCellValue("E" . $indice, $vale->referenciado);
            $sheet->setCellValue("F" . $indice, $vale->destino);
            $sheet->setCellValue("H" . $indice, $vale->fecha);
            $indice++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Plantilla.xlsx');
        $archivo = file_get_contents('Plantilla.xlsx');
        unlink('Plantilla.xlsx');

        return base64_encode($archivo);
    }

    public function importarvales(Request $request, Valera $valera)
    {
        if (Auth::user()->id != 119) {
            $file = $request->file('plantilla');
            $ext = $file->getClientOriginalExtension();

            if ($ext == "xls" or $ext == "xlsx") {

                $objPHPExcel = IOFactory::load($file);
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getActiveSheet();
                $numRows = $sheet->getHighestRow();

                if ($numRows >= 3) {
                    for ($i = 3; $i <= $numRows; $i++) {
                        $vale = Vale::where('valeras_id', $valera->id)->where('codigo', $sheet->getCell('A' . $i)->getCalculatedValue())->first();
                        if ($vale != null) {
                            if ($vale->estado == "Libre" || $vale->estado == "Asignado") {
                                $fecha = Carbon::now();
                                $ben = $sheet->getCell('C' . $i)->getValue();
                                $identificacion = $sheet->getCell('G' . $i)->getValue();
                                if (!empty($identificacion)) {
                                    $pasajero = Pasajero::where('identificacion', $identificacion)->first();
                                    if ($pasajero != null) {
                                        $ben = $pasajero->nombre;
                                        $to = $pasajero->email;
                                    }
                                }

                                $cos =  $sheet->getCell('D' . $i)->getValue();
                                $ref = $sheet->getCell('E' . $i)->getValue();
                                $des = $sheet->getCell('F' . $i)->getValue();
                                if ($ben != "" && $cos != "") {
                                    $vale->beneficiario = $ben;
                                    $vale->centrocosto = $cos;
                                    $vale->referenciado = $ref;
                                    $vale->destino = $des;
                                    $vale->estado = "Asignado";
                                    $vale->fecha = $fecha;
                                    $vale->save();

                                    if (!empty($to)) {
                                        Mail::send('emails.notificacionPasajero', compact('vale'), function ($message) use ($to) {
                                            $message->from("notificaciones@apptaxcenter.com", "Taxcenter");
                                            $message->to($to);
                                            $message->subject("Asignación de vale");
                                        });
                                    }
                                }
                            }
                        }
                    }
                }
                return redirect('valeras/' . $valera->id . '/vales');
            } else {
                return back()->withErrors(['sql' => 'El archivo ingresado no está en el formato correcto']);
            }
        } else {
            return redirect('valeras/' . $valera->id . '/vales');
        }
    }

    public function liberar(Request $request, $valera, Vale $vale)
    {
        if ($vale->centrocosto != null) {
            $vale->estado = "Asignado";
        } else {
            $vale->estado = "Libre";
        }
        //guardamos registro de quien libero ultima vez con observación y del servicio en que se visó
        $registroLiberar = Vale_servicio::where('vales_id', $vale->id)
            ->where('servicios_id', $vale->servicios_id)
            ->first();

        if ($registroLiberar != null) {
            $registroLiberar->users_id = Auth::user()->id;
            $registroLiberar->fecha_edicion = Carbon::now();
            $registroLiberar->ultima_observacion = $request->input('liberarObservaciones');
            $registroLiberar->save();
        }
        $vale->servicios_id = null;
        $vale->save();

        return redirect('valeras/' . $valera . '/vales');
    }

    public function claves()
    {
        $valera = Valera::with('vales')->find(1);
        foreach ($valera->vales as $vale) {
            $vale->clave = $this->genclave(4);
            $vale->save();
        }

        return "Hecho";
    }

    public function eliminar(Request $request, $valera, Vale $vale)
    {
        if ($vale->centrocosto != null) {
            $vale->estado = "Asignado";
        } else {
            $vale->estado = "Libre";
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $servicio = Servicio::with(['novedades', 'seguimientos', 'registros', 'cancelacion'])->find($vale->servicios_id);
        if ($servicio != null) {
            if ($servicio->novedades->count() > 0) {
                $servicio->novedades()->delete();
            }
            if ($servicio->registros->count() > 0) {
                $servicio->registros()->delete();
            }
            if ($servicio->seguimientos->count() > 0) {
                $servicio->seguimientos()->delete();
            }
            if ($servicio->cancelacion != null) {
                $servicio->cancelacion->delete();
            }
            $vale->servicios_id = null;
            $vale->save();
            $servicio->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return redirect('valeras/' . $valera . '/vales');
    }

    public function observaciones(Request $request, $valera, Vale $vale)
    {
        $nombreUserLogueado = Auth::user()->nombres;
        $ccUserLogueado = Auth::user()->identificacion;
        $fecha = Carbon::now();
        $fechaFormateada = $fecha->format('Y-m-d H:i:s');
        $observaciones = $request->input('observaciones', '');
        $vale->observaciones = $observaciones . "\n" . "Usuario que realizó la observación: Nombre: " . $nombreUserLogueado . "| CC: " . $ccUserLogueado . "| Fecha Observación: " . $fechaFormateada;
        $vale->save();

        return redirect('valeras/' . $valera . '/vales');
    }

    public function gestionCobro($servicio)
    {
        $servicio = Servicio::select('id', 'estado', 'estadocobro')->find($servicio);
        if ($servicio->estadocobro == 0) {
            $servicio->estadocobro = 1;
            $servicio->cobro = null;
            $servicio->users2_id = Auth::user()->id;
        } else {
            $servicio->estadocobro = 0;
            $servicio->cobro = "Unidades";
        }

        $servicio->save();

        return redirect('servicios/en_curso');
    }

    public function serviciosMajorel(Request $request)
    {
        $token = csrf_token();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $hoy = Carbon::now();

        return view('servicios.majorel', compact('usuario', 'hoy', 'token'));
    }

    public function archivoMajorel(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $i = 0;

        try {
            // Cargar el archivo
            $excel = IOFactory::load($request->file('archivo'));
            $hoja = $excel->setActiveSheetIndex(0);
            $filas = $hoja->getHighestRow();
            $idValera = $request->input('valera');
            $fecha = Carbon::parse($request->input('fechaprog'))->format('Y-m-d');
            $numRutas = Ruta::where('valeras_id', $idValera)->whereHas('programacion', function ($q) use ($fecha) {
                $q->where('dia', $fecha);
            })->count();
            $rutas = [];
            $pasajeros = [];
            $nrutaActual = null;
            $numero = $numRutas;
            $ruta = null; //variable objeto
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($filas > 1) { //PARA UN EXCEL COMPLETO HACER
                for ($i = 2; $i <= $filas; $i++) {
                    if ($hoja->getCell('D' . $i)->getValue() != '') {
                        $hora = $hoja->getCell('I' . $i)->getFormattedValue();
                        $nruta = $hoja->getCell('N' . $i)->getFormattedValue();
                        if ($nruta != $nrutaActual) {
                            if ($ruta != null) {
                                $ruta->pasajeros = $pasajeros;
                                if (count($pasajeros) > 0) {
                                    if (count($pasajeros) > 4) {
                                        return "Más de 4 pasajeros en una ruta ---> Error fila: " . $i;
                                    } else {
                                        $rutas[] = $ruta;
                                    }
                                } else {
                                    $numero--;
                                }
                            }
                            $numero++;
                            $ruta = new stdClass();
                            $ruta->numero = $numero;
                            $ruta->estimado = $hoja->getCell('X' . $i)->getValue();
                            $ruta->ceco = $hoja->getCell('B' . $i)->getValue();
                            //$ruta->servicio = $hoja->getCell('N' . $i)->getValue();
                            $placa = $hoja->getCell('O' . $i)->getValue();
                            if (!empty($placa)) {
                                $veh = Vehiculo::with(['conductores' => function ($q) {
                                    $q->with(['cuentac' => function ($r) {
                                        $r->select('id', 'conductor_CONDUCTOR');
                                    }])->where('SW_ACTIVO_NUEVO_CRM', '1');
                                }])->where('PLACA', $placa)->first();
                                if (count($veh->conductores) == 1) {
                                    $ruta->placa = $placa;
                                    $ruta->cuentac = $veh->conductores[0]->cuentac->id;
                                }
                            }
                            $nrutaActual = $nruta;

                            if (strlen($hora) == 5) { //LONGITUD DE HORA ES IGUAL 5
                                $h = substr($hora, -1, 1); //TOMA EL ULTIMO CARACTER HORA Y SE ASIGNA  A H ( CADENA, POSICION DEL SUB EN LA CADENA, ESPECIFICA LA LOONGITUD DEL SUB A EXTRAER)
                            } elseif (strlen($hora) == 8) {
                                $h = substr($hora, -4, 1);
                            }
                            if ($h == '0') { // en el excel todas las horas q terminan en cero van entrando
                                $ruta->tipoRuta = "Entrada";
                            } else { // en el excel las diferentes a cero van saliendo
                                $ruta->tipoRuta = "Salida";
                            }
                            $fecha = Carbon::parse($hoja->getCell('J' . $i)->getFormattedValue() . " " . $hora);
                            $ruta->fecha = $fecha->format('Y-m-d');
                            $ruta->hora = $fecha->format('H:i:s');
                            $pasajeros = [];
                        }
                        $identificacion = $hoja->getCell('C' . $i)->getValue();
                        if (!empty($identificacion)) {
                            $pasajero = Pasajero::where('identificacion', $identificacion)->first();
                            if ($pasajero == null) { //Registro de pasajero, si no tiene niingun valor asignado
                                $pasajero = new Pasajero();
                                $pasajero->ceco = utf8_encode($hoja->getCell('B' . $i)->getValue());
                                $pasajero->identificacion = utf8_encode($hoja->getCell('C' . $i)->getValue());
                                $pasajero->nombre = utf8_encode($hoja->getCell('D' . $i)->getValue());
                                //$pasajero->servicio = utf8_encode($hoja->getCell('R' . $i)->getValue());
                                $pasajero->direccion = utf8_encode($hoja->getCell('E' . $i)->getValue());
                                $pasajero->barrio = utf8_encode($hoja->getCell('F' . $i)->getValue());
                                $pasajero->municipio = utf8_encode($hoja->getCell('G' . $i)->getValue());
                                $pasajero->celulares = utf8_encode($hoja->getCell('H' . $i)->getValue());
                                curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->barrio . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                $json = json_decode(curl_exec($ch));
                                if (count($json->results) > 0) {
                                    $pasajero->lat = $json->results[0]->geometry->location->lat;
                                    $pasajero->lng = $json->results[0]->geometry->location->lng;
                                } else {
                                    curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                    $json = json_decode(curl_exec($ch));
                                    if (count($json->results) > 0) {
                                        $pasajero->lat = $json->results[0]->geometry->location->lat;
                                        $pasajero->lng = $json->results[0]->geometry->location->lng;
                                    } else {
                                        throw new Exception("Error en la fila " . $filas, 0);
                                    }
                                }
                                $pasajero->save();
                            } else { //si pasajero tiene algun valor
                                if ($pasajero->celulares != $hoja->getCell('H' . $i)->getValue() || $pasajero->ceco != $hoja->getCell('B' . $i)->getValue()) {
                                    $pasajero->celulares = utf8_encode($hoja->getCell('H' . $i)->getValue());
                                    $pasajero->ceco = utf8_encode($hoja->getCell('B' . $i)->getValue());
                                    //$pasajero->servicio = utf8_encode($hoja->getCell('R' . $i)->getValue());
                                }

                                if ($pasajero->direccion != $hoja->getCell('E' . $i)->getValue() || $pasajero->barrio != $hoja->getCell('F' . $i)->getValue()) {
                                    $pasajero->direccion = utf8_encode($hoja->getCell('E' . $i)->getValue());
                                    //$pasajero->barrio = utf8_encode($hoja->getCell('F' . $i)->getValue());
                                    $pasajero->barrio = mb_convert_encoding($hoja->getCell('F' . $i)->getValue(), 'UTF-8', 'UTF-8');
                                    $pasajero->municipio = utf8_encode($hoja->getCell('G' . $i)->getValue());
                                    if (count($pasajeros) == 0) {
                                        curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->barrio . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                        $json = json_decode(curl_exec($ch));
                                        if (count($json->results) > 0) {
                                            $pasajero->lat = $json->results[0]->geometry->location->lat;
                                            $pasajero->lng = $json->results[0]->geometry->location->lng;
                                        } else {
                                            curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                            $json = json_decode(curl_exec($ch));
                                            if (count($json->results) > 0) {
                                                $pasajero->lat = $json->results[0]->geometry->location->lat;
                                                $pasajero->lng = $json->results[0]->geometry->location->lng;
                                            } else {
                                                throw new Exception("Error en la fila " . $filas, 0);
                                            }
                                        }
                                    }
                                }
                                $pasajero->save();
                            }
                            $pasajeros[] = $pasajero; //se guarda la informacion del pasajero
                        }
                    }
                }
                if ($ruta != null) { //si la ruta no tiene ningun valor
                    $ruta->pasajeros = $pasajeros; //se unifica ruta(direccionminucipio) y usuario
                    if (count($pasajeros) > 0) {
                        if (count($pasajeros) > 4) {
                            return "Más de 4 pasajeros en una ruta ---> Error fila: " . $i;
                        } else {
                            $rutas[] = $ruta;
                        }
                    }
                }
            }
            return  json_encode($rutas);
        } catch (Exception $ex) {
            return $ex->getMessage() . "---" . $ex->getLine() . "---Error fila: " . $i;
        }
    }


    public function archivoTransamerica(Request $request)
    {

        set_time_limit(0);
        ini_set("memory_limit", -1);
        $i = 0;
        try {
            $excel = IOFactory::load($request->file('archivo'));
            $hoja = $excel->setActiveSheetIndex(0);
            $filas = $hoja->getHighestRow();
            $fecha = Carbon::parse($request->input('fechaprog'))->format('Y-m-d');
            $numRutas = Ruta::whereHas('programacion', function ($q) use ($fecha) {
                $q->where('dia', $fecha);
            })->count();
            $rutas = [];
            $pasajeros = [];
            $horaActual = '0';
            $muniActual = '';
            $numero = $numRutas;
            $ruta = null;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($filas > 1) {
                for ($i = 2; $i <= $filas; $i++) {
                    $hora = $hoja->getCell('G' . $i)->getFormattedValue();
                    $municipio = $hoja->getCell('F' . $i)->getValue();
                    $separar = $hoja->getCell('T' . $i)->getValue();
                    if (!empty($hora) && $hora != "HORARIO") {
                        if (($hora != $horaActual || $municipio != $muniActual || $hoja->getCell('M' . $i)->getValue() == "BAHIACHALA") && $separar == "") {
                            if ($ruta != null) {
                                $ruta->pasajeros = $pasajeros;
                                $rutas[] = $ruta;
                            }
                            $numero++;
                            $ruta = new stdClass();
                            $ruta->numero = $numero;
                            $ruta->tipoRuta = $hoja->getCell('L' . $i)->getValue();
                            $horaActual = $hora;
                            $muniActual = $municipio;

                            $fecha = Carbon::parse($hoja->getCell('I' . $i)->getFormattedValue() . " " . $hora);
                            $ruta->fecha = $fecha->format('Y-m-d');
                            $ruta->hora = $fecha->format('H:i:s');
                            $pasajeros = [];
                        }
                        $identificacion = $hoja->getCell('A' . $i)->getValue();
                        $pasajero = Pasajero::where('identificacion', $identificacion)->first();
                        if ($pasajero == null) {
                            $pasajero = new Pasajero();
                            $pasajero->celulares = $hoja->getCell('C' . $i)->getValue();
                            $pasajero->identificacion = $hoja->getCell('A' . $i)->getValue();
                            $pasajero->nombre = $hoja->getCell('B' . $i)->getValue();
                            $pasajero->municipio = $hoja->getCell('F' . $i)->getValue();
                            $direccion = utf8_encode($hoja->getCell('D' . $i)->getValue());
                            $pasajero->barrio = $hoja->getCell('E' . $i)->getValue();
                            $pasajero->direccion = $direccion;
                            curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->barrio . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                            $json = json_decode(curl_exec($ch));
                            if (count($json->results) > 0) {
                                $pasajero->lat = $json->results[0]->geometry->location->lat;
                                $pasajero->lng = $json->results[0]->geometry->location->lng;
                            } else {
                                curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                $json = json_decode(curl_exec($ch));
                                $pasajero->lat = $json->results[0]->geometry->location->lat;
                                $pasajero->lng = $json->results[0]->geometry->location->lng;
                            }
                            $pasajero->save();
                        } else {
                            if ($pasajero->direccion != $hoja->getCell('D' . $i)->getValue() || $pasajero->celulares != $hoja->getCell('C' . $i)->getValue()) {
                                $pasajero->direccion = utf8_encode($hoja->getCell('D' . $i)->getValue());
                                $pasajero->celulares = $hoja->getCell('C' . $i)->getValue();
                                $pasajero->barrio = $hoja->getCell('E' . $i)->getValue();
                                $pasajero->municipio = $hoja->getCell('F' . $i)->getValue();
                                curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->barrio . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                $json = json_decode(curl_exec($ch));
                                if (count($json->results) > 0) {
                                    $pasajero->lat = $json->results[0]->geometry->location->lat;
                                    $pasajero->lng = $json->results[0]->geometry->location->lng;
                                } else {
                                    curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->municipio) . "&language=ES&key=apikey");
                                    $json = json_decode(curl_exec($ch));
                                    $pasajero->lat = $json->results[0]->geometry->location->lat;
                                    $pasajero->lng = $json->results[0]->geometry->location->lng;
                                }
                                $pasajero->save();
                            }
                        }
                        $pasajeros[] = $pasajero;
                    }
                }
                if ($ruta != null) {
                    $ruta->pasajeros = $pasajeros;
                    $rutas[] = $ruta;
                }
            }
            $nuevas = [];
            $numRuta = $numRutas + 1;
            foreach ($rutas as $ruta) {
                $todos = count($ruta->pasajeros);

                for ($i = 0; $i < $todos; $i++) {
                    if ($ruta->pasajeros[$i]->acomodado == null) {
                        $pas = [];
                        $pas[] = $ruta->pasajeros[$i];
                        $ruta->pasajeros[$i]->distancia = 0;
                        $ruta->pasajeros[$i]->acomodados = 1;
                        if ($ruta->pasajeros[$i]->municipio == "BUCARAMANGA") {
                            $reordenados = [];
                            for ($j = $i + 1; $j < $todos; $j++) {
                                if ($ruta->pasajeros[$j]->acomodado == null) {
                                    $ruta->pasajeros[$j]->distancia = $this->distancia($ruta->pasajeros[$i]->lat, $ruta->pasajeros[$i]->lng, $ruta->pasajeros[$j]->lat, $ruta->pasajeros[$j]->lng);
                                    $ruta->pasajeros[$j]->indice = $j;
                                    $reordenados[] = $ruta->pasajeros[$j];
                                }
                            }
                            usort($reordenados, function ($a, $b) {
                                if ($a->distancia == $b->distancia)
                                    return (0);
                                return (($a->distancia < $b->distancia) ? -1 : 1);
                            });
                            for ($k = 0; $k < count($reordenados); $k++) {
                                $pas[] = $reordenados[$k];
                                $ruta->pasajeros[$reordenados[$k]->indice]->acomodado = 1;
                                if (count($pas) == 4) {
                                    break;
                                }
                            }
                        } else {
                            for ($j = $i + 1; $j < $todos; $j++) {
                                if ($ruta->pasajeros[$j]->acomodado == null) {
                                    $pas[] = $ruta->pasajeros[$i];
                                    $ruta->pasajeros[$j]->acomodado = 1;
                                    if (count($pas) == 4) {
                                        break;
                                    }
                                }
                            }
                        }
                        $nueva = clone $ruta;
                        $nueva->numero = $numRuta;
                        $nueva->pasajeros = $pas;
                        $numRuta++;
                        $nuevas[] = $nueva;
                    }
                }
            }
            unset($rutas);

            return json_encode($nuevas);
        } catch (Exception $ex) {
            return $ex->getMessage() . "---" . $ex->getLine() . "---Error fila:" . $i;
        }
    }

    public function getEditarServicio(Request $request)
    {
        $servicio = Servicio::with(["cuentac" => function ($q) {
            $q->select('id', 'placa', 'conductor_CONDUCTOR')->with(["conductor" => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->find($request->input('idservicio'));

        return json_encode($servicio);
    }

    public function actualizarServicio(Request $request)
    {
        $servicio = Servicio::select('id', 'fechaprogramada', 'placa', 'asignacion')->find($request->input('editarIdServicio'));
        if ($request->filled('editarPlaca')) {
            $placa = explode("_", $request->input('editarPlaca'));
            $servicio->asignacion = "Directo";
            $servicio->placa = $placa[0];
            $servicio->cuentasc_id = $placa[2];
            $servicio->users3_id = Auth::user()->id;
        } else {
            $servicio->asignacion = "Normal";
            $servicio->placa = null;
            $servicio->cuentasc_id = null;
        }
        $servicio->usuarios = $request->input('editarUsuarios');
        $servicio->direccion = $request->input('editarDireccion');
        $servicio->fechaprogramada = $request->input('editarFecha');
        $servicio->save();

        return redirect('servicios/filtrar_en_curso?id=' . $servicio->id);
    }

    public function programarMajorel(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $cliente = Cliente::find(90);
        $hoy = Carbon::parse($request->input('fechaprog'));
        $vales = [];
        $servis = [];
        try {
            DB::beginTransaction();
            $idValera = $request->input('valera');
            $rutas = json_decode($request->input('rutas'));
            $programacion = Programacion::where('dia', $hoy->format('Y-m-d'))->first();
            if ($programacion == null) {
                $programacion = new Programacion();
                $programacion->dia = $hoy->format('Y-m-d');
                $programacion->save();
            }
            foreach ($rutas as $ruta) {
                if ($ruta->programar == 1) {
                    $direccion = "";
                    $usuarios = "";
                    $adicional = "Llevar a ";
                    $lat = null;
                    $lng = null;
                    for ($i = 0; $i < count($ruta->pasajeros); $i++) {
                        if ($ruta->tipoRuta == "Entrada") {
                            $direccion = $direccion . $ruta->pasajeros[$i]->direccion . ", " . $ruta->pasajeros[$i]->barrio . ", " . $ruta->pasajeros[$i]->municipio . "\n";
                            $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                        } else {
                            $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                            $adicional = $adicional . " " . $ruta->pasajeros[$i]->barrio;
                        }
                    }
                    if ($ruta->tipoRuta == "Entrada") {
                        foreach ($ruta->pasajeros as $pasajero) {
                            if ($pasajero->lat != null) {
                                $lat = $pasajero->lat;
                                $lng = $pasajero->lng;
                                break;
                            }
                        }
                    } else {
                        $direccion = "Zona franca anillo vial";
                        $lat = 7.061521;
                        $lng = -73.126069;
                    }

                    if ($lat == null || $lng == null) {
                        throw new Exception("Servicio sin coordenadas");
                    }

                    $servicio = new Servicio();
                    $servicio->fecha = Carbon::now();
                    $servicio->latitud = $lat;
                    $servicio->longitud = $lng;
                    $servicio->direccion = $direccion;
                    if ($ruta->cuentac != null) {
                        $servicio->asignacion = "Directo";
                        $servicio->cuentasc_id = $ruta->cuentac;
                        $servicio->placa = $ruta->placa;
                    } else {
                        $servicio->asignacion = "Normal";
                    }
                    $servicio->pago = "Vale electrónico";
                    $servicio->cobro = "Unidades";
                    $servicio->usuarios = $usuarios;
                    if ($ruta->tipoRuta == "Salida") {
                        $servicio->adicional = "Va hacia " . $adicional;
                    }
                    $servicio->fechaprogramada = $ruta->horaRecogida;
                    $servicio->flotas_id = null;
                    $servicio->clientes_id = $cliente->id;
                    $servicio->users_id = Auth::user()->id;
                    $servicio->estado = "Pendiente";
                    $servicio->save();

                    $vale = Vale::where('valeras_id', $idValera)->where('estado', 'Libre')->first();
                    $vales[] = $vale->id;
                    $servis[] = $servicio->id;
                    $vale->servicios_id = $servicio->id;
                    $vale->estado = "Visado";
                    $vale->save();

                    $valeserv = new Vale_servicio();
                    $valeserv->vales_id = $vale->id;
                    $valeserv->servicios_id = $servicio->id;
                    $valeserv->save();

                    $rutaServicio = new Ruta();
                    $rutaServicio->tipo = $ruta->tipoRuta;
                    $rutaServicio->numero = $ruta->numero;
                    $rutaServicio->fecha = $ruta->fecha;
                    $rutaServicio->hora = $ruta->hora;
                    $rutaServicio->estimado = $ruta->estimado;
                    $rutaServicio->programaciones_id = $programacion->id;
                    $rutaServicio->servicios_id = $servicio->id;
                    $rutaServicio->valeras_id = $idValera;
                    $rutaServicio->save();

                    foreach ($ruta->pasajeros as $pasajero) {
                        $pasajeroxruta = new Pasajerosxruta();
                        $pasajeroxruta->pasajeros_id = $pasajero->id;
                        $pasajeroxruta->rutas_id = $rutaServicio->id;
                        $pasajeroxruta->save();

                        $numero = explode(" - ", $pasajero->celulares)[0];
                        if (is_numeric($numero) && strlen($numero) == 10 && $servicio->cuentac != null) {
                            try {
                                $this->enviarSMS($numero, $pasajero->nombre, $servicio->cuentac->conductor->PRIMER_NOMBRE . " " . $servicio->cuentac->conductor->PRIMER_APELLIDO, $servicio->placa, $servicio->fechaprogramada, $rutaServicio->tipo);
                            } catch (Exception $ex) {
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return $ex->getMessage() . "---" . $ex->getLine();
        }

        DB::commit();

        return "Listo";
    }

    public function programarTransamerica(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $cliente = Cliente::find(13977);
        $hoy = Carbon::parse($request->input('fechaprog'));
        $vales = [];
        $servis = [];
        try {
            $rutas = json_decode($request->input('rutas'));
            $programacion = Programacion::where('dia', $hoy->format('Y-m-d'))->first();
            if ($programacion == null) {
                $programacion = new Programacion();
                $programacion->dia = $hoy->format('Y-m-d');
                $programacion->save();
            }
            foreach ($rutas as $ruta) {
                if ($ruta->programar == 1) {
                    $direccion = "";
                    $usuarios = "";
                    $adicional = "Llevar a";
                    $lat = null;
                    $lng = null;
                    for ($i = 0; $i < count($ruta->pasajeros); $i++) {
                        $direccion = $direccion . $ruta->pasajeros[$i]->direccion . ", " . $ruta->pasajeros[$i]->barrio . ", " . $ruta->pasajeros[$i]->municipio . "\n";
                        $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                    }
                    $lat = $ruta->pasajeros[0]->lat;
                    $lng = $ruta->pasajeros[0]->lng;

                    $servicio = new Servicio();
                    $servicio->fecha = Carbon::now();
                    $servicio->latitud = $lat;
                    $servicio->longitud = $lng;
                    $servicio->direccion = $direccion;
                    if ($ruta->cuentac != null) {
                        $servicio->asignacion = "Directo";
                        $servicio->cuentasc_id = $ruta->cuentac;
                        $servicio->placa = $ruta->placa;
                    } else {
                        $servicio->asignacion = "Normal";
                    }
                    $servicio->pago = "Vale electrónico";
                    $servicio->cobro = "Unidades";
                    $servicio->usuarios = $usuarios;
                    $servicio->fechaprogramada = $ruta->horaRecogida;
                    $servicio->flotas_id = null;
                    $servicio->clientes_id = $cliente->id;
                    $servicio->users_id = Auth::user()->id;
                    $servicio->estado = "Pendiente";
                    $servicio->save();

                    $vale = Vale::where('valeras_id', 100)->where('estado', 'Libre')->first();
                    $vales[] = $vale->id;
                    $servis[] = $servicio->id;
                    $vale->servicios_id = $servicio->id;
                    $vale->estado = "Visado";
                    $vale->save();

                    $valeserv = new Vale_servicio();
                    $valeserv->vales_id = $vale->id;
                    $valeserv->servicios_id = $servicio->id;
                    $valeserv->save();

                    $rutaServicio = new Ruta();
                    $rutaServicio->numero = $ruta->numero; //Pendiente definir
                    $rutaServicio->tipo = $ruta->tipoRuta;
                    $rutaServicio->fecha = $ruta->fecha;
                    $rutaServicio->hora = $ruta->hora;
                    $rutaServicio->programaciones_id = $programacion->id;
                    $rutaServicio->servicios_id = $servicio->id;
                    $rutaServicio->clientes_id = $cliente->id;
                    $rutaServicio->save();

                    foreach ($ruta->pasajeros as $pasajero) {
                        $pasajeroxruta =  new Pasajerosxruta();
                        $pasajeroxruta->pasajeros_id = $pasajero->id;
                        $pasajeroxruta->rutas_id = $rutaServicio->id;
                        $pasajeroxruta->save();
                    }
                }
            }
        } catch (Exception $ex) {
            $valesUsados = Vale::whereIn('id', $vales)->get();
            foreach ($valesUsados as $valeUsado) {
                $valeUsado->estado = "Libre";
                $valeUsado->servicios_id = null;
                $valeUsado->save();
            }
            Servicio::whereIn('id', $servis)->delete();
            return $ex->getMessage() . "---" . $ex->getline();
        }
        return "Listo";
    }

    public function editarMajorel(Request $request)
    {
        $servicio = Servicio::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }, 'ruta.pasajeros'])->find($request->input('idservicio'));
        $novedades = NovedadMajorel::get();
        $servicio->novedadesmaj = $novedades;
        return json_encode($servicio);
    }

    public function actualizarMajorel(Request $request)
    {
        try {
            DB::beginTransaction();
            $servicio = Servicio::select('id', 'fechaprogramada', 'usuarios', 'direccion', 'adicional', 'estado', 'placa', 'asignacion', 'cuentasc_id')
                ->with([
                    'cuentac' => function ($q) {
                        $q->select('id', 'saldo');
                    },
                    'ruta.pasajeros',
                    'vale',
                    'cancelacion'
                ])
                ->find($request->input('idMajorel'));

            $idValera = $servicio->vale->valeras_id;

            foreach ($servicio->ruta->pasajeros as $pasajero) {
                if ($request->input($pasajero->id) != 0) {
                    $rutapas = Pasajerosxruta::where('rutas_id', $servicio->ruta->id)->where('pasajeros_id', $pasajero->id)->first();
                    if ($rutapas != null) {
                        $rutapas->novedadesmaj_id = $request->input($pasajero->id);
                        $rutapas->observaciones = $request->input('obs' . $pasajero->id);
                        $rutapas->save();
                    }
                }
            }

            for ($i = 0; $i < 3; $i++) {
                if ($request->filled('newpasajero' . $i)) {
                    $psj = Pasajero::where('identificacion', $request->input('newpasajero' . $i))->first();
                    if ($psj != null) {
                        $psjxruta = new Pasajerosxruta();
                        $psjxruta->pasajeros_id = $psj->id;
                        $psjxruta->rutas_id = $servicio->ruta->id;
                        $psjxruta->observaciones = $request->input('newobs' . $i);
                        if ($request->input('newnovedad' . $i) != 0) {
                            $psjxruta->novedadesmaj_id = $request->input('newnovedad' . $i);
                        }
                        $psjxruta->save();
                    } else {
                        throw new Exception("El pasajero con identificación " . $request->input('newpasajero' . $i) . " no está registrado");
                    }
                }
            }

            $ruta = Ruta::with('pasajeros')->find($servicio->ruta->id);
            $direccion = "";
            $usuarios = "";
            $adicional = "";
            $cantidad = count($ruta->pasajeros);
            $sacados = 0;
            for ($i = 0; $i < $cantidad; $i++) {
                if ($ruta->pasajeros[$i]->pivot->novedadesmaj_id == null) {
                    if ($ruta->tipo == "Entrada") {
                        $direccion = $direccion . $ruta->pasajeros[$i]->direccion . ", " . $ruta->pasajeros[$i]->barrio . ", " .  $ruta->pasajeros[$i]->municipio . "\n";
                        $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                    } else {
                        $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                        $adicional = $adicional . " " . $ruta->pasajeros[$i]->barrio;
                    }
                } else {
                    $sacados++;
                }
            }

            if ($sacados == $cantidad && $cantidad > 1) {
                $servicio->estado = "Cancelado";
                if ($servicio->vale != null) {
                    $servicio->vale->estado = "Libre";
                    $servicio->vale->servicios_id = null;
                    $servicio->vale->save();
                }
                $cancelacion = Cancelacion::where('servicios_id', $servicio->id)->first();
                if ($cancelacion == null) {
                    $cancelacion = new Cancelacion();
                }
                $cancelacion->razon = "Ruta sin pasajeros";
                $cancelacion->fecha = Carbon::now();
                $cancelacion->servicios_id = $servicio->id;
                $cancelacion->save();
            } else {
                if ($cantidad > 1) {
                    if ($ruta->tipo == "Entrada") {
                        $servicio->direccion = $direccion;
                    } else {
                        $servicio->adicional = $adicional;
                    }
                    $servicio->usuarios = $usuarios;
                }

                if ($request->filled('placaMajorel')) {
                    $placa = explode("_", $request->input('placaMajorel'));
                    $servicio->asignacion = "Directo";
                    $servicio->placa = $placa[0];
                    $servicio->cuentasc_id = $placa[2];
                    $servicio->users3_id = Auth::user()->id;
                } else {
                    $servicio->asignacion = "Normal";
                    $servicio->placa = null;
                    $servicio->cuentasc_id = null;
                }
                $servicio->fechaprogramada = $request->input('fechaMajorel');
                if ($servicio->estado == "No vehiculo" || $servicio->estado == "Cancelado" || $servicio->estado == "Cancelado devuelto") {

                    if ($servicio->cancelacion != null) {
                        if ($servicio->estado == "Cancelado") {
                            $servicio->cancelacion->users_id = Auth::user()->id;
                            $servicio->cancelacion->justificacion = "Servicio de Majorel cancelado por conductor";
                            $servicio->cancelacion->save();

                            $servicio->cuentac->saldo = $servicio->cuentac->saldo + 800;
                            $servicio->cuentac->save();

                            $mensaje = new Mensaje();
                            $mensaje->texto = "Devolución realizada por valor de: $800";
                            $mensaje->fecha = Carbon::now();
                            $mensaje->sentido = "Recibido";
                            $mensaje->estado = "Pendiente";
                            $mensaje->cuentasc_id = $servicio->cuentasc_id;
                            $mensaje->save();
                        }
                    }

                    $servicio->estado = "Pendiente";
                    $vale = Vale::where('valeras_id', $idValera)->where('estado', 'Libre')->first();
                    $vale->servicios_id = $servicio->id;
                    $vale->estado = "Visado";
                    $vale->save();

                    $valeserv = new Vale_servicio();
                    $valeserv->vales_id = $vale->id;
                    $valeserv->servicios_id = $servicio->id;
                    $valeserv->save();
                }
            }

            $servicio->save();

            DB::commit();

            return redirect('servicios/en_curso');
        } catch (Exception $ex) {
            DB::rollBack();

            return back()->withErrors(["sql" => $ex->getMessage()]);
        }
    }

    public function editarFinalizadoMajorel(Request $request)
    {
        $servicio = Servicio::with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }, 'ruta.pasajeros'])->find($request->input('idservicio'));
        $novedades = NovedadMajorel::get();
        $servicio->novedadesmaj = $novedades;

        return json_encode($servicio);
    }

    public function actualizarFinalizadoMajorel(Request $request)
    {
        $servicio = Servicio::select('id', 'fechaprogramada', 'usuarios', 'direccion', 'adicional', 'estado', 'placa', 'asignacion', 'cuentasc_id')->with(['cuentac' => function ($q) {
            $q->select('id', 'saldo');
        }, 'ruta.pasajeros', 'vale', 'cancelacion'])->find($request->input('idMajorel'));
        foreach ($servicio->ruta->pasajeros as $pasajero) {
            if ($request->input($pasajero->id) != 0) {
                $rutapas = Pasajerosxruta::where('rutas_id', $servicio->ruta->id)->where('pasajeros_id', $pasajero->id)->first();
                if ($rutapas != null) {
                    $rutapas->novedadesmaj_id = $request->input($pasajero->id);
                    $rutapas->observaciones = $request->input('obs' . $pasajero->id);
                    $rutapas->save();
                }
            }
        }

        return redirect('servicios/finalizados');
    }

    public function descargarProgramacion(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $colores = ['FC7C7C', '6C6CF5', '45E645', 'E7F57F', 'B5B3B3'];
        if ($request->filled('cliente')) {
            $cliente = $request->input('cliente');
        } else {
            $cliente = "Majorel";
        }
        try {
            if ($cliente == "Majorel") {
                // Filtra las valeras activas majorel
                $valerasMajorelOB = Valera::whereHas('cuentae', function ($q) {
                    $q->where('agencia_tercero_TERCERO', 3408);
                })->select('id', 'nombre')
                    ->where('estado', 1)
                    ->get();

                // Extraer IDs de las valeras
                $valerasMajorel = [];

                foreach ($valerasMajorelOB as $valera) {
                    $valerasMajorel[] = $valera->id;
                }

                $programacion = Programacion::with(
                    ['rutas' => function ($q) use ($valerasMajorel) {
                        $q->whereIn('valeras_id', $valerasMajorel)->orderBy('valeras_id', 'ASC')->orderBy('numero', 'ASC')  // Condición en la relación 'rutas'
                            ->with(['pasajeros', 'servicioRuta' => function ($r) {
                                $r->with(['vale.valera', 'cuentac' => function ($s) {
                                    $s->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($t) {
                                        $t->select('CONDUCTOR', 'CELULAR', 'NOMBRE');
                                    }]);
                                }]);
                            }]);
                    }]
                )->where('dia', $request->input('fechamaj'))->first();

                if ($programacion == null) {
                    return back()->withErrors(["sql" => "No se encontraron rutas programadas el día " . $request->input('fechamaj')]);
                } else {
                    $excel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "plantillaMajorel3.xlsx");
                    $hoja = $excel->setActiveSheetIndex(0);
                    $fila = 2;
                    $color = 0;
                    foreach ($programacion->rutas as $ruta) {
                        if ($ruta->servicioRuta->estado == "Finalizado" || $ruta->servicioRuta->estado == "Pendiente" || $ruta->servicioRuta->estado == "En curso" || $ruta->servicioRuta->estado == "Asignado" || $ruta->servicioRuta->estado == "No vehiculo") {
                            //if (true) {
                            if ($ruta->fecha != null) {
                                $entrada = Carbon::parse($ruta->fecha . " " . $ruta->hora);
                            } else {
                                $entrada = Carbon::parse($ruta->servicioRuta->fechaprogramada);
                            }
                            $recogidos = 0;
                            foreach ($ruta->pasajeros as $pasajero) {
                                if ($pasajero->pivot->novedadesmaj_id == null) {
                                    $recogidos++;
                                } else {
                                    if ($pasajero->pivot->novedad->codigo != "PA") {
                                        $recogidos++;
                                    }
                                }
                            }
                            if ($recogidos == 0) {
                                $recogidos = 1;
                            }
                            foreach ($ruta->pasajeros as $pasajero) {
                                $hoja->getCell('A' . $fila)->setValue("TAXCENTER");
                                if ($pasajero->ceco == null) {
                                    $hoja->getCell('B' . $fila)->setValue($ruta->ceco);
                                } else {
                                    $hoja->getCell('B' . $fila)->setValue($pasajero->ceco);
                                }
                                $hoja->getCell('C' . $fila)->setValue($pasajero->identificacion);
                                $hoja->getCell('D' . $fila)->setValue($pasajero->nombre);
                                $hoja->getCell('E' . $fila)->setValue($pasajero->direccion);
                                $hoja->getCell('F' . $fila)->setValue($pasajero->barrio);
                                $hoja->getCell('G' . $fila)->setValue($pasajero->municipio);
                                $hoja->getCell('H' . $fila)->setValue($pasajero->celulares);
                                $hoja->getCell('I' . $fila)->setValue($entrada->format('H:i'));
                                $hoja->getCell('J' . $fila)->setValue($entrada->format('Y/m/d'));
                                $hoja->getCell('K' . $fila)->setValue($ruta->tipo);
                                $hoja->getCell('L' . $fila)->setValue($ruta->servicioRuta->adicional);
                                $hoja->getCell('N' . $fila)->setValue($ruta->numero);
                                if ($ruta->servicioRuta->cuentasc_id != null) {
                                    $hoja->getCell('O' . $fila)->setValue($ruta->servicioRuta->placa);
                                }
                                if ($ruta->servicioRuta) {
                                    if ($ruta->servicioRuta->cuentac && $ruta->servicioRuta->cuentac->conductor) {
                                        $hoja->getCell('P' . $fila)->setValue($ruta->servicioRuta->cuentac->conductor->NOMBRE);
                                        $hoja->getCell('Q' . $fila)->setValue($ruta->servicioRuta->cuentac->conductor->CELULAR);
                                        $hoja->getCell('R' . $fila)->setValue(4);
                                    }
                                }

                                if ($ruta->servicioRuta->vale != null) {
                                    $hoja->getCell('Y' . $fila)->setValue($ruta->servicioRuta->vale->codigo);
                                    $hoja->getCell('Z' . $fila)->setValueExplicit($ruta->servicioRuta->vale->clave, DataType::TYPE_STRING);
                                    $hoja->getCell('AA' . $fila)->setValue($ruta->servicioRuta->vale->valera->nombre);
                                }

                                if ($ruta->servicioRuta->estado == "Finalizado") {
                                    // $hoja->getCell('AA' . $fila)->setValue($ruta->servicioRuta->unidades);
                                    $hoja->getCell('W' . $fila)->setValue($ruta->servicioRuta->valor / $recogidos);
                                } else {
                                    // $hoja->getCell('AA' . $fila)->setValue(0);
                                    $hoja->getCell('W' . $fila)->setValue(0);
                                }

                                if ($pasajero->pivot->novedadesmaj_id != null) {
                                    $hoja->getCell('M' . $fila)->setValue($pasajero->pivot->novedad->codigo);
                                    $hoja->getCell('V' . $fila)->setValue($pasajero->pivot->novedad->descripcion);
                                    $hoja->getCell('U' . $fila)->setValue($pasajero->pivot->observaciones);
                                    if (count($ruta->pasajeros) > 1 && $pasajero->pivot->novedad->codigo == "PA") { //Carlos
                                        $hoja->getCell('W' . $fila)->setValue(0);
                                    }
                                } else {
                                    /*if ($ruta->servicioRuta->estado == "No vehiculo" || $ruta->servicioRuta->estado == "Cancelado" || $ruta->servicioRuta->estado == "Cancelado devuelto") {
                                        $hoja->getCell('Q'.$fila)->setValue("CANCELADO");
                                        $hoja->getCell('AB'.$fila)->setValue(0);
                                    }*/
                                }
                                // if ($pasajero->servicio == null) {
                                //     $hoja->getCell('R' . $fila)->setValue($ruta->servicio);
                                // } else {
                                //     $hoja->getCell('R' . $fila)->setValue($pasajero->servicio);
                                // }
                                // $hoja->getCell('S' . $fila)->setValue("=DATEVALUE(J" . $fila . ")");
                                // $hoja->getCell('T' . $fila)->setValue('=+S' . $fila . '&"-"&O' . $fila);
                                if ($ruta->llegada != null && !empty($ruta->llegada)) {
                                    $hoja->getCell('S' . $fila)->setValue($ruta->llegada);
                                } else {
                                    if ($ruta->tipo == "Salida") {
                                        $registro = Registro::where('servicios_id', $ruta->servicioRuta->id)->where('evento', 'Arribo')->first();
                                    } else {
                                        $registro = Registro::where('servicios_id', $ruta->servicioRuta->id)->where('evento', 'Fin')->first();
                                    }
                                    if ($registro != null) {
                                        $hoja->getCell('S' . $fila)->setValue(Carbon::parse($registro->fecha)->format('H:i'));
                                    }
                                }

                                $hoja->getCell('X' . $fila)->setValue($ruta->estimado);
                                $hoja->getStyle('A' . $fila . ':W' . $fila)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colores[$color]);
                                $fila++;
                            }
                            $color++;
                            if ($color == 5) {
                                $color = 0;
                            }
                        }
                    }

                    $writer = new Xlsx($excel);
                    $writer->save('Majorel.xlsx');

                    return response()->download('Majorel.xlsx', 'Majorel' . $request->input('fechamaj') . '.xlsx', ['contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
                }
            } elseif ($cliente == "TransAmerica") {
                $clienteId = 13977;
                $programacion = Programacion::with(['rutas' => function ($q) use ($clienteId) {
                    $q->with('servicioRuta.vale', 'pasajeros')->where('clientes_id', $clienteId);
                }])
                    ->where('dia', $request->input('fechamaj'))->first();
                if ($programacion == null) {
                    return back()->whithErrors(["sql" => "No se encontraron rutas programadas de TransAmerica el dia" . $request->input('fechamaj')]);
                } else {
                    $excel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "plantillaTransAmerica.xlsx");
                    $hoja = $excel->setActiveSheetIndex(0);
                    $fila = 2;
                    $color = 0;
                    foreach ($programacion->rutas as $ruta) {
                        if ($ruta->servicioRuta->estado == "Finalizado" || $ruta->servicioRuta->estado == "Pendiente" || $ruta->servicioRuta->estado == "En curso" || $ruta->servicioRuta->estado == "Asignado") {
                            if ($ruta->fecha != null) {
                                $entrada = Carbon::parse($ruta->fecha . " " . $ruta->hora);
                            } else {
                                $entrada = Carbon::parse($ruta->servicioRuta->fechaprogramada);
                            }
                            $recogidos = 0;
                            foreach ($ruta->pasajeros as $pasajero) {
                                $recogidos++;
                            }
                            if ($recogidos == 0) {
                                $recogidos = 1;
                            }
                            foreach ($ruta->pasajeros as $pasajero) {
                                $hoja->getCell('A' . $fila)->setValue($pasajero->identificacion);
                                $hoja->getCell('B' . $fila)->setValue($pasajero->nombre);
                                $hoja->getCell('C' . $fila)->setValue($pasajero->celulares);
                                $hoja->getCell('D' . $fila)->setValue($pasajero->direccion);
                                $hoja->getCell('E' . $fila)->setValue($pasajero->barrio);
                                $hoja->getCell('F' . $fila)->setValue($pasajero->municipio);
                                $hoja->getCell('G' . $fila)->setValue($entrada->format('H:i'));
                                $hoja->getCell('H' . $fila)->setValue($this->getMes($entrada->month));
                                $hoja->getCell('I' . $fila)->setValue($entrada->format('Y/m/d'));
                                $hoja->getCell('J' . $fila)->setValue("TAXI SEGURO");
                                $hoja->getCell('K' . $fila)->setValue($ruta->numero);
                                $hoja->getCell('L' . $fila)->setValue($ruta->tipo);
                                if ($ruta->servicioRuta->cuentac_id != null) {
                                    $hoja->getCell('M' . $fila)->setValue($ruta->servicioRuta->placa);
                                }
                                if ($ruta->servicioRuta->vale != null) {
                                    $hoja->getCell('N' . $fila)->setValue($ruta->servicioRuta->vale->codigo);
                                    $hoja->getCell('O' . $fila)->setValueExplicit($ruta->servicioRuta->vale->clave, Datatype::TYPE_STRING);
                                }
                                if ($ruta->servicioRuta->estado == "Finalizado") {
                                    $hoja->getCell('P' . $fila)->setValue($ruta->servicioRuta->unidades);
                                    $hoja->getCell('Q' . $fila)->setValue($ruta->servicioRuta->valor / $recogidos);
                                }
                                if ($pasajero->pivot->novedadesmaj_id != null) {
                                    $hoja->getCell('R' . $fila)->setValue($pasajero->pivot->novedad->descripcion);
                                }
                                $hoja->getCell('S' . $fila)->setValue($ruta->llegada);

                                $hoja->getStyle('A' . $fila . ':S' . $fila)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colores[$color]);
                                $fila++;
                            }
                            $color++;
                            if ($color == 5) {
                                $color = 0;
                            }
                        }
                    }
                    $writer = new Xlsx($excel);
                    $writer->save('TransAmerica.xlsx');

                    return response()->download('TransAmerica.xlsx', 'TransAmerica' . $request->input('fechamaj') . '.xlsx', ['contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage() . "---" . $ex->getLine();
        }
    }

    function distancia($point1_lat, $point1_long, $point2_lat, $point2_long)
    {
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat)) * cos(deg2rad($point2_lat)) * cos(deg2rad($point1_long - $point2_long)))));
        if (is_nan($degrees)) {
            $distance = 0;
        } else {
            $distance = $degrees * 111.13384;
        }

        return round($distance, 3);
    }

    public function getMes($numero)
    {
        switch ($numero) {
            case 1:
                $mes = "ENERO";
                break;
            case 2:
                $mes = "FEBRERO";
                break;
            case 3:
                $mes = "MARZO";
                break;
            case 4:
                $mes = "ABRIL";
                break;
            case 5:
                $mes = "MAYO";
                break;
            case 6:
                $mes = "JUNIO";
                break;
            case 7:
                $mes = "JULIO";
                break;
            case 8:
                $mes = "AGOSTO";
                break;
            case 9:
                $mes = "SEPTIEMBRE";
                break;
            case 10:
                $mes = "OCTUBRE";
                break;
            case 11:
                $mes = "NOVIEMBRE";
                break;
            case 12:
                $mes = "DICIEMBRE";
                break;
        }
        return $mes;
    }

    public function corregirMajorel()
    {
        try {
            //$rutas = Ruta::with('servicioRuta','pasajeros')->whereHas('servicioRuta', function($q){$q->where('estado','Pendiente');})->where('programaciones_id', 3)->get();
            $rutas = Ruta::with('servicioRuta', 'pasajeros')->has('servicioRuta')->where('programaciones_id', 7)->get();
            foreach ($rutas as $ruta) {
                $direccion = "";
                $usuarios = "";
                $adicional = "Llevar a ";
                $hora = substr($ruta->hora, -4, 1);
                if ($hora == "0") {
                    $ruta->tipo = "Entrada";
                } else {
                    $ruta->tipo = "Salida";
                }
                $ruta->save();
                for ($i = count($ruta->pasajeros) - 1; $i >= 0; $i--) {
                    if ($ruta->pasajeros[$i]->pivot->cancelado == null) {
                        if ($ruta->tipo == "Entrada") {
                            $direccion = $direccion . $ruta->pasajeros[$i]->direccion . ", " . $ruta->pasajeros[$i]->barrio . ", " . $ruta->pasajeros[$i]->municipio . "\n";
                            $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                        } else {
                            $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                            $adicional = $adicional . " " . $ruta->pasajeros[$i]->barrio;
                        }
                    }
                }
                if ($ruta->tipo == "Entrada") {
                    $ruta->servicioRuta->direccion = $direccion;
                } else {
                    $ruta->servicioRuta->direccion = "Zona franca anillo vial";
                    $ruta->servicioRuta->adicional = $adicional;
                }
                $ruta->servicioRuta->estadocobro = 0;
                $ruta->servicioRuta->usuarios = $usuarios;
                $ruta->servicioRuta->save();
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return "Listo";
    }

    public function enviarSMS($numero, $nombre, $conductor, $placa, $fecha, $tipoRuta)
    {
        $texto = "Sr(a) " . strtoupper($nombre) . "; TAXCENTER informa que " . $conductor . "(" . $placa . ") fue asignado para su servicio el ";
        $fechaSms = Carbon::parse($fecha);
        if ($tipoRuta == "Entrada") {
            $fechaSms->addMinutes(20);
            $texto = $texto . $fechaSms->day . " de " . ucfirst(strtolower($this->getMes($fechaSms->month))) . ", para entrada a las " . $fechaSms->format('H:i');
        } else {
            $fechaSms->subMinutes(15);
            $texto = $texto . $fechaSms->day . " de " . ucfirst(strtolower($this->getMes($fechaSms->month))) . ", para salida a las " . $fechaSms->format('H:i');
        }
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
                    'unicode' => '0',
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
}
