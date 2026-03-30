<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔍 VERIFICACIÓN DE LA TABLA AREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Verificar si la tabla existe
    if (Schema::hasTable('areas')) {
        echo "✅ La tabla 'areas' existe\n\n";
        
        // Obtener estructura de la tabla
        $columns = Schema::getColumnListing('areas');
        echo "📋 Columnas actuales:\n";
        foreach ($columns as $column) {
            echo "   - {$column}\n";
        }
        
        // Verificar columnas específicas
        echo "\n🔍 Verificación de columnas específicas:\n";
        $requiredColumns = ['id', 'descripcion', 'cod_categoria', 'created_at', 'updated_at'];
        
        foreach ($requiredColumns as $column) {
            $exists = in_array($column, $columns);
            echo "   " . ($exists ? "✅" : "❌") . " {$column}\n";
        }
        
        // Mostrar estructura detallada
        echo "\n📊 Estructura detallada de la tabla:\n";
        $tableInfo = DB::select("DESCRIBE areas");
        foreach ($tableInfo as $column) {
            echo "   {$column->Field}: {$column->Type} " . 
                 ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . 
                 ($column->Key ? " ({$column->Key})" : '') . 
                 ($column->Default !== null ? " DEFAULT {$column->Default}" : '') . "\n";
        }
        
        // Contar registros
        $count = DB::table('areas')->count();
        echo "\n📈 Total de registros: {$count}\n";
        
    } else {
        echo "❌ La tabla 'areas' NO existe\n";
        echo "💡 Necesita ejecutar la migración\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
