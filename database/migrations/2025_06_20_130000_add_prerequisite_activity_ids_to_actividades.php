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
            if (!Schema::hasColumn('curso_actividades', 'prerequisite_activity_ids')) {
                $table->json('prerequisite_activity_ids')->nullable()->after('linked_material_ids')
                    ->comment('IDs de actividades que deben completarse antes de esta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_actividades', function (Blueprint $table) {
            if (Schema::hasColumn('curso_actividades', 'prerequisite_activity_ids')) {
                $table->dropColumn('prerequisite_activity_ids');
            }
        });
    }
};
