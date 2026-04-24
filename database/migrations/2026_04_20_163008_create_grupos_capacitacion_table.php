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
        Schema::create('grupos_capacitacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_grupo', 100);
            $table->enum('area_general', ['ventas', 'operaciones']);
            $table->date('fecha_capacitacion');
            $table->time('hora_capacitacion');
            $table->enum('estado', ['abierto', 'en_curso', 'finalizado'])->default('abierto');
            $table->timestamps();
        });

        // 2. Agregar grupo_id a la tabla postulantes
        Schema::table('postulantes', function (Blueprint $table) {
            $table->foreignId('grupo_id')
                  ->nullable()
                  ->after('estado_proceso') // Para mantener el orden visual en la tabla
                  ->constrained('grupos_capacitacion')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropColumn('grupo_id');
        });

        Schema::dropIfExists('grupos_capacitacion');
    }
};
