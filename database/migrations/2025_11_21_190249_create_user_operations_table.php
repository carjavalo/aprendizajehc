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
        Schema::create('user_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('operation_type', 50); // 'create', 'update', 'delete', 'view', 'login', etc.
            $table->string('entity_type', 100); // 'Curso', 'Actividad', 'Entrega', 'Perfil', etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID del registro afectado
            $table->text('description'); // Descripción legible
            $table->json('details')->nullable(); // Detalles adicionales en JSON
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Índices para consultas eficientes
            $table->index(['user_id', 'created_at']);
            $table->index(['operation_type', 'created_at']);
            $table->index('entity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_operations');
    }
};
