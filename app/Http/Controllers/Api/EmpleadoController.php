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
        // 1. Validar los datos que vienen de React
        $request->validate([
            'dni' => 'required|unique:empleados,dni',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'puesto_id' => 'required|exists:puestos,id',
            'fecha_ingreso' => 'required|date',
        ]);

        // 2. Crear el Usuario primero (para obtener el user_id)
        $user = \App\Models\User::create([
            'name' => $request->nombres . ' ' . $request->apellidos,
            'email' => $request->email,
            'password' => bcrypt($request->dni), // Contraseña por defecto es su DNI
        ]);

        // 3. Crear el Empleado vinculado al usuario
        $empleado = Empleado::create([
            'user_id' => $user->id,
            'puesto_id' => $request->puesto_id,
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'fecha_ingreso' => $request->fecha_ingreso,
            'jefe_id' => $request->jefe_id, // Puede ser null
            'estado' => 'activo'
        ]);

        return response()->json([
            'message' => 'Empleado creado exitosamente',
            'data' => $empleado->load('puesto')
        ], 201);
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
            'puesto_id' => 'sometimes|exists:puestos,id',
            'estado' => 'sometimes|in:activo,inactivo',
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
