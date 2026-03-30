<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🗄️ EJECUTAR SQL PARA CREAR TABLA AREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Verificar conexión a la base de datos
    echo "1. 🔌 Verificando conexión a la base de datos...\n";
    $connection = DB::connection();
    $databaseName = $connection->getDatabaseName();
    echo "   ✅ Conectado a: {$databaseName}\n\n";
    
    // 2. Verificar si la tabla areas ya existe
    echo "2. 🔍 Verificando si la tabla 'areas' existe...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'areas'");
    
    if (!empty($tableExists)) {
        echo "   ⚠️  La tabla 'areas' ya existe\n";
        
        // Verificar estructura
        $columns = DB::select("DESCRIBE areas");
        echo "   📋 Columnas actuales:\n";
        foreach ($columns as $column) {
            echo "      - {$column->Field}: {$column->Type}\n";
        }
        
        // Verificar si tiene las columnas necesarias
        $columnNames = array_column($columns, 'Field');
        $hasDescripcion = in_array('descripcion', $columnNames);
        $hasCodCategoria = in_array('cod_categoria', $columnNames);
        
        if ($hasDescripcion && $hasCodCategoria) {
            echo "   ✅ La tabla ya tiene todas las columnas necesarias\n";
            
            // Contar registros
            $count = DB::table('areas')->count();
            echo "   📊 Registros existentes: {$count}\n";
            
            if ($count === 0) {
                echo "\n3. 🧪 Creando datos de prueba...\n";
                // Crear datos de prueba
                $categorias = DB::table('categorias')->limit(3)->get();
                
                if ($categorias->count() > 0) {
                    foreach ($categorias as $index => $categoria) {
                        DB::table('areas')->insert([
                            'descripcion' => "Área de Prueba " . ($index + 1),
                            'cod_categoria' => $categoria->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    echo "   ✅ Datos de prueba creados\n";
                } else {
                    echo "   ⚠️  No hay categorías disponibles\n";
                }
            }
            
            echo "\n🎉 ¡La tabla 'areas' está lista!\n";
            echo "🌐 Accede a: http://127.0.0.1:8000/capacitaciones/areas\n";
            exit(0);
        }
        
        echo "   ⚠️  Faltan columnas, eliminando tabla para recrearla...\n";
        DB::statement("DROP TABLE IF EXISTS areas");
    }
    
    // 3. Verificar que existe la tabla categorias
    echo "\n3. 🔍 Verificando tabla 'categorias'...\n";
    $categoriasExists = DB::select("SHOW TABLES LIKE 'categorias'");
    
    if (empty($categoriasExists)) {
        echo "   ❌ La tabla 'categorias' no existe\n";
        echo "   💡 Debe crear la tabla 'categorias' primero\n";
        exit(1);
    }
    
    $categoriaCount = DB::table('categorias')->count();
    echo "   ✅ Tabla 'categorias' existe con {$categoriaCount} registros\n";
    
    // 4. Crear la tabla areas
    echo "\n4. 🏗️ Creando tabla 'areas'...\n";
    
    $createTableSQL = "
        CREATE TABLE `areas` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `cod_categoria` bigint(20) UNSIGNED NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `areas_cod_categoria_index` (`cod_categoria`),
            KEY `areas_descripcion_index` (`descripcion`),
            CONSTRAINT `areas_cod_categoria_foreign` FOREIGN KEY (`cod_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    DB::statement($createTableSQL);
    echo "   ✅ Tabla 'areas' creada exitosamente\n";
    
    // 5. Insertar datos de prueba
    echo "\n5. 🧪 Insertando datos de prueba...\n";
    
    $categorias = DB::table('categorias')->limit(5)->get();
    
    if ($categorias->count() > 0) {
        $areasData = [
            'Consulta Externa',
            'Urgencias',
            'Hospitalización',
            'Cirugía',
            'Laboratorio'
        ];
        
        foreach ($areasData as $index => $areaDesc) {
            $categoria = $categorias[$index % $categorias->count()];
            
            DB::table('areas')->insert([
                'descripcion' => $areaDesc,
                'cod_categoria' => $categoria->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        echo "   ✅ " . count($areasData) . " áreas de prueba insertadas\n";
    } else {
        echo "   ⚠️  No hay categorías disponibles para crear áreas\n";
    }
    
    // 6. Registrar la migración
    echo "\n6. 📝 Registrando migración...\n";
    
    $migrationExists = DB::table('migrations')
        ->where('migration', '2025_06_19_200000_create_areas_table')
        ->exists();
    
    if (!$migrationExists) {
        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        
        DB::table('migrations')->insert([
            'migration' => '2025_06_19_200000_create_areas_table',
            'batch' => $maxBatch + 1
        ]);
        
        echo "   ✅ Migración registrada\n";
    } else {
        echo "   ✅ Migración ya estaba registrada\n";
    }
    
    // 7. Verificar resultado final
    echo "\n7. ✅ Verificación final...\n";
    
    $finalColumns = DB::select("DESCRIBE areas");
    echo "   📋 Estructura final:\n";
    foreach ($finalColumns as $column) {
        echo "      - {$column->Field}: {$column->Type} " . 
             ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column->Key ? " ({$column->Key})" : '') . "\n";
    }
    
    $finalCount = DB::table('areas')->count();
    echo "\n   📊 Total de áreas: {$finalCount}\n";
    
    // Mostrar algunas áreas de ejemplo
    if ($finalCount > 0) {
        echo "\n   📋 Áreas de ejemplo:\n";
        $sampleAreas = DB::table('areas')
            ->join('categorias', 'areas.cod_categoria', '=', 'categorias.id')
            ->select('areas.id', 'areas.descripcion', 'categorias.descripcion as categoria')
            ->limit(3)
            ->get();
        
        foreach ($sampleAreas as $area) {
            echo "      - ID: {$area->id}, Área: {$area->descripcion}, Categoría: {$area->categoria}\n";
        }
    }
    
    echo "\n🎉 ¡TABLA 'AREAS' CREADA Y CONFIGURADA EXITOSAMENTE!\n";
    echo "🌐 Ahora puedes acceder a: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "📊 Total de áreas disponibles: {$finalCount}\n";
    echo "📊 Total de categorías disponibles: {$categoriaCount}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
    
    // Información adicional para debugging
    echo "\n🔍 Información de debugging:\n";
    echo "   - Base de datos: " . config('database.default') . "\n";
    echo "   - Host: " . config('database.connections.mysql.host') . "\n";
    echo "   - Database: " . config('database.connections.mysql.database') . "\n";
}
