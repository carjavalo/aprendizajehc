<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificando Entregas ===\n\n";

// Verificar tablas relacionadas con entregas
echo "1. Tablas relacionadas con entregas:\n";
$tables = DB::select("SHOW TABLES LIKE '%entrega%'");
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "   - $tableName\n";
}

echo "\n2. Estructura de curso_actividad_entregas:\n";
try {
    $columns = DB::select("DESCRIBE curso_actividad_entregas");
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n3. Entregas en curso_actividad_entregas:\n";
try {
    $entregas = DB::table('curso_actividad_entregas')->get();
    echo "   Total: " . $entregas->count() . " entregas\n";
    foreach ($entregas as $entrega) {
        echo "   - ID: {$entrega->id}, Actividad: {$entrega->actividad_id}, Estudiante: {$entrega->estudiante_id}, Estado: {$entrega->estado}\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n4. Buscando otras tablas de entregas:\n";
$allTables = DB::select("SHOW TABLES");
foreach ($allTables as $table) {
    $tableName = array_values((array)$table)[0];
    if (stripos($tableName, 'actividad') !== false || stripos($tableName, 'tarea') !== false) {
        echo "   - $tableName\n";
        
        // Ver si tiene datos
        try {
            $count = DB::table($tableName)->count();
            echo "     Registros: $count\n";
            
            // Si tiene datos, mostrar algunos
            if ($count > 0 && $count < 20) {
                $data = DB::table($tableName)->limit(5)->get();
                foreach ($data as $row) {
                    echo "     " . json_encode($row) . "\n";
                }
            }
        } catch (Exception $e) {
            // Ignorar errores
        }
    }
}

echo "\n5. Verificando usuario Estudianteuno@estudiante.com:\n";
try {
    $estudiante = DB::table('users')->where('email', 'Estudianteuno@estudiante.com')->first();
    if ($estudiante) {
        echo "   - ID: {$estudiante->id}\n";
        echo "   - Nombre: {$estudiante->name}\n";
        echo "   - Email: {$estudiante->email}\n";
    } else {
        echo "   - No encontrado\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n6. Verificando actividad ID 32:\n";
try {
    $actividad = DB::table('curso_actividades')->where('id', 32)->first();
    if ($actividad) {
        echo "   - ID: {$actividad->id}\n";
        echo "   - Título: {$actividad->titulo}\n";
        echo "   - Tipo: {$actividad->tipo}\n";
        echo "   - Curso ID: {$actividad->curso_id}\n";
    } else {
        echo "   - No encontrada\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de verificación ===\n";
