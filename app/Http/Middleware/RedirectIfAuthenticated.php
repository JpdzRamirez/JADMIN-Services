<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {

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
                Session::flush();
                return redirect('/')
                ->withErrors(['usuario' => 'El usuario ingresado se encuentra inactivo'])
                ->withInput(request(['usuario']));
            }
        }

        return $next($request);
    }
}
