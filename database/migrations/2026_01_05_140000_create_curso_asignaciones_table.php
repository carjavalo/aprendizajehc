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
        Schema::create('curso_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asignado_por')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['activo', 'inactivo', 'expirado'])->default('activo');
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['curso_id', 'estudiante_id'], 'curso_estudiante_asignacion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_asignaciones');
    }
};
