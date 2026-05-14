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
        $filtros = $request->only(['area_general', 'fecha', 'estado', 'tipo']);
    
        return response()->json($this->grupoCapacitacionService->getGruposFiltrados($filtros));
    }

    public function indexCalendario()
    {
        $eventos = $this->grupoCapacitacionService->getEventosCalendario();
        return response()->json($eventos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_grupo' => 'required|string|max:100',
            'area_general' => 'required|in:ventas,operaciones,administracion',
            'tipo'         => 'required|in:postulante,preseleccion',
            'fecha_capacitacion' => 'required|date',
            'hora_capacitacion' => 'required',
        ]);

        $grupo = $this->grupoCapacitacionService->crearGrupo($validated);
        return response()->json($grupo, 201);
    }

    public function asignar(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|integer|exists:grupos_capacitacion,id',
            'ids'      => 'required|array|min:1',
            'tipo'     => 'required|in:postulante,preseleccion'
        ]);
    
        $tablaDestino = ($request->tipo === 'preseleccion') ? 'pre_selecciones' : 'postulantes';
        
        $request->validate([
            'ids.*' => "required|integer|exists:{$tablaDestino},id"
        ]);
    
        try {
            $this->grupoCapacitacionService->asignarMasivo(
                (int)$request->grupo_id, 
                $request->ids, 
                $request->tipo
            );
    
            return response()->json(['message' => 'Asignación completada con éxito']);
        } catch (\InvalidArgumentException $e) {
            // Corregido: usamos ->getMessage() o un string directo
            return response()->json(['message' => 'El tipo de grupo no coincide con los candidatos'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error interno en el servidor'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $grupoData = $this->grupoCapacitacionService->obtenerGrupoConPostulantes($id);
            return response()->json($grupoData);
        } catch (\Exception $e) {
            // Esto te ayudará a ver el error real en la consola de red si vuelve a fallar
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombre_grupo'       => 'string|max:100',
            'area_general'       => 'in:ventas,operaciones,administracion',
            'fecha_capacitacion' => 'date',
            'hora_capacitacion'  => 'string',
            'estado'             => 'in:abierto,en_curso,finalizado',
        ]);

        $grupo = $this->grupoCapacitacionService->actualizarGrupo($id, $validated);
        return response()->json($grupo);
    }

    // En GrupoCapacitacionController.php

    public function desvincularUsuario(Request $request, $grupoId)
    {
        $request->validate([
            'usuario_id' => 'required|integer',
            'tipo'       => 'required|in:postulante,preseleccion'
        ]);

        try {
            $this->grupoCapacitacionService->desvincularCandidato(
                $grupoId, 
                $request->usuario_id, 
                $request->tipo
            );

            return response()->json(['message' => 'Usuario desvinculado con éxito']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al desvincular usuario'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->grupoCapacitacionService->eliminarGrupo($id);
        return response()->json(['message' => 'Grupo eliminado correctamente']);
    }
    
}