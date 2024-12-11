<?php

namespace App\Http\Controllers;

use App\Models\Agencia_tercero;
use App\Models\Contrato_vale;
use App\Models\Contrato_vale_ruta;
use App\Models\Cuentac;
use App\Models\Registro;
use App\Models\Servicio;
use App\Models\Servicio_usuariosav;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Usuarioav;
use App\Models\Valeav;
use App\Models\Valera;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SoapClient;
use SoapFault;
use stdClass;

class AviancaController extends Controller
{
    public function rutas(Request $request)
    {
        $hoy = Carbon::now()->format('Y-m-d');
        //$hoy = '2023-09-01';
        $contrato = Contrato_vale::where('tercero', 435)->where('FECHA_INICIO', '<=', $hoy)->where('FECHA_FIN', '>=', $hoy)->first();
        //$contrato = Contrato_vale::where('tercero', 435)->orderBy('CONTRATO_VALE', 'DESC')->first();
        if ($contrato != null) {
            $info = new stdClass();

            if ($request->input('tipovale') == "Equipaje") {
                $rutas = Contrato_vale_ruta::where('CONTRATO_VALE', $contrato->CONTRATO_VALE)->where('ORIGEN', 'like', 'equipaje%')->get();
            } else {
                $rutas = Contrato_vale_ruta::where('CONTRATO_VALE', $contrato->CONTRATO_VALE)->where(function ($q) {
                    $q->whereBetween('SECUENCIA', array(0, 5))->orWhereBetween('SECUENCIA', array(12, 15))->orWhereBetween('SECUENCIA', array(69, 70))->orWhere('SECUENCIA', 83)->orWhere('SECUENCIA', 94);
                })->get();
            }

            $valera = Valera::where('inicio', '<=', $hoy)->where('fin', '>=', $hoy)->whereHas('cuentae', function ($q) {
                $q->where('agencia_tercero_TERCERO', 435);
            })->first();
            //$valera = Valera::find(60);
            $vale = Valeav::where('valeras_id', $valera->id)->where('estado', 'Libre')->first();

            $info->rutas = $rutas;
            $info->valeras = [$valera];
            $info->vale = $vale;

            return json_encode($info);
        } else {
            return json_encode([]);
        }
    }

    public function getEdicion(Request $request)
    {
        $servicio = Servicio::select('id', 'fechaprogramada', 'placa', 'usuarios', 'asignacion', 'cuentasc_id')->with(['cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }, 'usuariosav'])->find($request->input('idservicio'));

        return json_encode($servicio);
    }

    public function setEdicion(Request $request)
    {
        $servicio = Servicio::select('id', 'usuarios', 'fechaprogramada', 'asignacion')->find($request->input('idservicio'));
        if ($request->filled('usuariosl')) {
            $servicio->usuarios = $request->input('usuariosl');
        } else {
            Servicio_usuariosav::where('servicios_id', $servicio->id)->delete();
            $txtusuarios = "";
            for ($i = 0; $i < 3; $i++) {
                $usuav = Usuarioav::where('identificacion', $request->input('usuav' . ($i + 1)))->first();
                if ($usuav != null) {
                    $txtusuarios = $txtusuarios . $usuav->nombres . " " . $usuav->apellidos . "\n";
                    $servusu = new Servicio_usuariosav();
                    $servusu->usuariosav_id = $usuav->id;
                    $servusu->servicios_id = $servicio->id;
                    $servusu->save();
                }
            }
            $servicio->usuarios = $txtusuarios;
        }
        if ($request->filled('placa') && $servicio->asignacion == "Directo") {
            $placa = explode("_", $request->input('placa'));
            $servicio->placa = $placa[0];
            $servicio->cuentasc_id = $placa[2];
            $servicio->users3_id = Auth::user()->id;
        }

        $servicio->fechaprogramada = $request->input('fechades');
        $servicio->save();

        return redirect('servicios/filtrar_en_curso?id=' . $servicio->id);
    }

    public function importarUsuarios()
    {
        $objPHPExcel = IOFactory::load("directorio.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $numRows = $sheet->getHighestRow();

        if ($numRows > 1) {
            for ($i = 2; $i < $numRows; $i++) {
                $ide = $sheet->getCell('A' . $i)->getCalculatedValue();
                $nombre = explode(" ", $sheet->getCell('F' . $i)->getCalculatedValue());
                $avi = Usuarioav::where('identificacion', $ide)->first();
                if ($ide != null && $nombre[0] != "" && $avi == null) {
                    $avuser = new Usuarioav();
                    $avuser->identificacion = $ide;
                    $leng = count($nombre);
                    if ($leng == 4) {
                        $avuser->nombres = $nombre[0] . " " . $nombre[1];
                        $avuser->apellidos = $nombre[2] . " " . $nombre[3];
                    } elseif ($leng == 3) {
                        $avuser->nombres = $nombre[0];
                        $avuser->apellidos = $nombre[1] . " " . $nombre[2];
                    } else {
                        $avuser->nombres = $nombre[0];
                        $avuser->apellidos = $nombre[1];
                    }
                    $avuser->centrocosto = $sheet->getCell('G' . $i)->getCalculatedValue();
                    if ($sheet->getCell('L' . $i)->getCalculatedValue() == "Tierra") {
                        $avuser->tipo = "Tierra";
                    } else {
                        $avuser->tipo = "Tripulación";
                    }
                    $avuser->base = $sheet->getCell('K' . $i)->getCalculatedValue();
                    $avuser->vicepresidencia = $sheet->getCell('N' . $i)->getCalculatedValue();
                    $avuser->division = $sheet->getCell('C' . $i)->getCalculatedValue();
                    $avuser->departamento = $sheet->getCell('D' . $i)->getCalculatedValue();
                    $avuser->zona = $sheet->getCell('P' . $i)->getCalculatedValue();
                    $avuser->direccion = $sheet->getCell('O' . $i)->getCalculatedValue();
                    $avuser->complemento = $sheet->getCell('Q' . $i)->getCalculatedValue();
                    $avuser->save();
                } elseif ($ide != null && $nombre[0] != "" && $avi != null) {
                    $avi->base = $sheet->getCell('K' . $i)->getCalculatedValue();
                    $avi->vicepresidencia = $sheet->getCell('N' . $i)->getCalculatedValue();
                    $avi->division = $sheet->getCell('C' . $i)->getCalculatedValue();
                    $avi->departamento = $sheet->getCell('D' . $i)->getCalculatedValue();
                    $avi->zona = $sheet->getCell('P' . $i)->getCalculatedValue();
                    $avi->direccion = $sheet->getCell('O' . $i)->getCalculatedValue();
                    $avi->complemento = $sheet->getCell('Q' . $i)->getCalculatedValue();
                    $avi->save();
                }
            }
        }

        return "Listo";
    }

    public function getUsuariosav(Request $request)
    {
        $usuariosav = Usuarioav::where('identificacion', 'like', $request->input('identificacion') . '%')->get();

        return json_encode($usuariosav);
    }

    public function valesAvianca($valera)
    {
        $valera = Valera::find($valera);
        $vales = Valeav::with(['servicio' => function ($q) {
            $q->select('id', 'usuarios', 'unidades', 'cobro', 'valor')->with(['registros' => function ($r) {
                $r->select('id', 'fecha', 'servicios_id');
            }, 'usuariosav']);
        }])->where('valeras_id', $valera->id)->paginate(50);
        $libres = Valeav::where('valeras_id', $valera->id)->where('estado', 'Libre')->count();
        $asignados = Valeav::where('valeras_id', $valera->id)->where('estado', 'Asignado')->count();
        $visados = Valeav::where('valeras_id', $valera->id)->where('estado', 'Visado')->count();
        $usados = Valeav::where('valeras_id', $valera->id)->where('estado', 'Usado')->count();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.valesavianca', compact('vales', 'valera', 'usuario', 'libres', 'asignados', 'visados', 'usados'));
    }

    public function filtrarVales(Request $request, Valera $valera)
    {

        // Inicializar la consulta básica
        $query = Valeav::with(['servicio' => function ($q) {
            $q->select('id', 'usuarios', 'unidades', 'cobro', 'valor', 'fecha')
                ->with(['registros' => function ($r) {
                    $r->select('id', 'fecha', 'servicios_id');
                }, 'usuariosav']);
        }])->where('valeras_id', $valera->id);

        // Contar los diferentes estados
        $libres = $query->clone()->where('estado', 'Libre')->count();
        $asignados = $query->clone()->where('estado', 'Asignado')->count();
        $visados = $query->clone()->where('estado', 'Visado')->count();
        $usados = $query->clone()->where('estado', 'Usado')->count();

        // Aplicar filtros según los parámetros de la solicitud
        if ($request->filled('codigo')) {
            $query->where('codigo', $request->input('codigo'));
            $filtro = ['Código', $request->input('codigo')];
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
            $filtro = ['Estado', $request->input('estado')];
        } elseif ($request->filled('fecha_inicio')) {
            $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
            $query->whereHas('servicio', function ($q) use ($fechaInicio) {
                $q->whereDate('fecha', $fechaInicio);
            });
            $filtro = ['FechaInicio', $fechaInicio->toDateString()];
        } elseif ($request->filled('fecha_fin')) {
            $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
            $query->whereHas('servicio', function ($q) use ($fechaFin) {
                $q->whereDate('fecha', $fechaFin);
            });
            $filtro = ['FechaFin', $fechaFin->toDateString()];
        } else {
            // Si no hay filtros aplicados, mostrar todos los registros hace lo que hacía el filled
            $vales = $query->paginate(50)->appends($request->query());
            $usuario = User::with('modulos')->find(Auth::id());
            return view('valeras.valesavianca', compact('vales', 'valera', 'usuario', 'libres', 'asignados', 'visados', 'usados', 'filtro'));
        }

        // Obtener los resultados con paginación
        $vales = $query->paginate(50)->appends($request->query());

        // Obtener el usuario autenticado
        $usuario = User::with('modulos')->find(Auth::id());

        return view('valeras.valesavianca', compact('vales', 'valera', 'usuario', 'libres', 'asignados', 'visados', 'usados', 'filtro'));
    }

    public function exportarVales(Request $request, Valera $valera)
    {
        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));
            if ($filtro[0] == "Código") {
                $vales = Valeav::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente' => function ($t) {
                        $t->select('id', 'nombres', 'telefono');
                    }, 'usuariosav']);
                }])->where('valeras_id', $valera->id)->where('codigo', $filtro[1])->get();
            } elseif ($filtro[0] == "Estado") {
                $vales = Valeav::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente' => function ($t) {
                        $t->select('id', 'nombres', 'telefono');
                    }, 'usuariosav']);
                }])->where('valeras_id', $valera->id)->where('estado', $filtro[1])->get();
            }
        } else {
            $vales = Valeav::with(['servicio' => function ($q) {
                $q->with(['cuentac' => function ($r) {
                    $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                        $s->select('CONDUCTOR', 'NOMBRE');
                    }]);
                }, 'cliente' => function ($t) {
                    $t->select('id', 'nombres', 'telefono');
                }, 'usuariosav']);
            }])->where('valeras_id', $valera->id)->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:S1");
        $sheet->setCellValue("A1", "Vales de " . $valera->nombre);
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:S1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID Servicio");
        $sheet->setCellValue("B2", "Fecha");
        $sheet->setCellValue("C2", "Cliente");
        $sheet->setCellValue("D2", "Usuarios");
        $sheet->setCellValue("E2", "Teléfono");
        $sheet->setCellValue("F2", "Dir. Origen");
        $sheet->setCellValue("G2", "Beneficiario");
        $sheet->setCellValue("H2", "Centro de Costo");
        $sheet->setCellValue("I2", "Despacho");
        $sheet->setCellValue("J2", "Pago");
        $sheet->setCellValue("K2", "Valera");
        $sheet->setCellValue("L2", "Código vale");
        $sheet->setCellValue("M2", "Ruta");
        $sheet->setCellValue("N2", "Valor servicio");
        $sheet->setCellValue("O2", "Asignación");
        $sheet->setCellValue("P2", "Conductor");
        $sheet->setCellValue("Q2", "Vehiculo");
        $sheet->setCellValue("R2", "Observaciones");
        $sheet->setCellValue("S2", "Estado");
        $sheet->getStyle("A1:S2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($vales as $vale) {
            if ($vale->servicio != null) {
                $sheet->setCellValue("A" . $indice, $vale->servicio->id);
                $sheet->setCellValue("B" . $indice, $vale->servicio->fecha);
                $sheet->setCellValue("C" . $indice, $vale->servicio->cliente->nombres);
                $sheet->setCellValue("D" . $indice, $vale->servicio->usuarios);
                $sheet->setCellValue("E" . $indice, $vale->servicio->cliente->telefono);
                $sheet->setCellValue("F" . $indice, $vale->servicio->direccion);
                $sheet->setCellValue("G" . $indice, $vale->servicio->usuarios);
                if (count($vale->servicio->usuariosav) > 0) {
                    $sheet->setCellValue("H" . $indice, $vale->servicio->usuariosav[0]->centrocosto);
                } else {
                    $sheet->setCellValue("H" . $indice, $vale->centrocosto);
                }
                if ($vale->servicio->fechaprogramada == null) {
                    $sheet->setCellValue("I" . $indice, "Inmediato");
                } else {
                    $sheet->setCellValue("I" . $indice, "Programado");
                }
                $sheet->setCellValue("J" . $indice, $vale->servicio->pago);
            } else {
                $sheet->setCellValue("A" . $indice, "");
                $sheet->setCellValue("B" . $indice, "");
                $sheet->setCellValue("C" . $indice, "");
                $sheet->setCellValue("D" . $indice, "");
                $sheet->setCellValue("E" . $indice, "");
                $sheet->setCellValue("F" . $indice, "");
                $sheet->setCellValue("G" . $indice, "");
                $sheet->setCellValue("H" . $indice, "");
                $sheet->setCellValue("I" . $indice, "");
                $sheet->setCellValue("J" . $indice, "");
            }

            $sheet->setCellValue("K" . $indice, $valera->nombre);
            $sheet->setCellValue("L" . $indice, $vale->codigo);

            if ($vale->servicio != null) {
                $sheet->setCellValue("M" . $indice, $vale->servicio->unidades . " " . $vale->servicio->cobro);
                $sheet->setCellValue("N" . $indice, $vale->servicio->valor);
                $sheet->setCellValue("O" . $indice, $vale->servicio->asignacion);

                if ($vale->servicio->cuentac != null) {
                    $sheet->setCellValue("P" . $indice, $vale->servicio->cuentac->conductor->NOMBRE);
                } else {
                    $sheet->setCellValue("P" . $indice, "");
                }

                $sheet->setCellValue("Q" . $indice, $vale->servicio->placa);
                $sheet->setCellValue("R" . $indice, $vale->servicio->observaciones);
                $sheet->setCellValue("S" . $indice, $vale->servicio->estado);
            } else {
                $sheet->setCellValue("M" . $indice, "");
                $sheet->setCellValue("N" . $indice, "");
                $sheet->setCellValue("O" . $indice, "");
                $sheet->setCellValue("P" . $indice, "");
                $sheet->setCellValue("Q" . $indice, "");
                $sheet->setCellValue("R" . $indice, "");
                $sheet->setCellValue("S" . $indice, "");
            }
            $indice++;
        }

        foreach (range('A', 'S') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Vales.xlsx');
        $archivo = file_get_contents('Vales.xlsx');
        unlink('Vales.xlsx');

        return base64_encode($archivo);
    }

    public function valesAgencia($agencia)
    {
        $agencia = explode("_", $agencia);
        $tercero = Tercero::select('TERCERO', 'RAZON_SOCIAL')->where('TERCERO', $agencia[0])->first();
        $agencia = Agencia_tercero::with('cuentae')->where('TERCERO', $tercero->TERCERO)->where('CODIGO', $agencia[1])->first();

        if ($agencia->cuentae != null) {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->mergeCells("A1:S1");
            $sheet->setCellValue("A1", "Vales usados por la agencia " . $agencia->NOMBRE);

            $style = array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                )
            );
            $sheet->getStyle("A1:S1")->applyFromArray($style);

            $sheet->setCellValue("A2", "ID");
            $sheet->setCellValue("B2", "Fecha");
            $sheet->setCellValue("C2", "Cliente");
            $sheet->setCellValue("D2", "Usuarios");
            $sheet->setCellValue("E2", "Teléfono");
            $sheet->setCellValue("F2", "Dir. Origen");
            $sheet->setCellValue("G2", "Beneficiario");
            $sheet->setCellValue("H2", "Centro de Costo");
            $sheet->setCellValue("I2", "Despacho");
            $sheet->setCellValue("J2", "Pago");
            $sheet->setCellValue("K2", "Valera");
            $sheet->setCellValue("L2", "Código vale");
            $sheet->setCellValue("M2", "Ruta");
            $sheet->setCellValue("N2", "Valor servicio");
            $sheet->setCellValue("O2", "Asignación");
            $sheet->setCellValue("P2", "Conductor");
            $sheet->setCellValue("Q2", "Vehiculo");
            $sheet->setCellValue("R2", "Observaciones");
            $sheet->setCellValue("S2", "Estado");
            $sheet->getStyle("A1:S2")->getFont()->setBold(true);

            $valeras = Valera::where('cuentase_id', $agencia->cuentae->id)->get();
            $indice = 3;
            foreach ($valeras as $valera) {
                $vales = Valeav::with(['servicio' => function ($q) {
                    $q->with(['cuentac' => function ($r) {
                        $r->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($s) {
                            $s->select('CONDUCTOR', 'NOMBRE');
                        }]);
                    }, 'cliente', 'usuariosav']);
                }])->where('valeras_id', $valera->id)->where('estado', 'Usado')->get();
                foreach ($vales as $vale) {
                    $sheet->setCellValue("A" . $indice, $vale->servicio->id);
                    $sheet->setCellValue("B" . $indice, $vale->servicio->fecha);
                    $sheet->setCellValue("C" . $indice, $vale->servicio->cliente->nombres);
                    $sheet->setCellValue("D" . $indice, $vale->servicio->usuarios);
                    $sheet->setCellValue("E" . $indice, $vale->servicio->cliente->telefono);
                    $sheet->setCellValue("F" . $indice, $vale->servicio->direccion);
                    $sheet->setCellValue("G" . $indice, $vale->servicio->usuarios);
                    if (count($vale->servicio->usuariosav) > 1) {
                        $sheet->setCellValue("H" . $indice, $vale->servicio->usuariosav[0]->centrocosto);
                    } else {
                        $sheet->setCellValue("H" . $indice, $vale->centrocosto);
                    }

                    if ($vale->servicio->fechaprogramada == null) {
                        $sheet->setCellValue("I" . $indice, "Inmediato");
                    } else {
                        $sheet->setCellValue("I" . $indice, "Programado");
                    }

                    $sheet->setCellValue("J" . $indice, $vale->servicio->pago);
                    $sheet->setCellValue("K" . $indice, $valera->nombre);
                    $sheet->setCellValue("L" . $indice, $vale->codigo);
                    $sheet->setCellValue("M" . $indice, $vale->servicio->unidades . " " . $vale->servicio->cobro);
                    $sheet->setCellValue("N" . $indice, $vale->servicio->valor);
                    $sheet->setCellValue("O" . $indice, $vale->servicio->asignacion);

                    if ($vale->servicio->cuentac == null) {
                        $sheet->setCellValue("P" . $indice, "Sin asignar");
                    } else {
                        $sheet->setCellValue("P" . $indice, $vale->servicio->cuentac->conductor->NOMBRE);
                    }

                    $sheet->setCellValue("Q" . $indice, $vale->servicio->placa);
                    $sheet->setCellValue("R" . $indice, $vale->servicio->observaciones);
                    $sheet->setCellValue("S" . $indice, $vale->servicio->estado);
                    $indice++;
                }
            }

            foreach (range('A', 'S') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('Vales.xlsx');
            $archivo = file_get_contents('Vales.xlsx');
            unlink('Vales.xlsx');

            return base64_encode($archivo);
        } else {
            return "Sin valeras";
        }
    }

    public function finalizar(Request $request)
    {
        //Inicializar transacción y configurar prompt
        $logFile = fopen("../storage/logToIcon.txt", 'a') or die("Error creando archivo");
        set_time_limit(90);
        ini_set('default_socket_timeout', 90);
        DB::beginTransaction();

        try {

            $servicio = Servicio::with(['valeav.valera.cuentae.agencia', 'usuariosav'])->find($request->input('idservicio'));
            $cuentac = Cuentac::with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');
            }])->select('id', 'saldovales', 'conductor_CONDUCTOR')
                ->find($request->input('idtaxista'));

            $cobro = 0;
            $pago = 0;

            $registro = new Registro();
            $registro->fecha = Carbon::now();
            $registro->evento = "Fin";
            $registro->servicios_id = $servicio->id;
            $servicio->cobro = "Ruta";

            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";

            $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $servicio->valeav->contrato)->where('SECUENCIA', $servicio->valeav->secuencia)->first();
            $cobro = $cobro + round($ruta->TARIFA_COBRO);
            $servicio->unidades = $ruta->ORIGEN . "---" . $ruta->DESTINO;
            $soapruta = "R." . $servicio->valeav->secuencia;
            $soapunidades = "0";
            $soaphoras = "0";
            $soapminutos = "0";
            $cuota = round(($ruta->TARIFA_PAGO - ($ruta->TARIFA_PAGO * 0.08)));
            $uno = $cuota % 100;
            $cuota = $cuota - $uno;
            $pago = $pago + $cuota;


            $client = new SoapClient($url, ['exceptions' => true]);
            $result = $client->registrarTicket();
            $dia = date_parse_from_format('Y-m-d H:i:s', $servicio->fecha);
            if ($dia["month"] < 10) {
                $dia["month"] = "0" . $dia["month"];
            }
            if ($dia["day"] < 10) {
                $dia["day"] = "0" . $dia["day"];
            }

            if (count($servicio->usuariosav) > 0) {
                $centrocosto = $servicio->usuariosav[0]->centrocosto;
            } else {
                $centrocosto = $servicio->valeav->centrocosto;
            }
            $servicio->valeav->estado = "Usado";

            $cuentac->saldovales = $cuentac->saldovales + $cuota;

            $parametros = array(
                "ticket" => $result->registrarTicketReturn,
                "numeroIdentificacionEmpresa" => $servicio->valeav->valera->cuentae->agencia->NRO_IDENTIFICACION,
                "nombreValera" => $servicio->valeav->valera->nombre,
                "codigoVale" => $servicio->valeav->codigo,
                "fechaServicio" => $dia["year"] . "/" . $dia["month"] . "/" . $dia["day"],
                "horaServicio" => $dia["hour"] . ":" . $dia["minute"] . ":" . $dia["second"],
                "usuarioVale" => $servicio->usuarios,
                "placa" => $servicio->placa,
                "numeroIdentificacionConductor" => $cuentac->conductor->NUMERO_IDENTIFICACION,
                "unidades" => $soapunidades,
                "horaMovimiento" => $soaphoras,
                "minutoMovimiento" => $soapminutos,
                "horaEspera" => "0",
                "minutoEspera" => "0",
                "rutas" => $soapruta,
                "valor" => $ruta->TARIFA_COBRO,
                "centroCosto" => $centrocosto,
                "referenciado" => "",
                "aeropuerto" => 0
            );

            $peticion = $client->registrarVale($parametros);

            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($parametros) . "-->" . json_encode($peticion->registrarValeReturn)) or die("Error escribiendo en el archivo");

            $finalizadoICON = 0;

            if ($peticion->registrarValeReturn->codigoError != "0000") {
                try {
                    DB::connection('mysql2')->table('ws_servicio')->where("DATOS", "LIKE", $servicio->valeav->valera->cuentae->agencia->NRO_IDENTIFICACION . "|" . $servicio->valeav->valera->nombre . "|" . $servicio->valeav->codigo . "|%")->where('CODIGO_ERROR', '!=', '0000')->delete();

                    $finalizadoICON = DB::connection('mysql2')
                        ->table('ws_servicio')
                        ->where("DATOS", "LIKE", $servicio->valeav->valera->cuentae->agencia->NRO_IDENTIFICACION . "|" . $servicio->valeav->valera->nombre . "|" . $servicio->valeav->codigo . "|%")
                        ->where('CODIGO_ERROR', '=', '0000')->count();
                } catch (Exception $th) {
                    $finalizadoICON = 0;
                }

                if ($finalizadoICON != 1) {
                    throw new Exception($peticion->registrarValeReturn->mensajeError);
                }
            }

            $transaccion = new Transaccion();
            $transaccion->tipo = "Servicio con vale";
            $transaccion->valor = $cuota;
            $transaccion->fecha = Carbon::now();
            $transaccion->cuentasc_id = $cuentac->id;
            $transaccion->save();

            $cuentac->save();
            $servicio->valeav->save();

            $servicio->estado = "Finalizado";
            $servicio->valor = $cobro;
            $servicio->valorc = $pago;
            $servicio->save();

            $registro->save();

            $cuentac->estado = "Libre";
            $cuentac->save();

            // DB::insert('insert into parametros_icon (contenido, servicios_id, transacciones_id) values (?, ?, ?)', [json_encode($parametros, JSON_UNESCAPED_UNICODE), $servicio->id, $transaccion->id]);

            DB::commit();

            fclose($logFile);
            return $servicio->valor;
        } catch (SoapFault $e) {
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") . json_encode($parametros) . "-->" . json_encode($peticion->registrarValeReturn)) or die("Error escribiendo en el archivo");
            DB::rollBack();
            fclose($logFile);
            return "Falla icon_" . $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            fwrite($logFile, "\n" . date("d/m/Y H:i:s") .  $e->getMessage() . ":" . $e->getLine()) or die("Error escribiendo en el archivo");
            fclose($logFile);
            return "Falla icon_" . $e->getMessage() . ":" . $e->getLine();
        }
    }

    public function aviancaGeneral(Request $request)
    {
        $fechaini = Carbon::parse($request->input('fechaini'));
        $fechafin = Carbon::parse($request->input('fechafin'));
        $fechafin->addDay();
        $rango = [$fechaini->format('Y-m-d H:i:s'), $fechafin->format('Y-m-d H:i:s')];
        $servicios = Servicio::has('valeav')->with(['valeav', 'usuariosav', 'cuentac' => function ($q) {
            $q->select('id', 'conductor_CONDUCTOR')->with(['conductor' => function ($r) {
                $r->select('CONDUCTOR', 'NOMBRE');
            }]);
        }])->where('estado', 'Finalizado')->where(function ($q) use ($rango) {
            $q->whereBetween('fechareportado', $rango)->orWhereBetween('fechavianca', $rango);
        })->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ),
            'font' => array(
                'bold' => true
            )
        );
        $sheet->getStyle("A1:AE1")->applyFromArray($style);

        $sheet->setCellValue("A1", "Grupo");
        $sheet->setCellValue("B1", "FechaServicio");
        $sheet->setCellValue("C1", "NroVale");
        $sheet->setCellValue("D1", "Orden/Voucher");
        $sheet->setCellValue("E1", "Servicio");
        $sheet->setCellValue("F1", "Tipo");
        $sheet->setCellValue("G1", "Persona que solicita servicio");
        $sheet->setCellValue("H1", "Vuelo");
        $sheet->setCellValue("I1", "HoraServicio");
        $sheet->setCellValue("J1", "Empresa");
        $sheet->setCellValue("K1", "Cedula");
        $sheet->setCellValue("L1", "Nombre_Usuario");
        $sheet->setCellValue("M1", "Base");
        $sheet->setCellValue("N1", "Vicepresidencia");
        $sheet->setCellValue("O1", "División");
        $sheet->setCellValue("P1", "Departamento");
        $sheet->setCellValue("Q1", "CenCostoDP");
        $sheet->setCellValue("R1", "Origen");
        $sheet->setCellValue("S1", "Destino");
        $sheet->setCellValue("T1", "IDZona");
        $sheet->setCellValue("U1", "Tipo de Vehiculo");
        $sheet->setCellValue("V1", "Movil");
        $sheet->setCellValue("W1", "Placa");
        $sheet->setCellValue("X1", "Nombre Conductor");
        $sheet->setCellValue("Y1", "Observación pasajeros");
        $sheet->setCellValue("Z1", "Valor Avianca");
        $sheet->setCellValue("AA1", "Agente_Reserva");
        $sheet->setCellValue("AB1", "Cantidad");
        $sheet->setCellValue("AC1", "Valor Tercero");
        $sheet->setCellValue("AD1", "Zona a Cobrar");
        $sheet->setCellValue("AE1", "Fecha de Reportado");

        $indice = 2;

        foreach ($servicios as $servicio) {
            if ($servicio->valeav->tipo == "Viajero") {
                $expusuarios = preg_split('/\n|\r\n?/', $servicio->usuarios);
                $lenusuarios = count($expusuarios);
            } else {
                $lenusuarios = count($servicio->usuariosav);
            }
            $userslen = $lenusuarios;
            $iusers = 0;
            do {
                $sheet->setCellValue('A' . $indice, $servicio->valeav->tipo);
                if ($servicio->fechareportado != null) {
                    $fecha = explode(" ", $servicio->fechareportado);
                } else {
                    if ($servicio->fechavianca != null) {
                        $fecha = explode(" ", $servicio->fechavianca);
                    } else {
                        $fecha = explode(" ", $servicio->fecha);
                    }
                }
                $sheet->setCellValue('B' . $indice, $fecha[0]);
                $sheet->setCellValue('C' . $indice, $servicio->valeav->codigo);
                $sheet->setCellValue('D' . $indice, $servicio->valeav->voucher);
                $sheet->setCellValue('E' . $indice, $servicio->valeav->tiposer);
                if ($userslen > 1) {
                    $sheet->setCellValue('F' . $indice, "COLECTIVO");
                } else {
                    $sheet->setCellValue('F' . $indice, "INDIVIDUAL");
                }
                $sheet->setCellValue('G' . $indice, "");
                $sheet->setCellValue('H' . $indice, $servicio->valeav->vuelo);
                $sheet->setCellValue('I' . $indice, $fecha[1]);
                $sheet->setCellValue('J' . $indice, "AVIANCA");
                if ($lenusuarios > 0) {
                    if ($servicio->valeav->tipo == "Viajero") {
                        $sheet->setCellValue('L' . $indice, $expusuarios[$iusers]);
                        $dirav = $servicio->direccion;
                    } else {
                        $sheet->setCellValue('K' . $indice, $servicio->usuariosav[$iusers]->identificacion);
                        $sheet->setCellValue('L' . $indice, $servicio->usuariosav[$iusers]->nombres . " " . $servicio->usuariosav[$iusers]->apellidos);
                        $sheet->setCellValue('M' . $indice, $servicio->usuariosav[$iusers]->base);
                        $sheet->setCellValue('N' . $indice, $servicio->usuariosav[$iusers]->vicepresidencia);
                        $sheet->setCellValue('O' . $indice, $servicio->usuariosav[$iusers]->division);
                        $sheet->setCellValue('P' . $indice, $servicio->usuariosav[$iusers]->departamento);
                        $sheet->setCellValue('Q' . $indice, $servicio->usuariosav[$iusers]->centrocosto);
                        $sheet->setCellValue('T' . $indice, $servicio->usuariosav[$iusers]->zona);
                        $dirav = $servicio->usuariosav[$iusers]->direccion . " " . $servicio->usuariosav[$iusers]->complemento;
                        if ($dirav == " ") {
                            $dirav = $servicio->direccion;
                        }
                    }
                    if ($servicio->valeav->tiposer == "Recogida") {
                        $sheet->setCellValue('R' . $indice, $dirav);
                    } else {
                        $sheet->setCellValue('R' . $indice, "AEROPUERTO");
                    }
                    if ($servicio->valeav->tiposer == "Reparto") {
                        $sheet->setCellValue('S' . $indice, $dirav);
                    } else {
                        $sheet->setCellValue('S' . $indice, "AEROPUERTO");
                    }
                    $iusers++;
                } else {
                    $sheet->setCellValue('K' . $indice, "");
                    $sheet->setCellValue('L' . $indice, str_replace("\n", ", ", $servicio->usuarios));
                    $sheet->setCellValue('M' . $indice, "");
                    $sheet->setCellValue('N' . $indice, "");
                    $sheet->setCellValue('O' . $indice, "");
                    $sheet->setCellValue('P' . $indice, "");
                    $sheet->setCellValue('Q' . $indice, $servicio->valeav->centrocosto);
                    if ($servicio->valeav->tiposer == "Recogida") {
                        $sheet->setCellValue('R' . $indice, $servicio->direccion);
                    } else {
                        $sheet->setCellValue('R' . $indice, "AEROPUERTO");
                    }
                    if ($servicio->valeav->tiposer == "Reparto") {
                        $sheet->setCellValue('S' . $indice, $servicio->direccion);
                    } else {
                        $sheet->setCellValue('S' . $indice, "AEROPUERTO");
                    }
                    $sheet->setCellValue('T' . $indice, "");
                }
                $sheet->setCellValue('U' . $indice, "CAMPERO");
                $sheet->setCellValue('V' . $indice, "0");
                $sheet->setCellValue('W' . $indice, $servicio->placa);
                $sheet->setCellValue('X' . $indice, $servicio->cuentac->conductor->NOMBRE);
                $sheet->setCellValue('Y' . $indice, "");
                $sheet->setCellValue('Z' . $indice, $servicio->valor);
                $sheet->setCellValue('AA' . $indice, "");
                if ($lenusuarios > 0) {
                    $sheet->setCellValue('AB' . $indice, $userslen);
                    $sheet->setCellValue('AC' . $indice, $servicio->valor / $userslen);
                } else {
                    $sheet->setCellValue('AB' . $indice, 1);
                    $sheet->setCellValue('AC' . $indice, $servicio->valor);
                }

                if ($servicio->valeav->secuencia == 0) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 1 - NORTE");
                } elseif ($servicio->valeav->secuencia == 1) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 2 - SUR");
                } elseif ($servicio->valeav->secuencia == 2) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 3 - LEBRIJA");
                } elseif ($servicio->valeav->secuencia == 3) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 4 - GIRÓN");
                } elseif ($servicio->valeav->secuencia == 4) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 5 - FLORIDABLANCA");
                } elseif ($servicio->valeav->secuencia == 5) {
                    $sheet->setCellValue('AD' . $indice, "ZONA 6 - PIEDECUESTA");
                } else {
                    $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $servicio->valeav->contrato)->where('SECUENCIA', $servicio->valeav->secuencia)->first();
                    $sheet->setCellValue('AD' . $indice, $ruta->ORIGEN . ", " . $ruta->DESTINO);
                }

                $sheet->setCellValue('AE' . $indice, $servicio->fechareportado);

                $indice++;
                $lenusuarios--;
            } while ($lenusuarios > 0);
        }

        for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }
        $sheet->getColumnDimension($i)->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $writer->save('Formato Avianca.xlsx');

        $file = public_path() . "/Formato Avianca.xlsx";

        $headers = array(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        return response()->download($file, 'Formato avianca.xlsx', $headers);
    }

    public function pasajerosAvianca()
    {
        $usuariosav = Usuarioav::paginate(20);
        $identificacion = "";
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.listaAvianca', compact('usuariosav', 'identificacion', 'usuario'));
    }

    public function buscarPasajero(Request $request)
    {
        $identificacion = $request->input('identificacion');
        $usuariosav = Usuarioav::where('identificacion', $identificacion)->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.listaAvianca', compact('usuariosav', 'identificacion', 'usuario'));
    }

    public function nuevoPasajero(Request $request)
    {
        $pasajero = new Usuarioav();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $route = ['pasajeros.registrar'];
        $method = 'post';

        return view('users.nuevoAvianca', compact('pasajero', 'route', 'method', 'usuario'));
    }

    public function registrarPasajero(Request $request)
    {
        try {
            $pasajero = new Usuarioav();
            $pasajero->identificacion = $request->input('identificacion');
            $pasajero->nombres = $request->input('nombres');
            $pasajero->apellidos = $request->input('apellidos');
            $pasajero->tipo = $request->input('tipo');
            $pasajero->centrocosto = $request->input('centrocosto');
            $pasajero->base = $request->input('base');
            $pasajero->vicepresidencia = $request->input('vicepresidencia');
            $pasajero->division = $request->input('division');
            $pasajero->departamento = $request->input('departamento');
            $pasajero->zona = $request->input('zona');
            $pasajero->direccion = $request->input('direccion');
            $pasajero->complemento = $request->input('complemento');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->zona) . "&language=ES&key=apikey");
            $json = json_decode(curl_exec($ch));
            if (count($json->results) > 0) {
                $pasajero->latitud = $json->results[0]->geometry->location->lat;
                $pasajero->longitud = $json->results[0]->geometry->location->lng;
            }

            $pasajero->save();

            return redirect('pasajeros/buscar?identificacion=' . $pasajero->identificacion);
        } catch (Exception $e) {
            return back()->withErrors(["sql" => "La identificación " . $request->input('identificacion') . " ya está registrada"]);
        }
    }

    public function editarPasajero(Request $request, Usuarioav $pasajero)
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $route = ['pasajeros.actualizar', 'pasajero' => $pasajero->id];
        $method = 'put';

        return view('users.nuevoAvianca', compact('pasajero', 'route', 'method', 'usuario'));
    }

    public function actualizarPasajero(Request $request, Usuarioav $pasajero)
    {
        $pasajero->tipo = $request->input('tipo');
        $pasajero->centrocosto = $request->input('centrocosto');
        $pasajero->base = $request->input('base');
        $pasajero->vicepresidencia = $request->input('vicepresidencia');
        $pasajero->celular = $request->input('celular');
        $pasajero->division = $request->input('division');
        $pasajero->departamento = $request->input('departamento');
        $pasajero->zona = $request->input('zona');
        $pasajero->direccion = $request->input('direccion');
        $pasajero->complemento = $request->input('complemento');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->zona) . "&language=ES&key=apikey");
        $json = json_decode(curl_exec($ch));
        if (count($json->results) > 0) {
            $pasajero->latitud = $json->results[0]->geometry->location->lat;
            $pasajero->longitud = $json->results[0]->geometry->location->lng;
        }
        $pasajero->save();

        return redirect('pasajeros/buscar?identificacion=' . $pasajero->identificacion);
    }

    public function importarCelulares()
    {
        $objPHPExcel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "usuariosav.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $numRows = $sheet->getHighestRow();

        if ($numRows > 1) {
            for ($i = 6; $i < $numRows; $i++) {
                $ide = $sheet->getCell('A' . $i)->getCalculatedValue();
                $celular = $sheet->getCell('F' . $i)->getCalculatedValue();
                $avi = Usuarioav::where('identificacion', $ide)->first();
                if ($avi != null) {
                    $avi->celular = $celular;
                    $avi->save();
                }
            }
        }

        return "Listo";
    }

    public function actualizarCoordenadas()
    {
        $pasajeros = Usuarioav::whereNotNull('direccion')->whereNull('latitud')->get();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        foreach ($pasajeros as $pasajero) {
            curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($pasajero->direccion . ", " . $pasajero->zona) . "&language=ES&key=apikey");
            $json = json_decode(curl_exec($ch));
            if (count($json->results) > 0) {
                $pasajero->latitud = $json->results[0]->geometry->location->lat;
                $pasajero->longitud = $json->results[0]->geometry->location->lng;
            }
            $pasajero->save();
        }

        return "Listo";
    }
}
