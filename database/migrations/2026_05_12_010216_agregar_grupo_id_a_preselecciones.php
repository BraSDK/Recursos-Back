<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_selecciones', function (Blueprint $table) {
            // Verificamos si por algún milagro ya existe para que no truene
            if (!Schema::hasColumn('pre_selecciones', 'grupo_id')) {
                $table->foreignId('grupo_id')
                      ->nullable()
                      ->after('dni')
                      ->constrained('grupos_capacitacion')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pre_selecciones', function (Blueprint $table) {
            if (Schema::hasColumn('pre_selecciones', 'grupo_id')) {
                $table->dropForeign(['grupo_id']);
                $table->dropColumn('grupo_id');
            }
        });
    }
};