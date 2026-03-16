<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmpleadoService
{

    public function createEmpleado(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear el usuario para el sistema
            $user = User::create([
                'name' => $data['nombres'] . ' ' . $data['apellidos'],
                'email' => $data['email'],
                'password' => bcrypt($data['dni']), // Password por defecto es su DNI
            ]);

            // 2. Crear el registro de empleado
            return Empleado::create([
                'user_id' => $user->id,
                'puesto_id' => $data['puesto_id'],
                'dni' => $data['dni'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'fecha_ingreso' => now(),
                'estado' => 'activo',
            ]);
        });
    }

    public function updateEmpleado($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $empleado = Empleado::findOrFail($id);
            
            // Actualizamos datos del Empleado
            $empleado->update($data);

            // Si se envió email o nombre, actualizamos el Usuario vinculado
            if (isset($data['email']) || isset($data['nombres'])) {
                $user = User::findOrFail($empleado->user_id);
                $user->update([
                    'name' => ($data['nombres'] ?? $empleado->nombres) . ' ' . ($data['apellidos'] ?? $empleado->apellidos),
                    'email' => $data['email'] ?? $user->email,
                ]);
            }

            return $empleado->load('puesto.departamento');
        });
    }

    public function deleteEmpleado($id)
    {
        return DB::transaction(function () use ($id) {
            $empleado = Empleado::findOrFail($id);
            // Eliminamos al usuario primero (por la clave foránea) o al empleado
            // Dependiendo de tu lógica de negocio, podrías hacer "Borrado Lógico" (Soft Delete)
            $user = User::find($empleado->user_id);
            
            $empleado->delete();
            if ($user) $user->delete();

            return true;
        });
    }
}