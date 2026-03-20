<?php

namespace App\Services;

use App\Models\Departamento;

class DepartamentoService
{     
    public function getAllDepartamentos()
    {
        return Departamento::all();
    }
    
    public function createDepartamento(array $data)
    {
        return Departamento::create([
            'nombre' => $data['nombre_departamento'],
            'codigo_dep' => $data['codigo_dep'],
        ]);
    }

    // MÉTODO PARA ACTUALIZAR (PUT)
    public function updateDepartamento($id, array $data)
    {
        $departamento = Departamento::findOrFail($id);
        
        $departamento->update([
            // Usamos ?? para mantener el valor actual si no viene en el request
            'nombre' => $data['nombre_departamento'] ?? $departamento->nombre,
            'codigo_dep' => $data['codigo_dep'] ?? $departamento->codigo_dep,
        ]);

        return $departamento;
    }

    // MÉTODO PARA ELIMINAR (DELETE)
    public function deleteDepartamento($id)
    {
        $departamento = Departamento::findOrFail($id);
        
        // Opcional: Podrías verificar si hay puestos asociados antes de borrar
        // if ($departamento->puestos()->count() > 0) { throw new \Exception("Área con puestos activos"); }

        return $departamento->delete();
    }
}