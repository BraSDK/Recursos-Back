<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Services\EmpleadoService;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    protected $empleadoService;

    /**
     * Display a listing of the resource.
     */

    // Inyectamos el servicio en el constructor
    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }

    public function index()
    {
        // Cargamos al empleado con su puesto y el departamento de ese puesto
        $empleados = Empleado::with(['puesto.departamento', 'jefe'])->get();
        
        return response()->json($empleados);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar
        $validated = $request->validate([
            'dni' => 'required|unique:empleados,dni',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'puesto_id' => 'required|exists:puestos,id',
            'fecha_ingreso' => 'required|date',
            'jefe_id' => 'nullable|exists:empleados,id',
        ]);

        try {
            // 2. Ejecutar lógica a través del servicio
            $empleado = $this->empleadoService->createEmpleado($validated);

            return response()->json([
                'message' => 'Empleado creado exitosamente',
                'data' => $empleado
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $empleado = Empleado::with(['puesto.departamento', 'jefe', 'puesto'])->find($id);

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        return response()->json($empleado);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validación básica
        $validatedData = $request->validate([
            'nombres' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . Empleado::findOrFail($id)->user_id, // Evita error de duplicado con el mismo usuario
            'puesto_id' => 'sometimes|exists:puestos,id',
            'estado' => 'sometimes|in:activo,inactivo',
            'fecha_ingreso' => 'sometimes|date',
        ]);

        $empleado = $this->empleadoService->updateEmpleado($id, $validatedData);
        return response()->json($empleado);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->empleadoService->deleteEmpleado($id);
        return response()->json(['message' => 'Empleado eliminado correctamente'], 200);
    }
}
