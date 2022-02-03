<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PujaController;
use App\Http\Controllers\AuthController;
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

Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);
Route::get('categorias', [CategoriaController::class, 'index']);
Route::get('categorias/{id}/productos', [CategoriaController::class, 'show']);
Route::get('productos/{id}', [ProductoController::class, 'show']);

Route::get('pujas', [PujaController::class, 'index']);

Route::group(['middleware' => ['jwt.verify']], function() {
    //Todo lo que este dentro de este grupo requiere verificación de usuario.
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('get-user', [AuthController::class, 'getUser']);
    Route::post('categorias', [CategoriaController::class, 'store']);
    Route::put('categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('categorias/{id}', [CategoriaController::class, 'destroy']);

    Route::post('productos', [ProductoController::class, 'store']);
    Route::put('productos/{id}', [ProductoController::class, 'update']);
    Route::delete('productos/{id}', [ProductoController::class, 'destroy']);

    Route::post('pujas/productos/{id}', [PujaController::class, 'store']);
    Route::get('pujas/productos/{id}/{num?}', [PujaController::class, 'listarPujasDeProducto']);
    Route::get('pujas/usuarios', [PujaController::class, 'listarPujasDeUsuario']);
    Route::get('pujas/productos', [PujaController::class, 'listarUltimaPujaProducto']);

});
