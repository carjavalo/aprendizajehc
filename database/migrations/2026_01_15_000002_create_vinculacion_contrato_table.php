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
        // Solo crear la tabla si no existe
        if (!Schema::hasTable('vinculacion_contrato')) {
            Schema::create('vinculacion_contrato', function (Blueprint $table) {
                $table->id(); // entero único auto-incrementable
                $table->string('nombre', 100);
                $table->timestamps();
            });

            // Insertar los datos iniciales
            DB::table('vinculacion_contrato')->insert([
                ['nombre' => 'Nomina', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Agesoc', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Asstracud', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Estudiante', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Docente', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Unidad Renal', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Otro', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vinculacion_contrato');
    }
};
