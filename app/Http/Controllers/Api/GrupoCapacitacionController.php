<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GrupoCapacitacionService;
use Illuminate\Http\Request;

class GrupoCapacitacionController extends Controller
{

    protected $grupoCapacitacionService;
    
    public function __construct(GrupoCapacitacionService $grupoCapacitacionService)
    {
        $this->grupoCapacitacionService = $grupoCapacitacionService;
    }

    public function index(Request $request)
    {
        // Obtenemos los filtros del request
        $filtros = $request->only(['area_general', 'fecha', 'estado']);
    
        return response()->json($this->grupoCapacitacionService->getGruposFiltrados($filtros));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_grupo' => 'required|string|max:100',
            'area_general' => 'required|in:ventas,operaciones,administracion',
            'fecha_capacitacion' => 'required|date',
            'hora_capacitacion' => 'required',
        ]);

        $grupo = $this->grupoCapacitacionService->crearGrupo($validated);
        return response()->json($grupo, 201);
    }

    public function asignar(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos_capacitacion,id',
            'postulante_ids' => 'required|array',
            'postulante_ids.*' => 'exists:postulantes,id',
        ]);

        $this->grupoCapacitacionService->asignarPostulantes($validated['grupo_id'], $validated['postulante_ids']);

        return response()->json(['message' => 'Grupo asignado con éxito']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

    }
    
}