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
        // Modificar el ENUM para agregar 'Operador'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Super Admin', 'Administrador', 'Docente', 'Estudiante', 'Registrado', 'Operador') NOT NULL DEFAULT 'Registrado'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el ENUM a su estado anterior (sin 'Operador')
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Super Admin', 'Administrador', 'Docente', 'Estudiante', 'Registrado') NOT NULL DEFAULT 'Registrado'");
    }
};
