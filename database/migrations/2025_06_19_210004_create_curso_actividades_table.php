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
        Schema::create('curso_actividades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['tarea', 'evaluacion', 'quiz', 'proyecto']);
            $table->text('instrucciones')->nullable();
            $table->datetime('fecha_apertura')->nullable();
            $table->datetime('fecha_cierre')->nullable();
            $table->integer('puntos_maximos')->default(100);
            $table->boolean('permite_entregas_tardias')->default(false);
            $table->integer('intentos_permitidos')->default(1);
            $table->boolean('es_obligatoria')->default(true);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');

            // Índices
            $table->index('curso_id');
            $table->index('tipo');
            $table->index('fecha_apertura');
            $table->index('fecha_cierre');
            $table->index('es_obligatoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_actividades');
    }
};
