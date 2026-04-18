<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PreSeleccionService;
use Illuminate\Http\Request;

class PreSeleccionController extends Controller
{
    protected $preSeleccionService;

    public function __construct(PreSeleccionService $preSeleccionService)
    {
        $this->preSeleccionService = $preSeleccionService;
    }

    public function index(Request $request)
    {
        // Recibimos el estado del tab (por defecto pendiente) y el buscador
        $estado = $request->query('estado', 'pendiente');
        $search = $request->query('search');

        $data = $this->preSeleccionService->getPaginado($estado, $search);
        
        // Al usar paginate(), Laravel devuelve un objeto con: data, current_page, last_page, total, etc.
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|unique:pre_selecciones,dni',
            'nombre_completo' => 'required|string|max:255',
            'puesto_id' => 'required|exists:puestos,id',
        ]);

        $invitacion = $this->preSeleccionService->registrarInvitacion($validated);

        return response()->json([
            'message' => 'Invitación de pre-selección registrada.',
            'data' => $invitacion->load('puesto.departamento')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'dni'             => 'sometimes|required|string|unique:pre_selecciones,dni,' . $id,
            'nombre_completo' => 'sometimes|required|string|max:255',
            'puesto_id'       => 'sometimes|required|exists:puestos,id',
            'estado'          => 'sometimes|required|in:pendiente,completado,expirado'
        ]);

        $pre = $this->preSeleccionService->actualizarInvitacion($id, $validated);
        return response()->json($pre);
    }

    public function destroy($id)
    {
        $this->preSeleccionService->deletePreSeleccion($id);
        return response()->json(['message' => 'Registro eliminado correctamente']);

    }

    /**
     * Endpoint para el formulario público de reclutamiento
     */
    public function verificarDniPublico($dni)
    {
        $pre = $this->preSeleccionService->verificarDniPendiente($dni);

        if (!$pre) {
            return response()->json([
                'message' => 'El DNI ingresado no cuenta con una invitación activa o ya ha completado su registro.'
            ], 404);
        }

        return response()->json($pre);
    }
}