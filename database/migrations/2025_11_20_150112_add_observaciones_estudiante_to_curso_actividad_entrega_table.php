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
        Schema::table('curso_actividad_entrega', function (Blueprint $table) {
            // Agregar columna para observaciones del estudiante
            $table->text('observaciones_estudiante')->nullable()->after('contenido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_actividad_entrega', function (Blueprint $table) {
            $table->dropColumn(' observaciones_estudiante');
        });
    }
};
