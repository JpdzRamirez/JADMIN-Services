<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function inicio(){

        return view('auth.login');
    }
    

    public function login(Request $request)
    {
        $credenciales =    $this->validate(request(), [
            'usuario' => 'required|string',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($credenciales)) {
            if(Auth::user()->estado == 1){
                if(Auth::user()->roles_id == 3){
                    return redirect('sucursales/recargas/nueva');
                }elseif(Auth::user()->roles_id == 4 || Auth::user()->roles_id == 5){
                    return redirect('empresas');
                }elseif(Auth::user()->roles_id == 6){
                    return redirect('sucursales/efectivo/nuevo');
                }else{
                    return redirect('home');
                }              
            }else{
                Auth::logout();
                return back()
                ->withErrors(['usuario' => 'El usuario ingresado se encuentra inactivo'])
                ->withInput(request(['usuario']));
            }        
        } else {
            return back()
                ->withErrors(['usuario' => trans('auth.failed')])
                ->withInput(request(['usuario']));
        }
    }

    public function logout()
    {

        Auth::logout();
        Session::flush();

        return redirect('/');
    }

    public function redirectTo()
    {
        Session::flush();
        if(Auth::check()){
            if(Auth::user()->roles_id == 3){
                return redirect('sucursales/recargas/nueva');
            }else{
                return redirect('home');
            }  
        }         
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
