<?php
/**
 * Script para crear la tabla welcome_banners en producción (cPanel).
 * Subir a public/ y ejecutar: https://localhost/AprendizajeHC/crear_welcome_banners.php
 * Eliminar después de usar.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "<h2>Crear tabla welcome_banners</h2>";

if (Schema::hasTable('welcome_banners')) {
    echo "<p style='color:green;'>✅ La tabla <strong>welcome_banners</strong> ya existe.</p>";
} else {
    Schema::create('welcome_banners', function (Blueprint $table) {
        $table->id();
        $table->string('banner_titulo')->nullable();
        $table->string('banner_subtitulo')->nullable();
        $table->string('banner_color_fondo', 20)->default('#2c4370');
        $table->string('banner_color_texto', 20)->default('#ffffff');
        $table->enum('media_tipo', ['video', 'imagen'])->default('video');
        $table->string('media_archivo')->nullable();
        $table->string('media_url')->nullable();
        $table->string('media_titulo')->nullable();
        $table->boolean('activo')->default(true);
        $table->integer('orden')->default(0);
        $table->timestamps();
    });
    echo "<p style='color:green;'>✅ Tabla <strong>welcome_banners</strong> creada exitosamente.</p>";
}

// Limpiar cachés
Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('route:clear');
Artisan::call('view:clear');
echo "<p style='color:blue;'>🔄 Cachés limpiadas.</p>";
echo "<p style='color:red;'><strong>⚠️ ELIMINA ESTE ARCHIVO del servidor después de usarlo.</strong></p>";
