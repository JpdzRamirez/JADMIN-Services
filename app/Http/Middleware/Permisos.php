<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Permisos
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $usuario = Auth::user();

        if($request->is('empresas/*') || $request->is('terceros/*')){
            $modu = 4;
        }elseif ($request->is('valeras/*')) {
            $modu = 5;
        }

        if($usuario->roles_id !=1 && $usuario->modulos[$modu]->pivot->ver != 1){
            abort(403, "Acceso denegado");
        };

        return $next($request);
    }
}
