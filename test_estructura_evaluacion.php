<?php

/**
 * Script para probar el método getEstructuraEvaluacion
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Curso;

echo "=== TEST ESTRUCTURA DE EVALUACIÓN ===\n\n";

$curso = Curso::with(['materiales.actividades'])->find(17);

if (!$curso) {
    echo "❌ Curso 17 no encontrado\n";
    exit(1);
}

echo "✓ Curso encontrado: {$curso->titulo}\n\n";

echo "=== ESTRUCTURA DE EVALUACIÓN ===\n\n";

$estructura = [];

foreach ($curso->materiales as $material) {
    $componentes = [];
    
    echo "Material: {$material->titulo}\n";
    echo "  ID: {$material->id}\n";
    echo "  Porcentaje: {$material->porcentaje_curso}%\n";
    echo "  Actividades:\n";
    
    foreach ($material->actividades as $actividad) {
        echo "    - {$actividad->titulo} ({$actividad->tipo}): {$actividad->porcentaje_curso}%\n";
        
        $componentes[] = [
            'id' => $actividad->id,
            'nombre' => $actividad->titulo,
            'tipo' => $actividad->tipo,
            'peso' => floatval($actividad->porcentaje_curso ?? 0),
        ];
    }
    
    $estructura[] = [
        'tipo' => 'material',
        'id' => $material->id,
        'nombre' => $material->titulo,
        'peso' => floatval($material->porcentaje_curso ?? 0),
        'componentes' => $componentes,
    ];
    
    echo "\n";
}

echo "=== ESTRUCTURA GENERADA (JSON) ===\n\n";
echo json_encode($estructura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n=== VERIFICACIÓN ===\n\n";

$sumaTotal = 0;
foreach ($estructura as $item) {
    echo "{$item['nombre']}: {$item['peso']}%\n";
    $sumaTotal += $item['peso'];
}

echo "\nSuma total: {$sumaTotal}%\n";

if ($sumaTotal == 100) {
    echo "✓ Los porcentajes suman 100% correctamente\n";
} else {
    echo "⚠️  Los porcentajes no suman 100%\n";
}

echo "\n=== FIN TEST ===\n";
