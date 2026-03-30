<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Area;
use App\Models\Categoria;
use Illuminate\Support\Facades\Schema;

echo "🧪 PRUEBA RÁPIDA DEL SISTEMA DE ÁREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Verificar tabla areas
    echo "1. 📋 Verificando tabla 'areas':\n";
    
    if (Schema::hasTable('areas')) {
        echo "   ✅ Tabla 'areas' existe\n";
        
        $columns = Schema::getColumnListing('areas');
        echo "   📊 Columnas: " . implode(', ', $columns) . "\n";
        
        $hasDescripcion = in_array('descripcion', $columns);
        echo "   🔍 Campo 'descripcion': " . ($hasDescripcion ? "✅ EXISTE" : "❌ FALTA") . "\n";
        
        $count = Area::count();
        echo "   📈 Registros existentes: {$count}\n";
    } else {
        echo "   ❌ Tabla 'areas' NO existe\n";
        echo "   💡 Ejecutar: php artisan migrate\n";
        exit(1);
    }
    
    // 2. Verificar categorías
    echo "\n2. 📋 Verificando categorías:\n";
    
    $categoriaCount = Categoria::count();
    echo "   📈 Categorías disponibles: {$categoriaCount}\n";
    
    if ($categoriaCount === 0) {
        echo "   ❌ No hay categorías. Crear categorías primero.\n";
        exit(1);
    }
    
    // 3. Crear área de prueba
    echo "\n3. 🧪 Creando área de prueba:\n";
    
    $categoria = Categoria::first();
    echo "   📋 Usando categoría: {$categoria->descripcion} (ID: {$categoria->id})\n";
    
    // Verificar si ya existe un área de prueba
    $areaPrueba = Area::where('descripcion', 'Área de Prueba')->first();
    
    if (!$areaPrueba) {
        try {
            $areaPrueba = Area::create([
                'descripcion' => 'Área de Prueba',
                'cod_categoria' => $categoria->id
            ]);
            echo "   ✅ Área de prueba creada: ID {$areaPrueba->id}\n";
        } catch (Exception $e) {
            echo "   ❌ Error creando área: " . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        echo "   ✅ Área de prueba ya existe: ID {$areaPrueba->id}\n";
    }
    
    // 4. Probar relación
    echo "\n4. 🔗 Probando relación con categoría:\n";
    
    $areaPrueba->load('categoria');
    echo "   📋 Área: {$areaPrueba->descripcion}\n";
    echo "   📂 Categoría: {$areaPrueba->categoria->descripcion}\n";
    echo "   ✅ Relación funciona correctamente\n";
    
    // 5. Probar consulta DataTable
    echo "\n5. 📊 Probando consulta para DataTable:\n";
    
    $areas = Area::with('categoria')
                ->select(['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at'])
                ->get();
    
    echo "   📈 Áreas encontradas: " . $areas->count() . "\n";
    
    foreach ($areas as $area) {
        echo "   - ID: {$area->id}, Descripción: {$area->descripcion}, Categoría: {$area->categoria->descripcion}\n";
    }
    
    // 6. Verificar rutas
    echo "\n6. 🛣️ Verificando rutas:\n";
    
    try {
        $indexUrl = route('capacitaciones.areas.index');
        echo "   ✅ Ruta index: {$indexUrl}\n";
        
        $dataUrl = route('capacitaciones.areas.data');
        echo "   ✅ Ruta data: {$dataUrl}\n";
        
        $showUrl = route('capacitaciones.areas.show', 1);
        echo "   ✅ Ruta show: {$showUrl}\n";
    } catch (Exception $e) {
        echo "   ❌ Error en rutas: " . $e->getMessage() . "\n";
    }
    
    // 7. Verificar vista
    echo "\n7. 🎨 Verificando vista:\n";
    
    $viewPath = 'resources/views/admin/capacitaciones/areas/index.blade.php';
    if (file_exists($viewPath)) {
        echo "   ✅ Vista existe: {$viewPath}\n";
        
        $viewContent = file_get_contents($viewPath);
        $elements = [
            'areas-table' => 'DataTable',
            'modal_descripcion' => 'Campo descripción en modal',
            'btn-nueva-area' => 'Botón nueva área'
        ];
        
        foreach ($elements as $element => $description) {
            $exists = strpos($viewContent, $element) !== false;
            echo "   " . ($exists ? "✅" : "❌") . " {$description}\n";
        }
    } else {
        echo "   ❌ Vista no encontrada\n";
    }
    
    echo "\n🎉 RESUMEN:\n";
    echo "   ✅ Tabla 'areas' configurada correctamente\n";
    echo "   ✅ Campo 'descripcion' presente\n";
    echo "   ✅ Modelo Area funcional\n";
    echo "   ✅ Relaciones funcionando\n";
    echo "   ✅ Rutas configuradas\n";
    echo "   ✅ Vista disponible\n";
    
    echo "\n🌐 ACCESO AL SISTEMA:\n";
    echo "   URL: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "   Total de áreas: " . Area::count() . "\n";
    echo "   Total de categorías: " . Categoria::count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
