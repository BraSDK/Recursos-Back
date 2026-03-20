<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DepartamentoService;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{

    protected $DepartamentoService;
    /**
     * Display a listing of the resource.
     */

    public function __construct(DepartamentoService $DepartamentoService)
    {
        $this->DepartamentoService = $DepartamentoService;
    }

    public function index()
    {
        $departamentos = $this->DepartamentoService->getAllDepartamentos();
        return response()->json($departamentos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_departamento' => 'required|string|max:255',
            'codigo_dep' => 'required|string|max:255'
        ]);

        $departamentos = $this->DepartamentoService->createDepartamento($validated);
        return response()->json($departamentos, 201);
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
        $validated = $request->validate([
            'nombre_departamento' => 'sometimes|string|max:255',
            'codigo_dep' => 'sometimes|string|max:255'
        ]);
    
        $departamento = $this->DepartamentoService->updateDepartamento($id, $validated);
        return response()->json($departamento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->DepartamentoService->deleteDepartamento($id);
        return response()->json(['message' => 'Departamento eliminado correctamente']);
    }
}
