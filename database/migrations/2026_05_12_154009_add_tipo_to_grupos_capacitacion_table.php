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
        Schema::table('grupos_capacitacion', function (Blueprint $table) {
            $table->enum('tipo', ['postulante', 'preseleccion'])
              ->default('postulante')
              ->after('area_general');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos_capacitacion', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
