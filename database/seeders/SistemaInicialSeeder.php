<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SistemaInicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear un Usuario para el sistema
        $usuario = User::create([
            'name' => 'Admin RRHH',
            'email' => 'admin@empresa.com',
            'password' => Hash::make('password'), // Siempre encriptar
        ]);

        // 2. Crear Departamentos
        $it = Departamento::create(['nombre' => 'Tecnología', 'codigo_dep' => 'DEP-IT']);
        $rh = Departamento::create(['nombre' => 'Recursos Humanos', 'codigo_dep' => 'DEP-RH']);

        // 3. Crear Puestos
        $puesto1 = Puesto::create([
            'departamento_id' => $it->id,
            'nombre_puesto' => 'Desarrollador Fullstack',
            'salario_base' => 2500.00
        ]);

        // 4. Crear un Empleado vinculado al usuario
        Empleado::create([
            'user_id' => $usuario->id,
            'puesto_id' => $puesto1->id,
            'dni' => '12345678',
            'nombres' => 'Admin',
            'apellidos' => 'Sistema',
            'fecha_ingreso' => now(),
            'estado' => 'activo'
        ]);
    }
}
