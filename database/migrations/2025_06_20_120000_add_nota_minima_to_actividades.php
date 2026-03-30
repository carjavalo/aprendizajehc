<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar nota mínima de aprobación a las actividades
     */
    public function up(): void
    {
        Schema::table('curso_actividades', function (Blueprint $table) {
            if (!Schema::hasColumn('curso_actividades', 'nota_minima_aprobacion')) {
                $table->decimal('nota_minima_aprobacion', 3, 2)->default(3.00)->after('porcentaje_curso')
                      ->comment('Nota mínima para aprobar la actividad (0.0 - 5.0)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_actividades', function (Blueprint $table) {
            if (Schema::hasColumn('curso_actividades', 'nota_minima_aprobacion')) {
                $table->dropColumn('nota_minima_aprobacion');
            }
        });
    }
};
