<?php

namespace App\Services;

use App\Models\HistorialCese;
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
                'postulante_id' => $data['postulante_id'] ?? null,
                'puesto_id' => $data['puesto_id'],
                'dni' => $data['dni'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'fecha_ingreso' => now(),
                'estado' => 'activo',
                'jefe_id' => $data['jefe_id'] ?? null,
            ]);
        });
    }

    public function updateEmpleado($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $empleado = Empleado::findOrFail($id);

            // Capturamos el estado anterior antes de actualizar
            $estadoAnterior = $empleado->estado;

            // Si el estado CAMBIA a vacaciones (y antes no lo estaba)
            if (isset($data['estado']) && $data['estado'] === 'vacaciones' && $estadoAnterior !== 'vacaciones') {
                $empleado->vacaciones()->create([
                    'fecha_inicio' => $data['fecha_inicio_vacas'] ?? now(),
                    'fecha_fin'    => $data['fecha_fin_vacas'] ?? now()->addDays(15),
                    'observaciones'=> $data['obs_vacas'] ?? 'Periodo programado',
                ]);
            }

            // Actualizamos datos del Empleado
            $empleado->update($data);

            if (isset($data['email']) || isset($data['nombres']) || isset($data['apellidos'])) {
                $user = User::findOrFail($empleado->user_id);
                $user->update([
                    'name' => trim(($data['nombres'] ?? $empleado->nombres) . ' ' . ($data['apellidos'] ?? $empleado->apellidos)),
                    'email' => $data['email'] ?? $user->email,
                ]);
            }

            return $empleado->load(['puesto.departamento', 'user']);
        });
    }

    public function darDeBaja($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $empleado = Empleado::findOrFail($id);

            // 1. Cambiamos el estado del empleado
            $empleado->update([
                'estado' => 'inactivo' 
            ]);

            // 2. Creamos el registro en el historial
            HistorialCese::create([
                'empleado_id'   => $id,
                'motivo_cese'   => $data['motivo_cese'],
                'observaciones' => $data['observaciones'],
                'recontratable' => $data['recontratable'],
                'fecha_salida'  => $data['fecha_salida'] ?? now()->toDateString(),
            ]);

            // 3. Opcional: Desactivar el usuario del sistema
            if ($empleado->user_id) {
                User::where('id', $empleado->user_id)->update(['activo' => false]);
            }

            return $empleado;
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