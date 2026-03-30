<?php

/**
 * Script para asignar porcentajes a los materiales del curso 17
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CursoMaterial;

echo "=== ASIGNACIÓN DE PORCENTAJES A MATERIALES ===\n\n";

// Definir porcentajes para cada material
$porcentajes = [
    66 => 30.00,  // Preliquidacion
    67 => 40.00,  // Liquidar
    68 => 30.00,  // Post Liquidar
];

foreach ($porcentajes as $materialId => $porcentaje) {
    $material = CursoMaterial::find($materialId);
    
    if (!$material) {
        echo "❌ Material ID {$materialId} no encontrado\n";
        continue;
    }
    
    echo "Actualizando Material ID {$materialId}: {$material->titulo}\n";
    echo "  Porcentaje anterior: {$material->porcentaje_curso}%\n";
    
    $material->porcentaje_curso = $porcentaje;
    $material->save();
    
    // Recargar para verificar
    $material->refresh();
    
    echo "  Porcentaje nuevo: {$material->porcentaje_curso}%\n";
    echo "  ✓ Actualizado correctamente\n\n";
}

echo "=== VERIFICACIÓN FINAL ===\n\n";

$materiales = CursoMaterial::where('curso_id', 17)->get();
$sumaTotal = 0;

foreach ($materiales as $material) {
    echo "Material: {$material->titulo} - {$material->porcentaje_curso}%\n";
    $sumaTotal += $material->porcentaje_curso;
}

echo "\nSuma total: {$sumaTotal}%\n";

if ($sumaTotal == 100) {
    echo "✓ Los porcentajes suman 100% correctamente\n";
} else {
    echo "⚠️  Los porcentajes no suman 100%\n";
}

echo "\n=== FIN ASIGNACIÓN ===\n";
