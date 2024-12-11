<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirectTo()
    {
        if(Auth::user()->roles_id == 3){
            return redirect('sucursales/recargas/nueva');
        }else{
            return redirect('home');
        }     
    }
    
    public function sendResetLinkEmail(Request $request)
    {
        $user = User::where('usuario', $request->input('usuario'))->first();
        if($user != null){
            if($user->roles_id == 3 || $user->roles_id == 4){
                $from = "EMAILNOTIFY";
                if($user->roles_id == 3){
                    $to = $user->sucursal->tercero->EMAIL;   
                }else{
                    $to = $user->cuentae->agencia->EMAIL;
                }
                $to = "EMAILDESTINATARY";
                $subject = "Restablecimiento de contraseña";
                $mensaje = "Se ha registrado un intento por restablecer su contraseña \n\n" .
                            "Usuario: " . $user->usuario . "\n" . 
                            
                            "Para continuar con el proceso de restablecimiento, use el siguiente enlace. \n\n" .
                            "URL RESET" . $user->usuario . "/" . md5($user->usuario);
                $headers = "From:" . $from;
                
                Mail::send([], [], function ($message) use($to, $subject, $mensaje, $from){
                    $message->from($from, "JADMIN");
                    $message->to($to);
                    $message->subject($subject);
                    $message->setBody($mensaje, 'text/html');
                });
                
                return back()->withErrors(["bien" => "Se ha enviado un enlace de restablecimiento a " . $to]);

            }else{
                return back()->withErrors(["sql" => "El usuario ingresado no tiene un correo electrónico registrado"]);
            }
        }else{
            return back()->withErrors(["sql" => "El usuario ingresado no se encuentra"]);
        }
        
    }
}
