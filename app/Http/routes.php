<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['prefix'=> '/api/v1', 'middleware' => ['api']], function () {
    Route::resource('/user', 'RestApi\UserController');
    Route::resource('/pedidos', 'RestApi\PedidoController');
    Route::resource('/articulos', 'RestApi\ArticuloController');

});

Route::controller('/app/edi', 'EdiController');


Route::group(['middleware' => ['web']], function () {
    Route::controller('/app/dasanci', 'DasanciController');
    Route::controller('/app/expediciones', 'ExpedicionesController');
    Route::controller('/app/clientes', 'ClienteController');
    Route::controller('/app', 'AppController');
    Route::controller('/barcode', 'BarcodeController');
    Route::controller('/amazon-mws', 'AmazonMWSController');
});





Route::controller('/api', 'ApiController');



