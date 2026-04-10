<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    DepartamentoController,
    PostulanteController,
    EmpleadoController,
    PuestoController
};

// 1. Recursos Simples
Route::apiResource('departamentos', DepartamentoController::class);
Route::apiResource('puestos', PuestoController::class);

// 2. Grupo de Empleados (Agrupamos por prefijo)
Route::prefix('empleados')->group(function () {
    Route::post('{id}/cesar', [EmpleadoController::class, 'cesar']);
    Route::apiResource('/', EmpleadoController::class)->parameters(['' => 'empleado']);
});

// 3. Grupo de Postulantes (Más organizado)
Route::prefix('postulantes')->group(function () {
    // Procesos de Contratación (Campanita)
    Route::get('pendientes-alta', [PostulanteController::class, 'getPendientes']);
    Route::get('{id}/pre-alta', [PostulanteController::class, 'getPreAlta']);
    
    // Gestión de Asistencia y Foto
    Route::put('{id}/asistencia', [PostulanteController::class, 'updateAsistencia']);
    Route::delete('{id}/asistencia', [PostulanteController::class, 'destroyAsistencia']);
    Route::post('{id}/foto', [PostulanteController::class, 'updateFotoPostulante']);
    
    // Recurso base
    Route::apiResource('/', PostulanteController::class)->parameters(['' => 'postulante']);
});

// 4. Rutas Públicas
Route::post('public/postular', [PostulanteController::class, 'store']);