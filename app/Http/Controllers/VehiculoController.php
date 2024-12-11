<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\Cuentac;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;
use stdClass;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
            $s->where('SW_ACTIVO_NUEVO_CRM', "1");
        })->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('vehiculos.lista', compact('vehiculos', 'usuario'));
    }

    public function buscar(Request $request)
    {

        $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
            $s->where('SW_ACTIVO_NUEVO_CRM', "1");
        })->where('PLACA', 'like', '%' . $request->input('placa') . '%')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('vehiculos.lista', compact('vehiculos', 'usuario'));
    }

    public function ubicar(Request $request)
    {

        if ($request->ajax()) {
            $resp = new stdClass();

            if ($request->filled('placa')) {
                $cuenta = Cuentac::with(['conductor' => function ($query) {
                    $query->select('CONDUCTOR', 'NOMBRE', 'TELEFONO', 'CELULAR');
                }])->select('id', 'latitud', 'longitud', 'estado', 'placa', 'conductor_CONDUCTOR')->where('placa', $request->input('placa'))->where(function ($q) {
                    $q->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio');
                })->first();

                if ($cuenta != null) {
                    $resp->vehiculos = [$cuenta];
                } else {
                    $resp->vehiculos = [];
                }
            } else {
                $cuentas = Cuentac::with(['conductor' => function ($q) {
                    $q->select('CONDUCTOR', 'NOMBRE', 'TELEFONO', 'CELULAR');
                }])->select('id', 'latitud', 'longitud', 'estado', 'placa', 'conductor_CONDUCTOR')->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio')->get();
                $resp->vehiculos = $cuentas;
            }

            return json_encode($resp);
        } else {
            $hora = Carbon::now();
            $placa = $request->input('placa');
            $cuentas = Cuentac::select('id', 'estado')->where('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio')->count();
            $curso = Servicio::select('id', 'estado', 'fecha')->where('estado', 'En curso')->orWhere('estado', 'Asignado')->orWhere('estado', 'Pendiente')->count();
            $finalizados = Servicio::select('id', 'estado', 'fecha')->where('estado', 'Finalizado')->whereDate('fecha', $hora)->count();
            $cancelados = Servicio::select('id', 'estado', 'fecha')->whereDate('fecha', $hora)->where(function ($q) {
                $q->where('estado', 'Cancelado')->orWhere('estado', 'No vehiculo');
            })->count();
            $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

            return view('vehiculos.ubicar', compact('usuario', 'placa', 'hora', 'cancelados', 'curso', 'finalizados', 'cuentas'));
        }
    }

    public function filtrar(Request $request)
    {

        if ($request->filled('placa')) {
            $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->where('PLACA', 'like', '%' . $request->input('placa') . '%')->paginate(20)->appends($request->query());
            $filtro = array('Placa', $request->input('placa'));
        } elseif ($request->filled('marca')) {
            $marca = $request->input('marca');
            $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('marca', function ($q) use ($marca) {
                $q->where('DESCRIPCION', 'like', '%' . $marca . '%');
            })->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->paginate(20)->appends($request->query());
            $filtro = array('Marca', $request->input('marca'));
        } elseif ($request->filled('modelo')) {
            $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->where('MODELO', $request->input('modelo'))->paginate(20)->appends($request->query());
            $filtro = array('Modelo', $request->input('modelo'));
        } elseif ($request->filled('propietario')) {
            $prop = $request->input('propietario');
            $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('propietario', function ($q) use ($prop) {
                $q->whereHas('tercero', function ($q2) use ($prop) {
                    $q2->where('PRIMER_NOMBRE', 'like', '%' . $prop . '%');
                });
            })->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->paginate(20)->appends($request->query());
            $filtro = array('Propietario', $request->input('propietario'));
        } else {
            return redirect('vehiculos');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('vehiculos.lista', compact('vehiculos', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {

        if ($request->filled('filtro')) {
            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "Placa") {
                $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
                    $s->where('SW_ACTIVO_NUEVO_CRM', "1");
                })->where('PLACA', 'like', '%' . $filtro[1] . '%')->get();
            } elseif ($filtro[0] == "Marca") {
                $marca = $filtro[1];
                $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('marca', function ($q) use ($marca) {
                    $q->where('DESCRIPCION', 'like', '%' . $marca . '%');
                })->whereHas('conductores', function ($s) {
                    $s->where('SW_ACTIVO_NUEVO_CRM', "1");
                })->get();
            } elseif ($filtro[0] == "Modelo") {
                $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->where('MODELO', $filtro[1])->whereHas('conductores', function ($s) {
                    $s->where('SW_ACTIVO_NUEVO_CRM', "1");
                })->get();
            } elseif ($filtro[0] == "Propietario") {
                $prop = $filtro[1];
                $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('propietario', function ($q) use ($prop) {
                    $q->whereHas('tercero', function ($q2) use ($prop) {
                        $q2->where('PRIMER_NOMBRE', 'like', '%' . $prop . '%');
                    });
                })->whereHas('conductores', function ($s) {
                    $s->where('SW_ACTIVO_NUEVO_CRM', "1");
                })->get();
            }
        } else {
            $vehiculos = Vehiculo::with(['propietario.tercero', 'marca'])->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:C1");
        $sheet->setCellValue("B1", "Lista de Vehiculos");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:C1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Placa");
        $sheet->setCellValue("B2", "Marca");
        $sheet->setCellValue("C2", "Modelo");
        $sheet->setCellValue("D2", "Propietario");
        $sheet->getStyle("A1:D2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($vehiculos as $vehiculo) {
            $sheet->setCellValue("A" . $indice, $vehiculo->PLACA);
            $sheet->setCellValue("B" . $indice, $vehiculo->marca->DESCRIPCION);
            $sheet->setCellValue("C" . $indice, $vehiculo->MODELO);
            $sheet->setCellValue("D" . $indice, $vehiculo->propietario->tercero->PRIMER_NOMBRE . " " . $vehiculo->propietario->tercero->PRIMER_APELLIDO);
            $indice++;
        }

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Vehiculos.xlsx');
        $archivo = file_get_contents('Vehiculos.xlsx');
        unlink('Vehiculos.xlsx');

        return base64_encode($archivo);
    }

    public function conductoresPlaca(Request $request)
    {
        $vehiculos = [];
        $placas = Vehiculo::with(['conductores' => function ($q) {
            $q->with(['cuentac' => function ($r) {
                $r->select('id', 'conductor_CONDUCTOR');
            }]);
        }])->whereHas('conductores', function ($s) {
            $s->where('SW_ACTIVO_NUEVO_CRM', "1");
        })->where('PLACA', 'like', $request->input('placa') . '%')->get();

        foreach ($placas as $placa) {
            //$taxistas = "";
            foreach ($placa->conductores as $conductor) {
                if ($conductor->pivot->SW_ACTIVO_NUEVO_CRM == "1" && $conductor->cuentac != null) {
                    $veh = new stdClass();
                    $veh->placa = $placa->PLACA;
                    $veh->nombre = $conductor->NOMBRE;
                    $veh->cuenta = $conductor->cuentac->id;
                    $vehiculos[] = $veh;
                }
            }
        }

        return json_encode($vehiculos);
    }

    public function crearCertificacion()
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('vehiculos.certificaciones', compact('usuario'));
    }

    public function generarCertificacion(Request $request)
    {
        //return json_encode($request->input());
        $modelos = explode(" - ", $request->input('modelo'));
        if ($request->input('tipo') == "Todos") {
            $vehiculos = Vehiculo::with('marca')->whereBetween('MODELO', $modelos)->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->get();
        } else {
            $vehiculos = Vehiculo::with('marca')->whereBetween('MODELO', $modelos)->where('TIPO_VEHICULO', $request->input('tipo'))->whereHas('conductores', function ($s) {
                $s->where('SW_ACTIVO_NUEVO_CRM', "1");
            })->get();
        }

        $phpWord = new PhpWord();
        $language = new Language(Language::ES_ES);
        $phpWord->getSettings()->setThemeFontLang($language);

        $section = $phpWord->addSection();
        $todo = 'todo';
        $phpWord->addFontStyle(
            $todo,
            array('name' => 'Arial', 'size' => 12, 'color' => '000000')
        );

        $negrita = 'negrita';
        $phpWord->addFontStyle(
            $negrita,
            array('name' => 'Arial', 'size' => 12, 'color' => '000000', 'bold' => true)
        );

        $spie = 'spie';
        $phpWord->addFontStyle(
            $spie,
            array('name' => 'Arial', 'size' => 10, 'color' => '009900')
        );

        $parrafoizq = 'parrafoizq';
        $phpWord->addParagraphStyle($parrafoizq, array('alignment' => Jc::START));
        $parrafocen = 'parrafocen';
        $phpWord->addParagraphStyle($parrafocen, array('alignment' => Jc::CENTER));
        $parrafopie = 'parrafopie';
        $phpWord->addParagraphStyle($parrafopie, array('alignment' => Jc::CENTER, 'spaceAfter' => 0));

        $tableStyle = array(
            'borderColor' => 'black',
            'borderSize'  => 3,
            'cellMargin'  => 10,
            'alignment' => Jc::CENTER
        );
        $firstRowStyle = array('bgColor' => 'blue', 'name' => 'Arial', 'alignment' => Jc::CENTER, 'size' => 12, 'color' => 'white');
        $phpWord->addTableStyle('tbvehiculos', $tableStyle, $firstRowStyle);


        $subsequent = $section->addHeader();
        $subsequent->addImage('../imagenes/title.png', array('alignment' => Jc::START));

        $pie = $section->addFooter();
        $pie->addText("NIT: 900.886.956-2", $spie, $parrafopie);
        $pie->addText("Carrera 33 No. 49-35 Oficina 300-6 Cabecera II Etapa – Conmutador: 633 92 15", $spie, $parrafopie);
        $pie->addText("Nuestro objetivo es la seguridad en el transporte Publico Individual de personas", $spie, $parrafopie);


        $section->addText("JADMIN", $negrita, $parrafocen);

        $section->addTextBreak(2);

        $txtrun = $section->addTextRun($parrafoizq);
        $txtrun->addText("Fecha: ", $negrita);
        $txtrun->addText(Carbon::now(), $todo);

        $txtrun = $section->addTextRun($parrafoizq);
        $txtrun->addText("Modelos: ", $negrita);
        $txtrun->addText($modelos[0] . "-" . $modelos[1], $todo);

        $txtrun = $section->addTextRun($parrafoizq);
        $txtrun->addText("Tipo de servicio: ", $negrita);
        if ($request->input('tipo') == "Todos") {
            $txtrun->addText("Servicio individual de transporte de pasajeros, Servicio especial", $todo);
        } else {
            if ($request->input('tipo') == "0") {
                $txtrun->addText("Servicio individual de transporte de pasajeros", $todo);
            } else {
                $txtrun->addText("Servicio especial", $todo);
            }
        }

        $txtrun = $section->addTextRun($parrafoizq);
        $txtrun->addText("Total de vehículos: ", $negrita);
        $txtrun->addText(number_format(count($vehiculos)), $todo);

        $section->addTextBreak();

        $tabla = $section->addTable('tbvehiculos');
        $tabla->addRow();
        $tabla->addCell()->addText("Placa");
        $tabla->addCell()->addText("Modelo");
        $tabla->addCell()->addText("Marca");
        $tabla->addCell()->addText("Tipo de servicio");

        foreach ($vehiculos as $vehiculo) {
            $tabla->addRow();
            $tabla->addCell()->addText($vehiculo->PLACA, $todo);
            $tabla->addCell()->addText($vehiculo->MODELO, $todo);
            $tabla->addCell()->addText($vehiculo->marca->DESCRIPCION, $todo);
            if ($vehiculo->TIPO_VEHICULO == "1") {
                $tabla->addCell()->addText("Servicio especial", $todo);
            } else {
                $tabla->addCell()->addText("Taxi", $todo);
            }
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $namecert = 'Vehículos JADMIN.docx';
        $objWriter->save($namecert);

        $file = public_path() . '/' . $namecert;

        $headers = array('Content-Type: application/octet-stream');

        return response()->download($file, "Vehículos JADMION.docx", $headers);
    }

    public function expVehiculos()
    {
        $objPHPExcel = PhpSpreadsheetIOFactory::load("../imagenes/placas.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetR = $objPHPExcel->getActiveSheet();
        $numRows = $sheetR->getHighestRow();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheetC = new Spreadsheet();
        $sheetC = $spreadsheetC->getActiveSheet();

        $sheet->setCellValue("A1", "PLACA");
        $sheet->setCellValue("B1", "INTERNO");
        $sheet->setCellValue("C1", "ORDEN");
        $sheet->setCellValue("D1", "MOVILIZACION");
        $sheet->setCellValue("E1", "VENCIMIENTO");

        $sheetC->setCellValue("A1", "CEDULA");
        $sheetC->setCellValue("B1", "NOMBRES");
        $sheetC->setCellValue("C1", "APELLIDOS");
        $sheetC->setCellValue("D1", "EPS");
        $sheetC->setCellValue("E1", "ARL");
        $sheetC->setCellValue("F1", "SANGRE");

        $iconds = 2;

        if ($numRows >= 1) {
            for ($i = 2; $i <= $numRows; $i++) {
                $placa = $sheetR->getCell('A' . $i)->getCalculatedValue();;
                $vehiculo = Vehiculo::with(['documentos' => function ($q) {
                    $q->where('TIPO_DOCUMENTO', '1');
                }, 'conductores' => function ($r) {
                    $r->where('SW_ACTIVO_NUEVO_CRM', '1')->with('eps', 'arp');
                }])->where('PLACA', $placa)->first();
                $sheet->setCellValueExplicit("A" . $i, $vehiculo->PLACA, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B" . $i, $vehiculo->TAX_SUR, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C" . $i, $vehiculo->NUMERO_VERDE, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D" . $i, $vehiculo->documentos[0]->DESCRIPCION, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("E" . $i, $vehiculo->documentos[0]->FECHA_VENCIMIENTO, DataType::TYPE_STRING);

                foreach ($vehiculo->conductores as $conductor) {
                    if ($conductor->CONDUCTOR != "1") {
                        $sheetC->setCellValueExplicit("A" . $iconds, $conductor->NUMERO_IDENTIFICACION, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("B" . $iconds, $conductor->PRIMER_NOMBRE . " " . $conductor->SEGUNDO_NOMBRE, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("C" . $iconds, $conductor->PRIMER_APELLIDO . " " . $conductor->SEGUNDO_APELLIDO, DataType::TYPE_STRING);
                        if ($conductor->eps != null) {
                            $sheetC->setCellValueExplicit("D" . $iconds, $conductor->eps->DESCRIPCION, DataType::TYPE_STRING);
                        } else {
                            $sheetC->setCellValueExplicit("D" . $iconds, "", DataType::TYPE_STRING);
                        }
                        if ($conductor->arp != null) {
                            $sheetC->setCellValueExplicit("E" . $iconds, $conductor->arp->DESCRIPCION, DataType::TYPE_STRING);
                        } else {
                            $sheetC->setCellValueExplicit("E" . $iconds, "", DataType::TYPE_STRING);
                        }
                        if ($conductor->GRUPO_SANGUINEO == 1) {
                            $rh = "A";
                        } elseif ($conductor->GRUPO_SANGUINEO == 2) {
                            $rh = "B";
                        } elseif ($conductor->GRUPO_SANGUINEO == 3) {
                            $rh = "AB";
                        } elseif ($conductor->GRUPO_SANGUINEO == 4) {
                            $rh = "O";
                        } else {
                            $rh = "";
                        }
                        if ($conductor->RH == 1) {
                            $rh = $rh . "+";
                        } elseif ($conductor->RH == 2) {
                            $rh = $rh . "-";
                        } else {
                            $rh = "";
                        }
                        $sheetC->setCellValueExplicit("F" . $iconds, $rh, DataType::TYPE_STRING);
                        $iconds++;
                    }
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('vehiculos.xlsx');

        $writer = new Xlsx($spreadsheetC);
        $writer->save('conductores.xlsx');

        return "listo";
    }

    public function expPropietarios()
    {
        $objPHPExcel = PhpSpreadsheetIOFactory::load("../imagenes/placas.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetR = $objPHPExcel->getActiveSheet();
        $numRows = $sheetR->getHighestRow();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue("A1", "PLACA");
        $sheet->setCellValue("B1", "NOMBRES");
        $sheet->setCellValue("C1", "APELLIDOS");
        $sheet->setCellValue("D1", "IDENTIFICACION");
        $sheet->setCellValue("E1", "LICENCIA");
        $sheet->setCellValue("F1", "DIRECCION");
        $sheet->setCellValue("G1", "BARRIO");
        $sheet->setCellValue("H1", "MUNICIPIO");
        $sheet->setCellValue("I1", "FIJO");
        $sheet->setCellValue("J1", "CELULAR");
        $sheet->setCellValue("K1", "EMAIL");

        if ($numRows >= 1) {
            for ($i = 2; $i <= $numRows; $i++) {
                $placa = $sheetR->getCell('A' . $i)->getCalculatedValue();
                $vehiculo = Vehiculo::with('propietario.tercero.municipio')->where('PLACA', $placa)->first();
                if ($vehiculo->propietario != null) {
                    $sheet->setCellValueExplicit("A" . $i, $vehiculo->PLACA, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("B" . $i, $vehiculo->propietario->tercero->PRIMER_NOMBRE . " " . $vehiculo->propietario->tercero->SEGUNDO_NOMBRE, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C" . $i, $vehiculo->propietario->tercero->PRIMER_APELLIDO . " " . $vehiculo->propietario->tercero->SEGUNDO_APELLIDO, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D" . $i, $vehiculo->propietario->tercero->NRO_IDENTIFICACION, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("E" . $i, $vehiculo->propietario->LICENCIA, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("F" . $i, $vehiculo->propietario->tercero->DIRECCION, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("G" . $i, $vehiculo->propietario->tercero->BARRIO, DataType::TYPE_STRING);
                    if ($vehiculo->propietario->tercero->municipio != null) {
                        $sheet->setCellValueExplicit("H" . $i, $vehiculo->propietario->tercero->municipio->DESCRIPCION, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValueExplicit("H" . $i, "", DataType::TYPE_STRING);
                    }
                    $sheet->setCellValueExplicit("I" . $i, $vehiculo->propietario->tercero->TELEFONO, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("J" . $i, $vehiculo->propietario->tercero->CELULAR, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("K" . $i, $vehiculo->propietario->tercero->EMAIL, DataType::TYPE_STRING);
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('propietarios.xlsx');

        return $numRows;
    }

    public function expInfo()
    {
        $objPHPExcel = PhpSpreadsheetIOFactory::load("../imagenes/placas.xlsx");
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetR = $objPHPExcel->getActiveSheet();
        $numRows = $sheetR->getHighestRow();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheetC = new Spreadsheet();
        $sheetC = $spreadsheetC->getActiveSheet();

        $sheet->setCellValue("A1", "PLACA");
        $sheet->setCellValue("B1", "MARCA");
        $sheet->setCellValue("C1", "MODELO");
        $sheet->setCellValue("D1", "MOTOR");
        $sheet->setCellValue("E1", "CAPACIDAD");
        $sheet->setCellValue("F1", "COMBUSTIBLE");
        $sheet->setCellValue("G1", "CARROCERIA");

        $sheetC->setCellValue("A1", "CEDULA");
        $sheetC->setCellValue("B1", "LICENCIA");
        $sheetC->setCellValue("C1", "DIRECCION");
        $sheetC->setCellValue("D1", "BARRIO");
        $sheetC->setCellValue("E1", "MUNICIPIO");
        $sheetC->setCellValue("F1", "FIJO");
        $sheetC->setCellValue("G1", "CELULAR");
        $sheetC->setCellValue("H1", "EMAIL");
        $sheetC->setCellValue("I1", "PENSION");

        $iconds = 2;

        if ($numRows >= 1) {
            for ($i = 2; $i <= $numRows; $i++) {
                $placa = $sheetR->getCell('A' . $i)->getCalculatedValue();;
                $vehiculo = Vehiculo::with(['documentos' => function ($q) {
                    $q->where('TIPO_DOCUMENTO', '1');
                }, 'conductores' => function ($r) {
                    $r->with('eps', 'arp', 'municipio', 'pension');
                }, 'marca'])->where('PLACA', $placa)->first();
                $sheet->setCellValueExplicit("A" . $i, $vehiculo->PLACA, DataType::TYPE_STRING);
                if ($vehiculo->marca != null) {
                    $sheet->setCellValueExplicit("B" . $i, $vehiculo->marca->DESCRIPCION, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicit("B" . $i, "", DataType::TYPE_STRING);
                }
                $sheet->setCellValueExplicit("C" . $i, $vehiculo->MODELO, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D" . $i, $vehiculo->NUMERO_MOTOR, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("E" . $i, $vehiculo->CAPACIDAD, DataType::TYPE_STRING);
                if ($vehiculo->TIPO_COMBUSTIBLE == 1) {
                    $sheet->setCellValueExplicit("F" . $i, "GASOLINA", DataType::TYPE_STRING);
                } elseif ($vehiculo->TIPO_COMBUSTIBLE == 2) {
                    $sheet->setCellValueExplicit("F" . $i, "GAS", DataType::TYPE_STRING);
                } elseif ($vehiculo->TIPO_COMBUSTIBLE == 3) {
                    $sheet->setCellValueExplicit("F" . $i, "DIESEL", DataType::TYPE_STRING);
                } elseif ($vehiculo->TIPO_COMBUSTIBLE == 4) {
                    $sheet->setCellValueExplicit("F" . $i, "MIXTO", DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicit("F" . $i, "", DataType::TYPE_STRING);
                }
                if ($vehiculo->TIPO_CARROCERIA == 1) {
                    $sheet->setCellValueExplicit("G" . $i, "SEDAN", DataType::TYPE_STRING);
                } elseif ($vehiculo->TIPO_CARROCERIA == 2) {
                    $sheet->setCellValueExplicit("G" . $i, "HATCHBACK", DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicit("G" . $i, "", DataType::TYPE_STRING);
                }

                foreach ($vehiculo->conductores as $conductor) {
                    if ($conductor->CONDUCTOR != "1") {
                        $sheetC->setCellValueExplicit("A" . $iconds, $conductor->NUMERO_IDENTIFICACION, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("B" . $iconds, $conductor->LICENCIA, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("C" . $iconds, $conductor->DIRECCION, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("D" . $iconds, $conductor->BARRIO, DataType::TYPE_STRING);
                        if ($conductor->municipio != null) {
                            $sheetC->setCellValueExplicit("E" . $iconds, $conductor->municipio->DESCRIPCION, DataType::TYPE_STRING);
                        } else {
                            $sheetC->setCellValueExplicit("E" . $iconds, "", DataType::TYPE_STRING);
                        }
                        $sheetC->setCellValueExplicit("F" . $iconds, $conductor->TELEFONO, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("G" . $iconds, $conductor->CELULAR, DataType::TYPE_STRING);
                        $sheetC->setCellValueExplicit("H" . $iconds, $conductor->EMAIL, DataType::TYPE_STRING);
                        if ($conductor->pension != null) {
                            $sheetC->setCellValueExplicit("I" . $iconds, $conductor->pension->DESCRIPCION, DataType::TYPE_STRING);
                        } else {
                            $sheetC->setCellValueExplicit("I" . $iconds, "", DataType::TYPE_STRING);
                        }
                        $iconds++;
                    }
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('vehiculos.xlsx');

        $writer = new Xlsx($spreadsheetC);
        $writer->save('conductores.xlsx');

        return "listo";
    }
}


// Función para calcular la distancia entre dos puntos de latitud/longitud en kilómetros Ecuación de Haversine
//https://upcommons.upc.edu/bitstream/handle/2117/82817/Annex%205.pdf?sequence=7&isAllowed=y
function distanciaMedia($latConductor, $lonConductor, $latAeropuerto, $lonAeropuerto)
{
    $radioTierra = 6371; // Radio de la Tierra en kilómetros
    //Diferencia entre coordenadas en radianes
    $distanciaLat = deg2rad($latAeropuerto - $latConductor);
    $distanciaLon = deg2rad($lonAeropuerto - $lonConductor);

    //: distanciaAprox = sin²(distanciaLat/2) + cos latConductor ⋅ cos latAeropuerto ⋅ sin²(distanciaLon/2)
    $distanciaAprox = sin($distanciaLat / 2) * sin($distanciaLat / 2) +
        cos(deg2rad($latConductor)) * cos(deg2rad($latAeropuerto)) *
        sin($distanciaLon / 2) * sin($distanciaLon / 2);

    //angulosSuperficie = 2 ⋅ atan2( √distanciaAprox, √(1−distanciaAprox) )
    $angulosSuperficie = 2 * atan2(sqrt($distanciaAprox), sqrt(1 - $distanciaAprox));

    //distancia=radioTierra*angulosSuperficie
    $distancia = $radioTierra * $angulosSuperficie;
    return $distancia;
}
