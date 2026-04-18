<?php

namespace App\Services;

use App\Models\Puesto;

class PuestoService
{
    public function getAllPuestos($search = null, $departamentoId = null)
    {
        $query = Puesto::with('departamento');

        // Filtro por nombre de puesto
        if ($search) {
            $query->where('nombre_puesto', 'LIKE', "%{$search}%");
        }

        // Filtro por departamento
        if ($departamentoId) {
            $query->where('departamento_id', $departamentoId);
        }

        return $query->latest()->paginate(10);
    }

    public function getPuestosPorDepartamento($departamentoId)
    {
        return Puesto::where('departamento_id', $departamentoId)
                    ->with('departamento')
                    ->get();
    }

    public function updatePuesto($id, array $data)
    {
        $puesto = Puesto::findOrFail($id);
        
        // fill() asigna los valores que coincidan con el $fillable del modelo
        $puesto->fill($data);
        $puesto->save();

        return $puesto->load('departamento');
    }

    public function createPuesto(array $data)
    {
        return Puesto::create([
            'nombre_puesto' => $data['nombre_puesto'],
            'departamento_id' => $data['departamento_id'],
            'salario_base' => $data['salario_base'] ?? 0,
        ]);
    }

    public function deletePuesto($id)
    {
        $puesto = Puesto::findOrFail($id);
        return $puesto->delete();
    }
}