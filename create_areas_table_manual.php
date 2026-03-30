<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

echo "🏗️ CREACIÓN MANUAL DE LA TABLA AREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Verificar si la tabla ya existe
    if (Schema::hasTable('areas')) {
        echo "⚠️  La tabla 'areas' ya existe\n";
        
        // Verificar si tiene las columnas necesarias
        $columns = Schema::getColumnListing('areas');
        echo "📋 Columnas actuales: " . implode(', ', $columns) . "\n";
        
        $hasDescripcion = in_array('descripcion', $columns);
        $hasCodCategoria = in_array('cod_categoria', $columns);
        
        echo "   - descripcion: " . ($hasDescripcion ? "✅" : "❌") . "\n";
        echo "   - cod_categoria: " . ($hasCodCategoria ? "✅" : "❌") . "\n";
        
        if ($hasDescripcion && $hasCodCategoria) {
            echo "✅ La tabla ya tiene todas las columnas necesarias\n";
            exit(0);
        }
        
        // Si faltan columnas, las agregamos
        if (!$hasCodCategoria) {
            echo "\n🔧 Agregando columna 'cod_categoria'...\n";
            Schema::table('areas', function (Blueprint $table) {
                $table->unsignedBigInteger('cod_categoria')->after('descripcion');
                $table->foreign('cod_categoria')->references('id')->on('categorias')->onDelete('cascade');
                $table->index('cod_categoria');
            });
            echo "✅ Columna 'cod_categoria' agregada\n";
        }
        
        if (!$hasDescripcion) {
            echo "\n🔧 Agregando columna 'descripcion'...\n";
            Schema::table('areas', function (Blueprint $table) {
                $table->string('descripcion', 100)->after('id');
                $table->index('descripcion');
            });
            echo "✅ Columna 'descripcion' agregada\n";
        }
        
    } else {
        echo "🏗️ Creando tabla 'areas' desde cero...\n";
        
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
        
        echo "✅ Tabla 'areas' creada exitosamente\n";
    }
    
    // 2. Registrar la migración
    echo "\n📝 Registrando migración...\n";
    
    $migrationExists = DB::table('migrations')
        ->where('migration', '2025_06_19_200000_create_areas_table')
        ->exists();
    
    if (!$migrationExists) {
        DB::table('migrations')->insert([
            'migration' => '2025_06_19_200000_create_areas_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
        echo "✅ Migración registrada\n";
    } else {
        echo "✅ Migración ya estaba registrada\n";
    }
    
    // 3. Verificar estructura final
    echo "\n🔍 Verificando estructura final...\n";
    
    $finalColumns = Schema::getColumnListing('areas');
    echo "📋 Columnas finales: " . implode(', ', $finalColumns) . "\n";
    
    // Mostrar estructura detallada
    $tableInfo = DB::select("DESCRIBE areas");
    echo "\n📊 Estructura detallada:\n";
    foreach ($tableInfo as $column) {
        echo "   {$column->Field}: {$column->Type} " . 
             ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column->Key ? " ({$column->Key})" : '') . "\n";
    }
    
    // 4. Verificar claves foráneas
    echo "\n🔗 Verificando claves foráneas...\n";
    
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
    
    // 5. Crear algunos datos de prueba
    echo "\n🧪 Creando datos de prueba...\n";
    
    // Verificar si hay categorías
    $categoriaCount = DB::table('categorias')->count();
    if ($categoriaCount > 0) {
        $categoria = DB::table('categorias')->first();
        
        // Verificar si ya existe un área de prueba
        $areaPruebaExists = DB::table('areas')->where('descripcion', 'Área de Prueba')->exists();
        
        if (!$areaPruebaExists) {
            DB::table('areas')->insert([
                'descripcion' => 'Área de Prueba',
                'cod_categoria' => $categoria->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✅ Área de prueba creada\n";
        } else {
            echo "✅ Área de prueba ya existe\n";
        }
    } else {
        echo "⚠️  No hay categorías disponibles para crear áreas de prueba\n";
    }
    
    $areaCount = DB::table('areas')->count();
    echo "\n📈 Total de áreas en el sistema: {$areaCount}\n";
    
    echo "\n🎉 ¡Tabla 'areas' configurada correctamente!\n";
    echo "🌐 Ahora puedes acceder a: http://127.0.0.1:8000/capacitaciones/areas\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
