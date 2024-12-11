<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Carbon\Carbon;
use App\Models\Servicio;
use App\Models\Cuentac;
use App\Models\Tercero;
use App\Models\Vale;
use App\Models\Agencia_tercero;
use App\Models\Beneficiario;
use App\Models\CalificacionVeh;
use App\Models\Cancelacion;
use App\Models\Vale_servicio;
use App\Models\Vehiculo;
use App\Models\Version;
use Exception;
use Illuminate\Support\Facades\Mail;
use stdClass;

class ClienteController extends Controller
{
    public function registrar(Request $request){

        $cliente = Cliente::where('email', $request->input('email'))->first();
        if($cliente == null){
            $cliente = new Cliente();
        }

        $cliente->nombres = $request->input('nombres');
        $cliente->email = $request->input('email');
        $cliente->telefono = $request->input('telefono');
        $cliente->password = $request->input('password');
        $cliente->save();

    }

    public function validarBeneficiario(Request $request)
    {
        $email = $request->input('email');
        $cliente = Cliente::where('email', $email)->first();
        if($cliente == null){
            $beneficiario = Beneficiario::where('email', $email)->first();
            if($beneficiario != null){
                $codigo = "";
                for ($i=0; $i < 6; $i++) { 
                    $codigo = $codigo . rand(0, 9);
                }

                try{
                    Mail::send("alertas.codigoEmail", compact('codigo', 'email'), function ($message) use($email)
                    {
                        $message->from("EMAILNotify", "JADMIN");
                        $message->to($email);
                        $message->subject("Código de verificación");
                    });
                }catch(Exception $ex){
                    file_put_contents("falla.log", $ex->getMessage());
                    return "falla";
                }
                return $codigo;
            }else{
                return "registro";
            }
        }else{
            return "registrado";
        }
    }

    public function login(Request $request){

        $cliente = Cliente::where('email', $request->input('email'))->where('estado', 1)->first();

        if ($cliente != null) {
            if ($request->filled('plataforma')) {
                $version = Version::find($request->input('plataforma'));
                $cliente->version = $version->numero;
            }
            
            return json_encode($cliente);
        }else{
            return json_encode([]);
        }  
    }

    public function servicio(Request $request){

        //$cuentac = Cuentac::select('id', 'estado', 'placa')->find($request->input('idtaxista'));
            $servicio = new Servicio();
            $servicio->fecha = Carbon::now('-05:00');
            $servicio->estado = "Pendiente";
            $servicio->direccion = $request->input('direccion');
            $servicio->adicional = $request->input('adicional');
            $servicio->pago = $request->input('pago');
            $servicio->latitud = str_replace(",", ".", $request->input('latitud'));
            $servicio->longitud = str_replace(",", ".", $request->input('longitud'));
            $servicio->usuarios = $request->input('usuarios');
            $servicio->asignacion = "Normal";
            $servicio->clientes_id = $request->input('cliente');
            //$servicio->cuentasc_id = $request->input('idtaxista');
            $servicio->save();
    
            if($servicio->pago == "Vale electrónico"){
                $vale = Vale::find($request->input('idvale'));
                $valeserv = Vale_servicio::where('servicios_id', $servicio->id)->where('vales_id', $vale->id)->first();
                if($valeserv == null){
                    $valeserv = new Vale_servicio();
                    $valeserv->vales_id = $vale->id;
                    $valeserv->servicios_id = $servicio->id;
                    $valeserv->save();
                }
                $vale->estado = "Visado";
                $limite = 0;
                $vale->servicios_id = null;
                do {  
                    sleep(1);                  
                    $vale->servicios_id = $servicio->id;
                    $limite++;
                } while ($vale->servicios_id == null || $limite == 10);
                
                if($vale->servicios_id == null){
                    $servicio->estado = "No vehiculo";
                    $servicio->save();
    
                    abort(500);
                }else{
                    $vale->save();
                }                 
            } 
            return $servicio->id;   
    }

    function distancia($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 3) 
    {
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
     
        switch($unit) {
            case 'km':
                $distance = $degrees * 111.13384; // 1 grado = 111.13384 km, basándose en el diametro promedio de la Tierra (12.735 km)
                break;
            case 'mi':
                $distance = $degrees * 69.05482; // 1 grado = 69.05482 millas, basándose en el diametro promedio de la Tierra (7.913,1 millas)
                break;
            case 'nmi':
                $distance =  $degrees * 59.97662; // 1 grado = 59.97662 millas naúticas, basándose en el diametro promedio de la Tierra (6,876.3 millas naúticas)
        }
        return round($distance, $decimals);
    }

    public function majorelKm()
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $servicios = Servicio::with(['seguimientos'=>function($q){$q->select('id', 'latitud', 'longitud', 'servicios_id');}])->select('id', 'fecha')->whereBetween('fecha', ['2023-01-01 00:00', '2023-12-31 23:59:59'])->whereHas('vale', function($q){$q->where('valeras_id', 101);})->get();
        $kms = 0;
        foreach ($servicios as  $servicio) {
            $cant = count($servicio->seguimientos);
            if ($cant > 3) {
                $primero = $servicio->seguimientos[0];
                $mitad = $servicio->seguimientos[$cant/2];
                $ultimo = $servicio->seguimientos[$cant-1];

                $a = $this->distancia($primero->latitud, $primero->longitud, $mitad->latitud, $mitad->longitud);
                $b = $this->distancia($mitad->latitud, $mitad->longitud, $ultimo->latitud, $ultimo->longitud);

                if(!is_nan($a)){
                    $kms = $kms + $a;
                }
                if(!is_nan($b)){
                    $kms = $kms + $b;
                }
            }
        }

        return 'kms: ' . $kms . ', servicios: ' . count($servicios);
    }

    public function getinfocliente(Request $request){

        if ($request->filled('empresa')) {
            $empresa = Tercero::select('TERCERO', 'RAZON_SOCIAL', 'EMAIL', 'TELEFONO')->where('TERCERO', $request->input('empresa'))->first();
        }else{
            $empresa = Agencia_tercero::select('TERCERO', 'CODIGO', 'NOMBRE', 'EMAIL', 'TELEFONO')->where('NRO_IDENTIFICACION', $request->input('agencia'))->first();
        }

        return json_encode($empresa);     
    }

    public function verificarvale(Request $request)
    {
        $agencia = Agencia_tercero::with('cuentae')->where('NRO_IDENTIFICACION', $request->input('nit'))->where('SW_ACTIVO', 1)->first();
        if($agencia != null){
            if($agencia->cuentae != null){
                $idcuentae = $agencia->cuentae->id;
                $vale = Vale::where('codigo', $request->input('codigo'))->where(function($q){$q->where('estado', 'Libre')->orWhere('estado', 'Asignado');})->whereHas('valera', function($q) use($idcuentae){$q->where('cuentase_id', $idcuentae);})->first();
                if($vale != null){
                    return $vale->id;
                }else{
                    return "0";
                }
            }else{
                return "0";
            }
        }else{
            return "0";
        }
    }

    public function revisar(Request $request)
    {
        $servicio = Servicio::select('id', 'placa', 'estado', 'valor', 'pago', 'cuentasc_id')->with('vale')->find($request->input('idservicio'));
        if($servicio->valor == null){
            $servicio->valor = 0;
        }
        if($servicio->estado == "Asignado" || $servicio->estado == "En curso"){
            $cuentac = Cuentac::with(['conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE', 'CELULAR');}])->select('id', 'foto', 'latitud', 'longitud', 'conductor_CONDUCTOR')->find($servicio->cuentasc_id);
            $cuentac->placa = $servicio->placa;
            $cuentac->nombre = $cuentac->conductor->NOMBRE;
            $cuentac->servicio = $servicio->id;
            $cuentac->estadoserv = $servicio->estado;
            $cuentac->celular = $cuentac->conductor->CELULAR;            
            $cuentac->valor = $servicio->valor;
            $cuentac->pago = $servicio->pago;
            if($servicio->vale != null){
                $cuentac->vale = $servicio->vale->clave;
            }

            return json_encode($cuentac);

        }else{
            $cuenta = new stdClass();
            $cuenta->estadoserv = $servicio->estado;
            $cuenta->placa = $servicio->placa;
            $cuenta->id = $servicio->cuentasc_id;
            $cuenta->valor = $servicio->valor;
            $cuenta->pago = $servicio->pago;
            $cuenta->vale = 0;

            return json_encode($cuenta);
        }      
    }

    public function seguirtaxi(Request $request)
    {
        $servicio = Servicio::with('registros')->select('id', 'pago', 'valor', 'estado')->find($request->input('idservicio'));
        $cuentac = Cuentac::select('id', 'latitud', 'longitud', 'placa')->find($request->input('idtaxista'));
        foreach ($servicio->registros as $registro) {
            if($registro->evento == "Arribo"){
                $cuentac->arribo = "Arribo";
                break;
            }
        }
        if($servicio->estado == "Finalizado"){
            $cuentac->pago = $servicio->pago;
            if ($servicio->pago == "Vale electrónico") {
                $cuentac->valor = $servicio->valor;
            }         
        }
        $cuentac->estadoserv = $servicio->estado;

        return json_encode($cuentac); 
    }

    public function cancelar(Request $request)
    {
        $servicio = Servicio::with('vale')->select('id', 'fecha', 'estado')->find($request->input('idservicio'));
        if($servicio->estado != "Finalizado"){
            $servicio->estado = "Cancelado";
            $servicio->save();
    
            if ($servicio->vale != null){
                if($servicio->vale->centrocosto != null){
                    $servicio->vale->estado = "Asignado";
                }else{
                    $servicio->vale->estado = "Libre";
                }
                $servicio->vale->servicios_id = null;
                $servicio->vale->save();
            }
    
            $cancelacion = new Cancelacion();
            $cancelacion->razon = "Cancelado por el usuario";
            $cancelacion->fecha = Carbon::now('-05:00');
            $cancelacion->servicios_id = $servicio->id;
            $cancelacion->save();
        }
        
        return "OK";
    }

    public function calificar(Request $request)
    {
        $calificacion = new Calificacion();
        $calificacion->puntaje = $request->input('calc');
        $calificacion->cuentasc_id = $request->input('idtaxista');
        $calificacion->save();

        $servicio = Servicio::find($request->input('servicio'));
        if($servicio != null){
            $servicio->calificacion = $request->input('cals');
            $servicio->comentarios = $request->input('comentarios');
            $servicio->save();
        }

        $vehiculo = Vehiculo::where('placa', $request->input('placa'))->first();
        if($vehiculo != null){
            $calv = new CalificacionVeh();
            $calv->vehiculo_VEHICULO = $vehiculo->VEHICULO;
            $calv->puntaje = $request->input('calv');
            $calv->save();
        }

        return "OK";
    }

    public function historial(Request $request)
    {
        $servicios = Servicio::select('id', 'direccion', 'placa', 'pago', 'valor', 'estado', 'clientes_id')->where("clientes_id", $request->input('idcliente'))->orderBy('id', 'DESC')->get()->take(50);

        foreach ($servicios as $servicio) {
            $servicio->direccion = $servicio->direccion . " - " . $servicio->estado;
            $servicio->pago = $servicio->pago . ": $" . number_format($servicio->valor); 
        }

        return json_encode($servicios);
    }

    public function informacion(Request $request)
    {
        $cliente = Cliente::find($request->input('idcliente'));

        return json_encode($cliente);
    }

    public function updatecuenta(Request $request)
    {
        $cliente = Cliente::find($request->input('idcliente'));

        if($request->filled('nombres')){
            $cliente->nombres = $request->input('nombres');
        }
        if($request->filled('telefono')){
            $cliente->telefono = $request->input('telefono');
        }
        if ($request->filled('clave')) {
            $cliente->password = $request->input('clave');
        }

        $cliente->save();

        return "OK";
    }

    public function liberarVale(Request $request)
    {
        $vale = Vale::find($request->input('idvale'));
        if($vale->estado != "Usado"){
            $vale->estado = "Libre";
            $vale->servicios_id = null;
            $vale->save();
        }
        
        $servicio = Servicio::find($request->input('idservicio'));
        if($servicio->estado == "Pendiente"){
            $servicio->estado = "No vehiculo";
            $servicio->save();
        }       

        return "OK";
    }
    
    public function noVehiculo(Request $request)
    {
        $servicio = Servicio::find($request->input('idservicio'));
        if($servicio->estado == "Pendiente"){
            $servicio->estado = "No vehiculo";
            $servicio->save();
        }
        
        return "OK";
    }
    
    public function restablecer(Request $request)
    {
         $cliente = Cliente::where('email', $request->input('email'))->first();
         
         if($cliente != null){
             
            $from = "notificaciones@apptaxcenter.com";
            $to = $cliente->email;
            $subject = "Restablecer contraseña";
            $mensaje = '<html><p>Se ha recibido una petición de restablecimiento de contraseña, para continuar con el proceso dirigirse al siguiente enlace </p> <br>' .
                      '<a href="https://crm.apptaxcenter.com/clientes/' . $cliente->email . '/' . md5($cliente->id) . '/restablecer"> Enlace de restablecimiento </a></html>'; 
            $headers = "From:" . $from;
            
            Mail::send([], [], function ($message) use($to, $subject, $mensaje, $from){
                $message->from($from, "JADMIN");
                $message->to($to);
                $message->subject($subject);
                $message->setBody($mensaje, 'text/html');
            });
             
            return "OK";
         }else{
             
             return "no user";
         }
    }
    
    public function cambiarClave($email, $sum){
        
        $cliente = Cliente::where('email', $email)->first();
        
        if(md5($cliente->id) == $sum){
            
            return view('users.newpass', compact('cliente'));
        }else{
            abort(404, "Link inválido");
        }
    }
    
    public function newPass(Request $request){
        
        $cliente = Cliente::find($request->input('clienteid'));
        if($request->input('password') == $request->input('password-confirm')){
            $cliente->password = $request->input('password');
            $cliente->save();
            return back()->withErrors(['sql' => 'Las contraseña fue restablecida']);
        }else{
            return back()->withErrors(['sql' => 'Las contraseñas no coinciden']);
        }
    }

    public function vehiculosCercanos(Request $request)
    {
        $cuentas = Cuentac::select('id', 'latitud', 'longitud', 'foto', 'placa', 'conductor_CONDUCTOR')->with(['calificaciones', 'conductor'=>function($q){$q->select('CONDUCTOR', 'NOMBRE');}])->where('estado', 'Libre')->where('saldo', '>', '600')->get();
        $cercanos = [];

        foreach ($cuentas as $cuenta) {
            $diferencia = $this->distancia($request->input('latitud'), $request->input('longitud'), $cuenta->latitud, $cuenta->longitud);
            if($diferencia < $request->input('distancia')){
                $vehiculo = Vehiculo::select('VEHICULO', 'PLACA', 'MODELO')->where('placa', $cuenta->placa)->with('calificaciones')->first();
                if(count($cuenta->calificaciones) == 0){
                    $calc = 5;
                }else{
                    $calc = $cuenta->calificaciones->avg('puntaje');
                }
                $cuenta->nombre = $cuenta->conductor->NOMBRE . " (" . $calc . "/5)";
                if(count($vehiculo->calificaciones) == 0){
                    $calv = 5;
                }else{
                    $calv = $vehiculo->calificaciones->avg('puntaje');
                }
                $cuenta->placa = $vehiculo->PLACA . ", " . $diferencia . " Km, Mod:" . $vehiculo->MODELO . " (" . $calv . "/5)";
                $cuenta->diferencia = $diferencia;
                $cercanos[] = $cuenta;
            }
        }
        
        return json_encode($cercanos);
    }

    public function getAgencias(Request $request)
    {
        $agencias = Agencia_tercero::select('TERCERO', 'CODIGO', 'NOMBRE', 'NRO_IDENTIFICACION', 'DESCRIPCION')->where('SW_ACTIVO', '1')->where('NOMBRE', 'like', '%' . $request->input('empresa') . '%')->get()->toArray();

        return json_encode($agencias);
    }

    public function taxisLibres()
    {
        $taxis = Cuentac::select('id', 'estado', 'latitud', 'longitud')->where('estado', 'Libre')->whereNotNull('latitud')->whereNotNull('longitud')->get();

        return json_encode($taxis);
    }

    public function servicioEnCurso(Request $request)
    {
        $servicio = Servicio::select('id', 'clientes_id')->where('clientes_id', $request->input('cliente'))->where(function($q){$q->where('estado', 'Asignado')->orWhere('estado', 'En curso');})->latest('id')->first();
    
        if($servicio != null){
            return $servicio->id;
        }else{
            return -1;
        }
    }

    public function consultarValera(Request $request)
    {
        $respuesta = "NO";
        $beneficiario = Beneficiario::with('valera.cuentae.agencia')->has('valera')->where('email', $request->input('email'))->first();
        if($beneficiario != null){
             if($beneficiario->valera->estado == 1){
                 $vale = Vale::where('valeras_id', $beneficiario->valera->id)->where('estado', 'Libre')->first();
                 if($vale != null){
                    $respuesta =  $beneficiario->valera->cuentae->agencia->NRO_IDENTIFICACION . ";" . $vale->codigo;
                 }  
             }
        }

        return $respuesta;
    }

    public function desactivarCuenta(Request $request) {
        $cliente = Cliente::find($request->input('idcliente'));
        $cliente->estado = 0;
        $cliente->save();

        return "Eliminado";
    }
}

/*$epayco = new Epayco\Epayco(array(
    "apiKey" => "4c73c09d6e562a7fa7686631b955c725",
    "privateKey" => "0878cd1c2b1a2a728f720e39a11aa351",
    "lenguage" => "ES",
    "test" => true
));

$token = $epayco->token->create(array(
    "card[number]" => '4575623182290326',
    "card[exp_year]" => "2025",
    "card[exp_month]" => "12",
    "card[cvc]" => "123"
));

$customer = $epayco->customer->create(array(
    "token_card" => $token->id,
    "name" => "Joe",
    "last_name" => "Doe",
    "email" => "joe@payco.co",
    "default" => false,
    "city" => "Bogota",
    "address" => "Cr 4 # 55 36",
    "phone" => "3005234321",
    "cell_phone"=> "3010000001",
));

$pay = $epayco->charge->create(array(
    "token_card" => $token->id,
    "customer_id" => $customer->data->id_customer,
    "doc_type" => "CC",
    "doc_number" => "1070620729",
    "name" => "John",
    "last_name" => "Doe",
    "email" => "example@email.com",
    "bill" => "OR-1234",
    "description" => "Test Payment",
    "value" => "119000",
    "tax" => "19000",
    "tax_base" => "100000",
    "currency" => "COP",
    "dues" => "1",
    "address" => "cr 44 55 66",
    "phone"=> "2550102",
    "cell_phone"=> "3010000001",
    "ip" => "201.221.157.188",
    "url_response" => "https://crm.apptaxcenter.com/epayco/respuesta",
    "url_confirmation" => "https://crm.apptaxcenter.com/epayco/confirmacion",
));*/
