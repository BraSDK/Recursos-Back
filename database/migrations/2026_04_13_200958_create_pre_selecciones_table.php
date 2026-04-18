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
        Schema::create('pre_selecciones', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->unique();
            $table->string('nombre_completo'); // Nombre referencial
            $table->foreignId('puesto_id')->constrained('puestos');
            $table->enum('estado', ['pendiente', 'completado', 'expirado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_selecciones');
    }
};
