<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('auth.login');
});

// Nuevas rutas

Auth::routes(); //Registra las rutas asociadas a login registro de usuario etc.
Route::get('/home',function(){
	return view('index');
});

Route::get('/chat','ChatController@index')->name('chat.index');
Route::get('/pruebas','ChatController@pruebas')->name('chat.pruebas');


Route::post('/chat/message/{id}','ChatController@messagesSent');


Route::post('/room/{id}','ChatController@loadRoom');


