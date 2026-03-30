<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para agregar 'clase_en_linea'
        DB::statement("ALTER TABLE curso_materiales MODIFY COLUMN tipo ENUM('archivo', 'video', 'imagen', 'documento', 'clase_en_linea') NOT NULL");
        
        // Agregar columnas para fechas de clase en línea
        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->dateTime('fecha_inicio')->nullable()->after('orden');
            $table->dateTime('fecha_fin')->nullable()->after('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
        });
        
        DB::statement("ALTER TABLE curso_materiales MODIFY COLUMN tipo ENUM('archivo', 'video', 'imagen', 'documento') NOT NULL");
    }
};
