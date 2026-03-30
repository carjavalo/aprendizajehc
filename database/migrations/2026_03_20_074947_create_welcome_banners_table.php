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
        if (Schema::hasTable('welcome_banners')) {
            return;
        }

        Schema::create('welcome_banners', function (Blueprint $table) {
            $table->id();
            $table->string('banner_titulo')->nullable();
            $table->string('banner_subtitulo')->nullable();
            $table->string('banner_color_fondo', 20)->default('#2c4370');
            $table->string('banner_color_texto', 20)->default('#ffffff');
            $table->enum('media_tipo', ['video', 'imagen'])->default('video');
            $table->string('media_archivo')->nullable(); // ruta del archivo subido
            $table->string('media_url')->nullable(); // URL externa (YouTube, etc.)
            $table->string('media_titulo')->nullable(); // título descriptivo del media
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_banners');
    }
};
