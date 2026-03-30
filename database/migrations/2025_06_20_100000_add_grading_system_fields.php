<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Sistema de Calificaciones:
     * - Nota máxima del curso: 5.0 (equivalente al 100%)
     * - Materiales tienen porcentaje sobre el curso y nota mínima de aprobación
     * - Actividades tienen porcentaje sobre el curso (suma = porcentaje del material)
     * - Quizzes/Evaluaciones: suma de puntos de preguntas no puede superar 5.0
     * - Tareas: nota asignada manualmente por el docente (máximo 5.0)
     */
    public function up(): void
    {
        // Agregar campos al curso
        Schema::table('cursos', function (Blueprint $table) {
            $table->decimal('nota_minima_aprobacion', 3, 2)->default(3.00)->after('duracion_horas')
                  ->comment('Nota mínima para aprobar el curso (0.0 - 5.0)');
            $table->decimal('nota_maxima', 3, 2)->default(5.00)->after('nota_minima_aprobacion')
                  ->comment('Nota máxima del curso (siempre 5.0)');
        });

        // Agregar campos a los materiales
        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->decimal('porcentaje_curso', 5, 2)->default(0.00)->after('es_publico')
                  ->comment('Porcentaje que representa el material sobre el curso (0-100%)');
            $table->decimal('nota_minima_aprobacion', 3, 2)->default(3.00)->after('porcentaje_curso')
                  ->comment('Nota mínima para aprobar el material (0.0 - 5.0)');
        });

        // Agregar campos a las actividades
        Schema::table('curso_actividades', function (Blueprint $table) {
            $table->decimal('porcentaje_curso', 5, 2)->default(0.00)->after('es_obligatoria')
                  ->comment('Porcentaje que representa la actividad sobre el curso (0-100%)');
            $table->unsignedBigInteger('material_id')->nullable()->after('curso_id')
                  ->comment('Material al que pertenece esta actividad');
            
            // Agregar índice y foreign key
            $table->index('material_id');
            $table->foreign('material_id')
                  ->references('id')
                  ->on('curso_materiales')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_actividades', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropIndex(['material_id']);
            $table->dropColumn(['porcentaje_curso', 'material_id']);
        });

        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->dropColumn(['porcentaje_curso', 'nota_minima_aprobacion']);
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['nota_minima_aprobacion', 'nota_maxima']);
        });
    }
};
