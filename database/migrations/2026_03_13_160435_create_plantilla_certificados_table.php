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
        Schema::create('plantilla_certificados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('fondo_path')->nullable();
            $table->longText('elementos_json')->nullable(); // JSON with positions, texts, logos, fondo_base64
            $table->longText('html_content')->nullable(); // Cached HTML (can be very large with inline images)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_certificados');
    }
};
