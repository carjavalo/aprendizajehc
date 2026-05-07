<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cargo_id')) {
                $table->unsignedBigInteger('cargo_id')->nullable()->after('vinculacion_contrato_id');
                $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cargo_id')) {
                $table->dropForeign(['cargo_id']);
                $table->dropColumn('cargo_id');
            }
        });
    }
};
