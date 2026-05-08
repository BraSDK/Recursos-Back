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
        Schema::table('postulantes', function (Blueprint $table) {
            $table->enum('sexo', ['M', 'F', 'O'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            // Revertimos al estado original
            $table->enum('sexo', ['M', 'F'])->change();
        });
    }
};
