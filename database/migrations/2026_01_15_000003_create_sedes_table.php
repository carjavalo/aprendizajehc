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
        if (!Schema::hasTable('sedes')) {
            Schema::create('sedes', function (Blueprint $table) {
                $table->id(); // entero único auto-incrementable
                $table->string('nombre', 100);
                $table->timestamps();
            });

            // Insertar los datos iniciales
            DB::table('sedes')->insert([
                ['nombre' => 'HUV-CALI', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'HUV-CARTAGO', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};
