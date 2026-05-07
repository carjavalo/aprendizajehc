<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'actividad_id')) {
                $table->unsignedBigInteger('actividad_id')->nullable()->after('cargo_id');
                // Se remueve la llave foránea para evitar error 150 en servidor de producción
                // $table->foreign('actividad_id')->references('id')->on('actividades')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'actividad_id')) {
                // $table->dropForeign(['actividad_id']);
                $table->dropColumn('actividad_id');
            }
        });
    }
};
