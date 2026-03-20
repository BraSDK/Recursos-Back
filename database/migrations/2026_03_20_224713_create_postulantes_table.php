<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puesto_id')->constrained('puestos'); // A qué puesto postula
            $table->string('foto_path')->nullable();
            
            // Datos Personales
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('nombres');
            $table->string('dni', 20)->unique();
            $table->integer('edad');
            $table->string('estado_civil');
            $table->string('ubigeo_dep_prov'); 
            $table->string('distrito');
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F']);

            // Domicilio y Contacto
            $table->string('direccion');
            $table->string('telefono_fijo')->nullable();
            $table->string('celular');
            $table->string('email')->unique();

            // Formación y Experiencia (Usaremos JSON para no crear 20 tablas extra ahora)
            $table->json('formacion_academica')->nullable(); // Institución, especialidad, fechas, ciclo
            $table->json('experiencia_laboral')->nullable(); // Los 3 últimos empleos

            // Datos Adicionales
            $table->boolean('tiene_hijos')->default(false);
            $table->integer('cantidad_hijos')->default(0);
            $table->boolean('esta_embarazada')->default(false);
            
            // Emergencia y Salud
            $table->string('emergencia_nombre');
            $table->string('emergencia_parentesco');
            $table->string('emergencia_telefono');
            $table->text('enfermedades_alergias')->nullable();

            // Feedback
            $table->text('motivo_laborar');
            $table->string('horario_interes');

            // Control Administrativo
            $table->enum('estado_proceso', ['reclutamiento', 'capacitacion', 'gestion', 'no_apto'])->default('reclutamiento');
            $table->boolean('es_reingreso')->default(false);
            $table->text('comentarios_reclutador')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
