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
        Schema::create('procesos_seleccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->onDelete('cascade');
            $table->string('etapa'); // Entrevista, Capacitación Día 1, Día 2, etc.
            $table->enum('resultado', ['aprobado', 'desaprobado', 'pendiente'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->date('fecha_evaluacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos_seleccions');
    }
};
