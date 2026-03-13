<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\DepartamentoController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Api\EmpleadoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para departamentos
Route::apiResource('departamentos', DepartamentoController::class);

// Rutas para puestos
Route::apiResource('puestos', PuestoController::class);

// Rutas para empleados
Route::apiResource('empleados', EmpleadoController::class);