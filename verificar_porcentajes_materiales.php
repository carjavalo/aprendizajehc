<?php

/**
 * Script para verificar que los porcentajes de materiales se están guardando correctamente
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Curso;
use App\Models\CursoMaterial;

echo "=== VERIFICACIÓN DE PORCENTAJES DE MATERIALES ===\n\n";

// Obtener curso 17
$curso = Curso::find(17);

if (!$curso) {
    echo "❌ Curso 17 no encontrado\n";
    exit(1);
}

echo "✓ Curso encontrado: {$curso->titulo}\n\n";

// Obtener materiales del curso
$materiales = CursoMaterial::where('curso_id', 17)->get();

echo "Total de materiales: " . $materiales->count() . "\n\n";

$sumaPorcentajes = 0;

foreach ($materiales as $material) {
    $porcentaje = $material->porcentaje_curso ?? 0;
    $sumaPorcentajes += $porcentaje;
    
    echo "Material ID: {$material->id}\n";
    echo "  Título: {$material->titulo}\n";
    echo "  Porcentaje: {$porcentaje}%\n";
    echo "  Actividades: " . $material->actividades()->count() . "\n";
    
    // Mostrar actividades del material
    foreach ($material->actividades as $actividad) {
        $porcentajeActividad = $actividad->porcentaje_curso ?? 0;
        echo "    - {$actividad->titulo} ({$actividad->tipo}): {$porcentajeActividad}%\n";
    }
    
    echo "\n";
}

echo "=== RESUMEN ===\n";
echo "Suma total de porcentajes de materiales: {$sumaPorcentajes}%\n";

if ($sumaPorcentajes == 100) {
    echo "✓ Los porcentajes suman 100% correctamente\n";
} elseif ($sumaPorcentajes == 0) {
    echo "⚠️  ADVERTENCIA: Todos los porcentajes están en 0\n";
    echo "   Esto indica que no se han asignado porcentajes a los materiales\n";
    echo "   o que no se están guardando correctamente.\n";
} else {
    echo "⚠️  ADVERTENCIA: Los porcentajes no suman 100%\n";
    echo "   Diferencia: " . (100 - $sumaPorcentajes) . "%\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
