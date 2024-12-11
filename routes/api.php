<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'UserController@obtenerToken');

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/sisorg/puntos', 'SisorgController@puntos');
    Route::post('/sisorg/destinos', 'SisorgController@destinos');
    Route::post('/sisorg/recibir_servicio', 'SisorgController@recibirServicio');
    Route::post('/sisorg/consultar_servicio', 'SisorgController@consultarServicio');
    //Route::post('/sisorg/notificar_fecha', 'SisorgController@notificarFecha');
    Route::post('/sisorg/cancelar_servicio', 'SisorgController@cancelarServicio');
    Route::post('/sisorg/editar_servicio', 'SisorgController@editarServicio');
});
