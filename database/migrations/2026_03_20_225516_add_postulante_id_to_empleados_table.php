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
        Schema::table('empleados', function (Blueprint $table) {
            // Creamos la columna como nullable para que los empleados que ya existen no den error
            $table->unsignedBigInteger('postulante_id')->nullable()->after('user_id');
            
            // Opcional: Si quieres la llave foránea física (asegúrate de que la tabla postulantes exista primero)
            // $table->foreign('postulante_id')->references('id')->on('postulantes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('postulante_id');
        });
    }
};
