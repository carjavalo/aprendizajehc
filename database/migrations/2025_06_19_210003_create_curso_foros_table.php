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
        Schema::create('curso_foros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('titulo', 200);
            $table->text('contenido');
            $table->unsignedBigInteger('parent_id')->nullable(); // Para respuestas
            $table->boolean('es_anuncio')->default(false);
            $table->boolean('es_fijado')->default(false);
            $table->integer('likes')->default(0);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('curso_foros')->onDelete('cascade');

            // Índices
            $table->index('curso_id');
            $table->index('usuario_id');
            $table->index('parent_id');
            $table->index('es_anuncio');
            $table->index('es_fijado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_foros');
    }
};
