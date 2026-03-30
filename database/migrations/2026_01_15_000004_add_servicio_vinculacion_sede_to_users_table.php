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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos de relación con las nuevas tablas
            $table->unsignedBigInteger('servicio_area_id')->nullable()->after('numero_documento');
            $table->unsignedBigInteger('vinculacion_contrato_id')->nullable()->after('servicio_area_id');
            $table->unsignedBigInteger('sede_id')->nullable()->after('vinculacion_contrato_id');

            // Agregar llaves foráneas
            $table->foreign('servicio_area_id')->references('id')->on('servicios_areas')->onDelete('set null');
            $table->foreign('vinculacion_contrato_id')->references('id')->on('vinculacion_contrato')->onDelete('set null');
            $table->foreign('sede_id')->references('id')->on('sedes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['servicio_area_id']);
            $table->dropForeign(['vinculacion_contrato_id']);
            $table->dropForeign(['sede_id']);
            $table->dropColumn(['servicio_area_id', 'vinculacion_contrato_id', 'sede_id']);
        });
    }
};
