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
        Schema::create('certificados_emitidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plantilla_id')->nullable()->constrained('plantilla_certificados')->onDelete('set null');
            $table->string('codigo_verificacion', 64)->unique();
            $table->decimal('nota_final', 5, 2)->default(0);
            $table->timestamp('fecha_emision')->useCurrent();
            $table->timestamps();

            $table->unique(['curso_id', 'estudiante_id'], 'cert_curso_estudiante_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados_emitidos');
    }
};
