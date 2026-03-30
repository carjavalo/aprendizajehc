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
        if (!Schema::hasTable('servicios_areas')) {
            Schema::create('servicios_areas', function (Blueprint $table) {
                $table->id(); // entero único auto-incrementable
                $table->string('nombre', 100);
                $table->timestamps();
            });

            // Insertar los datos iniciales
            DB::table('servicios_areas')->insert([
                ['nombre' => 'Administrativo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Consulta Externa', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Hospitalizacion', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Sala de Operaciones', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Uci', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Urgencias', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Banco de Sangre', 'created_at' => now(), 'updated_at' => now()],
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
        Schema::dropIfExists('servicios_areas');
    }
};
