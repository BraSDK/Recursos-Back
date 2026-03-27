<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\DepartamentoController;
use App\Http\Controllers\Api\PostulanteController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\PuestoController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para departamentos
Route::apiResource('departamentos', DepartamentoController::class);

// Rutas para puestos
Route::apiResource('puestos', PuestoController::class);

// Rutas para empleados
Route::apiResource('empleados', EmpleadoController::class);

// Ruta tipo Resource para el resto (index, show, etc.)
Route::apiResource('postulantes', PostulanteController::class);

// Ruta Pública para el Formulario
Route::post('public/postular', [PostulanteController::class, 'store']);

// Ruta para actualizar asistencia (la que usaremos en Reclutamiento.jsx)
Route::put('postulantes/{id}/asistencia', [PostulanteController::class, 'updateAsistencia']);

// Esta es personalizada para la lógica de anular
Route::delete('postulantes/{id}/asistencia', [PostulanteController::class, 'destroyAsistencia']);