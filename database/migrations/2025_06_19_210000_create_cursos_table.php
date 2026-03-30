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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('instructor_id');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['borrador', 'activo', 'finalizado', 'archivado'])->default('borrador');
            $table->string('codigo_acceso', 10)->unique()->nullable();
            $table->integer('max_estudiantes')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->text('objetivos')->nullable();
            $table->text('requisitos')->nullable();
            $table->integer('duracion_horas')->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('id_area')->references('id')->on('areas')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');

            // Índices
            $table->index('id_area');
            $table->index('instructor_id');
            $table->index('estado');
            $table->index('codigo_acceso');
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
