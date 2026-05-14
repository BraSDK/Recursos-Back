<?php

namespace App\Services;

use App\Models\PreSeleccion;
use Illuminate\Support\Facades\DB;

class PreSeleccionService
{
    public function getAll()
    {
        return PreSeleccion::with(['puesto.departamento'])->latest()->get();
    }

    public function getPaginado($estado = 'pendiente', $search = null, $grupoId = null)
    {
        // 1. Iniciamos la consulta con las relaciones necesarias
        $query = PreSeleccion::with(['puesto.departamento'])
            ->where('estado', $estado);

        // 2. NUEVO: Filtro por Grupo (Crítico para el CapacitacionDrawer)
        if ($grupoId) {
            $query->where('grupo_id', $grupoId);
        }

        // 3. Filtro de búsqueda por DNI o Nombre
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('dni', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_completo', 'LIKE', "%{$search}%");
            });
        }

        // 4. Ordenamos por los más recientes y paginamos
        return $query->latest()->paginate(10);
    }

    public function registrarInvitacion(array $data)
    {
        return DB::transaction(function () use ($data) {
            return PreSeleccion::create([
                'dni' => $data['dni'],
                'nombre_completo' => $data['nombre_completo'],
                'puesto_id' => $data['puesto_id'],
                'estado' => 'pendiente'
            ]);
        });
    }

    public function actualizarInvitacion($id, array $data)
    {
        $pre = PreSeleccion::findOrFail($id);
    
        // fill() detecta automáticamente qué campos coinciden con el $fillable 
        // y solo prepara los cambios para los datos que REALMENTE vienen en el array.
        $pre->fill($data);

        // save() solo ejecutará el SQL si hubo cambios reales (isDirty)
        $pre->save();

        return $pre->load('puesto.departamento');
    }

    public function verificarDniPendiente($dni)
    {
        // 1. Buscamos el registro en la base de datos
        $pre = PreSeleccion::with(['puesto.departamento'])
            ->where('dni', $dni)
            ->where('estado', 'pendiente')
            ->first();

        // 2. Si no existe, devolvemos null para que el controlador lance el 404
        if (!$pre) {
            return null;
        }

        // 3. Si existe, aplicamos la lógica de formateo (Data Transfer Object)
        // Solo devolvemos lo estrictamente necesario por seguridad.
        return [
            'dni' => $pre->dni,
            'nombre_completo' => $pre->nombre_completo,
            'puesto_id' => $pre->puesto_id,
            'puesto' => [
                'id' => $pre->puesto->id,
                'nombre_puesto' => $pre->puesto->nombre_puesto,
                'departamento_id' => $pre->puesto->departamento_id,
                'departamento' => [
                    'area_general' => $pre->puesto->departamento->area_general
                ]
            ]
        ];
    }

    public function deletePreSeleccion($id)
    {
        return DB::transaction(function () use ($id) {
            $preseleccion = PreSeleccion::findOrFail($id);
            
            return $preseleccion->delete();
        });
    }
}