<?php

namespace App\Services;

use App\Models\Puesto;

class PuestoService
{
    public function getAllPuestos()
    {
        // Traemos el puesto junto con su departamento vinculado
        return Puesto::with('departamento')->get();
    }

    public function createPuesto(array $data)
    {
        return Puesto::create([
            'nombre_puesto' => $data['nombre_puesto'],
            'departamento_id' => $data['departamento_id'],
            'salario_base' => $data['salario_base'] ?? 0,
        ]);
    }
}