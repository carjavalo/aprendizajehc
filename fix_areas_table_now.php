<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Categoria;

echo "🔧 REPARACIÓN INMEDIATA DE LA TABLA AREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Verificar y crear tabla areas
    echo "1. 🏗️ Verificando/creando tabla 'areas'...\n";
    
    if (Schema::hasTable('areas')) {
        echo "   ✅ La tabla 'areas' ya existe\n";
        
        // Verificar columnas
        $columns = Schema::getColumnListing('areas');
        echo "   📋 Columnas actuales: " . implode(', ', $columns) . "\n";
        
        $requiredColumns = ['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            echo "   ⚠️  Columnas faltantes: " . implode(', ', $missingColumns) . "\n";
            echo "   🔧 Agregando columnas faltantes...\n";
            
            Schema::table('areas', function (Blueprint $table) use ($missingColumns) {
                if (in_array('descripcion', $missingColumns)) {
                    $table->string('descripcion', 100)->after('id');
                    $table->index('descripcion');
                }
                if (in_array('cod_categoria', $missingColumns)) {
                    $table->unsignedBigInteger('cod_categoria')->after('descripcion');
                    $table->foreign('cod_categoria')->references('id')->on('categorias')->onDelete('cascade');
                    $table->index('cod_categoria');
                }
            });
            echo "   ✅ Columnas agregadas\n";
        } else {
            echo "   ✅ Todas las columnas están presentes\n";
        }
    } else {
        echo "   🏗️ Creando tabla 'areas'...\n";
        
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 100);
            $table->unsignedBigInteger('cod_categoria');
            $table->timestamps();

            // Clave foránea
            $table->foreign('cod_categoria')
                  ->references('id')
                  ->on('categorias')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Índices
            $table->index('cod_categoria');
            $table->index('descripcion');
        });
        
        echo "   ✅ Tabla 'areas' creada\n";
    }
    
    // 2. Verificar tabla categorias
    echo "\n2. 📋 Verificando tabla 'categorias'...\n";
    
    if (!Schema::hasTable('categorias')) {
        echo "   ❌ La tabla 'categorias' no existe\n";
        echo "   🏗️ Creando tabla 'categorias'...\n";
        
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 100);
            $table->timestamps();
            $table->index('descripcion');
        });
        
        echo "   ✅ Tabla 'categorias' creada\n";
    }
    
    $categoriaCount = DB::table('categorias')->count();
    echo "   📊 Categorías existentes: {$categoriaCount}\n";
    
    // 3. Crear categorías de prueba si no existen
    if ($categoriaCount === 0) {
        echo "\n3. 🧪 Creando categorías de prueba...\n";
        
        $categoriasPrueba = [
            'Medicina General',
            'Pediatría',
            'Ginecología',
            'Cardiología',
            'Neurología',
            'Dermatología',
            'Oftalmología',
            'Traumatología'
        ];
        
        foreach ($categoriasPrueba as $cat) {
            DB::table('categorias')->insert([
                'descripcion' => $cat,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        echo "   ✅ " . count($categoriasPrueba) . " categorías creadas\n";
    }
    
    // 4. Registrar migración
    echo "\n4. 📝 Registrando migración...\n";
    
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
    
    // 5. Crear áreas de prueba
    echo "\n5. 🧪 Creando áreas de prueba...\n";
    
    $areaCount = DB::table('areas')->count();
    
    if ($areaCount === 0) {
        $categorias = DB::table('categorias')->get();
        
        if ($categorias->count() > 0) {
            $areasPrueba = [
                'Consulta Externa',
                'Urgencias',
                'Hospitalización',
                'Cirugía General',
                'Laboratorio Clínico',
                'Radiología',
                'Farmacia',
                'Rehabilitación'
            ];
            
            foreach ($areasPrueba as $index => $area) {
                $categoria = $categorias[$index % $categorias->count()];
                
                DB::table('areas')->insert([
                    'descripcion' => $area,
                    'cod_categoria' => $categoria->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            echo "   ✅ " . count($areasPrueba) . " áreas de prueba creadas\n";
        }
    } else {
        echo "   ✅ Ya existen {$areaCount} áreas\n";
    }
    
    // 6. Verificar estructura final
    echo "\n6. ✅ Verificación final...\n";
    
    $finalColumns = DB::select("DESCRIBE areas");
    echo "   📋 Estructura de la tabla 'areas':\n";
    foreach ($finalColumns as $column) {
        echo "      - {$column->Field}: {$column->Type} " . 
             ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
             ($column->Key ? " ({$column->Key})" : '') . "\n";
    }
    
    // Verificar claves foráneas
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
    
    echo "\n   🔗 Claves foráneas:\n";
    if (!empty($foreignKeys)) {
        foreach ($foreignKeys as $fk) {
            echo "      ✅ {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    } else {
        echo "      ⚠️  No se encontraron claves foráneas\n";
    }
    
    // 7. Estadísticas finales
    echo "\n7. 📊 Estadísticas finales:\n";
    
    $finalAreaCount = DB::table('areas')->count();
    $finalCategoriaCount = DB::table('categorias')->count();
    
    echo "   - Total de categorías: {$finalCategoriaCount}\n";
    echo "   - Total de áreas: {$finalAreaCount}\n";
    
    if ($finalAreaCount > 0) {
        echo "\n   📋 Áreas de ejemplo:\n";
        $sampleAreas = DB::table('areas')
            ->join('categorias', 'areas.cod_categoria', '=', 'categorias.id')
            ->select('areas.id', 'areas.descripcion', 'categorias.descripcion as categoria')
            ->limit(5)
            ->get();
        
        foreach ($sampleAreas as $area) {
            echo "      - ID: {$area->id}, Área: {$area->descripcion}, Categoría: {$area->categoria}\n";
        }
    }
    
    echo "\n🎉 ¡TABLA 'AREAS' REPARADA Y LISTA!\n";
    echo "🌐 Accede a: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "💡 Ahora deberías poder crear y editar áreas sin errores\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la reparación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
