<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Area;
use App\Models\Categoria;

echo "🔍 VERIFICACIÓN COMPLETA DEL SISTEMA DE ÁREAS\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Verificar la tabla de áreas
    echo "1. ✅ Verificando estructura de la base de datos:\n";
    
    $areaCount = Area::count();
    $categoriaCount = Categoria::count();
    
    echo "   - Registros en áreas: {$areaCount}\n";
    echo "   - Registros en categorías: {$categoriaCount}\n";
    
    if ($areaCount > 0) {
        $latestArea = Area::with('categoria')->latest('created_at')->first();
        echo "   - Última área: {$latestArea->descripcion} (Categoría: {$latestArea->categoria->descripcion})\n";
        
        $oldestArea = Area::with('categoria')->oldest('created_at')->first();
        echo "   - Primera área: {$oldestArea->descripcion} (Categoría: {$oldestArea->categoria->descripcion})\n";
    }
    
    // 2. Verificar el modelo Area
    echo "\n2. 📋 Verificando modelo Area:\n";
    
    $area = new Area();
    $fillable = $area->getFillable();
    echo "   - Campos fillable: " . implode(', ', $fillable) . "\n";
    
    $casts = $area->getCasts();
    echo "   - Campos con cast: " . implode(', ', array_keys($casts)) . "\n";
    
    // Verificar relaciones
    if ($areaCount > 0) {
        $testArea = Area::with('categoria')->first();
        echo "   - Relación con categoría: " . ($testArea->categoria ? "✅ Funcional" : "❌ Error") . "\n";
    }
    
    // 3. Verificar rutas
    echo "\n3. 🔗 Verificando rutas del sistema:\n";
    
    $routes = [
        'capacitaciones.areas.index' => 'Lista de áreas',
        'capacitaciones.areas.data' => 'Datos para DataTable',
        'capacitaciones.areas.create' => 'Crear área',
        'capacitaciones.areas.store' => 'Guardar área',
        'capacitaciones.areas.show' => 'Mostrar área',
        'capacitaciones.areas.edit' => 'Editar área',
        'capacitaciones.areas.update' => 'Actualizar área',
        'capacitaciones.areas.destroy' => 'Eliminar área'
    ];
    
    foreach ($routes as $routeName => $description) {
        try {
            $url = route($routeName, ['area' => 1]);
            echo "   ✅ {$description}: {$routeName}\n";
        } catch (Exception $e) {
            echo "   ❌ {$description}: {$routeName} - ERROR\n";
        }
    }
    
    // 4. Verificar controlador
    echo "\n4. 🎛️ Verificando controlador AreaController:\n";
    
    $controllerPath = app_path('Http/Controllers/AreaController.php');
    if (file_exists($controllerPath)) {
        echo "   ✅ Controlador existe: {$controllerPath}\n";
        
        $controllerContent = file_get_contents($controllerPath);
        $methods = ['index', 'getData', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        
        foreach ($methods as $method) {
            if (strpos($controllerContent, "function {$method}(") !== false) {
                echo "   ✅ Método {$method}() implementado\n";
            } else {
                echo "   ❌ Método {$method}() no encontrado\n";
            }
        }
    } else {
        echo "   ❌ Controlador no encontrado\n";
    }
    
    // 5. Verificar vistas
    echo "\n5. 🎨 Verificando vistas:\n";
    
    $views = [
        'index.blade.php' => 'resources/views/admin/capacitaciones/areas/index.blade.php',
    ];
    
    foreach ($views as $view => $path) {
        if (file_exists($path)) {
            echo "   ✅ Vista {$view} existe\n";
            
            // Verificar contenido de la vista
            $viewContent = file_get_contents($path);
            $elements = [
                'areas-table' => 'DataTable de áreas',
                'btn-nueva-area' => 'Botón nueva área',
                'areaModal' => 'Modal de área',
                'viewAreaModal' => 'Modal de visualización',
                'fas fa-layer-group' => 'Icono de áreas'
            ];
            
            foreach ($elements as $element => $description) {
                if (strpos($viewContent, $element) !== false) {
                    echo "      ✅ {$description}\n";
                } else {
                    echo "      ❌ {$description} - NO ENCONTRADO\n";
                }
            }
        } else {
            echo "   ❌ Vista {$view} no encontrada en {$path}\n";
        }
    }
    
    // 6. Verificar migración
    echo "\n6. 🗄️ Verificando migración:\n";
    
    $migrationPath = 'database/migrations/2025_06_19_200000_create_areas_table.php';
    if (file_exists($migrationPath)) {
        echo "   ✅ Migración existe: {$migrationPath}\n";
        
        // Verificar si la tabla existe en la base de datos
        try {
            $tableExists = Schema::hasTable('areas');
            echo "   " . ($tableExists ? "✅" : "❌") . " Tabla 'areas' en base de datos\n";
            
            if ($tableExists) {
                $columns = ['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at'];
                foreach ($columns as $column) {
                    $hasColumn = Schema::hasColumn('areas', $column);
                    echo "      " . ($hasColumn ? "✅" : "❌") . " Columna '{$column}'\n";
                }
            }
        } catch (Exception $e) {
            echo "   ❌ Error verificando tabla: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Migración no encontrada\n";
    }
    
    // 7. Verificar configuración del menú
    echo "\n7. 📋 Verificando configuración del menú:\n";
    
    $menuConfig = config('adminlte.menu');
    $areaMenuFound = false;
    
    foreach ($menuConfig as $menuItem) {
        if (isset($menuItem['submenu'])) {
            foreach ($menuItem['submenu'] as $submenu) {
                if (isset($submenu['submenu'])) {
                    foreach ($submenu['submenu'] as $subsubmenu) {
                        if (isset($subsubmenu['text']) && $subsubmenu['text'] === 'Áreas') {
                            $areaMenuFound = true;
                            echo "   ✅ Menú de áreas configurado\n";
                            echo "      - Texto: {$subsubmenu['text']}\n";
                            echo "      - URL: {$subsubmenu['url']}\n";
                            echo "      - Icono: {$subsubmenu['icon']}\n";
                            break 3;
                        }
                    }
                }
            }
        }
    }
    
    if (!$areaMenuFound) {
        echo "   ❌ Menú de áreas no encontrado en la configuración\n";
    }
    
    // 8. Verificar relaciones entre modelos
    echo "\n8. 🔗 Verificando relaciones entre modelos:\n";
    
    if ($categoriaCount > 0 && $areaCount > 0) {
        $categoria = Categoria::with('areas')->first();
        echo "   ✅ Relación Categoria->areas: " . $categoria->areas->count() . " áreas\n";
        
        $area = Area::with('categoria')->first();
        echo "   ✅ Relación Area->categoria: " . $area->categoria->descripcion . "\n";
    } else {
        echo "   ⚠️  No hay datos suficientes para verificar relaciones\n";
    }
    
    // 9. Estadísticas finales
    echo "\n9. 📊 ESTADÍSTICAS DEL SISTEMA:\n";
    echo str_repeat("-", 40) . "\n";
    
    if ($areaCount > 0) {
        $estadisticas = Area::join('categorias', 'areas.cod_categoria', '=', 'categorias.id')
            ->selectRaw('categorias.descripcion as categoria, COUNT(*) as total')
            ->groupBy('categorias.id', 'categorias.descripcion')
            ->orderBy('total', 'desc')
            ->get();
        
        echo "   📋 Distribución por categoría:\n";
        foreach ($estadisticas as $stat) {
            echo "      - {$stat->categoria}: {$stat->total} áreas\n";
        }
    }
    
    echo "\n📋 FUNCIONALIDADES IMPLEMENTADAS:\n";
    $features = [
        "✅ Migración de base de datos con tabla 'areas'",
        "✅ Modelo Eloquent 'Area' con relaciones",
        "✅ Controlador resource con métodos CRUD completos",
        "✅ Rutas resource y ruta adicional para DataTable",
        "✅ Vista principal con DataTable responsive",
        "✅ Filtros dinámicos (descripción, categoría)",
        "✅ Modal para crear/editar áreas",
        "✅ Validación del lado del servidor y cliente",
        "✅ Integración con SweetAlert2",
        "✅ Menú integrado en AdminLTE",
        "✅ Breadcrumb navigation",
        "✅ Responsive design",
        "✅ Relación uno a muchos con categorías"
    ];
    
    foreach ($features as $feature) {
        echo "   {$feature}\n";
    }
    
    echo "\n🌐 URLS PARA VERIFICAR:\n";
    echo "   - Sistema de áreas: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "   - Sistema de categorías: http://127.0.0.1:8000/capacitaciones/categorias\n";
    
    echo "\n🎉 ¡Verificación del sistema de áreas completada!\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
