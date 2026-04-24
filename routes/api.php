<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    GrupoCapacitacionController,
    PreSeleccionController,
    DepartamentoController,
    PostulanteController,
    EmpleadoController,
    PuestoController
};

// 1. Recursos Simples
Route::apiResource('pre-selecciones', PreSeleccionController::class);
Route::apiResource('departamentos', DepartamentoController::class);

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

// 4. Grupo de Puesto (Más organizado)
Route::prefix('puestos')->group(function () {
    Route::get('departamento/{id}', [PuestoController::class, 'getByDepartamento']);
    Route::apiResource('/', PuestoController::class)->parameters(['' => 'puesto']);
});

// 5. Nuevo Grupo: Capacitación y Grupos
Route::prefix('capacitacion')->group(function () {
    // Ruta personalizada para la acción masiva (debe ir antes del resource)
    Route::post('grupos/asignar', [GrupoCapacitacionController::class, 'asignar']);
    
    // apiResource maneja index, store, show, update, destroy automáticamente
    Route::apiResource('grupos', GrupoCapacitacionController::class);
});

// 6. Rutas Públicas
Route::prefix('public')->group(function () {
    Route::post('postular', [PostulanteController::class, 'store']);
    // Nuevo endpoint para validar antes de mostrar el formulario
    Route::get('verificar-dni/{dni}', [PreSeleccionController::class, 'verificarDniPublico']);
});