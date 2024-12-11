<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Modulo;
use App\Models\Pasajero;
use App\Models\Permiso;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;
use stdClass;

class UserController extends Controller
{
    public function editcuenta(User $user)
    {

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.update', ['user' => $user, 'usuario' => $usuario, 'method' => 'put', 'route' => ['users.updatecuenta', $user->id]]);
    }

    public function updatecuenta(Request $request, User $user)
    {
        $user = User::find($user->id);
        if ($request->input('password') != '') {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();

        if (Auth::user()->roles_id == 3) {
            return redirect('sucursales/recargas/nueva');
        }elseif(Auth::user()->roles_id == 4 || Auth::user()->roles_id == 5){
            return redirect('empresas');
        }else{
            return redirect('home');
        }  
    }

    public function index()
    {
        $users = User::with('rol')->get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.lista', compact('users', 'usuario'));
    }

    public function nuevo()
    {

        $user = new User();
        $roles = Rol::get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.form', ['user' => $user, 'roles' => $roles, 'usuario' => $usuario, 'method' => 'post', 'route' => ['users.agregar']]);
    }

    public function editar(User $user)
    {

        $roles = Rol::get();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.form', ['user' => $user, 'roles' => $roles, 'usuario' => $usuario, 'method' => 'put', 'route' => ['users.actualizar', $user->id]]);
    }

    public function store(Request $request)
    {
        try {
            $user = new User();
            $user->nombres = $request->input('nombres');
            $user->identificacion = $request->input('identificacion');
            $user->usuario = $request->input('usuario');
            $user->password = Hash::make($request->input('password'));
            $user->estado = $request->input('estado');
            $user->roles_id = $request->input('roles_id');
            $user->save();

            if ($request->input('roles_id') == "2") {
                $modulos = Modulo::get();

                foreach ($modulos as $modulo) {
                    $permiso = new Permiso();
                    $permiso->users_id = $user->id;
                    $permiso->modulos_id = $modulo->id;
                    $permiso->ver = 1;
                    if ($modulo->id == 0) {
                        $permiso->editar = 1;
                    }else{
                        $permiso->editar = 0;
                    }
                    $permiso->save();
                }

                return redirect('users/permisos/' . $user->id);
            }else{
                return redirect('users');
            }
            
        } catch (QueryException $ex) {
            $errorCode = $ex->errorInfo[1];
            if ($errorCode == 1062) {
                return back()
                    ->withErrors(['sql' => 'El usuario ingresado ya está en uso']);
            }
        } catch(Exception $e)
        {
            $user->delete();
            return back()->withErrors(['sql' => 'El usuario no se pudo crear']);
        }
    }

    public function update(Request $request, User $user)
    {
        $user = User::find($user->id);
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->estado = $request->input('estado');
        if($user->roles_id == 1 || $user->roles_id == 2){
            $user->roles_id = $request->input('roles_id');
        }       
        $user->save();

        return redirect('users');
    }

    public function permisos(User $user)
    {

        $user = User::with('modulos')->where('id', $user->id)->first();
        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.permisos', ['user' => $user, 'usuario' => $usuario, 'method' => 'post', 'route' => ['users.savepermisos']]);
    }

    public function savepermisos(Request $request)
    {

        $permisos = $request->input();

        $oldpermisos = Permiso::where('users_id', $request->input('id'))->get();
        foreach ($oldpermisos as $permi) {
            $encontrado = false;
            foreach ($permisos as $key => $perm) {
                if ($key == $permi->modulos_id) {
                    $encontrado = true;
                    if ($perm[0] == "on") {
                        $permi->ver = 1;
                    } else {
                        $permi->ver = 0;
                    }
                    if (key_exists(1, $perm)) {
                        $permi->editar = 1;
                    } else {
                        $permi->editar = 0;
                    }
    
                    $permi->save();
                    break;
                }
            }
            if($encontrado == false){
                $permi->ver = 0;
                $permi->editar = 0;
                $permi->save();
            }              
        }

        return redirect('users');
    }

    public function filtrar(Request $request)
    {

        if ($request->filled('usuario')) {
            $users = User::with('rol')->where('usuario', 'like', '%' . $request->input('usuario') . '%')->paginate(20)->appends($request->query());
            $filtro = array('Usuario', $request->input('usuario'));
        } elseif ($request->filled('nombres')) {
            $users = User::with('rol')->where('nombres', 'like', '%' . $request->input('nombres') . '%')->paginate(20)->appends($request->query());
            $filtro = array('Nombre', $request->input('nombres'));
        } elseif ($request->filled('estado')) {
            $users = User::with('rol')->where('estado', $request->input('estado'))->paginate(20)->appends($request->query());
            if ($request->input('estado') == 1) {
                $filtro = array('Estado', "Activo");
            } else {
                $filtro = array('Estado', "Inactivo");
            }
        } elseif ($request->filled('rol')) {
            $users = User::with('rol')->where('roles_id', $request->input('rol'))->paginate(20)->appends($request->query());
            if ($request->input('rol') == 1) {
                $filtro = array('Rol', "Administrador");
            } elseif($request->input('rol') == 2) {
                $filtro = array('Rol', "Usuario");
            }elseif($request->input('rol') == 3) {
                $filtro = array('Rol', "Sucursal");
            }elseif($request->input('rol') == 4) {
                $filtro = array('Rol', "Empresa");
            }
        } else {
            return redirect('users');
        }

        $usuario = User::with('modulos')->where('id', Auth::user()->id)->first();

        return view('users.lista', compact('users', 'usuario', 'filtro'));
    }

    public function exportar(Request $request)
    {

        if ($request->filled('filtro')) {

            $filtro = explode("_", $request->input('filtro'));

            if ($filtro[0] == "Usuario") {
                $users = User::with('rol')->where('usuario', 'like', '%' . $filtro[1] . '%')->get();
            } elseif ($filtro[0] == "Nombre") {
                $users = User::with('rol')->where('nombres', 'like', '%' . $filtro[1] . '%')->get();
            } elseif ($filtro[0] == "Estado") {
                $users = User::with('rol')->where('estado', $filtro[1])->get();
            } elseif ($filtro[0] == "rol") {
                $users = User::with('rol')->where('roles_id', $filtro[1])->get();
            }
        } else {
            $users = User::with('rol')->get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("B1:D1");
        $sheet->setCellValue("B1", "Lista de Usuarios");
        $style = array(
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("B1:C1")->applyFromArray($style);

        $sheet->setCellValue("A2", "Usuario");
        $sheet->setCellValue("B2", "Nombre");
        $sheet->setCellValue("C2", "Estado");
        $sheet->setCellValue("D2", "Rol");
        $sheet->getStyle("A1:D2")->getFont()->setBold(true);

        $indice = 3;
        foreach ($users as $user) {
            $sheet->setCellValue("A" . $indice, $user->usuario);
            $sheet->setCellValue("B" . $indice, $user->nombres);
            if ($user->estado == 1) {
                $sheet->setCellValue("C" . $indice, "Activo");
            } else {
                $sheet->setCellValue("C" . $indice, "Inactivo");
            }
            if ($user->roles_id == 1) {
                $sheet->setCellValue("D" . $indice, "Administrador");
            } else {
                $sheet->setCellValue("D" . $indice, "Usuario");
            }
            $indice++;
        }

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('Usuarios.xlsx');
        $archivo = file_get_contents('Usuarios.xlsx');
        unlink('Usuarios.xlsx');

        return base64_encode($archivo);
    }
    
    public function privacidad()
    {
         return view('elements.privacidad');
    }
    
    public function restablecer($usuario, $clave)
    {
        $user = User::where('usuario', $usuario)->first();

        return view('auth.passwords.reset', compact('user'));
    }
    
    public function restablecerclave(Request $request)
    {
        $user = User::find($request->input('user'));
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect("/");
    }

    public function privacidadJadmin()
    {
         return view('elements.JADMIN');
    }

    public function agregarModulo()
    {
        $users = User::where('roles_id', 2)->get();
        foreach ($users as $user) {
            $permiso = new Permiso();
            $permiso->ver = 0;
            $permiso->editar = 0;
            $permiso->users_id = $user->id;
            $permiso->modulos_id = 11;
            $permiso->save();
        }

        return "OK";
    }

    public function obtenerToken(Request $request)
    {
        $credentials = $request->only('usuario', 'password');
        $respuesta = new stdClass();

        try {
            if (Auth::attempt($credentials)) {
                $token = Str::random(80);
                Auth::user()->api_token = $token;
                Auth::user()->save();
    
                $respuesta->estado = "OK";
                $respuesta->mensaje = $token;            
            }else{
                $respuesta->estado = "Error";
                $respuesta->mensaje = "";
                $respuesta->mensajeError = "Credenciales incorrectas";
            }
        } catch (Exception $ex) {
            $respuesta->estado = "Error";
            $respuesta->mensaje = "";
            $respuesta->mensajeError = $ex->getMessage();
        }
 
        return response(json_encode($respuesta), 200, ["Content-Type" => "application/json"]);
    } 

    public function getUsuarios(Request $request){
        $identificador=$request->input('identificacion');
        $usuarios = Pasajero::where(function($q) use ($identificador) {
            $q->where('identificacion', 'like', "{$identificador}%")
                  ->orWhere('nombre', 'like', "{$identificador}%");
        })
        ->whereNotNull('sub_cuenta')
        ->whereNotNull('affe')
        ->get();
        
        return json_encode($usuarios);

    }
}
