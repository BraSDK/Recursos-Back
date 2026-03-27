<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostulanteService;
use Illuminate\Http\Request;

class PostulanteController extends Controller
{
    protected $postulanteService;

    public function __construct(PostulanteService $postulanteService)
    {
        $this->postulanteService = $postulanteService;
    }

    public function index()
    {
        $postulantes = $this->postulanteService->getAllPostulantes();
        return response()->json($postulantes);
    }

    public function store(Request $request)
    {
        // 1. Capturamos todos los datos
        $inputs = $request->all();

        // 2. Limpieza de Booleanos (Convertir strings "true"/"false" a booleanos reales)
        // Esto es necesario porque FormData envía todo como texto
        if ($request->has('tiene_hijos')) {
            $inputs['tiene_hijos'] = filter_var($request->tiene_hijos, FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('esta_embarazada')) {
            $inputs['esta_embarazada'] = filter_var($request->esta_embarazada, FILTER_VALIDATE_BOOLEAN);
        }

        // 3. Validación (Usamos $inputs en lugar de $request directamente)
        $validated = validator($inputs, [
            'puesto_id' => 'required|exists:puestos,id',
            'dni' => 'required|string|unique:postulantes,dni',
            'nombres' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'edad' => 'required|integer',
            'estado_civil' => 'required|string',
            'ubigeo_dep_prov' => 'required|string',
            'distrito' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'direccion' => 'required|string',
            'celular' => 'required|string',
            'email' => 'required|email',
            'emergencia_nombre' => 'required|string',
            'emergencia_parentesco' => 'required|string',
            'emergencia_telefono' => 'required|string',
            'motivo_laborar' => 'required|string',
            'horario_interes' => 'required|string',
            'formacion_academica' => 'nullable', 
            'experiencia_laboral' => 'nullable',
            'tiene_hijos' => 'boolean',
            'esta_embarazada' => 'boolean',
            'cantidad_hijos' => 'nullable|integer'
        ])->validate();

        // 4. Decodificación de JSONs (como ya lo teníamos)
        if (isset($validated['formacion_academica']) && is_string($validated['formacion_academica'])) {
            $validated['formacion_academica'] = json_decode($validated['formacion_academica'], true);
        }
        if (isset($validated['experiencia_laboral']) && is_string($validated['experiencia_laboral'])) {
            $validated['experiencia_laboral'] = json_decode($validated['experiencia_laboral'], true);
        }

        // 5. Llamada al Service
        $postulante = $this->postulanteService->registrarPostulacion($validated, $request->file('foto'));

        return response()->json([
            'message' => 'Ficha de postulación enviada correctamente.',
            'data' => $postulante
        ], 201);
    }

    public function show($id)
    {
        $postulante = $this->postulanteService->getPostulanteById($id);
        return response()->json($postulante);
    }

    public function updateAsistencia(Request $request, $id)
    {
        // Validamos lo que realmente envía el Front
        $validated = $request->validate([
            'num_dia' => 'required|integer|min:1|max:4',
            'asistencia' => 'required|boolean' 
        ]);

        // Preparamos el array $progreso tal como lo espera tu PostulanteService
        $progreso = [
            'etapa' => $validated['num_dia'] . "° Día de Capa",
            'asistencia' => $validated['asistencia']
        ];

        $postulante = $this->postulanteService->actualizarProgresoDiario($id, $progreso);
        
        return response()->json([
            'message' => 'Asistencia actualizada correctamente',
            'postulante' => $postulante
        ]);
    }

    public function destroyAsistencia(Request $request, $id)
    {
        $validated = $request->validate([
            'num_dia' => 'required|integer|min:1|max:4'
        ]);

        $postulante = $this->postulanteService->anularProgresoDiario($id, $validated['num_dia']);

        return response()->json([
            'message' => 'Asistencia anulada, estado restaurado',
            'postulante' => $postulante
        ]);
    }
}