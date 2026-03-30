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
        // Tabla para registrar materiales vistos por estudiantes
        Schema::create('curso_material_visto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('visto_at');
            $table->timestamps();

            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('curso_materiales')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['curso_id', 'material_id', 'user_id']);
            $table->index(['curso_id', 'user_id']);
        });

        // Tabla para registrar entregas de actividades
        Schema::create('curso_actividad_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('actividad_id');
            $table->unsignedBigInteger('user_id');
            $table->text('contenido')->nullable();
            $table->string('archivo_path')->nullable();
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('comentarios_instructor')->nullable();
            $table->enum('estado', ['entregado', 'revisado', 'aprobado', 'rechazado'])->default('entregado');
            $table->timestamp('entregado_at');
            $table->timestamp('revisado_at')->nullable();
            $table->timestamps();

            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->foreign('actividad_id')->references('id')->on('curso_actividades')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['curso_id', 'actividad_id', 'user_id']);
            $table->index(['curso_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_actividad_entrega');
        Schema::dropIfExists('curso_material_visto');
    }
};
