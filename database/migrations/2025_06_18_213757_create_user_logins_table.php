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
        Schema::create('user_logins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable para intentos fallidos
            $table->string('email'); // Email usado en el intento de login
            $table->string('ip_address', 45); // IPv4 e IPv6
            $table->string('user_agent')->nullable(); // Información del navegador
            $table->enum('status', ['success', 'failed']); // Estado del login
            $table->enum('email_verified', ['verified', 'unverified'])->nullable(); // Estado de verificación
            $table->string('failure_reason')->nullable(); // Razón del fallo si aplica
            $table->timestamp('attempted_at'); // Momento del intento
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id', 'attempted_at']);
            $table->index(['email', 'attempted_at']);
            $table->index(['status', 'attempted_at']);
            $table->index('ip_address');

            // Relación con usuarios (nullable para intentos fallidos)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logins');
    }
};
