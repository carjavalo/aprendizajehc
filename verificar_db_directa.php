<?php

/**
 * Script para verificar directamente en la base de datos
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DIRECTA EN BASE DE DATOS ===\n\n";

$materiales = DB::table('curso_materiales')
    ->where('curso_id', 17)
    ->select('id', 'titulo', 'porcentaje_curso')
    ->get();

echo "Materiales del curso 17:\n\n";

$sumaTotal = 0;

foreach ($materiales as $material) {
    echo "ID: {$material->id}\n";
    echo "  Título: {$material->titulo}\n";
    echo "  Porcentaje: {$material->porcentaje_curso}%\n\n";
    $sumaTotal += $material->porcentaje_curso;
}

echo "Suma total: {$sumaTotal}%\n";

if ($sumaTotal == 100) {
    echo "✓ Los porcentajes suman 100%\n";
} elseif ($sumaTotal == 0) {
    echo "❌ Todos los porcentajes están en 0\n";
} else {
    echo "⚠️  Los porcentajes no suman 100% (diferencia: " . (100 - $sumaTotal) . "%)\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
