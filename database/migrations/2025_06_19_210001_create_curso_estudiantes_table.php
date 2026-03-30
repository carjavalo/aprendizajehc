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
        Schema::create('curso_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('estudiante_id');
            $table->timestamp('fecha_inscripcion')->useCurrent();
            $table->enum('estado', ['activo', 'inactivo', 'completado', 'abandonado'])->default('activo');
            $table->integer('progreso')->default(0); // Porcentaje de progreso
            $table->timestamp('ultima_actividad')->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');

            // Índice único para evitar inscripciones duplicadas
            $table->unique(['curso_id', 'estudiante_id']);

            // Índices
            $table->index('curso_id');
            $table->index('estudiante_id');
            $table->index('estado');
            $table->index('fecha_inscripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_estudiantes');
    }
};
