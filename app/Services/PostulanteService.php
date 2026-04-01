<?php

namespace App\Services;

use App\Models\Postulante;
use App\Models\Empleado;
use App\Models\HistorialCese;
use App\Models\ProcesoSeleccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostulanteService
{
    public function getAllPostulantes()
    {
        return Postulante::with(['puesto', 'procesosSeleccion'])->latest()->get();
    }

    public function registrarPostulacion(array $data, $fotoFile = null, $cv = null)
    {
        return DB::transaction(function () use ($data, $fotoFile, $cv) {
            
            // 1. Lógica de Detección de Reingreso
            $empleadoAnterior = Empleado::where('dni', $data['dni'])->first();
            $esReingreso = false;
            $comentarios = null;

            if ($empleadoAnterior) {
                $esReingreso = true;
                $cese = HistorialCese::where('empleado_id', $empleadoAnterior->id)->first();
                
                $status = ($cese && !$cese->recontratable) ? "⚠️ NO RECONTRATABLE" : "✅ RECONTRATABLE";
                $motivo = $cese ? $cese->motivo_cese : "No registrado";
                
                $comentarios = "ALERTA DE REINGRESO: Ex-empleado. Motivo cese: {$motivo}. Status: {$status}.";
            }

            // 2. Manejo de la Foto (si existe)
            $fotoPath = null;
            if ($fotoFile) {
                $fotoPath = $fotoFile->store('postulantes/fotos', 'public');
            }

            $hojaPath = null;
            if ($cv) {
                // Guardamos el CV en una carpeta privada o pública
                $hojaPath = $cv->store('postulantes/cvs', 'public');
            }

            // 3. Crear el registro con todos los campos
            return Postulante::create(array_merge($data, [
                'foto_path' => $fotoPath,
                'cv_path' => $hojaPath,
                'es_reingreso' => $esReingreso,
                'comentarios_reclutador' => $comentarios,
                'estado_proceso' => 'reclutamiento'
            ]));
        });
    }

    public function getPostulanteById($id)
    {
        return Postulante::with(['puesto', 'procesosSeleccion'])->findOrFail($id);
    }

    public function actualizarProgresoDiario($id, array $progreso)
    {
        return DB::transaction(function () use ($id, $progreso) {
            $postulante = Postulante::findOrFail($id);
            $esAsistencia = $progreso['asistencia'];
            $etapa = $progreso['etapa']; // Ej: "1° Día de Capa"

            // 1. Lógica de Descalificación Automática
            if (!$esAsistencia) {
                $postulante->estado_proceso = 'no_apto';
                $postulante->comentarios_reclutador = "SISTEMA: Descalificado por inasistencia en {$etapa}.";
            } 
            else {
                // 2. Cambio de Reclutamiento a Capacitación (Al marcar cualquier día como asistido)
                if ($postulante->estado_proceso === 'reclutamiento') {
                    $postulante->estado_proceso = 'capacitacion';
                }

                // 3. Verificación de Finalización (Si es el 4° Día)
                if (str_contains($etapa, '4° Día')) {
                    $postulante->comentarios_reclutador = "SISTEMA: Capacitación completada con éxito. Pendiente de pase a Gestión.";
                }
            }

            $postulante->save();

            // 4. Registrar en el historial (Tabla: procesos_seleccions)
            return ProcesoSeleccion::create([
                'postulante_id' => $id,
                'etapa' => $etapa,
                'resultado' => $esAsistencia ? 'aprobado' : 'desaprobado',
                'fecha_evaluacion' => now()->format('Y-m-d H:i:s')
            ]);
        });
    }

    public function actualizarInformacion($id, array $datos)
    {
        $postulante = Postulante::findOrFail($id);

        // LOGICA PROFESIONAL DE INTEGRIDAD:
        // Si el nuevo estado es 'reclutamiento', borramos sus asistencias
        if (isset($datos['estado_proceso']) && $datos['estado_proceso'] === 'reclutamiento') {
            // Borramos físicamente los registros de la tabla procesos_seleccions
            $postulante->procesosSeleccion()->delete();
        }
        
        // Actualizamos los datos validados
        $postulante->update($datos);

        // Retornamos el objeto con sus relaciones cargadas si es necesario para el Front
        return $postulante->load('procesosSeleccion');
    }

    public function actualizarFotoPostulante($id, $archivo)
    {
        $postulante = Postulante::findOrFail($id);

        // 1. Si ya tenía una foto anterior, la borramos para no llenar el servidor de basura
        if ($postulante->foto_path && Storage::disk('public')->exists($postulante->foto_path)) {
            Storage::disk('public')->delete($postulante->foto_path);
        }

        // 2. Guardamos la nueva foto en la carpeta 'postulantes/fotos'
        $path = $archivo->store('postulantes/fotos', 'public');

        // 3. Actualizamos la base de datos
        $postulante->update([
            'foto_path' => $path
        ]);

        return $postulante;
    }

    public function anularProgresoDiario($id, $numDia)
    {
        return DB::transaction(function () use ($id, $numDia) {
            $postulante = Postulante::findOrFail($id);
            $etapaBuscada = "{$numDia}° Día de Capa";

            // 1. Eliminamos el registro de ese día en el historial
            ProcesoSeleccion::where('postulante_id', $id)
                ->where('etapa', $etapaBuscada)
                ->delete();

            // 2. Recalculamos el estado del postulante
            // Miramos si tiene más asistencias o faltas en otros días
            $historialRestante = ProcesoSeleccion::where('postulante_id', $id)->get();
            
            if ($historialRestante->isEmpty()) {
                // Si no queda nada, vuelve al inicio
                $postulante->estado_proceso = 'reclutamiento';
                $postulante->comentarios_reclutador = null;
            } else {
                // Si quedan registros, verificamos si hay alguna falta (desaprobado)
                $tieneFallas = $historialRestante->contains('resultado', 'desaprobado');
                
                if ($tieneFallas) {
                    $postulante->estado_proceso = 'no_apto';
                } else {
                    // Si todo lo que queda es aprobado, sigue en capacitación
                    $postulante->estado_proceso = 'capacitacion';
                }
            }

            $postulante->save();
            return $postulante->load('procesosSeleccion');
        });
    }
}