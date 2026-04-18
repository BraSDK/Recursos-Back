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

    public function getPaginado($estado = 'pendiente', $search = null)
    {
        $query = PreSeleccion::with(['puesto.departamento'])
            ->where('estado', $estado);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('dni', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_completo', 'LIKE', "%{$search}%");
            });
        }

        // Paginamos de 10 en 10. Laravel se encarga de leer el parámetro 'page' de la URL
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
        return PreSeleccion::with(['puesto.departamento'])
            ->where('dni', $dni)
            ->where('estado', 'pendiente')
            ->first();
    }

    public function deletePreSeleccion($id)
    {
        return DB::transaction(function () use ($id) {
            $preseleccion = PreSeleccion::findOrFail($id);
            
            return $preseleccion->delete();
        });
    }
}