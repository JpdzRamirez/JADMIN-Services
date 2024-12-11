<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Artisan; //Generar esqueletos de controladores y modelos.
use Illuminate\Support\Facades\Auth; // autenticación de usuarios
use Illuminate\Support\Facades\Route;

Route::get('/', 'Auth\LoginController@inicio')->name('inicio');

//Autenticación
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('politica_privacidad', 'UserController@privacidad');
Route::get('/JADMIN/politica_privacidad', 'UserController@JADMIN');
Route::get('clientes/{email}/{sum}/restablecer', 'ClienteController@cambiarClave');
Route::post('clientes/newpass', 'ClienteController@newPass');
Route::get('ivr/direccion/{numero}', 'IvrController@consultarDireccion');
Route::get('ivr/servicio/{numero}', 'IvrController@servicio');
Route::get('ivr/revisar/{servi}', 'IvrController@consultarServicio');
Route::get('ivr/novehiculo/{servi}', 'IvrController@noVehiculo');
Route::get('restablecer/{usuario}/{clave}', 'UserController@restablecer');
Route::post('restablecer/clave', 'UserController@restablecerClave');


Route::get('JADMIN/informacion_empresa', function () {
    return view('elements.JADMINTERMS');
});
Route::get('JADMIN/informacion_de_interes', function () {
    return view('alertas.info');
});

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return "Cleared!";
});

//ICON
Route::post('/integracion/login', 'IconController@token');
Route::post('/integracion/registrar_pago', 'IconController@registrarpago');
Route::post('/integracion/registrar_cobro', 'IconController@registrarcobro');
Route::post('/integracion/anular_recarga', 'IconController@anularRecarga');
Route::post('/integracion/editar_vale', 'IconController@editarVale');
Route::post('/integracion/legalizar_servicios', 'AlertaController@legalizarServicios');
Route::get('/integracion/cliente_conductor', 'IconController@cahorsConductor');
Route::get('/integracion/cliente_propietario', 'IconController@cahorsPropietario');
Route::get('/integracion/placas_propietario', 'IconController@placasPropietario');
Route::post('/integracion/logs_finalizar', 'IconController@logsFinalizar');
Route::post('/integracion/borrar_logs', 'IconController@borrarLogs');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    Route::get('home', 'HomeController@index')->name('home');
    //Users
    Route::get('users/actualizar/{user}', 'UserController@editcuenta')->name('users.editcuenta');
    Route::put('users/actualizar/{user}', 'UserController@updatecuenta')->name('users.updatecuenta');
    Route::get('users', 'UserController@index')->name('users.listar');
    Route::get('users/filtrar', 'UserController@filtrar');
    Route::get('users/exportar', 'UserController@exportar');
    Route::get('users/nuevo', 'UserController@nuevo')->name('users.nuevo');
    Route::post('users/nuevo', 'UserController@store')->name('users.agregar');
    Route::get('users/{user}', 'UserController@editar')->name('users.editar');
    Route::put('users/{user}', 'UserController@update')->name('users.actualizar');
    Route::get('users/permisos/{user}', 'UserController@permisos')->name('users.permisos');
    Route::post('users/permisos', 'UserController@savepermisos')->name('users.savepermisos');
    Route::get('usuarios/permisos/agregar_modulo', 'UserController@agregarModulo');

    //Terceros
    Route::get('empresas', 'TerceroController@empresas')->name('empresas.listar');
    Route::get('empresas/buscar', 'TerceroController@buscar')->name('empresas.buscar');
    Route::get('empresas/filtrar', 'TerceroController@filtrar');
    Route::get('empresas/exportar', 'TerceroController@exportar');
    Route::get('empresas/{agencia}', 'TerceroController@editar')->name('empresas.editar');
    Route::put('empresas/{agencia}', 'TerceroController@actualizar')->name('empresas.actualizar');
    Route::get('empresas/exportarvales/{agencia}', 'TerceroController@valesxagencia')->name('empresas.valesxagencia');
    Route::get('empresas/rutas/{contrato}', 'TerceroController@rutas')->name('empresas.rutas');
    Route::get('empresas/getagencias/{tercero}', 'TerceroController@getAgencias');
    Route::get('terceros', 'TerceroController@terceros')->name('terceros.listar');
    Route::get('terceros/{tercero}/{metodo}', 'TerceroController@userTercero')->name('terceros.editar');
    Route::post('terceros/{tercero}', 'TerceroController@upUsertercero')->name('terceros.actualizar');
    Route::post('terceros/valeras/ampliar', 'TerceroController@ampliarValera');
    Route::get('terceros/filtrar', 'TerceroController@filtrarTerceros');
    Route::get('empresas/{tercero}/rutas/activas', 'TerceroController@rutasActivas');
    Route::post('empresas/{tercero}/{codigo}/exportar/registros', 'TerceroController@exportarRegistros');
    Route::get('pasajeros/CRM/listar', 'TerceroController@pasajeros');
    Route::get('pasajeros/CRM/{pasajero}/editar', 'TerceroController@editarPasajero');
    Route::put('pasajeros/CRM/{pasajero}/actualizar', 'TerceroController@actualizarPasajero');
    Route::get('pasajeros/CRM/nuevo', 'TerceroController@crearPasajero');
    Route::post('pasajeros/CRM/nuevo', 'TerceroController@nuevoPasajero');
    Route::get('pasajeros/CRM/planilla','TerceroController@descargarPlanilla');
    Route::post('pasajeros/CRM/importar', 'TerceroController@importarPasajeros');
    //Afiliados
    Route::get('afiliados', 'PropietarioController@afiliados')->name('afiliados.listar');
    Route::post('afiliados/filtrar', 'PropietarioController@filtrar');
    Route::get('afiliados/filtrar', 'PropietarioController@filtrar');
    Route::get('afiliados/exportar', 'PropietarioController@exportar');

    //Propietarios
    Route::get('propietarios', 'PropietarioController@index')->name('propietarios.listar');
    Route::get('propietarios/{propietario}', 'PropietarioController@editar')->name('propietarios.editar');
    Route::put('propietarios/{propietario}', 'PropietarioController@update')->name('propietarios.actualizar');
    Route::get('propietarios/vehiculos/{propietario}', 'PropietarioController@vehiculos')->name('propietarios.vehiculos');

    //Conductores
    Route::get('conductores', 'ConductorController@index')->name('conductores.listar');
    Route::get('conductores/{conductor}', 'ConductorController@editar')->name('conductores.editar');
    Route::put('conductores/{conductor}', 'ConductorController@update')->name('conductores.actualizar');
    Route::get('conductores/vehiculos/{conductor}', 'ConductorController@vehiculos')->name('conductores.vehiculos');

    //Vehiculos
    Route::get('vehiculos', 'VehiculoController@index')->name('vehiculos.listar');
    Route::get('vehiculos/buscar', 'VehiculoController@buscar')->name('vehiculos.buscar');
    Route::get('vehiculos/ubicar', 'VehiculoController@ubicar')->name('vehiculos.ubicar');
    Route::get('vehiculos/filtrar', 'VehiculoController@filtrar');
    Route::get('vehiculos/exportar', 'VehiculoController@exportar');
    Route::get('getconductores_placa', 'VehiculoController@conductoresPlaca');
    Route::get('vehiculos/certificaciones', 'VehiculoController@crearCertificacion');
    Route::post('vehiculos/certificaciones', 'VehiculoController@generarCertificacion');
    Route::get('vehiculos/ubicar/avianca', 'VehiculoController@ubicarAvianca');
    Route::get('vehiculos/kilometraje', 'ClienteController@majorelKm');

    //Servicios
    Route::get('servicios/en_curso', 'ServicioController@encurso')->name('servicios.encurso');
    Route::get('servicios/finalizados', 'ServicioController@finalizados')->name('servicios.finalizados');
    Route::get('servicios/nuevo', 'ServicioController@nuevo')->name('servicios.nuevo');
    Route::get('servicios/nuevo/{telefono}', 'ServicioController@nuevo');
    Route::get('servicios/duplicar/{servicio}', 'ServicioController@duplicar');
    Route::post('servicios/nuevo', 'ServicioController@store')->name('servicios.agregar');
    //Route::get('servicios/getlibres', 'ServicioController@getlibres');
    Route::get('servicios/sincronizar', 'ServicioController@getconductor');
    Route::get('servicios/liberar', 'ServicioController@liberarservicio');
    Route::get('servicios/detener', 'ServicioController@stopservicio');
    Route::get('servicios/detalles/{servicio}', 'ServicioController@detalles')->name('servicios.detalles');
    Route::get('servicios/getvale', 'ValeraController@validarvale');
    Route::get('servicios/filtrar_en_curso', 'ServicioController@filtrarencurso');
    Route::get('servicios/filtrar_finalizados', 'ServicioController@filtrarfinalizados');
    Route::post('serviciosfinalizados/exportar', 'ServicioController@exportarfinalizados');
    Route::get('servicioscurso/exportar', 'ServicioController@exportarcurso');
    Route::get('servicios/getvaleras/{tercero}', 'ValeraController@valerasxtercero');
    Route::get('servicios/sincronizar', 'ServicioController@getconductor');
    Route::get('servicios/devoluciones', 'TransaccionController@devoluciones')->name('servicios.devoluciones');
    Route::get('servicios/devoluciones/filtrar', 'TransaccionController@filtrarDevoluciones');
    Route::get('servicios/devoluciones/exportar', 'TransaccionController@exportarDevoluciones');
    Route::get('servicios/ajaxencurso', 'ServicioController@ajaxCurso');
    Route::get('servicios/filtrar_empresa', 'ServicioController@filtrarEmpresa');
    Route::get('servicios/gestion_cobro/{servicio}', 'ValeraController@gestionCobro');
    Route::get('servicios/get_editar', 'ValeraController@getEditarServicio');
    Route::post('servicios/editar', 'ValeraController@actualizarServicio');
    Route::get('servicios/vale_automatico', 'TerceroController@ValeAutomatico');
    Route::get('servicios/get_usuarios', 'UserController@getUsuarios');
    //petrosantander ccosto
    Route::post('pasajero/{pasajero}/{servicio}/actualizar/CECO','ServicioController@actualizarCECOFinalizado');

    Route::get('servicios/majorel', 'ValeraController@serviciosMajorel');
    Route::post('servicios/majorel/archivo', 'ValeraController@archivoMajorel');
    Route::post('servicios/transamerica/archivo', 'ValeraController@archivoTransamerica');
    Route::post('servicios/comfenalco/archivo', 'TerceroController@archivoComfenalco');
    Route::post('servicios/majorel/programar', 'ValeraController@programarMajorel');
    Route::post('servicios/transamerica/programar', 'ValeraController@programarTransamerica');
    Route::post('servicios/comfenalco/programar', 'TerceroController@programarComfenalco');
    Route::get('servicios/majorel/editar', 'ValeraController@editarMajorel');
    Route::post('servicios/majorel/actualizar', 'ValeraController@actualizarMajorel');
    Route::post('servicios/majorel/finalizado/actualizar', 'ValeraController@actualizarFinalizadoMajorel');
    Route::get('servicios/majorel/finalizado/editar', 'ValeraController@editarFinalizadoMajorel');
    Route::get('servicios/majorel/descargar_programacion', 'ValeraController@descargarProgramacion');
    Route::get('servicios/majorel/corregir', 'ValeraController@corregirMajorel');

    //Valeras
    Route::get('valeras', 'ValeraController@index')->name('valeras.listar');
    Route::get('valeras/buscar', 'ValeraController@buscar')->name('valeras.buscar');
    Route::get('valeras/nuevo', 'ValeraController@nuevo')->name('valeras.nuevo');
    Route::get('valeras/nuevo/{error}', 'ValeraController@nuevoerror');
    Route::post('valeras/nuevo', 'ValeraController@store')->name('valeras.agregar');
    Route::get('valeras/filtrar', 'ValeraController@filtrar');
    Route::get('valeras/exportar', 'ValeraController@exportar');
    Route::get('valeras/{valera}', 'ValeraController@editar')->name('valeras.editar');
    Route::put('valeras/{valera}', 'ValeraController@actualizar')->name('valeras.actualizar');
    Route::get('valeras/{valera}/vale/nuevo', 'ValeraController@asignarvale')->name('valeras.asignar');
    Route::get('valeras/{valera}/vale/nuevo/{vale}', 'ValeraController@asignarvale');
    Route::post('valeras/vale/nuevo/{vale}', 'ValeraController@guardarvale')->name('valeras.guardarvale');
    Route::get('valeras/{valera}/vales', 'ValeraController@vales')->name('valeras.vales');
    Route::get('valeras/getagencias/{tercero}', 'ValeraController@getagencias');
    Route::get('valeras/{valera}/vales/filtrar', 'ValeraController@filtrarvales');
    Route::get('valeras/{valera}/vales/exportar', 'ValeraController@exportarvales');
    Route::get('valeras/{valera}/vales/{vale}/editar', 'ValeraController@editarvale');
    Route::put('valeras/{valera}/vales/{vale}/editar', 'ValeraController@updatevale')->name('vales.editar');
    Route::post('valeras/{valera}/vales/{vale}/liberar', 'ValeraController@liberar');
    Route::get('valeras/{valera}/vales/{vale}/eliminar', 'ValeraController@eliminar');
    Route::post('valeras/{valera}/vales/{vale}/observaciones', 'ValeraController@observaciones');
    Route::get('valeras/{valera}/plantilla', 'ValeraController@descargarplantilla');
    Route::post('valeras/{valera}/importar', 'ValeraController@importarvales');
    Route::get('valeras/{valera}/listanegra', 'ListanegraController@listaNegra');
    Route::post('valeras/listanegra/agregar', 'ListanegraController@addConductor');
    Route::post('valeras/listanegra/remover', 'ListanegraController@removeConductor');
    Route::get('valeras/{valera}/listanegra/exportar', 'ListanegraController@exportarLista');


    //Valeras Fisicas
    Route::get('valeras_fisicas', 'ListanegraController@valerasFisicas')->name('valerasfisicas.listar');
    Route::get('valeras_fisicas/{valera}/listanegra', 'ListanegraController@listaNegraFisica');
    Route::post('valeras_fisicas/listanegra/agregar', 'ListanegraController@addConductorFisica');
    Route::post('valeras_fisicas/listanegra/remover', 'ListanegraController@removeConductorFisica');

    //Clientes
    Route::get('servicios/getcliente', 'ClienteController@getinfocliente');
    Route::get('pasajeros/autocomplete', 'TerceroController@buscarPasajeros');
    Route::post('pasajero/{id}/actualizar/CCosto','TerceroController@actualizarCCosto');


    //Mensajes
    Route::get('mensajes', 'MensajeController@index')->name('mensajes.listar');
    Route::post('mensajes/nuevo', 'MensajeController@store')->name('mensajes.agregar');
    Route::get('mensajes/pendientes', 'MensajeController@pendientes')->name('mensajes.pendientes');
    Route::get('mensajes/chat/{cuentac}', 'MensajeController@chat')->name('mensajes.chat');
    Route::post('mensajes/chatcrm', 'MensajeController@chatmensaje')->name('mensajes.chatmensaje');
    Route::get('mensajes/pendientes/{cuentac}', 'MensajeController@chatcuenta')->name('mensajes.chatcuenta');
    Route::get('mensajes/filtrar', 'MensajeController@filtrarMensajes');
    Route::get('mensajes/programados', 'MensajeController@programados')->name('mensajes.programados');
    Route::post('mensajes/programados/editar', 'MensajeController@editarSmsProgramado');
    Route::get('mensajes/programados/{idsms}/inactivar', 'MensajeController@inactivarSmsProgramado');
    Route::get('mensajes/programados/{idsms}/activar', 'MensajeController@activarSmsProgramado');

    //Alertas
    Route::get('alertas', 'AlertaController@index')->name('alertas.listar');
    Route::get('alertas/filtrar', 'AlertaController@filtrar')->name('alertas.filtrar');
    Route::get('alertas/pendientes', 'AlertaController@pendientes')->name('alertas.pendientes');
    Route::get('alertas/gestionar/{alerta}', 'AlertaController@gestionar')->name('alertas.gestionar');
    Route::put('alertas/gestionar/{alerta}', 'AlertaController@atender')->name('alertas.atender');
    Route::get('alertas/logsicon', 'AlertaController@logsIcon');
    Route::get('alertas/finalizados_sin_icon', 'AlertaController@serviciosAJSON');

    //Novedades    
    Route::get('novedades/{servicio}', 'NovedadController@index')->name('novedades.listar');
    Route::get('novedades/nuevo/{servicio}', 'NovedadController@nuevo')->name('novedades.nuevo');
    Route::post('novedades/nuevo', 'NovedadController@guardar')->name('novedades.guardar');
    Route::post('novedades/nueva', 'NovedadController@nuevotipo');
    Route::post('sanciones/nueva', 'NovedadController@nuevasancion');
    Route::get('novedades/{novedad}/editar', 'NovedadController@editar')->name('novedades.editar');
    Route::put('novedades/{novedad}/editar', 'NovedadController@actualizar')->name('novedades.actualizar');

    //Transacciones
    Route::get('transacciones', 'TransaccionController@index')->name('transacciones.listar');
    Route::post('transacciones/nueva/{cuentac}', 'TransaccionController@store')->name('transacciones.agregar');
    Route::post('transacciones/editarsaldo/{cuenta}', 'TransaccionController@editarSaldo')->name('transacciones.editarsaldo');
    Route::get('historial/recargas/{cuenta}', 'TransaccionController@cuentarecargas');
    Route::get('historial/consumos/{cuenta}', 'TransaccionController@cuentaconsumos');
    Route::get('historial/transacciones/{cuenta}', 'TransaccionController@cuentatransacciones');
    Route::get('transacciones/{cuenta}/exportar', 'TransaccionController@exportar');
    Route::get('vales/{cuenta}', 'TransaccionController@valesporcuenta')->name('vales.cuenta');
    Route::get('vales/{cuenta}/filtrar', 'TransaccionController@filtrarvales');
    Route::get('vales/{cuenta}/exportar', 'TransaccionController@exportarvales');
    Route::post('transacciones/editarsaldos', 'TransaccionController@editarsaldos');
    Route::post('cuentas_empresas/editarsaldo', 'TransaccionController@editarsaldo');
    Route::post('servicios/devolver', 'TransaccionController@devolver');
    Route::get('movimientos', 'TransaccionController@movimientos')->name('cuentasc.movimientos');
    Route::get('movimientos/filtrar', 'TransaccionController@filtrarMovimientos');
    Route::get('movimientos/exportar', 'TransaccionController@exportarMovimientos');

    //Cuentas
    Route::get('cuentas_afiliados', 'ConductorController@cuentascorrientes')->name('cuentasc.listar');
    Route::get('cuentas_afiliados/crearlas', 'ConductorController@crearcuentas');
    Route::get('cuentas_afiliados/filtrar', 'ConductorController@filtrar');
    Route::get('cuentas_afiliados/exportar', 'ConductorController@exportar');

    //Sucursales
    Route::get('sucursales', 'SucursalController@index')->name('sucursales.listar');
    Route::get('sucursales/nuevo', 'SucursalController@nuevo')->name('sucursales.nuevo');
    Route::post('sucursales/nuevo', 'SucursalController@store')->name('sucursales.agregar');
    Route::get('sucursales/{sucursal}/editar', 'SucursalController@editar')->name('sucursales.editar');
    Route::put('sucursales/{sucursal}/editar', 'SucursalController@update')->name('sucursales.actualizar');
    Route::get('sucursales/filtrar', 'SucursalController@filtrar');
    Route::get('sucursales/exportar', 'SucursalController@exportar');
    Route::get('sucursales/abrircaja', 'SucursalController@abrircaja');
    Route::get('transacciones/sucursal/{sucursal}', 'SucursalController@transacciones');
    Route::get('sucursales/recargas/nueva', 'SucursalController@nuevarecarga');
    Route::post('sucursales/recargas/nueva', 'SucursalController@saverecarga')->name('recargas.nueva');
    Route::get('sucursales/pagos/nuevo', 'SucursalController@nuevopago');
    Route::post('sucursales/pagos/nuevo', 'SucursalController@savepago')->name('pagos.nuevo');
    Route::get('sucursales/cierrecaja', 'SucursalController@cierrecaja');
    Route::post('sucursales/cierrecaja', 'SucursalController@cerrarcaja');
    Route::get('sucursales/informacion/{sucursal}', 'SucursalController@informacion');
    Route::get('sucursales/validarinfo', 'SucursalController@validarinfo');
    Route::get('transacciones/sucursal/{sucursal}/filtrar', 'SucursalController@filtrartrans');
    Route::post('transacciones/sucursal/{sucursal}/exportar', 'SucursalController@exportartrans');
    Route::get('sucursales/movimientos', 'SucursalController@movimientos');
    Route::get('sucursales/movimientos/filtrar', 'SucursalController@filtrarMovimientos');
    Route::get('sucursales/cajas', 'SucursalController@historialCajas');
    Route::get('sucursales/cajas/filtrar', 'SucursalController@filtrarCajas');
    Route::get('sucursales/efectivo/nuevo', 'SucursalController@nuevoEfectivo');
    Route::post('sucursales/efectivo/nuevo', 'SucursalController@saveEfectivo');
    Route::get('sucursales/efectivo/movimientos', 'SucursalController@movimientosEfectivo');

    //Flotas
    Route::get('flotas', 'FlotaController@index')->name('flotas.listar');
    Route::get('flotas/nuevo', 'FlotaController@nuevo')->name('flotas.nuevo');
    Route::post('flotas/nuevo', 'FlotaController@store')->name('flotas.agregar');
    Route::get('flotas/vehiculos/{flota}', 'FlotaController@vehiculos')->name('flotas.vehiculos');
    Route::post('flotas/vehiculos/agregar', 'FlotaController@agregar');
    Route::get('flotas/actualizar/{flota}', 'FlotaController@actualizarFlota');
    Route::get('flotas/{flota}/borrar', 'FlotaController@borrarFlota');
    Route::get('flotas/{flota}/remover/{vehiculo}', 'FlotaController@removerVehiculo');

    //Avianca
    Route::get('servicios/avianca/rutas', 'AviancaController@rutas');
    Route::get('servicios/avianca/get_edicion', 'AviancaController@getEdicion');
    Route::post('servicios/avianca/editar', 'AviancaController@setEdicion');
    Route::get('servicios/avianca/importar_usuarios', 'AviancaController@importarUsuarios');
    Route::get('servicios/avianca/get_usuariosav', 'AviancaController@getUsuariosav');
    Route::get('valera/avianca/{valera}/vales', 'AviancaController@valesAvianca');
    Route::get('valera/avianca/filtrar/{valera}', 'AviancaController@filtrarVales');
    Route::get('valera/avianca/exportar/{valera}', 'AviancaController@exportarVales');
    Route::get('empresa/avianca/exportarvales/{agencia}', 'AviancaController@valesAgencia');
    Route::post('servicios/avianca/formato_general', 'AviancaController@aviancaGeneral');
    Route::get('pasajeros/avianca', 'AviancaController@pasajerosAvianca');
    Route::get('pasajeros/nuevo', 'AviancaController@nuevoPasajero')->name('pasajeros.nuevo');
    Route::post('pasajeros/registrar', 'AviancaController@registrarPasajero')->name('pasajeros.registrar');
    Route::get('pasajeros/{pasajero}/editar', 'AviancaController@editarPasajero');
    Route::put('pasajeros/{pasajero}/actualizar', 'AviancaController@actualizarPasajero')->name('pasajeros.actualizar');
    Route::get('pasajeros/buscar', 'AviancaController@buscarPasajero');
    Route::get('pasajeros/importar_celulares', 'AviancaController@importarCelulares');
    Route::get('pasajeros/get_coordenadas', 'AviancaController@actualizarCoordenadas');

    //Cartera
    Route::get('carteras/listar', 'CarteraController@deudores')->name('carteras.listar');
    Route::get('carteras/filtrar', 'CarteraController@filtrarDeudores');
    Route::get('carteras/{tercero}/registros', 'CarteraController@registrosTercero');
    Route::get('carteras/{tercero}/demanda', 'CarteraController@demanda');
    Route::get('acuerdos/listar', 'CarteraController@listarAcuerdos');
    Route::get('acuerdos/filtrar', 'CarteraController@filtrarAcuerdos');
    Route::get('acuerdos/nuevo', 'CarteraController@nuevoAcuerdo');
    Route::get('acuerdos/buscar_propietario/{identificacion}', 'CarteraController@buscarPropietario');
    Route::post('acuerdos/nuevo', 'CarteraController@registrarAcuerdo');
    Route::get('acuerdos/{acuerdo}/cuotas', 'CarteraController@cuotasPorAcuerdo');
    Route::get('acuerdos/registrar_pago', 'CarteraController@registrarPago');
    Route::get('cartera/desde_archivo', 'CarteraController@carteraDesdeArchivo');
    Route::post('acuerdos/pagar_cuota', 'CarteraController@pagarCuota');
    Route::get('acuerdos/iniciar_proceso/{acuerdo}', 'CarteraController@iniciarProceso');
});

Route::group(['middleware' => 'aplicaciones'], function () {

    Route::post('aplicaciones/cliente/nuevo', 'ClienteController@registrar');
    Route::post('aplicaciones/cliente/login', 'ClienteController@login');
    Route::post('aplicaciones/cliente/servicio', 'ClienteController@servicio');
    Route::post('aplicaciones/cliente/verificar_vale', 'ClienteController@verificarvale');
    Route::post('aplicaciones/cliente/revisar', 'ClienteController@revisar');
    Route::post('aplicaciones/cliente/seguirtaxi', 'ClienteController@seguirtaxi');
    Route::post('aplicaciones/cliente/cancelar', 'ClienteController@cancelar');
    Route::post('aplicaciones/cliente/calificar', 'ClienteController@calificar');
    Route::post('aplicaciones/cliente/historial', 'ClienteController@historial');
    Route::post('aplicaciones/cliente/informacion', 'ClienteController@informacion');
    Route::post('aplicaciones/cliente/update_datos', 'ClienteController@updatecuenta');
    Route::post('aplicaciones/cliente/liberar_vale', 'ClienteController@liberarVale');
    Route::post('aplicaciones/cliente/no_vehiculo', 'ClienteController@noVehiculo');
    Route::post('aplicaciones/cliente/restablecer', 'ClienteController@restablecer');
    Route::post('aplicaciones/cliente/vehiculos_cercanos', 'ClienteController@vehiculosCercanos');
    Route::post('aplicaciones/cliente/get_agencias', 'ClienteController@getAgencias');
    Route::get('aplicaciones/cliente/taxis_libres', 'ClienteController@taxisLibres');
    Route::get('aplicaciones/cliente/servicio_encurso', 'ClienteController@servicioEnCurso');
    Route::get('aplicaciones/cliente/validar_beneficiario', 'ClienteController@validarBeneficiario');
    Route::get('aplicaciones/cliente/consultar_valera', 'ClienteController@consultarValera');
    Route::post('aplicaciones/cliente/tramite', 'AlertaController@nuevoTramiteUsuario');
    Route::post('aplicaciones/cliente/agregar_tarjeta', 'ClienteController@agregarTarjeta');
    Route::post('aplicaciones/cliente/listar_tarjetas', 'ClienteController@getTarjetas');
    Route::post('aplicaciones/cliente/eliminar_tarjeta', 'ClienteController@desactivarTarjeta');
    Route::post('aplicaciones/cliente/listar_departamentos', 'ClienteController@getDepartamentos');
    Route::post('aplicaciones/cliente/listar_municipios', 'ClienteController@getMunicipios');


    Route::post('aplicaciones/taxista/login', 'ConductorController@login');
    Route::post('aplicaciones/taxista/logout', 'ConductorController@logout');
    Route::post('aplicaciones/taxista/servicios', 'ConductorController@servicioslibres');
    Route::post('aplicaciones/taxista/tomarservicio', 'ConductorController@tomarservicio');
    Route::post('aplicaciones/taxista/arribo', 'ConductorController@arribo');
    Route::post('aplicaciones/taxista/cancelar', 'ConductorController@cancelar');
    Route::post('aplicaciones/taxista/seguimiento', 'ConductorController@seguimiento');
    Route::post('aplicaciones/taxista/iniciar', 'ConductorController@inicioservicio');
    Route::post('aplicaciones/taxista/finalizar', 'ConductorController@finservicio');
    Route::post('aplicaciones/taxista/alerta', 'ConductorController@alerta');
    Route::post('aplicaciones/taxista/estado', 'ConductorController@cambiarestado');
    Route::post('aplicaciones/taxista/rutas', 'ConductorController@getrutas');
    Route::post('aplicaciones/taxista/saldos', 'ConductorController@getsaldos');
    Route::post('aplicaciones/taxista/servicio_incompleto', 'ConductorController@servicioincompleto');
    Route::post('aplicaciones/taxista/revisar_servicio', 'ConductorController@revisarservicio');
    Route::post('aplicaciones/taxista/historial', 'ConductorController@historial');
    Route::post('aplicaciones/taxista/chat_historial', 'MensajeController@chathistorial');
    Route::post('aplicaciones/taxista/nuevo_mensaje', 'MensajeController@chattaxista');
    Route::post('aplicaciones/taxista/sincro_chat', 'MensajeController@sincrotaxista');
    Route::post('aplicaciones/taxista/informacion', 'ConductorController@informacion');
    Route::post('aplicaciones/taxista/update_datos', 'ConductorController@updatecuenta');
    Route::post('aplicaciones/taxista/recargar', 'ConductorController@recargar');
    Route::post('aplicaciones/taxista/cerrar_sesion', 'ConductorController@salir');
    Route::post('aplicaciones/taxista/cambiar_placa', 'ConductorController@cambiarPlaca');
    Route::post('aplicaciones/taxista/consultar_servicio', 'ConductorController@consultarServicio');
    Route::post('aplicaciones/taxista/prefinalizar', 'ConductorController@preServicio');
    Route::post('aplicaciones/taxista/calificar', 'ConductorController@calificar');
    Route::post('aplicaciones/taxista/eliminarCuenta', 'ConductorController@desactivarCuenta');
    Route::post('aplicaciones/taxista/tramite', 'AlertaController@nuevoTramite');
    Route::post('aplicaciones/taxista/avianca/finalizar', 'AviancaController@finalizar');
    Route::post('aplicaciones/taxista/taxista/eliminarCuenta', 'ConductorController@desactivarCuenta');
    Route::post('aplicaciones/taxista/finalizar/cobro_anticipado', 'TerceroController@finalizarCobroAnticipado');
    
});
