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
        Schema::create('curso_materiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['archivo', 'video', 'imagen', 'documento']);
            $table->string('archivo_path')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_extension', 10)->nullable();
            $table->bigInteger('archivo_size')->nullable(); // Tamaño en bytes
            $table->string('url_externa')->nullable(); // Para videos de YouTube, etc.
            $table->integer('orden')->default(0);
            $table->boolean('es_publico')->default(true);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');

            // Índices
            $table->index('curso_id');
            $table->index('tipo');
            $table->index('orden');
            $table->index('es_publico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_materiales');
    }
};
