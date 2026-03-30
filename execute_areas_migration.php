<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 EJECUTAR MIGRACIÓN DE TABLA AREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Verificar si la tabla areas existe
    echo "1. 🔍 Verificando estado actual de la tabla 'areas':\n";
    
    $tableExists = Schema::hasTable('areas');
    echo "   - Tabla 'areas' existe: " . ($tableExists ? "✅ SÍ" : "❌ NO") . "\n";
    
    if ($tableExists) {
        // Verificar columnas existentes
        $columns = Schema::getColumnListing('areas');
        echo "   - Columnas existentes: " . implode(', ', $columns) . "\n";
        
        // Verificar específicamente la columna descripcion
        $hasDescripcion = Schema::hasColumn('areas', 'descripcion');
        echo "   - Columna 'descripcion' existe: " . ($hasDescripcion ? "✅ SÍ" : "❌ NO") . "\n";
        
        if ($hasDescripcion) {
            echo "\n✅ La tabla 'areas' ya tiene la columna 'descripcion'. No se requiere migración.\n";
            
            // Mostrar estructura de la tabla
            $tableInfo = DB::select("DESCRIBE areas");
            echo "\n📋 Estructura actual de la tabla 'areas':\n";
            foreach ($tableInfo as $column) {
                echo "   - {$column->Field}: {$column->Type} " . 
                     ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
                     ($column->Key ? " ({$column->Key})" : '') . "\n";
            }
            
            // Contar registros
            $count = DB::table('areas')->count();
            echo "\n📊 Registros en la tabla: {$count}\n";
            
            exit(0);
        }
    }
    
    // 2. Crear la tabla si no existe
    echo "\n2. 🏗️ Creando/actualizando tabla 'areas':\n";
    
    if (!$tableExists) {
        echo "   - Creando tabla 'areas' desde cero...\n";
        
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 100);
            $table->unsignedBigInteger('cod_categoria');
            $table->timestamps();

            // Definir clave foránea con restricciones de integridad
            $table->foreign('cod_categoria')
                  ->references('id')
                  ->on('categorias')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Índices para optimizar consultas
            $table->index('cod_categoria');
            $table->index('descripcion');
        });
        
        echo "   ✅ Tabla 'areas' creada exitosamente\n";
    } else {
        echo "   - Agregando columna 'descripcion' a tabla existente...\n";
        
        Schema::table('areas', function (Blueprint $table) {
            $table->string('descripcion', 100)->after('id');
            $table->index('descripcion');
        });
        
        echo "   ✅ Columna 'descripcion' agregada exitosamente\n";
    }
    
    // 3. Verificar la migración en la tabla migrations
    echo "\n3. 📝 Actualizando registro de migraciones:\n";
    
    $migrationExists = DB::table('migrations')
        ->where('migration', '2025_06_19_200000_create_areas_table')
        ->exists();
    
    if (!$migrationExists) {
        DB::table('migrations')->insert([
            'migration' => '2025_06_19_200000_create_areas_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
        echo "   ✅ Registro de migración agregado\n";
    } else {
        echo "   ✅ Registro de migración ya existe\n";
    }
    
    // 4. Verificar estructura final
    echo "\n4. ✅ Verificando estructura final:\n";
    
    $finalColumns = Schema::getColumnListing('areas');
    echo "   - Columnas finales: " . implode(', ', $finalColumns) . "\n";
    
    $tableInfo = DB::select("DESCRIBE areas");
    echo "\n📋 Estructura final de la tabla 'areas':\n";
    foreach ($tableInfo as $column) {
        echo "   - {$column->Field}: {$column->Type} " . 
             ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column->Key ? " ({$column->Key})" : '') . "\n";
    }
    
    // 5. Verificar claves foráneas
    echo "\n5. 🔗 Verificando claves foráneas:\n";
    
    $foreignKeys = DB::select("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'areas' 
        AND TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if (!empty($foreignKeys)) {
        foreach ($foreignKeys as $fk) {
            echo "   ✅ {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    } else {
        echo "   ⚠️  No se encontraron claves foráneas\n";
    }
    
    echo "\n🎉 ¡Migración de tabla 'areas' completada exitosamente!\n";
    echo "🌐 Ahora puedes acceder a: http://127.0.0.1:8000/capacitaciones/areas\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la migración: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
    
    // Mostrar información adicional para debugging
    echo "\n🔍 Información adicional:\n";
    echo "   - Base de datos: " . config('database.default') . "\n";
    echo "   - Host: " . config('database.connections.mysql.host') . "\n";
    echo "   - Database: " . config('database.connections.mysql.database') . "\n";
}
