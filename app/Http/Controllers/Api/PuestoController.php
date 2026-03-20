<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PuestoService;
use Illuminate\Http\Request;

class PuestoController extends Controller
{
    protected $puestoService;
    /**
     * Display a listing of the resource.
     */

    public function __construct(PuestoService $puestoService)
    {
        $this->puestoService = $puestoService;
    }
 
    public function index()
    {
        $puestos = $this->puestoService->getAllPuestos();
        return response()->json($puestos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_puesto' => 'required|string|max:255',
            'departamento_id' => 'required|exists:departamentos,id',
            'salario_base' => 'nullable|numeric'
        ]);

        $puesto = $this->puestoService->createPuesto($validated);
        return response()->json($puesto, 201);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
