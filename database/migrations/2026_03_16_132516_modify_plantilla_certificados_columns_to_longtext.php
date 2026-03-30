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
        Schema::table('plantilla_certificados', function (Blueprint $table) {
            $table->longText('html_content')->nullable()->change();
            $table->longText('elementos_json')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_certificados', function (Blueprint $table) {
            $table->text('html_content')->nullable()->change();
            $table->json('elementos_json')->nullable()->change();
        });
    }
};
