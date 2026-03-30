<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 VERIFICACIÓN DE TABLAS DE CURSOS\n";
echo "=====================================\n\n";

try {
    $tables = [
        'cursos' => 'Tabla principal de cursos',
        'curso_estudiantes' => 'Relación cursos-estudiantes',
        'curso_materiales' => 'Materiales de los cursos',
        'curso_foros' => 'Foros de discusión',
        'curso_actividades' => 'Actividades y tareas'
    ];

    foreach ($tables as $table => $description) {
        echo "📋 {$description} ({$table}):\n";
        
        if (Schema::hasTable($table)) {
            echo "   ✅ La tabla existe\n";
            
            // Contar registros
            $count = DB::table($table)->count();
            echo "   📊 Registros: {$count}\n";
            
            // Mostrar columnas
            $columns = Schema::getColumnListing($table);
            echo "   🏗️ Columnas: " . implode(', ', $columns) . "\n";
        } else {
            echo "   ❌ La tabla NO existe\n";
        }
        echo "\n";
    }

    // Verificar modelos
    echo "🏗️ VERIFICACIÓN DE MODELOS:\n";
    echo "============================\n\n";

    $models = [
        'App\Models\Curso' => 'Curso',
        'App\Models\CursoMaterial' => 'CursoMaterial',
        'App\Models\CursoForo' => 'CursoForo',
        'App\Models\CursoActividad' => 'CursoActividad'
    ];

    foreach ($models as $class => $name) {
        echo "📦 Modelo {$name}:\n";
        
        if (class_exists($class)) {
            echo "   ✅ La clase existe\n";
            
            try {
                $model = new $class();
                echo "   ✅ Se puede instanciar\n";
                echo "   📋 Tabla: " . $model->getTable() . "\n";
            } catch (Exception $e) {
                echo "   ❌ Error al instanciar: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ❌ La clase NO existe\n";
        }
        echo "\n";
    }

    // Probar consulta específica que falló
    echo "🧪 PRUEBA DE CONSULTA ESPECÍFICA:\n";
    echo "==================================\n\n";

    try {
        $curso = \App\Models\Curso::with('materiales')->first();
        if ($curso) {
            echo "✅ Consulta exitosa: Curso '{$curso->titulo}' cargado con materiales\n";
            echo "📊 Materiales encontrados: " . $curso->materiales->count() . "\n";
        } else {
            echo "⚠️ No hay cursos en la base de datos\n";
        }
    } catch (Exception $e) {
        echo "❌ Error en la consulta: " . $e->getMessage() . "\n";
        echo "📍 Línea: " . $e->getLine() . "\n";
        echo "📄 Archivo: " . $e->getFile() . "\n";
    }

    echo "\n🎯 RESUMEN:\n";
    echo "===========\n";
    echo "Si todas las tablas existen y los modelos se pueden instanciar,\n";
    echo "el error debería estar resuelto.\n\n";
    
    echo "🌐 Prueba acceder a: http://127.0.0.1:8000/capacitaciones/cursos/1/classroom\n";

} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
