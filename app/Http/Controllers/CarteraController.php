<?php

namespace App\Http\Controllers;

use App\Models\Acuerdo;
use App\Models\Cartera;
use App\Models\Cuota;
use App\Models\FacturaEncabezadoIcon;
use App\Models\Interes;
use App\Models\Tercero;
use App\Models\User;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;
use Luecano\NumeroALetras\NumeroALetras;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\ListItem;
use stdClass;
use ZipArchive;

class CarteraController extends Controller
{
    public function deudores()
    {
        $terceros = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'PRIMER_NOMBRE', 'SEGUNDO_NOMBRE', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO')->has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->take(10)->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cartera.lista', compact('terceros', 'usuario'));
    }

    public function registrosTercero(Tercero $tercero)
    {
		//No lo ignore
        $registros = Cartera::select(DB::raw('CARTERA_GENERICA, AFECTA, FACTURA, FECHA, FECHA_VENCIMIENTO, SALDO_VENCIDO, CUENTA'))->where('TERCERO', $tercero->TERCERO)->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA')->orderBy('FECHA')->paginate(20);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('cartera.registros', compact('registros', 'tercero', 'usuario'));
    }

    public function filtrarDeudores(Request $request)
    {
        $c1 = ""; $c2 = "";
        if($request->filled('identificacion')){
            $c1 = $request->input('identificacion');
            $terceros = Tercero::has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->where('NRO_IDENTIFICACION', $c1)->paginate(20);
        }elseif ($request->filled('nombre')) {
            $c2 = $request->input('nombre');
            $terceros = Tercero::has('cartera')->with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA');}])->where(function($s) use($c2){$s->where('PRIMER_NOMBRE', 'like', '%' . $c2 . '%')->orWhere('PRIMER_APELLIDO', 'like', '%' . $c2 . '%');})->paginate(20);
        }else{
            return redirect('/cartera/listar');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();
        
        return view('cartera.lista', compact('terceros', 'c1', 'c2', 'usuario'));
    }

    public function demanda($tercero)
    {
        $tercero = Tercero::with(['cartera' => function($q){$q->where(function($r){$r->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->groupBy('FACTURA')->orderBy('FECHA');}, 'municipio'])->where('tercero', $tercero)->first();

        $phpWord = new PhpWord();
        $language = new Language(Language::ES_ES);
        $phpWord->getSettings()->setThemeFontLang($language);

        $section = $phpWord->addSection();
        $todo = 'todo';
        $phpWord->addFontStyle($todo,
            array('name' => 'Arial', 'size' => 12, 'color' => '000000')
        );
        $negrita = 'negrita';
        $phpWord->addFontStyle($negrita,
            array('name' => 'Arial', 'size' => 11, 'color' => '000000', 'bold' => true)
        );
        $pie = 'pie';
        $phpWord->addFontStyle($pie,
            array('name' => 'Calibri', 'size' => 7, 'color' => '000000', 'bold' => true)
        );

        $parrafo0 = 'parrafo0';
        $phpWord->addParagraphStyle($parrafo0, array('alignment' => Jc::BOTH, 'spaceAfter' => 0));
        $parrafo0cen = 'parrafo0cen';
        $phpWord->addParagraphStyle($parrafo0cen, array('alignment' => Jc::CENTER, 'spaceAfter' => 0));
        $parrafojus = 'parrafojus';
        $phpWord->addParagraphStyle($parrafojus, array('alignment' => Jc::BOTH));
        $parrafocen = 'parrafocen';
        $phpWord->addParagraphStyle($parrafocen, array('alignment' => Jc::CENTER));
        $sangria = 'sangria';
        $phpWord->addParagraphStyle($sangria, array('indentation' => array('left' => 540, 'right' => 120)));

        $lstnumeros = array('listType' => ListItem::TYPE_NUMBER_NESTED);
        $formatter = new NumeroALetras();
        $subsequent = $section->addHeader();
        $subsequent->addImage('../imagenes/abogado.png', array('width' => 80, 'height' => 100, 'alignment' => Jc::END));
        $footer = $section->addFooter();
        $footer->addTextBreak();
        $footer->addLine(array(
                'width'       => Converter::cmToPixel(12),
                'height'      => Converter::cmToPixel(0),
                'positioning' => 'absolute',));
        $footer->addText("Centro Internacional de Negocios la Triada Calle 35 No 19-41 Oficina 506 Torre Sur", $pie, $parrafo0cen);
        $footer->addText("Teléfonos: 6702178 / Cel. 3115642879-3168757003", $pie, $parrafo0cen);
        $footer->addText("fernandocastillo@abogadosfc.com", $pie, $parrafo0cen);
        $footer->addText("Bucaramanga-Colombia", $pie, $parrafo0cen);
        $section->addText("Señor Juez", $todo, $parrafo0);
        $section->addText("CIVIL MUNICIPAL-REPARTO- ", $negrita, $parrafo0);
        $section->addText($tercero->municipio->DESCRIPCION, $todo, $parrafojus);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("REFERENCIA: PROCESO EJECUTIVO SINGULAR ", $negrita);
        $textrun->addText("promovido por ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("en contra de ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ".", $negrita);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("FERNANDO ENRIQUE CASTILLO GUARIN ", $negrita);
        $textrun->addText("mayor de edad, vecina de esta ciudad, identificado con la cédula de ciudadanía número 13.541.463 de Bucaramanga, abogado portador de la Tarjeta Profesional número 147.006 del Consejo Superior de la Judicatura, actuando en calidad de apoderado judicial de la empresa ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("identificada con Nit. No. 890.211.768-2, con domicilio en la ciudad de Bucaramanga, representada legalmente por la señora ", $todo);
        $textrun->addText("MILSE IDARRAGA BERNAL, ", $negrita);
        $textrun->addText("mayor de edad, identificada con cedula de Ciudadanía No. 37.827.097 expedida en Bucaramanga, con domicilio en la ciudad de Bucaramanga, según poder adjunto; por medio del presente escrito me permito presentar ", $todo);
        $textrun->addText("DEMANDA EJECUTIVA SINGULAR DE MENOR CUANTÍA, ", $negrita);
        $textrun->addText("en contra del señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("identificada con Cedula de Ciudadanía No. " . number_format($tercero->NRO_IDENTIFICACION, 0, ',', '.') . ", domiciliada en la ciudad de " . $tercero->municipio->DESCRIPCION . ", para que se hagan las siguientes o similares condenas. ", $todo);
        $section->addTextBreak();

        $section->addText("PRETENSIONES", $negrita, $parrafocen);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("PRIMERO: ", $negrita);
        $textrun->addText("Librar mandamiento de pago a favor de la empresa ", $todo);
        $textrun->addText("TAXSUR S.A, ", $negrita);
        $textrun->addText("y en contra del señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("por la suma de ", $todo);
        $deuda = 0;
        $intereses = 0;
        $hoy = Carbon::parse("2022-10-21");
        $tasas = Interes::get();
        $tabla = [];
        foreach ($tercero->cartera as $cartera) {
            $fecha = Carbon::parse($cartera->FECHA);
            $fila = new stdClass();
            $fila->mes = strtoupper($this->getMes($fecha->month)) . " DE " . $fecha->year;
            if($cartera->SALDO_VENCIDO > 0){
                $saldof = $cartera->SALDO_VENCIDO;
            }else{
                $saldof = $cartera->SALDO_FACTURA;              
            }
            $fila->concepto = $cartera->AFECTA;
            $fila->capital = $saldof;          
            $deuda = $deuda + $saldof;
            $meses = $hoy->diffInMonths($fecha) + 1;
            $inte = 0;
            for ($i=0; $i < $meses ; $i++) {
                $interes = null;
                foreach ($tasas as $tasa) {
                    if($tasa->year == $fecha->year && $tasa->mes == $fecha->month){
                        $interes = $tasa;
                        break;
                    }
                }
                if($interes != null){
                    $intmes = ($saldof * $interes->tasa) / 100;
                    $inte = $inte + $intmes;
                    $intereses = $intereses + $intmes; 
                } 
                $fecha->addMonth();            
            }
            $fila->interes = $inte;
            $tabla[] = $fila;         
        }
        $facturas = FacturaEncabezadoIcon::where('TERCERO', $tercero->TERCERO)->where('EMPRESA', '001')->where('SALDO_FACTURA', '>', '0')->where('FECHA_VENCIMIENTO', '<', $hoy->format('Y-m-d'))->get();
        foreach ($facturas as $factura) {
            if(strpos(strtolower($factura->OBSERVACION), "administracion") === false && strpos(strtolower($factura->OBSERVACION), "sostenimiento") === false){
                $fecha = Carbon::parse($factura->FECHA_VENCIMIENTO);
                $fila = new stdClass();
                $fila->mes = strtoupper($this->getMes($fecha->month)) . " DE " . $fecha->year;
                $fila->capital = $factura->SALDO_FACTURA;
                $fila->concepto = $factura->OBSERVACION;
                $meses = $hoy->diffInMonths($fecha) + 1;
                $inte = 0;
                for ($i=0; $i < $meses ; $i++) {
                    $interes = null;
                    foreach ($tasas as $tasa) {
                        if($tasa->year == $fecha->year && $tasa->mes == $fecha->month){
                            $interes = $tasa;
                            break;
                        }
                    }
                    if($interes != null){
                        $intmes = ($factura->SALDO_FACTURA * $interes->tasa) / 100;
                        $inte = $inte + $intmes;
                        $intereses = $intereses + $intmes; 
                    } 
                    $fecha->addMonth();            
                }
                $fila->interes = $inte;
                $tabla[] = $fila;
                $deuda = $deuda + $factura->SALDO_FACTURA;
            }  
        }

        $dompdf = PDF::loadView('cartera.modeloIntereses', ['cuotas'=>$tabla, 'placa'=>$tercero->cartera[0]->AFECTA, 'propietario'=>$tercero->cartera[0]->RAZON_SOCIAL]);
        $dompdf->setPaper('Legal', 'Portrait');
        $nametabla = "Tabla de intereses " . $tercero->NRO_IDENTIFICACION . ".pdf"; 
        $dompdf->save($nametabla);

        $textrun->addText($formatter->toWords($deuda, 0) . " PESOS MCTE ($" . number_format($deuda, 0, ',', '.') . ") ", $negrita);
        $textrun->addText("junto con los intereses de mora según la tasa de intereses bancarios corrientes fijado por la superintendencia financiera de Colombia, desde el día que se hizo exigible hasta cuando se cancele la totalidad de la obligación correspondientes a las siguientes sumas de dineros: ", $todo);
        $section->addTextBreak();

        foreach ($tercero->cartera as $cartera) {
            if($cartera->SALDO_VENCIDO > 0){
                $saldof = $cartera->SALDO_VENCIDO;
            }else{
                $saldof = $cartera->SALDO_FACTURA;              
            }
            $listItemRun = $section->addListItemRun(0, $lstnumeros, $parrafojus);
            $listItemRun->addText("Por la suma de ", $todo);
            $listItemRun->addText($formatter->toWords($saldof, 0) . " PESOS MCTE ($" . number_format($saldof, 0, ',', '.') . "), ", $negrita);
            $fecha = Carbon::parse($cartera->FECHA);
            $listItemRun->addText("como cuota de administración correspondiente al mes de " . $this->getMes($fecha->month) . " de " . $fecha->year . ", ");
            $listItemRun->addText("más los intereses de mora liquidados desde el día 06 de " . $this->getMes($fecha->month) . " de " . $fecha->year . " y hasta que se genere el pago de la obligación.");
        }

        foreach ($facturas as $factura) {
            if(!str_contains(strtolower($factura->OBSERVACION), "administracion") && !str_contains(strtolower($factura->OBSERVACION), "sostenimiento")){
                $listItemRun = $section->addListItemRun(0, $lstnumeros, $parrafojus);
                $listItemRun->addText("Por la suma de ", $todo);
                $listItemRun->addText($formatter->toWords($factura->SALDO_FACTURA, 0) . " PESOS MCTE ($" . number_format($factura->SALDO_FACTURA, 0, ',', '.') . "), ", $negrita);
                $fecha = Carbon::parse($factura->FECHA_VENCIMIENTO);
                $listItemRun->addText("correspondiente a " . $factura->OBSERVACION . " del periodo comprendido entre el " . $fecha->day . " de " . $this->getMes($fecha->month) . " de " . $fecha->year . " a " . $fecha->day . " de " . $this->getMes($fecha->month) . " de " . ($fecha->year+1) . ", ");
                $fecha->addDay();
                $listItemRun->addText("más los intereses de mora liquidados desde el día " . $fecha->day . " de " . $this->getMes($fecha->month) . " de " . $fecha->year . " y hasta que se genere el pago de la obligación.");
            }
        }
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("Para un total de capital de ", $todo);
        $textrun->addText($formatter->toWords($deuda, 0) . " PESOS MCTE. ", $negrita);
        $textrun->addText("($" . number_format($deuda, 0, ',', '.') . ").", $negrita);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("SEGUNDO: ", $negrita);
        $textrun->addText("Que se condene al señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . " ", $negrita);
        $textrun->addText("al pago de las costas y agencias en derecho que entrañe esta ejecución.", $todo);
        $section->addTextBreak();

        $section->addText("HECHOS", $negrita, $parrafocen);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("PRIMERO: TAXSUR S.A, ", $negrita);
        $textrun->addText("empresa legalmente constituida tiene como objeto social la organización y explotación del servicio público del transporte terrestre automotor de pasajeros en sus modalidades individual, intermunicipal e interdepartamental, escolar, empresarial y social.", $todo);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("SEGUNDO: ", $negrita);
        $textrun->addText("De acuerdo con lo anterior, la sociedad demandante ", $todo);
        $textrun->addText("TAXSUR S.A; ", $negrita);
        $textrun->addText("suscribió contrato con el señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("para la vinculación del vehículo tipo taxi de placas ", $todo);
        $textrun->addText($tercero->cartera[0]->AFECTA . ".", $negrita);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("TERCERO: ", $negrita);
        $textrun->addText("En el contrato de vinculación, se establece que el contratista, es decir ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("se obliga a pagar mensualmente a ", $todo);
        $textrun->addText("TAXSUR S.A, ", $negrita);
        $textrun->addText("la suma que fije la junta directiva de la empresa por concepto de cuota de administración, , esto se encuentra especificado en la cláusula novena del contrato de vinculación.", $todo);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("CUARTO: ", $negrita);
        $textrun->addText("Como consecuencia de lo anterior, el demandado adeuda a la fecha por cuotas de administración, a ", $todo);
        $textrun->addText($this->getMes($fecha->month) . " de " . $fecha->year . ", un total de ", $todo);
        $textrun->addText($formatter->toWords($deuda, 0) . " PESOS MCTE. ($" . number_format($deuda, 0, ',', '.') . "). ", $negrita);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("QUINTO: ", $negrita);
        $textrun->addText("El título valor presentado al cobro contiene una obligación clara, expresa y actualmente exigible de cancelar sumas liquidadas de dinero e intereses, contenidas de forma conjunta en el contrato de vinculación suscrito entre las partes, el cual consagra la mención del derecho incorporado y la firma de quienes lo crean, y la certificación expedida por la contadora de la empresa ", $todo);
        $textrun->addText("TAXSUR S.A, ", $negrita);
        $textrun->addText("documentos que en conjunto demuestran la existencia de una obligación que se reviste de las características de título ejecutivo. ", $todo);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("SEXTO: ", $negrita);
        $textrun->addText("Me permito indicar de conformidad con lo estipulado en el artículo 245 del Código General del Proceso, el documento título valor original base de la acción (Certificación de Cuota de Administración), se encuentra bajo la custodia del Apoderado Demandante en la calle 35 No. 19-41 oficina 506 torre sur, edificio centro internacional la triada.", $todo);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("SEPTIMO: ", $negrita);
        $textrun->addText("El representante legal de la empresa demandante ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("me ha otorgado poder para adelantar la presente acción.", $todo);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("OCTAVO: ", $negrita);
        $textrun->addText("Me permito manifestar bajo la gravedad de juramento que la presente demanda no ha sido presentada anteriormente, y no ha sido objeto de sentencia, desistimiento tácito, transacción y otra causal de terminación procesal. ", $todo);
        $section->addTextBreak();

        $section->addText("PRUEBAS", $negrita, $parrafocen);
        $section->addTextBreak();

        $listItemRun = $section->addListItemRun(0, null, $parrafojus);
        $listItemRun->addText("Poder para actuar.", $todo);
        $listItemRun = $section->addListItemRun(0, null, $parrafojus);
        $listItemRun->addText("Certificación sobre el monto adeudado expedida por ", $todo);
        $listItemRun->addText("TAXSUR S.A.", $negrita);
        $listItemRun = $section->addListItemRun(0, null, $parrafojus);
        $listItemRun->addText("Contrato de vinculación suscrito entre ", $todo);
        $listItemRun->addText("TAXSUR S.A Y " . $tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ".", $negrita);
        $listItemRun = $section->addListItemRun(0, null, $parrafojus);
        $listItemRun->addText("Certificado de Existencia y Representación Legal de la empresa ", $todo);
        $listItemRun->addText("TAXSUR S.A.", $negrita);
        $section->addTextBreak();

        $section->addText("ANEXOS", $negrita, $parrafocen);
        $section->addTextBreak();

        $section->addText("Además de los documentos señalados en el acápite anterior, acompaño poder para actuar y sendas copias de la demanda para el archivo del Juzgado y las pertinentes para el traslado.", $todo);
        $section->addTextBreak();

        $section->addText("FUNDAMENTOS DE DERECHO", $negrita, $parrafocen);
        $section->addTextBreak();

        $section->addText("Invoco las disposiciones legales aplicables las que siguen: Artículos 82, 83, 88, 422, 423, 431 y siguientes al Código General del Proceso y 774 del Código fe Comercio y numeral 4 del artículo 2 de la ley 712 de 2001.", $todo);
        $section->addTextBreak();

        $section->addText("COMPETENCIA Y CUANTÍA", $negrita, $parrafocen);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("Por la naturaleza del asunto, el domicilio del demandado, el lugar de cumplimiento de la obligación y la cuantía, la cual estimo en la suma de ", $todo);
        $textrun->addText($formatter->toWords($deuda + $intereses, 0) . " PESOS MCTE. ($" . number_format($deuda + $intereses, 0, ',', '.') . " mcte), ", $negrita);
        $textrun->addText("es usted señor Juez competente para conocer de este proceso.", $todo);
        $section->addTextBreak();

        $section->addText("NOTIFICACIONES", $negrita, $parrafocen);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("El señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . " ", $negrita);
        $textrun->addText("recibirá notificaciones en " . $tercero->DIRECCION . " ", $todo);
        if($tercero->BARRIO != null){
            $textrun->addText("Barrio " . $tercero->BARRIO . " ", $todo);
        }
        $textrun->addText("de la ciudad de " . $tercero->municipio->DESCRIPCION . ", ", $todo);
        if ($tercero->TELEFONO != null) {
            $textrun->addText("al teléfono " . $tercero->TELEFONO, $todo);
        }
        if ($tercero->CELULAR != null) {
            if ($tercero->TELEFONO != null) {
                $textrun->addText("-" . $tercero->CELULAR, $todo);
            }else{
                $textrun->addText("al teléfono " . $tercero->CELULAR, $todo);
            }       
        }
        if ($tercero->EMAIL != null) {
            $textrun->addText(" y en la dirección de correo electrónico " . $tercero->EMAIL . ", mediante información entregada por el propietario según consta en la base de datos", $todo);
        }else{
            $textrun->addText(" e indico bajo la gravedad de juramento que desconozco la dirección de correo electrónico donde el demandado puede ser notificado.", $todo);
        }
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("La empresa demandante ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("las recibirá en la Carrera 33 No. 49 – 35 Oficina 300 – 5 de la ciudad de Bucaramanga – Santander; al teléfono 6339215 y al correo electrónico notificaciones@taxsur.com.", $todo);
        $section->addTextBreak();

        $section->addText("Las personales las recibiré en la Calle 35 No. 19 – 41, oficina 506 Torre Sur del Edificio la Triada de Bucaramanga, al teléfono 6702178 y al correo electrónico fernandocastillo@abogadosfc.com.", $todo);
        $section->addTextBreak(2);

        $section->addText("Atentamente;", $todo);
        $section->addTextBreak(2);

        $section->addText("FERNANDO ENRIQUE CASTILLO GUARIN ", $negrita, $parrafo0);
        $section->addText("C.C. N°. 13.541.463 de Bucaramanga", $todo, $parrafo0);
        $section->addText("T.P. N°. 147.006 del C.S de la Judicatura", $todo, $parrafojus);
        $section->addPageBreak();

        $section->addText("Señor Juez", $todo, $parrafo0);
        $section->addText("CIVIL MUNICIPAL-REPARTO- ", $negrita, $parrafo0);
        $section->addText($tercero->municipio->DESCRIPCION, $todo, $parrafojus);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("REFERENCIA: PROCESO EJECUTIVO SINGULAR ", $negrita);
        $textrun->addText("promovido por ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("en contra de ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ".", $negrita);
        $section->addTextBreak();

        $section->addText("     ASUNTO: SOLICITUD MEDIDA CAUTELAR", $negrita, $parrafojus);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("FERNANDO ENRIQUE CASTILLO GUARIN ", $negrita);
        $textrun->addText("mayor de edad, vecina de esta ciudad, identificado con la cédula de ciudadanía número 13.541.463 de Bucaramanga, abogado portador de la Tarjeta Profesional número 147.006 del Consejo Superior de la Judicatura, actuando en calidad de apoderado judicial de la empresa ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("identificada con Nit. No. 890.211.768-2, con domicilio en la ciudad de Bucaramanga, representada legalmente por la señora ", $todo);
        $textrun->addText("MILSE IDARRAGA BERNAL, ", $negrita);
        $textrun->addText("mayor de edad, identificada con cedula de Ciudadanía No. 37.827.097 expedida en Bucaramanga, con domicilio en la ciudad de Bucaramanga, según poder adjunto; por medio del presente escrito me permito solicitar ", $todo);
        $textrun->addText("DECRETE MEDIDA CAUTELAR DE EMBARGO Y SECUESTRO, ", $negrita);
        $textrun->addText("en contra del señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("identificada con Cedula de Ciudadanía No. " . number_format($tercero->NRO_IDENTIFICACION, 0, ',', '.') . ", domiciliada en la ciudad de " . $tercero->municipio->DESCRIPCION . ", así: ", $todo);  
        $section->addTextBreak();

        $section->addText("1.   PERTENECIENTES AL SEÑOR(A) " . $tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ":", $negrita, $sangria);
        $section->addTextBreak();

        $textrun = $section->addTextRun($sangria);
        $textrun->addText("•    CUENTAS CORRIENTES, CUENTAS DE AHORRO Y CDT: ", $negrita);
        $textrun->addText("Solicito el embargo y secuestro de los dineros consignados en cuentas corrientes, cuentas de ahorro o CDT a nombre del señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("identificada con cedula de ciudadanía No. " . number_format($tercero->NRO_IDENTIFICACION, 0, ',', '.') . ", en las siguientes entidades financieras: ", $todo);
        $section->addTextBreak();
        
        $section->addText("BANCOLOMBIA, BANCO DAVIVIENDA, BANCO AV-VILLAS, BANCO DE BOGOTA, BANCO BBVA, BANCO DE OCCIDENTE, BANCO POPULAR, BANCO COLPATRIA, BANCO GNB SUDAMERIS, BANCO CAJA SOCIAL BCSC, BANCO AGRARIO, BANCO PICHINCHA, BANCO BANCOOMEVA, BANCO ITAÚ, BANCO FINANCIERA COOMULTRASAN, BANCO COOPCENTRAL, BANCO FALABELLA S.A, BANCO W, BANCO BANCAMIA, BANCO JURISCOOP, BANCO MUNDO MUJER Y BANCO FINANDINA S.A.", $todo, $sangria);
        $section->addTextBreak();

        $section->addText("En consecuencia, solicito señor Juez oficiar a cada una de las entidades mencionadas para que procedan a consignar los dineros existentes o que en el futuro se consignen a nombre del demandado a órdenes del Juzgado y en la cuenta de depósitos judiciales correspondientes.", $todo);
        $section->addTextBreak(2);

        $section->addText("Atentamente;", $todo);
        $section->addTextBreak(2);

        $section->addText("FERNANDO ENRIQUE CASTILLO GUARIN ", $negrita, $parrafo0);
        $section->addText("C.C. N°. 13.541.463 de Bucaramanga", $todo, $parrafo0);
        $section->addText("T.P. N°. 147.006 del C.S de la Judicatura", $todo, $parrafojus);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $namefile = 'Demanda ' . $tercero->NRO_IDENTIFICACION . '.docx';
        $objWriter->save($namefile);

        $phpWord = new PhpWord();
        $language = new Language(Language::ES_ES);
        $phpWord->getSettings()->setThemeFontLang($language);

        $phpWord->addFontStyle($todo,array('name' => 'Arial', 'size' => 11, 'color' => '000000'));
        $phpWord->addFontStyle($negrita,array('name' => 'Arial', 'size' => 11, 'color' => '000000', 'bold' => true));
        $phpWord->addParagraphStyle($parrafo0, array('alignment' => Jc::BOTH, 'spaceAfter' => 0));
        $phpWord->addParagraphStyle($parrafojus, array('alignment' => Jc::BOTH));
        $section = $phpWord->addSection();

        $section->addText("Señor Juez", $todo, $parrafo0);
        $section->addText("CIVIL MUNICIPAL-REPARTO- ", $negrita, $parrafo0);
        $section->addText($tercero->municipio->DESCRIPCION, $todo, $parrafojus);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("REFERENCIA: PROCESO EJECUTIVO SINGULAR ", $negrita);
        $textrun->addText("promovido por ", $todo);
        $textrun->addText("TAXSUR S.A ", $negrita);
        $textrun->addText("en contra de ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ".", $negrita);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("MILSE IDARRAGA BERNAL, ", $negrita);
        $textrun->addText("mayor de edad, vecina de esta ciudad, identificada con cédula de ciudadanía No. 37.827.097 expedida Bucaramanga, actuando en calidad de representante legal de la empresa ", $todo);
        $textrun->addText("TAXSUR S.A., ", $negrita);
        $textrun->addText("identificada con NIT 890.211.768-2, domiciliada en la ciudad de Bucaramanga, por medio del presente escrito me permito manifestarle muy respetuosamente que confiero poder especial, amplio y suficiente al abogado ", $todo);
        $textrun->addText("FERNANDO ENRIQUE CASTILLO GUARIN, ", $negrita);
        $textrun->addText("mayor de edad, vecino de esta ciudad, identificado con cédula de ciudadanía número 13.541.463 expedida en Bucaramanga y portador de la Tarjeta Profesional No. 147.006 del Consejo Superior de la Judicatura, para que en mi nombre y representación inicie tramite y lleve hasta su culminación ", $todo);
        $textrun->addText("DEMANDA EJECUTIVA SINGULAR DE MÍNIMA CUANTÍA ", $negrita);
        $textrun->addText("en contra del señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("con domicilio en la ciudad de " . $tercero->municipio->DESCRIPCION . ", mayor de edad, identificada con Cédula de Ciudadanía No." . number_format($tercero->NRO_IDENTIFICACION, 0, ',', '.') . ", con el fin de obtener el pago representado en el título ejecutivo, derivado del contrato de vinculación suscrito entre ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . " Y TAXSUR S.A. ", $negrita);
        $section->addTextBreak();

        $section->addText("Mi Apoderado queda facultado para solicitar medidas cautelares, desistir, transigir, conciliar, recibir dineros, renunciar, sustituir, reasumir, y todo cuanto derecho sea necesario para el cabal cumplimiento de este mandato, en los términos del artículo 77 del Código General del Proceso.", $todo, $parrafojus);
        $section->addTextBreak();

        $section->addText("Sírvase señor Juez reconocer   personería a mi apoderado judicial para los efectos y dentro del términos del presente mandato.", $todo, $parrafojus);
        $section->addTextBreak(2);

        $section->addText("Atentamente,", $todo);
        $section->addTextBreak(2);

        $section->addText("MILSE IDARRAGA BERNAL", $negrita, $parrafo0);
        $section->addText("C.C. No. 37.827.097 de Bucaramanga.", $todo, $parrafo0);
        $textrun = $section->addTextRun($parrafo0);
        $textrun->addText("Representante Legal de ", $todo);
        $textrun->addText("TAXSUR S.A.", $negrita);
        $section->addText("notificaciones@taxsur.com", $todo, $parrafo0);
        $section->addTextBreak(2);

        $section->addText("Acepto,", $todo);
        $section->addTextBreak(2);

        $section->addText("FERNANDO ENRIQUE CASTILLO GUARIN ", $negrita, $parrafo0);
        $section->addText("C.C. N°. 13.541.463 de Bucaramanga", $todo, $parrafo0);
        $section->addText("T.P. N°. 147.006 del C.S de la Judicatura", $todo, $parrafo0);
        $section->addText("fernandocastillo@abogadosfc.com", $todo, $parrafo0);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $namepoder = 'Poder demanda ' . $tercero->NRO_IDENTIFICACION . '.docx';
        $objWriter->save($namepoder);

        $phpWord = new PhpWord();
        $language = new Language(Language::ES_ES);
        $phpWord->getSettings()->setThemeFontLang($language);

        $phpWord->addFontStyle($todo,array('name' => 'Tahoma', 'size' => 12, 'color' => '000000'));
        $phpWord->addFontStyle($negrita,array('name' => 'Tahoma', 'size' => 12, 'color' => '000000', 'bold' => true));
        $phpWord->addParagraphStyle($parrafo0, array('alignment' => Jc::BOTH, 'spaceAfter' => 0));
        $phpWord->addParagraphStyle($parrafojus, array('alignment' => Jc::BOTH));
        $phpWord->addParagraphStyle($parrafo0cen, array('alignment' => Jc::CENTER, 'spaceAfter' => 0));

        $tableStyle = array('borderColor' => 'black', 'borderSize'  => 3, 'cellMargin'  => 10, 'alignment' => Jc::CENTER);
        $firstRowStyle = array('bgColor' => 'blue', 'name' => 'Tahoma', 'size' => 11, 'color' => 'white');
        $phpWord->addTableStyle('tbcartera', $tableStyle, $firstRowStyle);

        $stylefirma = array('borderColor' => 'white','borderSize' => 0,'cellMargin' => 10, 'name' => 'Tahoma', 'size' => 11);
        $phpWord->addTableStyle('tbfirma', $stylefirma, null);
        $section = $phpWord->addSection();

        $section->addText("LAS SUSCRITAS REPRESENTANTE LEGAL Y CONTADORA", $negrita, $parrafo0cen);
        $section->addText("DE TAXSUR S.A", $negrita, $parrafo0cen);
        $section->addText("NIT 890.211.768-2", $negrita, $parrafo0cen);
        $section->addTextBreak();

        $section->addText("HACEN CONSTAR QUE:", $negrita, $parrafo0cen);
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("El señor(a) ", $todo);
        $textrun->addText($tercero->PRIMER_NOMBRE . " " . $tercero->SEGUNDO_NOMBRE . " " . $tercero->PRIMER_APELLIDO . " " . $tercero->SEGUNDO_APELLIDO . ", ", $negrita);
        $textrun->addText("identificado con C.C. No. ", $todo);
        $textrun->addText($tercero->NRO_IDENTIFICACION, $negrita);
        $textrun->addText(" propietario del vehículo ", $todo);
        $textrun->addText($tercero->cartera[0]->AFECTA, $negrita);
        $textrun->addText(" debe a TAXSUR S.A., por concepto de administración lo siguiente: ", $todo);
        $section->addTextBreak();

        $tabla = $section->addTable('tbcartera');
        $tabla->addRow();
        $tabla->addCell()->addText("Placa");
        $tabla->addCell()->addText("Factura");
        $tabla->addCell()->addText("Fecha");
        $tabla->addCell()->addText("Vencimiento");
        $tabla->addCell()->addText("Valor");
        $tabla->addCell()->addText("Abono");
        $tabla->addCell()->addText("Saldo");
        $tabla->addCell()->addText("Mora");
        $facturas = 0;
        $saldo = 0;

        foreach ($tercero->cartera as $cartera) {
            $tabla->addRow();
            $tabla->addCell()->addText($cartera->AFECTA, $todo);
            $tabla->addCell()->addText($cartera->FACTURA, $todo);
            $tabla->addCell()->addText($cartera->FECHA, $todo);
            $tabla->addCell()->addText(substr_replace($cartera->FECHA_VENCIMIENTO, '06', 8, 2), $todo);
            $tabla->addCell()->addText(number_format($cartera->VALOR_FACTURA, 0, ',', '.'), $todo);
            $tabla->addCell()->addText(number_format($cartera->ABONO, 0, ',', '.'), $todo);
            $tabla->addCell()->addText(number_format($cartera->SALDO_FACTURA, 0, ',', '.'), $todo);
            $fecha = Carbon::parse($cartera->FECHA)->setDay(6);
            $tabla->addCell()->addText($hoy->diffInDays($fecha), $todo);
            $facturas = $facturas + $cartera->VALOR_FACTURA;
            $saldo = $saldo + $cartera->SALDO_FACTURA;
        }

        $tabla->addRow();
        $tabla->addCell()->addText("");
        $tabla->addCell()->addText("");
        $tabla->addCell()->addText("TOTAL", $negrita);
        $tabla->addCell()->addText("");
        $tabla->addCell()->addText(number_format($facturas, 0, ',', '.'), $todo);
        $tabla->addCell()->addText("");
        $tabla->addCell()->addText(number_format($saldo, 0, ',', '.'), $todo);
        $tabla->addCell()->addText();
        $section->addTextBreak();

        $textrun = $section->addTextRun($parrafojus);
        $textrun->addText("El valor total de la Deuda a la Fecha es de ", $todo);
        $textrun->addText("$" . number_format($saldo, 0, ',', '.') . " (" . $formatter->toWords($saldo, 0) . " pesos m/cte.)", $negrita);
        $section->addTextBreak();

        $section->addText("Esta certificación que presta mérito ejecutivo, se expide el día " . $hoy->day . " (" . $formatter->toWords($hoy->day, 0) . ") de " . $this->getMes($hoy->month) . " de " . $hoy->year . ".", $todo, $parrafojus);
        $section->addTextBreak(2);

        $tablafirma = $section->addTable('tbfirma');
        $tablafirma->addRow();
        $celda = $tablafirma->addCell();
        $celda->addText("MILSE IDARRAGA BERNAL", $negrita, $parrafo0);
        $celda->addText("Representante Legal", $todo, $parrafo0); 
        $celda = $tablafirma->addCell();
        $celda->addText("        MERY YOLANDA VÁSQUEZ VÁSQUEZ", $negrita, $parrafo0);
        $celda->addText("        Contadora", $todo, $parrafo0);
        $celda->addText("        T.P. 263513-T", $todo, $parrafo0);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $namecert = 'Certificación ' . $tercero->NRO_IDENTIFICACION . '.docx';
        $objWriter->save($namecert);

        $namezip = "Archivos Demanda " . $tercero->NRO_IDENTIFICACION . ".zip";

        $zip = new ZipArchive();
        $zip->open($namezip, ZipArchive::CREATE);
        $zip->addFile($namefile);
        $zip->addFile($namepoder);
        $zip->addFile($namecert);
        $zip->addFile($nametabla);
        $zip->close();

        $file = public_path(). '/' . $namezip;
        
        unlink($namefile);
        unlink($namepoder);
        unlink($namecert);
        unlink($nametabla);

        $headers = array('Content-Type: application/octet-stream'); 

        return response()->download($file, $namezip, $headers);

    }

    public function buscarMes($tabla, $mes)
    {
        for ($i=0; $i < count($tabla); $i++) { 
            if($tabla[$i]->mes == $mes){
                return $i;
            }
        }

        return 0;
    }

    public function getMes($numero)
    {
        switch ($numero) {
            case 1:
                $mes = "enero";
                break;
            case 2:
                $mes = "febrero";
                break;
            case 3:
                $mes = "marzo";
                break;
            case 4:
                $mes = "abril";
                break;
            case 5:
                $mes = "mayo";
                break;
            case 6:
                $mes = "junio";
                break;
            case 7:
                $mes = "julio";
                break;
            case 8:
                $mes = "agosto";
                break;
            case 9:
                $mes = "septiembre";
                break;
            case 10:
                $mes = "octubre";
                break;
            case 11:
                $mes = "noviembre";
                break;
            case 12:
                $mes = "diciembre";
                break;
        }

        return $mes;
    }

    public function listarAcuerdos()
    {
        $acuerdos = Acuerdo::with(['propietario.tercero'])->orderBy('vencidas', 'desc')->paginate('20');
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.lista', compact('acuerdos', 'usuario'));
    }

    public function nuevoAcuerdo()
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.form', compact('usuario'));
    }

    public function buscarPropietario($identificacion)
    {
        $propietarios = Tercero::select('TERCERO', 'NRO_IDENTIFICACION', 'RAZON_SOCIAL')->whereHas('propietario', function($q){$q->has('vehiculos')->orHas('vehiculospri');})->where('NRO_IDENTIFICACION', 'like', $identificacion . '%')->get();
        if(count($propietarios) > 0){
            foreach ($propietarios as $propietario) {
                $tercero = $propietario->TERCERO;
                $propietario->placas = Vehiculo::select('VEHICULO', 'PLACA')->whereHas('propietario',  function($q) use($tercero){$q->where('propietario.TERCERO', $tercero);})->orWhereHas('propietarios',  function($q) use($tercero){$q->where('propietario.TERCERO', $tercero);})->get();
            }           
        }
        return json_encode($propietarios);
    }

    public function registrarAcuerdo(Request $request)
    {
        $tercero = Tercero::select('TERCERO', 'RAZON_SOCIAL', 'NRO_IDENTIFICACION', 'DIRECCION', 'EMAIL')->find($request->input('tercero'));
        $fecha = Carbon::parse($request->input('fecha'));
        $cartera = Cartera::where('TERCERO', $tercero->TERCERO)->where(function($q){$q->where('CUENTA', '13050505')->orWhere('CUENTA', '13050503');})->where('AFECTA', $request->input('placa'))->get();
        $acuerdo = new Acuerdo();
        $acuerdo->fecha = $fecha;
        $acuerdo->placa = $request->input('placa');
        $acuerdo->deuda = $request->input('deuda');
        $acuerdo->inicial = $request->input('inicial');
        $acuerdo->cuotas = $request->input('cuotas');
        $acuerdo->celular = $request->input('celular');
        $acuerdo->saldo = $acuerdo->deuda - $acuerdo->inicial;
        $acuerdo->pago_mensual = $acuerdo->saldo /$acuerdo->cuotas;
        $acuerdo->estado = "Vigente";
        $acuerdo->propietario_tercero = $request->input('tercero');
        $acuerdo->save();

        for ($i=0; $i < $acuerdo->cuotas; $i++) { 
            $fecha->addMonth();
            $cuota = new Cuota();
            $cuota->numero = $i+1;
            $cuota->estado = "Vigente";
            $cuota->fecha_vencimiento = $fecha;
            $cuota->acuerdos_id = $acuerdo->id;
            $cuota->save();
        }

        $phpWord = new PhpWord();
        $language = new Language(Language::ES_ES);
        $formatter = new NumeroALetras();
        $fecha = Carbon::parse($request->input('fecha'));
        $phpWord->getSettings()->setThemeFontLang($language);
        $fnormal = 'fnormal';
        $phpWord->addFontStyle($fnormal, array('name' => 'Calibri', 'size' => 11, 'color' => '000000'));
        $negrita = 'negrita';
        $phpWord->addFontStyle($negrita, array('name' => 'Calibri', 'size' => 11, 'color' => '000000', 'bold' => true));
        $parfcen = 'parfcen';
        $phpWord->addParagraphStyle($parfcen, array('alignment' => Jc::CENTER, 'spaceAfter' => 0));
        $parfjus = 'parfjus';
        $phpWord->addParagraphStyle($parfjus, array('alignment' => Jc::BOTH));
        $sangria = 'sangria';
        $phpWord->addParagraphStyle($sangria, array('indentation' => array('left' => 540, 'right' => 120)));

        $section = $phpWord->addSection();
        $section->addText("ACUERDO DE TRANSACCIÓN", $negrita, $parfcen);
        $section->addTextBreak();

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText("Entre los suscritos, por una parte, ", $fnormal);
        $txtrun->addText("MILSE IDARRAGA BERNAL, ", $negrita);
        $txtrun->addText("identificada con la cédula de ciudadanía No. 37.827.097 expedida en Bucaramanga quien obra en nombre y representación de la empresa TAXIS DEL SUR, TAXSUR S.A., Nit. 890.211.768-2 quien en adelante se denomina la EMPRESA y ", $fnormal);
        $txtrun->addText($tercero->RAZON_SOCIAL . " identificada con cédula de ciudadanía No. " . number_format($tercero->NRO_IDENTIFICACION, 0, ",", ".") . " en calidad de propietario del vehículo de placas ", $fnormal);
        $txtrun->addText($acuerdo->placa . ", quien se denomina EL AFILIADO, hemos convenido suscribir el presente acuerdo de pago, previas las siguientes: ", $fnormal);
 
        $section->addText("CONSIDERACIONES: ", $negrita, $parfcen);
        $section->addTextBreak();

        $txtrun = $section->addTextRun($sangria);
        $txtrun->addText("1. Entre las partes, EL AFILIADO se encuentra adeudando un valor total de ", $fnormal);
        $txtrun->addText($formatter->toWords($acuerdo->deuda, 0) . " PESOS MCTE. (" . number_format($acuerdo->deuda, 0, ",", ".") . ")", $negrita);
        $txtrun->addText(" por conceptos de administraciones ", $fnormal);
        $ncart = count($cartera);
        if($ncart > 0){
            $inicio = Carbon::parse($cartera[0]->FECHA);
            $final = Carbon::parse($cartera[$ncart-1]->FECHA);
            $txtrun->addText($this->getMes($inicio->month) . " " . $inicio->year . " a " . $this->getMes($final->month) . " " . $final->year . ".", $fnormal);
        }else{
            $txtrun->addText('("Colocar rango de fechas de la deuda").');
        }

        $section->addText("ACUERDO Y TRANSACCIÓN: ", $negrita, $parfcen);
        $section->addTextBreak();

        $section->addText("Con el fin de zanjar diferencias y evitar procesos jurídicos que incrementarían el valor, las partes llegan al presente acuerdo de pago.", $fnormal, $parfjus);

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText('PRIMERA:', $negrita);
        $txtrun->addText(" las partes acuerdan fijar el valor total de ", $fnormal);
        $txtrun->addText($formatter->toWords($acuerdo->deuda, 0) . " PESOS MCTE. ($" . number_format($acuerdo->deuda, 0, ",", ".") . "),", $negrita);
        $txtrun->addText(" los que serán pagados de la siguiente manera: ", $fnormal);

        if($acuerdo->inicial > 0){
            $txtrun = $section->addTextRun($parfjus);
            $txtrun->addText("Un primer pago el día " . $fecha->day . " de " . $this->getMes($fecha->month) . " de " . $fecha->year . " por valor de ", $fnormal);
            $txtrun->addText($formatter->toWords($acuerdo->inicial, 0) . " PESOS MCTE. ($" . number_format($acuerdo->inicial, 0, ",", ".") . ").", $negrita);   
        }

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText("El saldo ", $fnormal);
        $txtrun->addText($formatter->toWords($acuerdo->deuda-$acuerdo->inicial, 0) . " PESOS MCTE. ($" . number_format($acuerdo->deuda-$acuerdo->inicial, 0, ",", ".") . ")", $negrita);
        $txtrun->addText(" se pagará en " . $formatter->toWords($acuerdo->cuotas), $fnormal);
        $txtrun->addText(" (" . $acuerdo->cuotas . ")", $negrita);
        $txtrun->addText(" cuotas iguales mensuales consecutivas así: ", $fnormal);
        
        if($acuerdo->inicial > 0){
            $i = 2;
            $limite = $acuerdo->cuotas + 2;
        }else{
            $i = 1;
            $limite = $acuerdo->cuotas + 1;
        }

        $pagoletra = $formatter->toWords($acuerdo->pago_mensual);
        $pagoformat = number_format($acuerdo->pago_mensual, 0, ",", ".");
        for ($i; $i < $limite; $i++) {
            $fecha->addMonth();
            $txtrun = $section->addTextRun($parfjus);
            $txtrun->addText("Un " . $this->numeroToOrdinario($i) . " pago el ", $fnormal);
            $txtrun->addText($fecha->day . " de " . $this->getMes($fecha->month) . " de " . $fecha->year, $negrita);
            $txtrun->addText(" por valor de " . $pagoletra . " Pesos Mcte ($" . $pagoformat . ")", $fnormal);
        }

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText("Parágrafo: ", $negrita);
        $txtrun->addText("El afiliado asume el compromiso de mantenerse al día con el pago oportuno de las nuevas administraciones que se causen a partir de la fecha.", $fnormal);

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText("SEGUNDA: ", $negrita);
        $txtrun->addText("En caso de darse cualquier incumplimiento en el valor de pago acordado; se declara la finalización del plazo y en consecuencia el afiliado se obliga a pagar de inmediato el valor total adeudado a la fecha del incumplimiento.", $fnormal);

        $txtrun = $section->addTextRun($parfjus);
        $txtrun->addText("TERCERA: ", $negrita);
        $txtrun->addText("El afiliado de manera libre y voluntaria autoriza de manera expresa e irrevocable a la empresa para consultar, solicitar, suministrar, reportar, procesar y divulgar, toda la información que se refiera a su comportamiento, crediticio, financiero, comercial a la central de información ", $fnormal);
        $txtrun->addText("CIFIN S.A ", $negrita);
        $txtrun->addText("o a quien represente sus derechos en consecuencia quienes se encuentren afiliados y/o tengan acceso a la ", $fnormal);
        $txtrun->addText("CIFIN S.A.", $negrita);
        $txtrun->addText(" Podrán conocer esta información de conformidad con la legislación aplicable. ", $fnormal);
        $txtrun->addText("PARAGRAFO ", $negrita);
        $txtrun->addText("en caso de que la empresa realice a favor de un tercero la venta o cesión a cualquier título de las obligaciones del afiliado los efectos enunciados en esta cláusula se extenderán a este en los mismos términos y condiciones.", $fnormal);

        $fecha = Carbon::parse($request->input('fecha'));
        $section->addText("En señal de aceptación firmamos el día " . $fecha->day . " de " . $this->getMes($fecha->month) . " de " . $fecha->year . ".", $fnormal, $parfjus);
        $section->addTextBreak(3);

        $stylefirma = array('borderColor' => 'white','borderSize' => 0,'cellMargin' => 10, 'name' => 'Calibri', 'size' => 9.5);
        $phpWord->addTableStyle('tbfirma', $stylefirma, null);
        $parfirma = 'parfirma';
        $phpWord->addParagraphStyle($parfirma, array('alignment' => Jc::BOTH, 'spaceAfter' => 0));

        $tablafirma = $section->addTable('tbfirma');
        $tablafirma->addRow();
        $celda = $tablafirma->addCell();
        $celda->addText("LA EMPRESA", $fnormal, $parfirma);
        $celda = $tablafirma->addCell();
        $celda->addText("                                EL AFILIADO", $fnormal, $parfirma);
        $tablafirma->addRow();
        $celda = $tablafirma->addCell();
        $celda = $tablafirma->addCell();
        $tablafirma->addRow();
        $celda = $tablafirma->addCell();
        $celda = $tablafirma->addCell();
        $tablafirma->addRow();
        $celda = $tablafirma->addCell();
        $celda->addText("MILSE IDARRAGA BERNAL", $negrita, $parfirma);
        $celda->addText("CC. 37.827.097 de Bucaramanga", $negrita, $parfirma);
        $celda->addText("TAXSUR S.A.", $negrita, $parfirma);
        $celda->addText("Nit. 890.211.768-2", $negrita, $parfirma);

        $celda = $tablafirma->addCell();
        $celda->addText("                                " . $tercero->RAZON_SOCIAL, $negrita, $parfirma);
        $celda->addText("                                CC. " . $tercero->NRO_IDENTIFICACION, $negrita, $parfirma);
        $txtrun = $celda->addTextRun($parfirma);
        $txtrun->addText("                                Dirección: ", $negrita);
        $txtrun->addText($tercero->DIRECCION, $fnormal);
        $txtrun = $celda->addTextRun($parfirma);
        $txtrun->addText("                                Teléfono: ", $negrita);
        $txtrun->addText($acuerdo->celular, $fnormal);
        $txtrun = $celda->addTextRun($parfirma);
        $txtrun->addText("                                Correo: ", $negrita);
        $txtrun->addText($tercero->EMAIL, $fnormal);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $namefile = 'Acuerdo ' . $tercero->NRO_IDENTIFICACION . '.docx';
        $objWriter->save($namefile);

        $archivo = file_get_contents('Acuerdo ' . $tercero->NRO_IDENTIFICACION . '.docx');
        unlink('Acuerdo ' . $tercero->NRO_IDENTIFICACION . '.docx');

        return base64_encode($archivo);


        //return redirect('/acuerdos/filtrar?propietario=' . $request->input('propietario'));
    }

    public function cuotasPorAcuerdo($acuerdo)
    {
        $acuerdo = Acuerdo::with('cuotasAll')->find($acuerdo);
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.cuotas', compact('acuerdo', 'usuario'));
    }

    public function registrarPago(Request $request)
    {
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        if($request->filled('identificacion')){
            $tercero = Tercero::where('NRO_IDENTIFICACION', $request->input('identificacion'))->first();
            if($tercero != null){
                $ter = $tercero->TERCERO;
                $acuerdos = Acuerdo::with(["cuotasAll"=>function($q){$q->where('estado', '!=', 'Pagada');}])->whereHas('propietario', function($q) use($ter){$q->where('TERCERO', $ter);})->where('estado', '!=', 'Pagado')->get();
                $busq = 1;
            }else{
                return back()->withErrors(["sql" => "La identificación ingresada no corresponde a ningun propietario"]);
            }

            return view('acuerdos.pagar', compact('acuerdos', 'tercero', 'busq', 'usuario'));
            
        }elseif($request->filled('placa')){
            $acuerdos = Acuerdo::with(["cuotasAll"=>function($q){$q->where('estado', '!=', 'Pagada');}])->where('placa', $request->input('placa'))->where('estado', '!=', 'Pagado')->get();
            if(count($acuerdos) > 0){
                $tercero = Tercero::find($acuerdos[0]->propietario_tercero);
                $busq = 1;
            }else{
                return back()->withErrors(["sql" => "No se encontraron acuerdos de pago para la placa ingresada"]);
            }

            return view('acuerdos.pagar', compact('acuerdos', 'tercero', 'busq', 'usuario'));
        }else{
            return view('acuerdos.pagar', compact('usuario'));
        }
    }

    public function pagarCuota(Request $request)
    {
        $respuesta = new stdClass();
        try {
            $cuota = Cuota::with('acuerdo')->find($request->input('cuota'));
        if($cuota != null){
            if($cuota->estado == "Vencida"){
                $cuota->acuerdo->vencidas = $cuota->acuerdo->vencidas - 1;
            }
            $cuota->estado = "Pagada";
            $cuota->fecha_pago = Carbon::now();
            $cuota->save();

            $cuota->acuerdo->pagadas = $cuota->acuerdo->pagadas + 1;
            $cuota->acuerdo->saldo = $cuota->acuerdo->saldo - $cuota->acuerdo->pago_mensual;
            if($cuota->acuerdo->pagadas == $cuota->acuerdo->cuotas){
                $cuota->acuerdo->estado = "Pagado";
                $cuota->acuerdo->saldo = 0;
            }
            $cuota->acuerdo->save();
            $respuesta->estado = "success";
            $respuesta->mensaje = "La cuota ha sido pagada exitosamente";
            $respuesta->direccion = $request->root() . "/acuerdos/registrar_pago?identificacion=" . $request->input('idpropietario');
        }else{
            $respuesta->estado = "error";
            $respuesta->mensaje = "Cuota no encontrada";
        }
        } catch (Exception $ex) {
            $respuesta->estado = "error";
            $respuesta->mensaje = $ex->getMessage();
        }
        
        return json_encode($respuesta);
    }

    public function filtrarAcuerdos(Request $request)
    {
        $propietario = $request->input('propietario');
        $estado = $request->input('estado');
        if(!empty($propietario) && !empty($estado)){
            $acuerdos = Acuerdo::whereHas('propietario', function($q) use($propietario){$q->whereHas('tercero', function($r) use($propietario){$r->where('NRO_IDENTIFICACION', $propietario);});})->where('estado', $estado)->paginate(20)->appends($request->query());
        }elseif(!empty($propietario)){
            $acuerdos = Acuerdo::whereHas('propietario', function($q) use($propietario){$q->whereHas('tercero', function($r) use($propietario){$r->where('NRO_IDENTIFICACION', $propietario);});})->paginate(20)->appends($request->query());
        }elseif(!empty($estado)){
            $acuerdos = Acuerdo::where('estado', $estado)->paginate(20)->appends($request->query());
        }else{
            return redirect('/acuerdos/listar');
        }
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('acuerdos.lista', compact('acuerdos', 'usuario', 'propietario', 'estado'));
    }

    public function iniciarProceso(Acuerdo $acuerdo)
    {
        $acuerdo->estado = "Proceso";
        $acuerdo->fecha_proceso = Carbon::now();
        $acuerdo->save();

        return redirect('/acuerdos/listar');
    }

    public function numeroToOrdinario($numero, $resp = "")
    {
        if($numero == 1){
            if(empty($resp)){
                $resp = "primer";
            }else{
                $resp = $resp . "primero";
            }      
        }elseif($numero == 2){
            $resp = $resp . "segundo";
        }elseif($numero == 3){
            if(empty($resp)){
                $resp = "tercer";
            }else{
                $resp = $resp . "tercero";
            }
        }elseif($numero == 4){
            $resp = $resp . "cuarto";
        }elseif($numero == 5){
            $resp = $resp . "quinto";
        }elseif($numero == 6){
            $resp = $resp . "sexto";
        }elseif($numero == 7){
            $resp = $resp . "séptimo";
        }elseif($numero == 8){
            $resp = $resp . "octavo";
        }elseif($numero == 9){
            $resp = $resp . "noveno";
        }elseif($numero > 9 && $numero < 20){
            $resp = "Décimo " . $this->numeroToOrdinario($numero%10, $resp); 
        }elseif($numero > 20){
            $resp = "Vigésimo " . $this->numeroToOrdinario($numero%20, $resp);
        }
        
        return $resp;
    }

    public function carteraDesdeArchivo()
    {
        $excel = PhpSpreadsheetIOFactory::load(storage_path('facturas.xlsx'));
        $hoja = $excel->setActiveSheetIndex(0);
        $numRows = $hoja->getHighestRow();

        $deuda = 0;
        $intereses = 0;
        $hoy = Carbon::now();
        $tasas = Interes::get();
        $tabla = [];

        for ($i=3; $i < $numRows; $i++) { 
            try {
                $vencimiento = $hoja->getCell('F'.$i)->getFormattedValue();
                if(!empty($vencimiento)){
                    $fecha = Carbon::parse($vencimiento);
                    $fila = new stdClass();
                    $fila->mes = strtoupper($this->getMes($fecha->month)) . " DE " . $fecha->year;
                    $fila->capital = $hoja->getCell('G'.$i)->getCalculatedValue();
                    $fila->concepto = $hoja->getCell('D'.$i)->getValue();
                    $meses = $hoy->diffInMonths($fecha) + 1;
                    $inte = 0;
                    for ($j=0; $j < $meses ; $j++) {
                        $interes = null;
                        foreach ($tasas as $tasa) {
                            if($tasa->year == $fecha->year && $tasa->mes == $fecha->month){
                                $interes = $tasa;
                                break;
                            }
                        }
                        if($interes != null){
                            $intmes = ($fila->capital * $interes->tasa) / 100;
                            $inte = $inte + $intmes;
                            $intereses = $intereses + $intmes; 
                        } 
                        $fecha->addMonth();            
                    }
                    $fila->interes = $inte;
                    $tabla[] = $fila;
                    $deuda = $deuda + $fila->capital;
                }
                
            } catch (InvalidFormatException $ex) {
                
            }
        }

        $dompdf = PDF::loadView('cartera.modeloIntereses', ['cuotas'=>$tabla, 'placa'=> 'BUY-687', 'propietario'=> 'YEIMY ORTEGA DURAN']);
        $dompdf->setPaper('Legal', 'Portrait');
        $nametabla = "Tabla de intereses 1094779758.pdf"; 
        $dompdf->save(storage_path($nametabla));

        return response()->download(storage_path($nametabla), $nametabla, ["Content-Type" => "application/pdf"]);

    }
}
