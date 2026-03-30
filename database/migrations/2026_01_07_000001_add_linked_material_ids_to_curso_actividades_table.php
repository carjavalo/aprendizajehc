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
        Schema::table('curso_actividades', function (Blueprint $table) {
            $table->json('linked_material_ids')->nullable()->after('contenido_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_actividades', function (Blueprint $table) {
            $table->dropColumn('linked_material_ids');
        });
    }
};
