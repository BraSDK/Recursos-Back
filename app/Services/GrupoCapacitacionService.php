<?php

namespace App\Services;

use App\Models\GrupoCapacitacion;
use App\Models\Postulante;
use App\Models\ProcesoSeleccion;
use Illuminate\Support\Facades\DB;

class GrupoCapacitacionService
{
    public function crearGrupo(array $data)
    {
        return GrupoCapacitacion::create($data);
    }

    public function asignarPostulantes(int $grupoId, array $postulanteIds)
    {
        return DB::transaction(function () use ($grupoId, $postulanteIds) {
            $grupo = GrupoCapacitacion::findOrFail($grupoId);

            foreach ($postulanteIds as $id) {
                $postulante = Postulante::findOrFail($id);

                // A. Actualizamos postulante
                $postulante->update([
                    'grupo_id' => $grupo->id,
                    'estado_proceso' => 'capacitacion'
                ]);

                // B. Creamos registro de proceso
                ProcesoSeleccion::create([
                    'postulante_id' => $postulante->id,
                    'etapa' => '1° Día de Capa',
                    'resultado' => 'pendiente',
                    'fecha_evaluacion' => $grupo->fecha_capacitacion
                ]);
            }
        });
    }

    public function getGruposAbiertos()
    {
        return GrupoCapacitacion::where('estado', 'abierto')->latest()->get();
    }

    public function getGruposFiltrados(array $filtros)
    {
        $query = GrupoCapacitacion::query();

        // Filtro por Área
        if (!empty($filtros['area_general'])) {
            $query->where('area_general', $filtros['area_general']);
        }

        // Filtro por Fecha
        if (!empty($filtros['fecha'])) {
            $query->where('fecha_capacitacion', $filtros['fecha']);
        }

        // Filtro por Estado (por defecto solo abiertos)
        $query->where('estado', $filtros['estado'] ?? 'abierto');

        return $query->orderBy('fecha_capacitacion', 'asc')
                    ->orderBy('hora_capacitacion', 'asc')
                    ->get();
    }
}