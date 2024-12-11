<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use Illuminate\Http\Request;
use App\Models\Tercero;
use App\Models\TerceroIcon;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PropietarioController extends Controller
{

    public function editar($propietario){

        $propietario = Tercero::select('TERCERO', 'DIRECCION', 'TELEFONO', 'EMAIL', 'CELULAR' , 'PRIMER_NOMBRE', 'PRIMER_APELLIDO')->where('TERCERO', $propietario)->first();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('propietarios.form', ['propietario' => $propietario, 'usuario' => $usuario, 'method' => 'put', 'route' => ['propietarios.actualizar', $propietario->TERCERO]  ]); 
    }
   
    public function update(Request $request, $propietario)
    {
        $propietario = TerceroIcon::select('TERCERO', 'DIRECCION', 'TELEFONO', 'EMAIL', 'CELULAR', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO')->where('TERCERO', $propietario)->first();
        $propietario->DIRECCION = $request->input('DIRECCION');
        $propietario->TELEFONO = $request->input('TELEFONO');
        $propietario->EMAIL = $request->input('EMAIL');
        $propietario->CELULAR = $request->input('CELULAR');
        $propietario->save();

        $vehiculos = Vehiculo::with('propietario.tercero.municipio', 'documentos.aseguradora')->where('PROPIETARIO', $propietario->TERCERO)->get();
        $excel = IOFactory::load(storage_path() . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "propietarios.xlsx");
        $hoja = $excel->setActiveSheetIndex(0);

        $form1 = $hoja->getCell('N2')->getValue();
        $form2 = $hoja->getCell('O2')->getValue();

        $fila = 2;
        foreach ($vehiculos as $vehiculo) {
            if(count($vehiculo->documentos) > 0){
                $hoja->setCellValue('A'.$fila, str_replace("-", "", $vehiculo->PLACA));
                foreach ($vehiculo->documentos as $documento) {
                    if($documento->aseguradora != null){
                        if($documento->TIPO_DOCUMENTO == 2){
                            $hoja->setCellValue('D'.$fila, $documento->aseguradora->DESCRIPCION);
                            $hoja->setCellValue('E'.$fila, $documento->FECHA_VENCIMIENTO);
                        }elseif ($documento->TIPO_DOCUMENTO == 8) {
                            $hoja->setCellValue('B'.$fila, $documento->aseguradora->DESCRIPCION);
                            $hoja->setCellValue('C'.$fila, $documento->FECHA_VENCIMIENTO);
                        }
                    }else{
                        if($documento->TIPO_DOCUMENTO == 2){
                            $hoja->setCellValue('D'.$fila, $documento->DESCRIPCION);
                            $hoja->setCellValue('E'.$fila, $documento->FECHA_VENCIMIENTO);
                        }elseif ($documento->TIPO_DOCUMENTO == 8) {
                            $hoja->setCellValue('B'.$fila, $documento->DESCRIPCION);
                            $hoja->setCellValue('C'.$fila, $documento->FECHA_VENCIMIENTO);
                        }
                    }
                }
                $hoja->setCellValue('F'.$fila, $vehiculo->propietario->tercero->PRIMER_NOMBRE);
                $hoja->setCellValue('G'.$fila, $vehiculo->propietario->tercero->SEGUNDO_NOMBRE);
                $hoja->setCellValue('H'.$fila, $vehiculo->propietario->tercero->PRIMER_APELLIDO);
                $hoja->setCellValue('I'.$fila, $vehiculo->propietario->tercero->SEGUNDO_APELLIDO);
                $hoja->setCellValue('J'.$fila, $vehiculo->propietario->tercero->NRO_IDENTIFICACION);
                $hoja->setCellValue('K'.$fila, $vehiculo->propietario->tercero->DIRECCION);
                $hoja->setCellValue('L'.$fila, $vehiculo->propietario->tercero->BARRIO);
                $hoja->setCellValueExplicit('M'.$fila, $vehiculo->propietario->tercero->municipio->MUNICIPIO, DataType::TYPE_STRING);
                $hoja->setCellValue('N'.$fila, $form1);
                $hoja->setCellValue('O'.$fila, $form2);
                $hoja->setCellValueExplicit('P'.$fila, $vehiculo->propietario->tercero->TELEFONO, DataType::TYPE_STRING);
                $hoja->setCellValueExplicit('Q'.$fila, $vehiculo->propietario->tercero->CELULAR, DataType::TYPE_STRING);
                $hoja->setCellValue('R'.$fila, $vehiculo->propietario->tercero->EMAIL);
                $form1 = str_replace("M".$fila, "M".($fila+1), $form1);
                $form2 = str_replace("M".$fila, "M".($fila+1), $form2);
                $fila++;
            } 
        }

        foreach (range('A', 'R') as $columnID) {
            $hoja->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($excel);
        $writer->save('PlantillaProp.xlsx');
        $archivo = file_get_contents('PlantillaProp.xlsx');
        unlink('PlantillaProp.xlsx');

        return base64_encode($archivo);
    }

    public function vehiculos($propietario){

        $tercero = Tercero::select('TERCERO')->with(['propietario'=>function($q){$q->select('TERCERO')->with(['vehiculospri.marca', 'vehiculos.marca']);}])->where('TERCERO', $propietario)->first();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('propietarios.vehiculos', compact('tercero', 'usuario'));

    }

    public function afiliados(){

        $conductores = Conductor::select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION', 'CELULAR', 'EMAIL')->with(['cuentac'=>function($q){$q->select('id', 'estado', 'conductor_CONDUCTOR');}])->has('cuentac')->paginate(15);
        $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', 'CELULAR', 'EMAIL')->where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->paginate(15);

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('afiliados.lista', compact('propietarios', 'conductores', 'usuario'));

    }

    public function filtrar(Request $request){
        
        if (!$request->filled('perfil') && !$request->filled('identificacion') && !$request->filled('nombre') && !$request->filled('celular') && !$request->filled('email') && !$request->filled('estado')){
            return redirect('afiliados');
        }

        $idsc = [];
        $idsp = [];
        $filtro = "";
        $c1 = ""; $c2 = ""; $c3 = "";
        $c4= ""; $c5 = ""; $c6 = "";

        if($request->filled('perfil')){
            if(empty($idsc) && empty($idsp)){
                if($request->input('perfil') == "Conductor"){
                    $co1 = Conductor::has('cuentac')->pluck('CONDUCTOR')->toArray();
                    $pr1 = [];            
                }else{
                    $pr1 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->pluck('TERCERO')->toArray();
                    $co1 = [];
                }
            }else{
                if($request->input('perfil') == "Conductor"){
                    $co1 = Conductor::has('cuentac')->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();             
                    $pr1 = []; 
                }else{
                    $pr1 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();
                    $co1 = [];
                }
            }        
            $idsc = $co1;
            $idsp = $pr1;
            $c1 = $request->input('perfil');
            $filtro = $filtro . 'Perfil=' . $request->input('perfil') . ', ';
        }

        if ($request->filled('identificacion')) {
            if(empty($idsc) && empty($idsp)){
                $co2 = Conductor::has('cuentac')->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->pluck('CONDUCTOR')->toArray();
                $pr2 = Tercero::where('TIPO_IDENTIFICACION', '01')->where('NRO_IDENTIFICACION', $request->input('identificacion'))->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->pluck('TERCERO')->toArray();            
            }else{
                $co2 = Conductor::has('cuentac')->where('NUMERO_IDENTIFICACION', $request->input('identificacion'))->pluck('CONDUCTOR')->toArray();
                $pr2 = Tercero::where('TIPO_IDENTIFICACION', '01')->where('NRO_IDENTIFICACION', $request->input('identificacion'))->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();            
            
            }
            $idsc = $co2;
            $idsp = $pr2;
            $c2 = $request->input('identificacion');
            $filtro = $filtro . 'Identificación=' . $request->input('identificacion') . ', ';
        }

        if ($request->filled('nombre')) {
            if(empty($idsc) && empty($idsp)){
                $co3 = Conductor::has('cuentac')->where('NOMBRE', 'like', '%' . $request->input('nombre') . '%')->pluck('CONDUCTOR')->toArray();
                $pr3 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('PRIMER_NOMBRE', 'like', '%' . $request->input('nombre') . '%')->pluck('TERCERO')->toArray();                
            }else{
                $co3 = Conductor::has('cuentac')->where('NOMBRE', 'like', '%' . $request->input('nombre') . '%')->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
                $pr3 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('PRIMER_NOMBRE', 'like', '%' . $request->input('nombre') . '%')->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();                
            }
            $idsc = $co3;
            $idsp = $pr3;
            $c3 = $request->input('nombre');
            $filtro = $filtro . 'Nombre=' . $request->input('nombre') . ', ';
        }

        if ($request->filled('celular')) {
            if(empty($idsc) && empty($idsp)){
                $co4 = Conductor::has('cuentac')->where('CELULAR', $request->input('celular'))->pluck('CONDUCTOR')->toArray();
                $pr4 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('CELULAR', $request->input('celular'))->pluck('TERCERO')->toArray();            
            }else{
                $co4 = Conductor::has('cuentac')->where('CELULAR', $request->input('celular'))->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
                $pr4 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('CELULAR', $request->input('celular'))->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();            
            }
            $idsc = $co4;
            $idsp = $pr4;
            $c4 = $request->input('celular');
            $filtro = $filtro . 'Celular=' . $request->input('celular') . ', ';
        }

        if ($request->filled('email')) {
            if(empty($idsc) && empty($idsp)){
                $co5 = Conductor::has('cuentac')->where('EMAIL', 'like', '%' . $request->input('email') . '%')->pluck('CONDUCTOR')->toArray();
                $pr5 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('EMAIL', 'like', '%' . $request->input('email') . '%')->pluck('TERCERO')->toArray();             
            }else{
                $co5 = Conductor::has('cuentac')->where('EMAIL', 'like', '%' . $request->input('email') . '%')->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
                $pr5 = Tercero::where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->where('EMAIL', 'like', '%' . $request->input('email') . '%')->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();             
            }
            $idsc = $co5;
            $idsp = $pr5;
            $c5 = $request->input('email');
            $filtro = $filtro . 'Email=' . $request->input('email') . ', ';
        }

        if ($request->filled('estado')) {
            $pr6 = [];
            $estado = $request->input('estado');
            if(empty($idsc) && empty($idsp)){
                if($estado != "Activo"){
                    $co6 = Conductor::whereHas('cuentac', function($q) use($estado){$q->where('estado', $estado);})->pluck('CONDUCTOR')->toArray();
                }else{
                    $co6 = Conductor::whereHas('cuentac', function($q) use($estado){$q->where('estado', 'No disponible')->orWhere('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio');})->pluck('CONDUCTOR')->toArray();
                }
            }else{
                if($estado != "Activo"){
                    $co6 = Conductor::whereHas('cuentac', function($q) use($estado){ $q->where('estado', $estado);})->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
                }else{
                    $co6 = Conductor::whereHas('cuentac', function($q) use($estado){$q->where('estado', 'No disponible')->orWhere('estado', 'Libre')->orWhere('estado', 'Ocupado')->orWhere('estado', 'Ocupado propio');})->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
                }
            }
            $idsc = $co6;
            $idsp = $pr6;
            $c6 = $request->input('estado');
            $filtro = $filtro . 'Estado=' . $request->input('estado') . ', '; 
        }

        if(!empty($idsc)){
            $conductores = Conductor::select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION', 'CELULAR', 'EMAIL')->with(['cuentac'=>function($q){$q->select('id', 'estado', 'conductor_CONDUCTOR');}])->whereIn('CONDUCTOR', $idsc)->paginate(15)->appends($request->query());
        }else{
            $conductores = collect([]);
        }

        if (!empty($idsp)) {
            $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', 'CELULAR', 'EMAIL')->whereIn('TERCERO', $idsp)->paginate(15)->appends($request->query());
        } else {
            $propietarios = collect([]);
        }
        
        $filtroc = Conductor::select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION', 'CELULAR', 'EMAIL')->with(['cuentac'=>function($q){$q->select('id', 'estado', 'conductor_CONDUCTOR');}])->whereIn('CONDUCTOR', $idsc)->pluck('CONDUCTOR')->toArray();
        $filtrop = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', 'CELULAR', 'EMAIL')->whereIn('TERCERO', $idsp)->pluck('TERCERO')->toArray();

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('afiliados.lista', compact('conductores', 'usuario', 'propietarios', 'filtro', 'filtroc', 'filtrop', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6'));

    }


    public function exportar(Request $request){

        if ($request->filled('filtroc') || $request->filled('filtrop')) {
            $filtroc = explode(",", $request->input('filtroc'));
            $filtrop = explode(",", $request->input('filtrop'));
            $conductores = Conductor::select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION', 'CELULAR', 'EMAIL')->with(['cuentac'=>function($q){$q->select('id', 'estado', 'conductor_CONDUCTOR');}])->whereIn('CONDUCTOR', $filtroc)->get();
            $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', 'CELULAR', 'EMAIL')->whereIn('TERCERO', $filtrop)->get();
        }else{
            $conductores = Conductor::select('CONDUCTOR', 'NOMBRE', 'NUMERO_IDENTIFICACION', 'CELULAR', 'EMAIL')->with(['cuentac'=>function($q){$q->select('id', 'estado', 'conductor_CONDUCTOR');}])->has('cuentac')->get();
            $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', 'CELULAR', 'EMAIL')->where('TIPO_IDENTIFICACION', '01')->whereHas('propietario',function($q){$q->whereHas('vehiculospri', function($r){$r->whereHas('conductores', function($s){$s->where('SW_ACTIVO_NUEVO_CRM', "1");});});})->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("C1:D1");
        $sheet->setCellValue("C1", "Lista de Afiliados");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("C1:D1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Perfil");
        $sheet->setCellValue("B2", "Identificación");
        $sheet->setCellValue("C2", "Nombre");
        $sheet->setCellValue("D2", "Celular");
        $sheet->setCellValue("E2", "Email");
        $sheet->setCellValue("F2", "Estado");
        $sheet->getStyle("A1:F2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($propietarios as $propietario) {
            $sheet->setCellValue("A" . $indice, "Propietario");
            $sheet->setCellValue("B" . $indice, $propietario->NRO_IDENTIFICACION);
            $sheet->setCellValue("C" . $indice, $propietario->PRIMER_NOMBRE . " " . $propietario->PRIMER_APELLIDO);
            $sheet->setCellValue("D" . $indice, $propietario->CELULAR);
            $sheet->setCellValue("E" . $indice, $propietario->EMAIL);
            $sheet->setCellValue("F" . $indice, "-");
            $indice++;
        }

        foreach ($conductores as $conductor) {
            $sheet->setCellValue("A" . $indice, "Conductor");
            $sheet->setCellValue("B" . $indice, $conductor->NUMERO_IDENTIFICACION);
            $sheet->setCellValue("C" . $indice, $conductor->NOMBRE);
            $sheet->setCellValue("D" . $indice, $conductor->CELULAR);
            $sheet->setCellValue("E" . $indice, $conductor->EMAIL);
            if($conductor->cuentac->estado == "Bloqueado"){
                $sheet->setCellValue("F" . $indice, "Bloqueado");
            }elseif($conductor->cuentac->estado == "Inactivo"){
               $sheet->setCellValue("F" . $indice, "Inactivo"); 
            }else{
                $sheet->setCellValue("F" . $indice, "Activo"); 
            }
            $indice++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Afiliados.xlsx');
        $archivo = file_get_contents('Afiliados.xlsx');
        unlink('Afiliados.xlsx');

        return base64_encode($archivo);
    }
}
