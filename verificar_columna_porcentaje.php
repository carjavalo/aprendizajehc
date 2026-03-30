<?php

/**
 * Script para verificar si la columna porcentaje_curso existe en la tabla curso_materiales
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE COLUMNA porcentaje_curso ===\n\n";

// Verificar si la columna existe
$hasColumn = Schema::hasColumn('curso_materiales', 'porcentaje_curso');

if ($hasColumn) {
    echo "✓ La columna 'porcentaje_curso' EXISTE en la tabla 'curso_materiales'\n\n";
    
    // Obtener información de la columna
    $columns = DB::select("SHOW COLUMNS FROM curso_materiales WHERE Field = 'porcentaje_curso'");
    
    if (!empty($columns)) {
        $column = $columns[0];
        echo "Información de la columna:\n";
        echo "  Tipo: {$column->Type}\n";
        echo "  Null: {$column->Null}\n";
        echo "  Default: " . ($column->Default ?? 'NULL') . "\n";
        echo "  Extra: {$column->Extra}\n";
    }
} else {
    echo "❌ La columna 'porcentaje_curso' NO EXISTE en la tabla 'curso_materiales'\n";
    echo "   Esto explica por qué no se están guardando los porcentajes.\n\n";
    echo "SOLUCIÓN: Necesitas crear una migración para agregar esta columna.\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
