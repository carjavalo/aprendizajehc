<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Curso;
use App\Models\CursoMaterial;
use App\Models\CursoActividad;

echo "=== VERIFICACIÓN DE PORCENTAJES DEL CURSO ===\n\n";

$cursoId = 17; // Cambiar según necesidad

$curso = Curso::with(['materiales.actividades'])->find($cursoId);

if (!$curso) {
    echo "❌ Curso no encontrado\n";
    exit;
}

echo "📚 Curso: {$curso->titulo}\n";
echo "ID: {$curso->id}\n";
echo "Código: {$curso->codigo_acceso}\n\n";

echo "=== MATERIALES Y SUS PORCENTAJES ===\n\n";

$totalPorcentajeMateriales = 0;

foreach ($curso->materiales as $index => $material) {
    $porcentaje = floatval($material->porcentaje_curso ?? 0);
    $totalPorcentajeMateriales += $porcentaje;
    
    echo ($index + 1) . ". Material: {$material->titulo}\n";
    echo "   ID: {$material->id}\n";
    echo "   Porcentaje del Curso: {$porcentaje}%\n";
    echo "   Nota Mínima Aprobación: " . ($material->nota_minima_aprobacion ?? 'No definida') . "\n";
    
    if ($material->actividades->count() > 0) {
        echo "   Actividades:\n";
        $totalPorcentajeActividades = 0;
        
        foreach ($material->actividades as $actividad) {
            $porcentajeAct = floatval($actividad->porcentaje_curso ?? 0);
            $totalPorcentajeActividades += $porcentajeAct;
            
            echo "      - {$actividad->titulo} ({$actividad->tipo}): {$porcentajeAct}%\n";
        }
        
        echo "   Total Actividades: {$totalPorcentajeActividades}%\n";
        
        if ($totalPorcentajeActividades != $porcentaje && $porcentaje > 0) {
            echo "   ⚠️  ADVERTENCIA: Las actividades no suman el porcentaje del material\n";
        }
    } else {
        echo "   ⚠️  Sin actividades\n";
    }
    
    echo "\n";
}

echo "=== ACTIVIDADES INDEPENDIENTES (SIN MATERIAL) ===\n\n";

$actividadesIndependientes = CursoActividad::where('curso_id', $cursoId)
    ->whereNull('material_id')
    ->get();

$totalPorcentajeIndependientes = 0;

if ($actividadesIndependientes->count() > 0) {
    foreach ($actividadesIndependientes as $actividad) {
        $porcentaje = floatval($actividad->porcentaje_curso ?? 0);
        $totalPorcentajeIndependientes += $porcentaje;
        
        echo "- {$actividad->titulo} ({$actividad->tipo}): {$porcentaje}%\n";
    }
} else {
    echo "No hay actividades independientes\n";
}

echo "\n=== RESUMEN ===\n\n";
echo "Total Porcentaje Materiales: {$totalPorcentajeMateriales}%\n";
echo "Total Porcentaje Actividades Independientes: {$totalPorcentajeIndependientes}%\n";
$totalGeneral = $totalPorcentajeMateriales + $totalPorcentajeIndependientes;
echo "TOTAL GENERAL: {$totalGeneral}%\n\n";

if ($totalGeneral == 100) {
    echo "✅ El curso tiene una distribución correcta de porcentajes (100%)\n";
} elseif ($totalGeneral == 0) {
    echo "⚠️  El curso NO tiene porcentajes asignados\n";
    echo "💡 Sugerencia: Edita los materiales y actividades para asignar porcentajes\n";
} elseif ($totalGeneral < 100) {
    $faltante = 100 - $totalGeneral;
    echo "⚠️  Falta asignar {$faltante}% para completar el 100%\n";
} else {
    $exceso = $totalGeneral - 100;
    echo "❌ El curso excede el 100% por {$exceso}%\n";
}

echo "\n=== CONFIGURACIÓN DEL CURSO ===\n\n";
echo "Nota Máxima: " . ($curso->nota_maxima ?? '5.0') . "\n";
echo "Nota Mínima Aprobación: " . ($curso->nota_minima_aprobacion ?? 'No definida') . "\n";

echo "\n✅ Verificación completada\n";
