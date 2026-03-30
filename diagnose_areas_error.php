<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\Categoria;

echo "🔍 DIAGNÓSTICO COMPLETO DEL ERROR AL GUARDAR ÁREAS\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Verificar estructura de la tabla areas
    echo "1. 📋 Verificando estructura de la tabla 'areas':\n";
    
    if (!Schema::hasTable('areas')) {
        echo "   ❌ La tabla 'areas' NO EXISTE\n";
        echo "   💡 SOLUCIÓN: Crear la tabla areas\n\n";
        
        // Crear la tabla
        echo "   🏗️ Creando tabla 'areas'...\n";
        Schema::create('areas', function ($table) {
            $table->id();
            $table->string('descripcion', 100);
            $table->unsignedBigInteger('cod_categoria');
            $table->timestamps();
            $table->foreign('cod_categoria')->references('id')->on('categorias')->onDelete('cascade');
            $table->index('cod_categoria');
            $table->index('descripcion');
        });
        echo "   ✅ Tabla 'areas' creada\n";
        
        // Registrar migración
        DB::table('migrations')->insert([
            'migration' => '2025_06_19_200000_create_areas_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
        echo "   ✅ Migración registrada\n";
    } else {
        echo "   ✅ La tabla 'areas' existe\n";
    }
    
    // Verificar columnas
    $columns = Schema::getColumnListing('areas');
    echo "   📊 Columnas: " . implode(', ', $columns) . "\n";
    
    $requiredColumns = ['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo "   ❌ Columnas faltantes: " . implode(', ', $missingColumns) . "\n";
        
        // Agregar columnas faltantes
        Schema::table('areas', function ($table) use ($missingColumns) {
            if (in_array('descripcion', $missingColumns)) {
                $table->string('descripcion', 100)->after('id');
            }
            if (in_array('cod_categoria', $missingColumns)) {
                $table->unsignedBigInteger('cod_categoria')->after('descripcion');
                $table->foreign('cod_categoria')->references('id')->on('categorias')->onDelete('cascade');
            }
        });
        echo "   ✅ Columnas faltantes agregadas\n";
    } else {
        echo "   ✅ Todas las columnas requeridas están presentes\n";
    }
    
    // 2. Verificar tabla categorias
    echo "\n2. 📋 Verificando tabla 'categorias':\n";
    
    if (!Schema::hasTable('categorias')) {
        echo "   ❌ La tabla 'categorias' NO EXISTE\n";
        echo "   💡 PROBLEMA: No se pueden crear áreas sin categorías\n";
        exit(1);
    }
    
    $categoriaCount = Categoria::count();
    echo "   ✅ Tabla 'categorias' existe con {$categoriaCount} registros\n";
    
    if ($categoriaCount === 0) {
        echo "   ⚠️  No hay categorías disponibles\n";
        echo "   💡 Creando categorías de prueba...\n";
        
        $categoriasPrueba = [
            'Medicina General',
            'Pediatría',
            'Ginecología',
            'Cardiología'
        ];
        
        foreach ($categoriasPrueba as $cat) {
            Categoria::create(['descripcion' => $cat]);
        }
        
        echo "   ✅ " . count($categoriasPrueba) . " categorías de prueba creadas\n";
    }
    
    // 3. Verificar modelo Area
    echo "\n3. 🏗️ Verificando modelo Area:\n";
    
    $area = new Area();
    $fillable = $area->getFillable();
    echo "   📋 Campos fillable: " . implode(', ', $fillable) . "\n";
    
    $requiredFillable = ['descripcion', 'cod_categoria'];
    $missingFillable = array_diff($requiredFillable, $fillable);
    
    if (!empty($missingFillable)) {
        echo "   ❌ Campos faltantes en fillable: " . implode(', ', $missingFillable) . "\n";
    } else {
        echo "   ✅ Todos los campos requeridos están en fillable\n";
    }
    
    // 4. Probar creación de área
    echo "\n4. 🧪 Probando creación de área:\n";
    
    $categoria = Categoria::first();
    if ($categoria) {
        echo "   📋 Usando categoría: {$categoria->descripcion} (ID: {$categoria->id})\n";
        
        try {
            // Intentar crear área de prueba
            $areaPrueba = Area::create([
                'descripcion' => 'Área de Prueba - ' . date('Y-m-d H:i:s'),
                'cod_categoria' => $categoria->id
            ]);
            
            echo "   ✅ Área creada exitosamente: ID {$areaPrueba->id}\n";
            
            // Verificar relación
            $areaPrueba->load('categoria');
            echo "   ✅ Relación con categoría funciona: {$areaPrueba->categoria->descripcion}\n";
            
            // Eliminar área de prueba
            $areaPrueba->delete();
            echo "   ✅ Área de prueba eliminada\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error al crear área: " . $e->getMessage() . "\n";
            echo "   📍 Línea: " . $e->getLine() . " en " . $e->getFile() . "\n";
        }
    } else {
        echo "   ❌ No hay categorías disponibles para la prueba\n";
    }
    
    // 5. Verificar validaciones del controlador
    echo "\n5. 🎛️ Verificando controlador AreaController:\n";
    
    $controllerPath = app_path('Http/Controllers/AreaController.php');
    if (file_exists($controllerPath)) {
        echo "   ✅ Controlador existe\n";
        
        $controllerContent = file_get_contents($controllerPath);
        
        // Verificar método store
        if (strpos($controllerContent, 'function store(') !== false) {
            echo "   ✅ Método store() existe\n";
            
            // Verificar validaciones
            if (strpos($controllerContent, "'descripcion' => 'required") !== false) {
                echo "   ✅ Validación de descripción configurada\n";
            } else {
                echo "   ❌ Validación de descripción faltante\n";
            }
            
            if (strpos($controllerContent, "'cod_categoria' => 'required") !== false) {
                echo "   ✅ Validación de cod_categoria configurada\n";
            } else {
                echo "   ❌ Validación de cod_categoria faltante\n";
            }
        } else {
            echo "   ❌ Método store() no encontrado\n";
        }
    } else {
        echo "   ❌ Controlador no encontrado\n";
    }
    
    // 6. Verificar rutas
    echo "\n6. 🛣️ Verificando rutas:\n";
    
    try {
        $storeRoute = route('capacitaciones.areas.store');
        echo "   ✅ Ruta store: {$storeRoute}\n";
        
        $indexRoute = route('capacitaciones.areas.index');
        echo "   ✅ Ruta index: {$indexRoute}\n";
    } catch (Exception $e) {
        echo "   ❌ Error en rutas: " . $e->getMessage() . "\n";
    }
    
    // 7. Verificar permisos de base de datos
    echo "\n7. 🔐 Verificando permisos de base de datos:\n";
    
    try {
        // Intentar operaciones básicas
        DB::table('areas')->count();
        echo "   ✅ Permiso de lectura: OK\n";
        
        $testId = DB::table('areas')->insertGetId([
            'descripcion' => 'Test Permission',
            'cod_categoria' => $categoria->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "   ✅ Permiso de escritura: OK\n";
        
        DB::table('areas')->where('id', $testId)->delete();
        echo "   ✅ Permiso de eliminación: OK\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error de permisos: " . $e->getMessage() . "\n";
    }
    
    echo "\n📊 RESUMEN DEL DIAGNÓSTICO:\n";
    echo "   - Tabla 'areas': " . (Schema::hasTable('areas') ? "✅" : "❌") . "\n";
    echo "   - Tabla 'categorias': " . (Schema::hasTable('categorias') ? "✅" : "❌") . "\n";
    echo "   - Categorías disponibles: " . Categoria::count() . "\n";
    echo "   - Áreas existentes: " . (Schema::hasTable('areas') ? Area::count() : 0) . "\n";
    
    echo "\n🎯 PRÓXIMOS PASOS:\n";
    echo "1. Verificar que la tabla 'areas' esté correctamente creada\n";
    echo "2. Probar crear una nueva área desde la interfaz web\n";
    echo "3. Revisar logs de Laravel si persisten errores\n";
    echo "4. Verificar JavaScript en la consola del navegador\n";
    
    echo "\n🌐 ACCEDER AL SISTEMA:\n";
    echo "URL: http://127.0.0.1:8000/capacitaciones/areas\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el diagnóstico: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
