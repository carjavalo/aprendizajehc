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
        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->unsignedBigInteger('prerequisite_id')->nullable()->after('es_publico');
            
            // Agregar índice para mejorar rendimiento
            $table->index('prerequisite_id');
            
            // Agregar foreign key (opcional, comentar si causa problemas)
            $table->foreign('prerequisite_id')
                  ->references('id')
                  ->on('curso_materiales')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_materiales', function (Blueprint $table) {
            $table->dropForeign(['prerequisite_id']);
            $table->dropIndex(['prerequisite_id']);
            $table->dropColumn('prerequisite_id');
        });
    }
};
