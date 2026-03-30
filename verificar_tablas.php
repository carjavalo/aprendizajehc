<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFICACIÓN DEL ESTADO DE LA BASE DE DATOS\n";
echo "=============================================\n\n";

try {
    // Verificar conexión a la base de datos
    echo "📡 Verificando conexión a la base de datos...\n";
    $dbName = config('database.connections.mysql.database');
    $dbHost = config('database.connections.mysql.host');
    echo "   Base de datos: {$dbName}\n";
    echo "   Host: {$dbHost}\n";
    
    DB::connection()->getPdo();
    echo "   ✅ Conexión exitosa\n\n";
    
    // Verificar tabla migrations
    echo "📋 Verificando tabla de migraciones...\n";
    if (Schema::hasTable('migrations')) {
        echo "   ✅ Tabla 'migrations' existe\n";
        $migrationsCount = DB::table('migrations')->count();
        echo "   📊 Total de migraciones registradas: {$migrationsCount}\n";
        
        // Buscar migración específica de areas
        $areasMigration = DB::table('migrations')
            ->where('migration', 'like', '%create_areas_table%')
            ->first();
            
        if ($areasMigration) {
            echo "   ✅ Migración de areas encontrada: {$areasMigration->migration}\n";
        } else {
            echo "   ❌ Migración de areas NO encontrada\n";
        }
    } else {
        echo "   ❌ Tabla 'migrations' NO existe\n";
    }
    echo "\n";
    
    // Verificar tablas específicas
    $tablas = ['categorias', 'areas', 'cursos'];
    
    foreach ($tablas as $tabla) {
        echo "📋 Verificando tabla '{$tabla}'...\n";
        
        if (Schema::hasTable($tabla)) {
            echo "   ✅ La tabla existe\n";
            
            // Contar registros
            $count = DB::table($tabla)->count();
            echo "   📊 Registros: {$count}\n";
            
            // Mostrar columnas
            $columns = Schema::getColumnListing($tabla);
            echo "   🏗️ Columnas: " . implode(', ', $columns) . "\n";
        } else {
            echo "   ❌ La tabla NO existe\n";
        }
        echo "\n";
    }
    
    // Verificar modelos
    echo "🏗️ Verificando modelos Eloquent...\n";
    
    try {
        $categoria = new \App\Models\Categoria();
        echo "   ✅ Modelo Categoria cargado correctamente\n";
    } catch (Exception $e) {
        echo "   ❌ Error con modelo Categoria: " . $e->getMessage() . "\n";
    }
    
    try {
        $area = new \App\Models\Area();
        echo "   ✅ Modelo Area cargado correctamente\n";
    } catch (Exception $e) {
        echo "   ❌ Error con modelo Area: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 RESUMEN:\n";
    echo "===========\n";
    
    $areasExiste = Schema::hasTable('areas');
    $categoriasExiste = Schema::hasTable('categorias');
    
    if ($areasExiste && $categoriasExiste) {
        echo "✅ Todas las tablas necesarias existen\n";
        echo "🌐 La aplicación debería funcionar correctamente\n";
    } else {
        echo "❌ Faltan tablas por crear:\n";
        if (!$categoriasExiste) echo "   - categorias\n";
        if (!$areasExiste) echo "   - areas\n";
        echo "💡 Ejecutar: php artisan migrate\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
