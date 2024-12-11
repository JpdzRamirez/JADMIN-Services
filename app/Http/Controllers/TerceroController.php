<?php

namespace App\Http\Controllers;

use App\Models\Agencia_tercero;
use App\Models\Cliente;
use App\Models\Contrato_vale;
use App\Models\Contrato_vale_ruta;
use App\Models\Cuentac;
use App\Models\Pasajero;
use App\Models\Pasajerosxruta;
use App\Models\Programacion;
use App\Models\Registro;
use App\Models\Ruta;
use App\Models\Servicio;
use App\Models\Tercero;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Vale;
use App\Models\Vale_servicio;
use App\Models\Valera;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Str;
use SoapClient;
use SoapFault;
use stdClass;

class TerceroController extends Controller
{
    public function empresas()
    {
        if (Auth::user()->roles_id == 4) {
            if (Auth::user()->id == 119) {
                $identi = ["89021176868"];
            } else {
                //$identi = Auth::user()->cuentae->agencia->NRO_IDENTIFICACION;
                $identi = [];
                foreach (Auth::user()->cuentase as $cuentae) {
                    $identi[] = $cuentae->agencia->NRO_IDENTIFICACION;
                }
            }

            //$empresas = Agencia_tercero::with('tercero.contratovale')->where('NRO_IDENTIFICACION', $identi)->take(1)->get();
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereIn('NRO_IDENTIFICACION', $identi)->get();

            return view('empresas.empresa', compact('empresas'));
        } else if (Auth::user()->roles_id == 5) {
            if (Auth::user()->idtercero != null) {
                $tercero = Tercero::where('TERCERO', Auth::user()->idtercero)->first();
            } else {
                $tercero = Auth::user()->tercero;
            }

            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) use ($tercero) {
                $q->where('TERCERO', $tercero->TERCERO);
            })->where('SW_ACTIVO', 1)->get();
            return view('empresas.empresa', compact('empresas'));
        } else {
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('SW_NUEVO_CRM', "1");
            })->where('SW_ACTIVO', 1)->paginate(20);
            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

            return view('empresas.lista', compact('empresas', 'usuario'));
        }
    }

    public function editar($agencia)
    {
        $partes = explode("_", $agencia);
        $agenciat = Agencia_tercero::where('TERCERO', $partes[0])->where('CODIGO', $partes[1])->first();
        if (Auth::user()->id == 119) {
            $agenciat->NOMBRE = "Provisional administrativo";
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.form', ['agencia' => $agenciat, 'usuario' => $usuario, 'method' => 'put', 'route' => ['empresas.actualizar', $agencia]]);
    }


    public function actualizar(Request $request, $agencia)
    {

        if (Auth::user()->id != 119) {
            $partes = explode("_", $agencia);

            DB::table('agencia_tercero')->where('TERCERO', $partes[0])->where('CODIGO', $partes[1])
                ->update(['TELEFONO' => $request->input('TELEFONO'), 'EMAIL' => $request->input('EMAIL'), 'DIRECCION' => $request->input('DIRECCION')]);
        }

        return redirect('empresas');
    }

    public function rutas($contrato)
    {

        $contrato = Contrato_vale::with([
            'tercero' => function ($q) {
                $q->select('TERCERO', 'RAZON_SOCIAL');
            }
        ])->where('CONTRATO_VALE', $contrato)->first();
        $rutas = Contrato_vale_ruta::where('CONTRATO_VALE', $contrato->CONTRATO_VALE)->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.rutas', compact('rutas', 'usuario', 'contrato'));
    }

    public function valesxagencia($agencia)
    {
        $agencia = explode("_", $agencia);
        $tercero = Tercero::select('TERCERO', 'RAZON_SOCIAL')->where('TERCERO', $agencia[0])->first();
        $agencia = Agencia_tercero::with('cuentae')->where('TERCERO', $tercero->TERCERO)->where('CODIGO', $agencia[1])->first();

        if ($agencia->cuentae != null) {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->mergeCells("A1:V1");
            if (Auth::user()->id == 119) {
                $sheet->setCellValue("A1", "Vales usados por la agencia Provisional administrativo");
            } else {
                $sheet->setCellValue("A1", "Vales usados por la agencia " . $agencia->NOMBRE);
            }

            $style = array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                )
            );
            $sheet->getStyle("A1:V1")->applyFromArray($style);

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
            $sheet->getStyle("A1:V2")->getFont()->setBold(true);

            $valeras = Valera::where('cuentase_id', $agencia->cuentae->id)->get();
            $indice = 3;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            foreach ($valeras as $valera) {
                $vales = Vale::with([
                    'servicio' => function ($q) {
                        $q->with([
                            'cuentac' => function ($r) {
                                $r->select('id', 'conductor_CONDUCTOR')->with([
                                    'conductor' => function ($s) {
                                        $s->select('CONDUCTOR', 'NOMBRE');
                                    }
                                ]);
                            },
                            'cliente',
                            'seguimientos'
                        ]);
                    }
                ])->where('valeras_id', $valera->id)->where('estado', 'Usado')->get();
                foreach ($vales as $vale) {
                    $sheet->setCellValue("A" . $indice, $vale->servicio->id);
                    $sheet->setCellValue("B" . $indice, $vale->servicio->fecha);
                    $sheet->setCellValue("C" . $indice, $vale->servicio->cliente->nombres);
                    $sheet->setCellValue("D" . $indice, $vale->servicio->usuarios);
                    $sheet->setCellValue("E" . $indice, $vale->servicio->cliente->telefono);
                    $sheet->setCellValue("F" . $indice, $vale->servicio->direccion);
                    $sheet->setCellValue("G" . $indice, $vale->beneficiario);
                    $sheet->setCellValue("H" . $indice, $vale->centrocosto);
                    $sheet->setCellValue("I" . $indice, $vale->referenciado);
                    if ($vale->destino != null) {
                        $sheet->setCellValue("J" . $indice, $vale->destino);
                    } else {
                        if ($agencia->TERCERO == 356) {
                            $numseg = count($vale->servicio->seguimientos);
                            if ($numseg > 0) {
                                curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $vale->servicio->seguimientos[$numseg - 1]->latitud . "," . $vale->servicio->seguimientos[$numseg - 1]->longitud . "&language=ES&key=apikey");
                                $json = json_decode(curl_exec($ch));
                                if (count($json->results) > 0) {
                                    $sheet->setCellValue("J" . $indice, $json->results[0]->formatted_address);
                                }
                            }
                        }
                    }

                    if ($vale->servicio->fechaprogramada == null) {
                        $sheet->setCellValue("K" . $indice, "Inmediato");
                    } else {
                        $sheet->setCellValue("K" . $indice, "Programado");
                    }

                    $sheet->setCellValue("L" . $indice, $vale->servicio->pago);
                    $sheet->setCellValue("M" . $indice, $valera->nombre);
                    $sheet->setCellValue("N" . $indice, $vale->codigo);
                    $sheet->setCellValueExplicit("O" . $indice, strtoupper($vale->clave), DataType::TYPE_STRING);
                    $sheet->setCellValue("P" . $indice, $vale->servicio->unidades . " " . $vale->servicio->cobro);
                    $sheet->setCellValue("Q" . $indice, $vale->servicio->valor);
                    $sheet->setCellValue("R" . $indice, $vale->servicio->asignacion);

                    if ($vale->servicio->cuentac == null) {
                        $sheet->setCellValue("S" . $indice, "Sin asignar");
                    } else {
                        $sheet->setCellValue("S" . $indice, $vale->servicio->cuentac->conductor->NOMBRE);
                    }

                    $sheet->setCellValue("T" . $indice, $vale->servicio->placa);
                    $sheet->setCellValue("U" . $indice, $vale->servicio->observaciones);
                    $sheet->setCellValue("V" . $indice, $vale->servicio->estado);
                    $indice++;
                }
            }

            foreach (range('A', 'V') as $columnID) {
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

    public function filtrar(Request $request)
    {

        if ($request->filled('razon')) {
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('SW_NUEVO_CRM', "1");
            })->where('NOMBRE', 'like', '%' . $request->input('razon') . '%')->where('SW_ACTIVO', 1)->paginate(20)->appends($request->query());
            $filtro = array('Nombre', $request->input('razon'));
        } elseif ($request->filled('nit')) {
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('SW_NUEVO_CRM', "1");
            })->where('NRO_IDENTIFICACION', 'like', '%' . $request->input('nit') . '%')->where('SW_ACTIVO', 1)->paginate(20)->appends($request->query());
            $filtro = array('Nro. Identificación', $request->input('nit'));
        } elseif ($request->filled('direccion')) {
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('SW_NUEVO_CRM', "1");
            })->where('DIRECCION', 'like', '%' . $request->input('direccion') . '%')->where('SW_ACTIVO', 1)->paginate(20)->appends($request->query());
            $filtro = array('Dirección', $request->input('direccion'));
        } elseif ($request->filled('telefono')) {
            $empresas = Agencia_tercero::with('tercero.contratovale')->whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('SW_NUEVO_CRM', "1");
            })->where('TELEFONO', 'like', '%' . $request->input('telefono') . '%')->where('SW_ACTIVO', 1)->paginate(20)->appends($request->query());
            $filtro = array('Teléfono', $request->input('telefono'));
        } else {
            return redirect('empresas');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.lista', compact('empresas', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {

        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "Nombre") {
                $empresas = Agencia_tercero::whereHas('tercero', function ($q) {
                    $q->has('contratovale')->where('TIPO_IDENTIFICACION', "02")->where('SW_NUEVO_CRM', "1");
                })->where('NOMBRE', 'like', '%' . $filtro[1] . '%')->where('SW_ACTIVO', 1)->get();
            } elseif ($filtro[0] == "NIT") {
                $empresas = Agencia_tercero::whereHas('tercero', function ($q) {
                    $q->has('contratovale')->where('TIPO_IDENTIFICACION', "02")->where('SW_NUEVO_CRM', "1");
                })->where('NRO_IDENTIFICACION', 'like', '%' . $filtro[1] . '%')->where('SW_ACTIVO', 1)->get();
            } elseif ($filtro[0] == "Dirección") {
                $empresas = Agencia_tercero::whereHas('tercero', function ($q) {
                    $q->has('contratovale')->where('TIPO_IDENTIFICACION', "02")->where('SW_NUEVO_CRM', "1");
                })->where('DIRECCION', 'like', '%' . $filtro[1] . '%')->where('SW_ACTIVO', 1)->get();
            } elseif ($filtro[0] == "Teléfono") {
                $empresas = Agencia_tercero::whereHas('tercero', function ($q) {
                    $q->has('contratovale')->where('TIPO_IDENTIFICACION', "02")->where('SW_NUEVO_CRM', "1");
                })->where('TELEFONO', 'like', '%' . $filtro[1] . '%')->where('SW_ACTIVO', 1)->get();
            }
        } else {
            $empresas = Agencia_tercero::whereHas('tercero', function ($q) {
                $q->has('contratovale')->where('TIPO_IDENTIFICACION', "02")->where('SW_NUEVO_CRM', "1");
            })->where('SW_ACTIVO', 1)->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:D1");
        $sheet->setCellValue("B1", "Lista de Empresas");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:D1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Nombre");
        $sheet->setCellValue("B2", "Nro. Identificación");
        $sheet->setCellValue("C2", "Dirección");
        $sheet->setCellValue("D2", "Teléfono");
        $sheet->getStyle("A1:D2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($empresas as $empresa) {
            $sheet->setCellValue("A" . $indice, $empresa->NOMBRE);
            $sheet->setCellValue("B" . $indice, $empresa->NRO_IDENTIFICACION);
            $sheet->setCellValue("C" . $indice, $empresa->DIRECCION);
            $sheet->setCellValue("D" . $indice, $empresa->TELEFONO);
            $indice++;
        }

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Empresas.xlsx');
        $archivo = file_get_contents('Empresas.xlsx');
        unlink('Empresas.xlsx');

        return base64_encode($archivo);
    }

    public function getAgencias($tercero)
    {
        $agencias = Agencia_tercero::where('TERCERO', $tercero)->where('SW_ACTIVO', 1)->get();

        return json_encode($agencias);
    }

    public function terceros()
    {
        $terceros = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'TELEFONO', 'DIRECCION', 'RAZON_SOCIAL', 'users_id')->whereHas('agencias', function ($q) {
            $q->has('cuentae');
        })->paginate(15);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.terceros', compact('terceros', 'usuario'));
    }

    public function userTercero(Tercero $tercero, $metodo)
    {

        $route = ['terceros.actualizar', $tercero->TERCERO];
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.formTercero', compact('tercero', 'metodo', 'route', 'usuario'));
    }

    public function upUserTercero(Request $request, $tercero)
    {
        $tercero = Tercero::with('user')->select('TERCERO', 'RAZON_SOCIAL', 'NRO_IDENTIFICACION', 'EMAIL', 'TELEFONO', 'DIRECCION', 'users_id')->where('TERCERO', $tercero)->first();

        if ($tercero->user == null) {
            $user = new User();
            $user->nombres = $tercero->RAZON_SOCIAL;
            $user->identificacion = $tercero->NRO_IDENTIFICACION;
            $user->usuario = $tercero->NRO_IDENTIFICACION;
            $user->password = Hash::make($request->input('password'));
            $user->estado = 1;
            $user->roles_id = 5;
            $user->save();

            /* $terceroI = TerceroIcon::find($tercero->TERCERO);
            $terceroI->EMAIL = $request->input('EMAIL');
            $terceroI->DIRECCION = $request->input('DIRECCION');
            $terceroI->TELEFONO = $request->input('TELEFONO');
            $terceroI->users_id = $user->id;
            $terceroI->save();*/
        } else {
            /*  $terceroI = TerceroIcon::find($tercero->TERCERO);
            $terceroI->EMAIL = $request->input('EMAIL');
            $terceroI->DIRECCION = $request->input('DIRECCION');
            $terceroI->TELEFONO = $request->input('TELEFONO');*/

            if ($request->filled('password')) {
                $tercero->user->password = Hash::make($request->input('password'));
                $tercero->user->save();
            }

            // $terceroI->save();
        }

        return redirect('terceros');
    }

    public function filtrarTerceros(Request $request)
    {

        if ($request->filled('razon')) {
            $terceros = Tercero::whereHas('agencias', function ($q) {
                $q->has('cuentae');
            })->where('RAZON_SOCIAL', 'like', '%' . $request->input('razon') . '%')->paginate(15)->appends($request->query());
            $filtro = array('Nombre', $request->input('razon'));
        } elseif ($request->filled('nit')) {
            $terceros = Tercero::whereHas('agencias', function ($q) {
                $q->has('cuentae');
            })->where('NRO_IDENTIFICACION', 'like', '%' . $request->input('nit') . '%')->paginate(15)->appends($request->query());
            $filtro = array('Nro. Identificación', $request->input('nit'));
        } elseif ($request->filled('direccion')) {
            $terceros = Tercero::whereHas('agencias', function ($q) {
                $q->has('cuentae');
            })->where('DIRECCION', 'like', '%' . $request->input('direccion') . '%')->paginate(15)->appends($request->query());
            $filtro = array('Dirección', $request->input('direccion'));
        } elseif ($request->filled('telefono')) {
            $terceros = Tercero::whereHas('agencias', function ($q) {
                $q->has('cuentae');
            })->where('TELEFONO', 'like', '%' . $request->input('telefono') . '%')->paginate(15)->appends($request->query());
            $filtro = array('Teléfono', $request->input('telefono'));
        } else {
            return redirect('terceros');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('empresas.terceros', compact('terceros', 'usuario', 'filtro'));
    }

    public function ampliarValera(Request $request)
    {
        $valera = Valera::with('cuentae.agencia')->find($request->input('idValera'));
        $anterior = $valera->superior;
        $valera->superior = $request->input('fin');

        if ($valera->superior > $anterior) {
            $url = "http://201.221.157.189:8080/icon_crm/services/ModelValeVirtual?wsdl";
            try {
                $client = new SoapClient($url, ["trace" => 1, 'exceptions' => true]);
                $result = $client->registrarTicket();
                $parametros = array("ticket" => $result->registrarTicketReturn, "numeroIdentificacionEmpresa" => $valera->cuentae->agencia->NRO_IDENTIFICACION, "nombreValera" => $valera->nombre, "estadoValera" => $valera->estado, "limiteInferior" => $valera->inferior, "limiteSuperior" => $valera->superior, "fechaInicio" => str_replace("-", "", $valera->inicio), "fechaFin" => str_replace("-", "", $valera->fin));
                $peticion = $client->registrarValera($parametros);

                if ($peticion->registrarValeraReturn->codigoError != "0000") {
                    return back()->withErrors(["falla" => $peticion->registrarValeraReturn->mensajeError]);
                }
            } catch (SoapFault $e) {
                return back()->withErrors(["falla" => $e->getMessage()]);
            }
            $valera->save();
            if ($valera->centro != null) {
                $centro = $valera->centro;
            } else {
                $centro = null;
            }
            for ($i = $anterior + 1; $i <= $valera->superior; $i++) {
                $vale = new Vale();
                $vale->estado = "Libre";
                $vale->codigo = $i;
                $vale->centrocosto = $centro;
                $vale->clave = $this->genclave(4);
                $vale->valeras_id = $valera->id;
                $vale->save();
            }

            return redirect('valeras');
        } else {
            return back()->withErrors(["falla" => "El número final es incorrecto. Debe ser mayor al actual"]);
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

    public function exportarPropietarios()
    {
        $excelPlacas = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "placas.xlsx");
        $hojaPlacas = $excelPlacas->setActiveSheetIndex(2);
        $filasPlacas = $hojaPlacas->getHighestRow();

        $excelProp = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "propietarios.xlsx");
        $hojaProp = $excelProp->setActiveSheetIndex(0);

        $form1 = $hojaProp->getCell('N2')->getValue();
        $form2 = $hojaProp->getCell('O2')->getValue();

        $filaProp = 2;
        for ($i = 2; $i <= $filasPlacas; $i++) {
            $vehiculo = Vehiculo::with('propietario.tercero.municipio', 'documentos.aseguradora')->where('PLACA', $hojaPlacas->getCell('B' . $i)->getValue())->first();
            if ($vehiculo != null) {
                $hojaProp->setCellValue('A' . $filaProp, $vehiculo->PLACA);
                foreach ($vehiculo->documentos as $documento) {
                    if ($documento->aseguradora != null) {
                        if ($documento->TIPO_DOCUMENTO == 2) {
                            $hojaProp->setCellValue('D' . $filaProp, $documento->aseguradora->DESCRIPCION);
                            $hojaProp->setCellValue('E' . $filaProp, $documento->FECHA_VENCIMIENTO);
                        } elseif ($documento->TIPO_DOCUMENTO == 8) {
                            $hojaProp->setCellValue('B' . $filaProp, $documento->aseguradora->DESCRIPCION);
                            $hojaProp->setCellValue('C' . $filaProp, $documento->FECHA_VENCIMIENTO);
                        }
                    }
                }
                $hojaProp->setCellValue('F' . $filaProp, $vehiculo->propietario->tercero->PRIMER_NOMBRE);
                $hojaProp->setCellValue('G' . $filaProp, $vehiculo->propietario->tercero->SEGUNDO_NOMBRE);
                $hojaProp->setCellValue('H' . $filaProp, $vehiculo->propietario->tercero->PRIMER_APELLIDO);
                $hojaProp->setCellValue('I' . $filaProp, $vehiculo->propietario->tercero->SEGUNDO_APELLIDO);
                $hojaProp->setCellValue('J' . $filaProp, $vehiculo->propietario->tercero->NRO_IDENTIFICACION);
                $hojaProp->setCellValue('K' . $filaProp, $vehiculo->propietario->tercero->DIRECCION);
                $hojaProp->setCellValue('L' . $filaProp, $vehiculo->propietario->tercero->BARRIO);
                $hojaProp->setCellValueExplicit('M' . $filaProp, $vehiculo->propietario->tercero->municipio->MUNICIPIO, DataType::TYPE_STRING);
                $hojaProp->setCellValue('N' . $filaProp, $form1);
                $hojaProp->setCellValue('O' . $filaProp, $form2);
                $hojaProp->setCellValueExplicit('P' . $filaProp, $vehiculo->propietario->tercero->TELEFONO, DataType::TYPE_STRING);
                $hojaProp->setCellValueExplicit('Q' . $filaProp, $vehiculo->propietario->tercero->CELULAR, DataType::TYPE_STRING);
                $hojaProp->setCellValue('R' . $filaProp, $vehiculo->propietario->tercero->EMAIL);
                $form1 = str_replace("M" . $filaProp, "M" . ($filaProp + 1), $form1);
                $form2 = str_replace("M" . $filaProp, "M" . ($filaProp + 1), $form2);
                $filaProp++;
            }
        }
        $writer = new Xlsx($excelProp);
        $writer->save('PlantillaProp.xlsx');

        return response()->download("PlantillaProp.xlsx", "PlantillaProp.xlsx", ["Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]);
    }

    public function buscarPasajeros(Request $request)
    {
        $pasajeros = Pasajero::where('identificacion', 'like', $request->input('identificacion') . '%')->where('estado', 1)->get();

        return json_encode($pasajeros);
    }

    public function archivoComfenalco(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $i = 0;
        try {
            $excel = IOFactory::load($request->file('archivo'));
            $hoja = $excel->setActiveSheetIndex(0);
            $filas = $hoja->getHighestRow();
            $fecha = Carbon::parse($request->input('fechaprog'))->format('Y-m-d');
            $idValera = $request->input('valera');
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
                    $hora = $hoja->getCell('I' . $i)->getFormattedValue();
                    $nruta = $hoja->getCell('K' . $i)->getFormattedValue();
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
                        $ruta->estimado = $hoja->getCell('M' . $i)->getValue();
                        $placa = $hoja->getCell('J' . $i)->getValue();
                        if (!empty($placa)) {
                            $veh = Vehiculo::with([
                                'conductores' => function ($q) {
                                    $q->with([
                                        'cuentac' => function ($r) {
                                            $r->select('id', 'conductor_CONDUCTOR');
                                        }
                                    ])->where('SW_ACTIVO_NUEVO_CRM', '1');
                                }
                            ])->where('PLACA', $placa)->first();
                            if (count($veh->conductores) == 1) {
                                $ruta->placa = $placa;
                                $ruta->cuentac = $veh->conductores[0]->cuentac->id;
                            }
                        }
                        $nrutaActual = $nruta;
                        $fecha = Carbon::parse($hoja->getCell('H' . $i)->getFormattedValue() . " " . $hora);
                        $ruta->fecha = $fecha->format('Y-m-d');
                        $ruta->hora = $fecha->format('H:i:s');
                        $pasajeros = [];
                    }
                    $identificacion = $hoja->getCell('A' . $i)->getValue();
                    if (!empty($identificacion)) {
                        $pasajero = Pasajero::where('identificacion', $identificacion)->first();
                        if ($pasajero == null) { //si no tiene niingun valor asignado
                            $pasajero = new Pasajero();
                            $pasajero->celulares = utf8_encode($hoja->getCell('C' . $i)->getValue());
                            $pasajero->identificacion = utf8_encode($hoja->getCell('A' . $i)->getValue());
                            $pasajero->nombre = utf8_encode($hoja->getCell('B' . $i)->getValue());
                            $pasajero->municipio = utf8_encode($hoja->getCell('F' . $i)->getValue());
                            $direccion = utf8_encode($hoja->getCell('D' . $i)->getValue());
                            $pasajero->barrio = utf8_encode($hoja->getCell('E' . $i)->getValue());
                            $pasajero->direccion = $direccion;
                            curl_setopt($ch, CURLOPT_VERBOSE, true);
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
                            if ($pasajero->celulares != $hoja->getCell('C' . $i)->getValue()) {
                                $pasajero->celulares = utf8_encode($hoja->getCell('C' . $i)->getValue());
                            }

                            if ($pasajero->direccion != $hoja->getCell('D' . $i)->getValue() || $pasajero->barrio != $hoja->getCell('E' . $i)->getValue()) {
                                $pasajero->direccion = utf8_encode($hoja->getCell('D' . $i)->getValue());
                                $pasajero->barrio = utf8_encode($hoja->getCell('E' . $i)->getValue());
                                $pasajero->municipio = utf8_encode($hoja->getCell('F' . $i)->getValue());
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
            return json_encode($rutas);
        } catch (Exception $ex) {
            return $ex->getMessage() . "---" . $ex->getLine() . "---Error fila: " . $i;
        }
    }

    public function programarComfenalco(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $cliente = Cliente::find(103);
        $hoy = Carbon::parse($request->input('fechaprog'));
        $vales = [];
        $servis = [];
        try {
            DB::beginTransaction();
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
                        $direccion = $direccion . $ruta->pasajeros[$i]->direccion . ", " . $ruta->pasajeros[$i]->barrio . ", " . $ruta->pasajeros[$i]->municipio . "\n";
                        $usuarios = $usuarios . $ruta->pasajeros[$i]->nombre . "--" . $ruta->pasajeros[$i]->celulares . "\n";
                    }
                    foreach ($ruta->pasajeros as $pasajero) {
                        if ($pasajero->lat != null) {
                            $lat = $pasajero->lat;
                            $lng = $pasajero->lng;
                            break;
                        }
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
                    $servicio->fechaprogramada = $ruta->horaRecogida;
                    $servicio->flotas_id = null;
                    $servicio->clientes_id = $cliente->id;
                    $servicio->users_id = Auth::user()->id;
                    $servicio->estado = "Pendiente";
                    $servicio->save();

                    $vale = Vale::where('valeras_id', $request->input('valera'))->where('estado', 'Libre')->first();
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
                    $rutaServicio->numero = $ruta->numero;
                    $rutaServicio->fecha = $ruta->fecha;
                    $rutaServicio->hora = $ruta->hora;
                    $rutaServicio->estimado = $ruta->estimado;
                    $rutaServicio->programaciones_id = $programacion->id;
                    $rutaServicio->servicios_id = $servicio->id;
                    $rutaServicio->valeras_id = $request->input('valera');
                    $rutaServicio->save();

                    foreach ($ruta->pasajeros as $pasajero) {
                        $pasajeroxruta = new Pasajerosxruta();
                        $pasajeroxruta->pasajeros_id = $pasajero->id;
                        $pasajeroxruta->rutas_id = $rutaServicio->id;
                        $pasajeroxruta->save();
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

    public function ValeAutomatico(Request $request)
    {

        $vale = Vale::select('id', 'codigo', 'valeras_id')->where('valeras_id', $request->idValera)->where('estado', 'Libre')->first();

        return json_encode($vale);
    }

    public function rutasActivas(Request $request)
    {

        $tercero = $request->tercero;
        $hoy = Carbon::now()->format('Y-m-d');
        $contrato = Contrato_vale::where('tercero', $tercero)->where('FECHA_INICIO', '<=', $hoy)->where('FECHA_FIN', '>=', $hoy)->first();
        $rutas = [];
        if ($contrato != null) {
            $rutas = Contrato_vale_ruta::where('CONTRATO_VALE', $contrato->CONTRATO_VALE)->get();
        }

        return json_encode($rutas);
    }

    public function finalizarCobroAnticipado(Request $request)
    {
        set_time_limit(90);
        ini_set('default_socket_timeout', 90);
        DB::beginTransaction();
        try {
            $registro = new Registro();
            $registro->fecha = Carbon::now();
            $registro->evento = "Fin";
            $registro->servicios_id = $request->input('idservicio');
            $aeropuerto = 0;
            $servicio = Servicio::with('vale.valera.cuentae.agencia')->find($request->input('idservicio'));

            if ($servicio->estado == "En curso" || $servicio->estado == "Asignado") {
                if ($servicio->pago == "Vale electrónico") {

                    $ruta = Contrato_vale_ruta::where('CONTRATO_VALE', $servicio->CONTRATO_VALE)->where('SECUENCIA', $servicio->SECUENCIA)->first();
                    $servicio->cobro = "Ruta";
                    $servicio->valor = round($ruta->TARIFA_COBRO);
                    $soapruta = "R." . $ruta->SECUENCIA;
                    $soapunidades = "0";
                    $soaphoras = "0";
                    $soapminutos = "0";
                    $servicio->unidades = $ruta->ORIGEN . "-" . $ruta->DESTINO;
                    $servicio->estado = "Finalizado";

                    $vale = $servicio->vale;
                    $vale->estado = "Usado";
                    $cuentac = Cuentac::with([
                        'conductor' => function ($q) {
                            $q->select('CONDUCTOR', 'NUMERO_IDENTIFICACION');
                        }
                    ])->select('id', 'estado', 'saldo', 'saldovales', 'bono', 'conductor_CONDUCTOR')->find($servicio->cuentasc_id);

                    if ($request->input('cobro') == "Rutas") {
                        $cuota = round(($ruta->TARIFA_PAGO - ($ruta->TARIFA_PAGO * 0.08)), 0, PHP_ROUND_HALF_DOWN);
                    }
                    $uno = $cuota % 100;
                    $cuota = $cuota - $uno;

                    $cuentac->saldovales = $cuentac->saldovales + $cuota;
                    if ($cuentac->estado != "Bloqueado") {
                        $cuentac->estado = "Libre";
                    }
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

                    $parametros = array(
                        "ticket" => $result->registrarTicketReturn,
                        "numeroIdentificacionEmpresa" => $vale->valera->cuentae->agencia->NRO_IDENTIFICACION,
                        "nombreValera" => $vale->valera->nombre,
                        "codigoVale" => $vale->codigo,
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
                        "valor" => $servicio->valor,
                        "centroCosto" => $vale->centrocosto,
                        "referenciado" => $vale->referenciado,
                        "aeropuerto" => $aeropuerto
                    );

                    $peticion = $client->registrarVale($parametros);
                    $finalizadoICON = 0;
                    if ($peticion->registrarValeReturn->codigoError != "0000") {
                        try {
                            DB::connection('mysql2')->table('ws_servicio')->where("DATOS", "LIKE", $vale->valera->cuentae->agencia->NRO_IDENTIFICACION . "|" . $servicio->vale->valera->nombre . "|" . $servicio->vale->codigo . "|%")->where('CODIGO_ERROR', '!=', '0000')->delete();

                            $finalizadoICON = DB::connection('mysql2')
                                ->table('ws_servicio')
                                ->where("DATOS", "LIKE", $vale->valera->cuentae->agencia->NRO_IDENTIFICACION . "|" . $servicio->vale->valera->nombre . "|" . $servicio->vale->codigo . "|%")
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

                    $registro->save();
                    $servicio->save();
                    $vale->save();
                    $cuentac->save();

                    DB::commit();

                    return $servicio->valor;
                }
            } else {
                return "Finalizado";
            }
        } catch (SoapFault $e) {
            DB::rollBack();
            return "Falla icon_" . $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            return "Falla icon_" . $e->getMessage();
        }
    }

    public function actualizarCCosto(Request $request)
    {

        //Recuperamos el id de la uri
        $id = $request->route('id');


        $validatedData = $request->validate([
            'sub_cuenta' => 'required|string|max:10',
            'affe' => 'required|string|max:15',
        ]);

        // Recupera los datos validados
        $sub_cuenta = $validatedData['sub_cuenta'];
        $affe = $validatedData['affe'];

        try {
            // obtenemos al objeto pasajero
            $pasajero = Pasajero::where('id', $id)->first();
            if ($pasajero) {
                $pasajero->sub_cuenta = $sub_cuenta;
                $pasajero->affe = $affe;
                $pasajero->save();
                return response()->json([
                    'message' => 'Se ha actualizado el centro de costo exitosamente',
                    'sub_cuenta' => $sub_cuenta,
                    'affe' => $affe
                ], 200);
            } else {
                return response()->json(['message' => 'No se encontró registros del pasajero'], 404);
            }
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json(['message' => 'Ocurrió un error al actualizar el pasajero', 'error' => $e->getMessage()], 500);
        }
    }

    public function exportarRegistros(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        // Recupera los datos validados
        $validatedData = $validator->validated();

        $fechaInicio = new Carbon($validatedData['fechaInicio']);
        $fechaFin = new Carbon($validatedData['fechaFin']);

        $tercero = $request->route('tercero');
        $codigo = $request->route('codigo');

        $exportarTodo = $request->has('exportarTodo') ? true : false;

        $razonSocial = Tercero::select('RAZON_SOCIAL')->where('TERCERO', $tercero)->get();
        $servicios = Servicio::has('vale')
        ->with([
            'vale' => function ($v){
                $v->select('id', 'codigo', 'valeras_id','servicios_id');

                $v->with([
                    'valera' => function ($vl) {
                        $vl->select('id', 'nombre');
                    },
                ]);
            },
            'pasajeros',
            'cuentac' => function ($q) {
                $q->select('id', 'conductor_CONDUCTOR')->with([
                    'conductor' => function ($r) {
                        $r->select('CONDUCTOR', 'NOMBRE');
                    }
                ]);
            },
            'contratoValeRuta',
        ])
        ->where('estado', 'Finalizado')
        ->whereBetween('fecha', [$fechaInicio->startOfDay(), $fechaFin->endOfDay()])
        ->whereHas('vale.valera.cuentae', function ($q) use ($tercero, $codigo, $exportarTodo) {
            $q->where('agencia_tercero_TERCERO', '=', $tercero)
              ->when(!$exportarTodo, function($q) use ($codigo) {
                  $q->where('agencia_tercero_CODIGO', '=', $codigo);
              });
        })->get();

        //return json_encode($servicios, JSON_PRETTY_PRINT);   
        if (!$servicios->isNotEmpty()) {
            return response()->json(['message' => 'No hay registros en el periodo seleccionado.'], 404);
        } else {

            // Seteamos los colores que se van a usar
            $colores = ['FC7C7C', '6C6CF5', '45E645', 'E7F57F', 'B5B3B3'];
            // Creamos una hoja de calculo y luego aplicamos estilos  
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
            $sheet->getStyle("A1:O1")->applyFromArray($style);

            $sheet->setCellValue("A1", "Item");
            $sheet->setCellValue("B1", "Periodo");
            $sheet->setCellValue("C1", "Fecha");
            $sheet->setCellValue("D1", "Codigo Ruta");
            $sheet->setCellValue("E1", "Ruta");
            $sheet->setCellValue("F1", "Placa Vehiculo");
            $sheet->setCellValue("G1", "Nombre de Conductor");
            $sheet->setCellValue("H1", "Hora");
            $sheet->setCellValue("I1", "Pasajeros");
            $sheet->setCellValue("J1", "SUB-CTA");
            $sheet->setCellValue("K1", "CC/AFE");
            $sheet->setCellValue("L1", "Solicita");
            $sheet->setCellValue("M1", "Autoriza");
            $sheet->setCellValue("N1", "Observaciones");
            $sheet->setCellValue("O1", "Valor");

            if($exportarTodo){
                $sheet->setCellValue("P1", "Valera");
            }

            //El indice indica la fila que comienza a imprimir los campos
            $indice = 2;
            $indiceServicio = 2;
            $color = 0;
            $totalServicio = 0;
            foreach ($servicios as $servicio) {
                foreach ($servicio->pasajeros as $pasajero) {

                    $sheet->setCellValue("A{$indice}", $servicio->id);

                    // Se inicializan vacias las variables de fecha y hora para evitar excepciones
                    $fechaCompleta = '';
                    $soloFecha = '';
                    $soloHora = '';

                    if ($servicio->fechaprogramada) {
                        $fechaCompleta = new Carbon($servicio->fechaprogramada);
                        $soloFecha = $fechaCompleta->translatedFormat('l d, d \d\e F Y');
                        $soloHora = $fechaCompleta->toTimeString();
                        $mes = $fechaCompleta->month;
                    } elseif ($servicio->fecha) {
                        $fechaCompleta = new Carbon($servicio->fecha);
                        $soloFecha = $fechaCompleta->translatedFormat('l d, d \d\e F Y');
                        $soloHora = $fechaCompleta->toTimeString();
                        $mes = $fechaCompleta->month;
                    }

                    $sheet->setCellValue("B{$indice}", $mes);
                    $sheet->setCellValue("C{$indice}", $soloFecha ?? '');

                    $secuencia = $servicio->contratoValeRuta->SECUENCIA ?? '';
                    $sheet->setCellValue("D{$indice}", "{$secuencia}");

                    $origen = $servicio->contratoValeRuta->ORIGEN ?? '';
                    $destino = $servicio->contratoValeRuta->DESTINO ?? '';
                    $sheet->setCellValue("E{$indice}", "{$origen}-{$destino}");

                    $sheet->setCellValue("F{$indice}", $servicio->placa ?? '');

                    $conductorNombre = $servicio->cuentac->conductor->NOMBRE ?? '';
                    $sheet->setCellValue("G{$indice}", $conductorNombre);

                    $sheet->setCellValue("H{$indice}", $soloHora ?? '');

                    $sheet->setCellValue("I{$indice}", $pasajero->nombre ?? '');

                    $subCuenta = $pasajero->pivot->sub_cuenta ?? '';
                    $sheet->setCellValue("J{$indice}", $subCuenta);

                    $affe = $pasajero->pivot->affe ?? '';
                    $sheet->setCellValue("K{$indice}", $affe);

                    $solicitado = $pasajero->pivot->solicitado ?? '';
                    $sheet->setCellValue("L{$indice}", $solicitado);

                    $autorizado = $pasajero->pivot->autorizado ?? '';
                    $sheet->setCellValue("M{$indice}", $autorizado);

                    $sheet->setCellValue("N{$indice}", $servicio->observaciones ?? '');
                    $indice++;
                }
                // Combinar celdas en la columna O
                $finRango = $indice - 1;

                $sheet->mergeCells("O{$indiceServicio}:O{$finRango}");

                // Centrar el contenido de la columna valor para cada servicio
                $sheet->getStyle("O{$indiceServicio}:O{$finRango}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $totalServicio = $totalServicio + $servicio->valor;

                // Establecer el valor del servicio en la celda combinada
                $sheet->setCellValue("O{$indiceServicio}", $servicio->valor ?? '');

                // Aplicar estilos de color a todo el rango del servicio base al color
                $sheet->getStyle("A{$indiceServicio}:O{$finRango}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($colores[$color]);

                // Aplicar borde a todo el rango del servicio
                $sheet->getStyle("A{$indiceServicio}:O{$finRango}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                //Condición para diferenciar la valera
                if($exportarTodo){
                        $sheet->mergeCells("P{$indiceServicio}:P{$finRango}");

                        // Centrar el contenido de la columna valor para cada servicio
                        $sheet->getStyle("P{$indiceServicio}:P{$finRango}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                        $sheet->setCellValue("P{$indiceServicio}", $servicio->vale->valera->nombre ?? '');

                        $sheet->getStyle("P{$indiceServicio}:P{$finRango}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($colores[$color]);
    
                        $sheet->getStyle("P{$indiceServicio}:P{$finRango}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                // Incrementar y rotar el color
                $color++;
                if ($color == count($colores)) {
                    $color = 0;
                }
                $indiceServicio = $indice;
            }

            $sheet->setCellValue("N{$indice}", "Total Servicios");
            $sheet->setCellValue("O{$indice}", $totalServicio);

            for ($i = 'A'; $i != $sheet->getHighestColumn(); $i++) {
                $sheet->getColumnDimension($i)->setAutoSize(true);
            }

            // Sanitiza el nombre para evitar caracteres no permitidos
            $razonSocialSanitized = Str::slug($razonSocial, '_');

            $filename = "Reporte_{$razonSocialSanitized}.xlsx";
            $path = storage_path("docs/{$filename}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            // Verificación de archivo
            if (file_exists($path)) {
                $headers = [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ];

                return response()->download($path, $filename, $headers);
            } else {
                // Excepción de creación del documento
                return response()->json(['error' => 'Error interno del servidor.'], 500);
            }
        }
    }

    public function pasajeros(Request $request)
    {
        $identificacion = "";
        $nombre = "";
        // Inicializa la consulta base
        $query = Pasajero::whereNotNull('sub_cuenta')
            ->whereNotNull('affe');

        if ($request->filled('identificacion')) {
            $identificacion = $request->input('identificacion');
            $query->where('identificacion', $identificacion);
        } elseif ($request->filled('nombre')) {
            $nombre = $request->input('nombre');
            $query->where('nombre', 'like', "{$nombre}%");
        }

        // Ejecutar la consulta y paginar resultados
        $pasajeros = $query->paginate(20)->appends($request->query());

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();


        return view('users.listaPasajeros', compact('pasajeros', 'identificacion', 'nombre', 'usuario'));
    }

    public function crearPasajero(Request $request)
    {
        $pasajero = new Pasajero();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $route = 'pasajeros/CRM/nuevo';
        $method = 'post';

        return view('users.nuevoPasajero', compact('pasajero', 'route', 'method', 'usuario'));
    }

    public function nuevoPasajero(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identificacion' => 'nullable|string|regex:/^[0-9]+$/|max:20',
            'nombre' => 'required|string|max:125',
            'apellidos' => 'required|string|max:125',
            'sub_cuenta' => 'required|string|max:10',
            'affe' => 'required|string|max:15',
            'solicitado' => 'required|string|max:20',
            'autorizado' => 'required|string|max:20',
            'celulares' => 'nullable|string|regex:/^[0-9]+$/|min:10|max:50',
            'direccion' => 'nullable|string|max:250',
            'barrio' => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:45'
        ]);

        if ($validator->fails()) {
            return redirect("pasajeros/CRM/nuevo")
                ->withErrors($validator)
                ->withInput();
        }
        // Recupera los datos validados
        $validatedData = $validator->validated();

        // Recupera los datos validados
        $identificacion = $validatedData['identificacion'];
        $nombres = $validatedData['nombre'];
        $apellidos = $validatedData['apellidos'];
        $sub_cuenta = $validatedData['sub_cuenta'];
        $affe = $validatedData['affe'];
        $solicitado = $validatedData['solicitado'];
        $autorizado = $validatedData['autorizado'];
        $celulares = $validatedData['celulares'];
        $direccion = $validatedData['direccion'];
        $barrio = $validatedData['barrio'];
        $municipio = $validatedData['municipio'];

        try {

            $pasajero = Pasajero::where('identificacion', $identificacion)->whereNotNull('identificacion')->first();
            if ($pasajero != null) {
                throw new Exception("El usuario ya se encuentra registrado: {$pasajero->identificacion}");
            }

            $pasajero = new Pasajero();
            $pasajero->identificacion = $identificacion;
            $pasajero->nombre = $nombres . ' ' . $apellidos;
            $pasajero->sub_cuenta = $sub_cuenta;
            $pasajero->affe = $affe;
            $pasajero->solicitado = $solicitado;
            $pasajero->autorizado = $autorizado;
            $pasajero->celulares = $celulares;
            $pasajero->direccion = preg_replace('/\s+/', ' ', $direccion);
            $pasajero->barrio = $barrio;
            $pasajero->municipio = $municipio;

            if ($pasajero->direccion != null && $pasajero->municipio != null) {
                $ch = curl_init();
                $dirtemp = urlencode($pasajero->direccion . ", " . $municipio);
                $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$dirtemp}&language=ES&key=apikey";
                // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_VERBOSE, true); // Mostrar salida de depuración
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar verificación de SSL solo para desarrollo
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Solo desarrollo
                $response = curl_exec($ch);

                if ($response != false) {
                    $json = json_decode($response);
                    if (isset($json->results[0])) {
                        $pasajero->lat = $json->results[0]->geometry->location->lat;
                        $pasajero->lng = $json->results[0]->geometry->location->lng;
                    }
                }

                curl_close($ch);
            }

            $pasajero->save();

            if ($identificacion) {
                return redirect("pasajeros/CRM/listar?identificacion=" . urlencode($pasajero->identificacion));
            } else {
                return redirect("pasajeros/CRM/listar?nombre=" . urlencode($pasajero->nombre));
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            return back()->withErrors(['sql' => $e->getMessage()]);
        }
    }

    public function editarPasajero(Request $request, Pasajero $pasajero)
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        $route = 'pasajeros/CRM/' . $pasajero->id . '/actualizar';
        $method = 'put';

        return view('users.nuevoPasajero', compact('pasajero', 'route', 'method', 'usuario'));
    }

    public function actualizarPasajero(Request $request, Pasajero $pasajero)
    {
        $validator = Validator::make($request->all(), [
            'sub_cuenta' => 'required|string|max:10',
            'affe' => 'required|string|max:15',
            'solicitado' => 'required|string|max:20',
            'autorizado' => 'required|string|max:20',
            'celulares' => 'nullable|string|regex:/^[0-9]+$/|min:10|max:50',
            'direccion' => 'nullable|string|max:250',
            'barrio' => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:45'
        ]);

        if ($validator->fails()) {
            return redirect("pasajeros/CRM/{$pasajero->id}/editar")
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();

        $sub_cuenta = $validatedData['sub_cuenta'];
        $affe = $validatedData['affe'];
        $solicitado = $validatedData['solicitado'];
        $autorizado = $validatedData['autorizado'];
        $celulares = $validatedData['celulares'];
        $direccion = $validatedData['direccion'];
        $barrio = $validatedData['barrio'];
        $municipio = $validatedData['municipio'];

        try {
            if ($direccion != null && $municipio != null) {
                if ($pasajero->direccion != $direccion || $pasajero->municipio != $municipio) {
                    $ch = curl_init();
                    $dirtemp = urlencode($pasajero->direccion . ", " . $municipio);
                    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$dirtemp}&language=ES&key=apikey";
                    // Configurar opciones de cURL
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_VERBOSE, true); // Mostrar salida de depuración
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar verificación de SSL solo para desarrollo
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Solo desarrollo
                    $response = curl_exec($ch);

                    if ($response != false) {
                        $json = json_decode($response);
                        if (isset($json->results[0])) {
                            $pasajero->lat = $json->results[0]->geometry->location->lat;
                            $pasajero->lng = $json->results[0]->geometry->location->lng;
                        }
                    }
                    curl_close($ch);
                }
            }
            $pasajero->sub_cuenta = $sub_cuenta;
            $pasajero->affe = $affe;
            $pasajero->solicitado = $solicitado;
            $pasajero->autorizado = $autorizado;
            $pasajero->celulares = $celulares;
            $pasajero->direccion = preg_replace('/\s+/', ' ', $direccion);
            $pasajero->barrio = $barrio;
            $pasajero->municipio = $municipio;
            $pasajero->save();

            if ($pasajero->identificacion) {
                return redirect("pasajeros/CRM/listar?identificacion=" . urlencode($pasajero->identificacion));
            } else {
                return redirect("pasajeros/CRM/listar?nombre=" . urlencode($pasajero->nombre));
            }
        } catch (Exception $ex) {
            return back()->withErrors(['sql' => $ex->getMessage()]);
        }
    }
    public function descargarPlanilla(Request $request)
    {
        
        $pasajeros = Pasajero::whereNotNull('sub_cuenta')
        ->whereNotNull('affe')
        ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:K1");
        $sheet->setCellValue("A1", "Plantilla de actualización masiva CECO ");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:K1")->applyFromArray($style);

        $sheet->setCellValue("A2", "ID");
        $sheet->setCellValue("B2", "Identificacion");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Celulares");
        $sheet->setCellValue("E2", "Direccion");
        $sheet->setCellValue("F2", "Barrio");
        $sheet->setCellValue("G2", "Municipio");
        $sheet->setCellValue("H2", "Sub_Cuenta");
        $sheet->setCellValue("I2", "Affe");
        $sheet->setCellValue("J2", "Solicitado");
        $sheet->setCellValue("K2", "Autorizado");
        $sheet->getStyle("A1:K2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($pasajeros as $pasajero) {
            $sheet->setCellValue("A{$indice}", $pasajero->id);
            $sheet->setCellValue("B{$indice}", $pasajero->identificacion);
            $sheet->setCellValue("C{$indice}", $pasajero->nombre);
            $sheet->setCellValue("D{$indice}", $pasajero->celulares);
            $sheet->setCellValue("E{$indice}", $pasajero->direccion);
            $sheet->setCellValue("F{$indice}", $pasajero->barrio);
            $sheet->setCellValue("G{$indice}", $pasajero->municipio);
            $sheet->setCellValue("H{$indice}", $pasajero->sub_cuenta);
            $sheet->setCellValue("I{$indice}", $pasajero->affe);
            $sheet->setCellValue("J{$indice}", $pasajero->solicitado);
            $sheet->setCellValue("K{$indice}", $pasajero->autorizado);
            $indice++;
        }

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Plantilla_Pasajeros.xlsx');
        $archivo = file_get_contents('Plantilla_Pasajeros.xlsx');
        unlink('Plantilla_Pasajeros.xlsx');

        return base64_encode($archivo);
    }

    public function importarPasajeros(Request $request)
    {

            $file = $request->file('plantilla');
            $ext = $file->getClientOriginalExtension();

            if ($ext == "xls" or $ext == "xlsx") {

                $objPHPExcel = IOFactory::load($file);
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getActiveSheet();
                $numRows = $sheet->getHighestRow();

                if ($numRows >= 3) {
                    for ($i = 3; $i <= $numRows; $i++) {
                        $identificador=$sheet->getCell('A' . $i)->getValue();
                        $pasajero = Pasajero::find($identificador);
                        
                        if ($pasajero) {
                            $updates = [
                                'sub_cuenta' => $sheet->getCell("H{$i}")->getValue(),
                                'affe' => $sheet->getCell("I{$i}")->getValue(),
                                'solicitado' => $sheet->getCell("J{$i}")->getValue(),
                                'autorizado' => $sheet->getCell("K{$i}")->getValue()
                            ];

                            $hasChanges = false;
                            
                            foreach ($updates as $field => $value) {
                                if ($pasajero->{$field} != $value) {
                                    $pasajero->{$field} = $value;
                                    $hasChanges = true;
                                }
                            }
                        
                            if ($hasChanges) {
                                $pasajero->save();
                            }
                        }
                    }
                }
                return redirect('pasajeros/CRM/listar')->with('actualizacion', 'Se ha realizado la actualización masiva de los pasajeros');
            } else {
                return back()->withErrors(['sql' => 'El archivo ingresado no está en el formato correcto']);
            }
    }
}
