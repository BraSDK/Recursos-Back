<?php

namespace App\Services;

use App\Models\GrupoCapacitacion;
use App\Models\Postulante;
use App\Models\PreSeleccion;
use App\Models\ProcesoSeleccion;
use Illuminate\Support\Facades\DB;

class GrupoCapacitacionService
{
    public function obtenerGrupoConPostulantes($id)
    {
        $grupo = GrupoCapacitacion::with(['postulantes', 'preSelecciones'])->findOrFail($id);

        // Determinamos qué colección usar
        $inscritos = ($grupo->tipo === 'preseleccion') 
            ? $grupo->preSelecciones 
            : $grupo->postulantes;

        // Convertimos a array y añadimos manualmente la clave para evitar el error 500
        $data = $grupo->toArray();
        $data['inscritos'] = $inscritos;

        return $data;
    }

    public function crearGrupo(array $data)
    {
        return GrupoCapacitacion::create($data);
    }

    public function asignarMasivo(int $grupoId, array $ids, string $tipo)
    {
        // 1. Buscamos el grupo (El Service maneja el modelo)
        $grupo = GrupoCapacitacion::findOrFail($grupoId);

        // 2. Validación de Integridad de Negocio
        if ($grupo->tipo !== $tipo) {
            throw new \InvalidArgumentException("Error: El grupo es de tipo '{$grupo->tipo}' y no puede recibir candidatos de tipo '{$tipo}'.");
        }

        // 3. Ejecución de la transacción
        return DB::transaction(function () use ($grupo, $ids, $tipo) {
            
            if ($tipo === 'preseleccion') {
                return PreSeleccion::whereIn('id', $ids)
                    ->update(['grupo_id' => $grupo->id]);
            }

            // Lógica para Postulantes (Reclutamiento)
            foreach ($ids as $id) {
                $postulante = Postulante::findOrFail($id);
                
                $postulante->update([
                    'grupo_id' => $grupo->id,
                    'estado_proceso' => 'capacitacion'
                ]);

                ProcesoSeleccion::updateOrCreate(
                    [
                        'postulante_id' => $postulante->id, 
                        'etapa' => '1° Día de Capa'
                    ],
                    [
                        'resultado' => 'pendiente', 
                        'fecha_evaluacion' => $grupo->fecha_capacitacion
                    ]
                );
            }
            
            return true;
        });
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

    public function getEventosCalendario()
    {
        $grupos = GrupoCapacitacion::all();

        return $grupos->map(function($grupo) {
            return [
                'id'    => $grupo->id,
                'title' => $grupo->nombre_grupo,
                // Formato ISO8601 que FullCalendar ama
                'start' => $grupo->fecha_capacitacion . 'T' . $grupo->hora_capacitacion,
                'extendedProps' => [
                    'area'   => $grupo->area_general,
                    'estado' => $grupo->estado,
                    'total_postulantes' => $grupo->postulantes()->count(), // Útil para el calendario
                ],
                'backgroundColor' => $this->getColorPorArea($grupo->area_general),
                'borderColor'     => $this->getColorPorArea($grupo->area_general),
                'textColor'       => '#ffffff',
            ];
        });
    }

    private function getColorPorArea($area)
    {
        return match($area) {
            'ventas'         => '#4f46e5', // Indigo 600
            'operaciones'    => '#10b981', // Emerald 500
            'administracion' => '#f59e0b', // Amber 500
            default          => '#6b7280', // Gray 500
        };
    }

    public function getGruposFiltrados(array $filtros)
    {
        $query = GrupoCapacitacion::query();

        // Filtro por tipo (Módulo Pre-selección o Reclutamiento)
        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

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

    public function actualizarGrupo($id, array $datos)
    {
        $grupo = GrupoCapacitacion::findOrFail($id);
        $grupo->update($datos);
        return $grupo;
    }

    public function desvincularCandidato($grupoId, $usuarioId, $tipo)
    {
        // Determinamos el modelo basado en el tipo
        if ($tipo === 'preseleccion') {
            return \App\Models\PreSeleccion::where('id', $usuarioId)
                ->where('grupo_id', $grupoId)
                ->update(['grupo_id' => null]);
        }

        return \App\Models\Postulante::where('id', $usuarioId)
            ->where('grupo_id', $grupoId)
            ->update(['grupo_id' => null]);
    }

    public function eliminarGrupo($id)
    {
        $grupo = GrupoCapacitacion::findOrFail($id);
        
        // Opcional: Desvincular postulantes antes de borrar si no tienes cascade on delete
        $grupo->postulantes()->update(['grupo_id' => null]);
        
        return $grupo->delete();
    }

}