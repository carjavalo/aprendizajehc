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
        Schema::create('curso_actividad_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('curso_actividades')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_entrega')->nullable();
            $table->enum('estado', ['pendiente', 'entregado', 'tarde'])->default('pendiente');
            $table->string('archivo_path')->nullable();
            $table->text('comentarios')->nullable();
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('retroalimentacion')->nullable();
            $table->timestamp('fecha_calificacion')->nullable();
            $table->foreignId('calificado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices
            $table->unique(['actividad_id', 'estudiante_id']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_actividad_entregas');
    }
};
