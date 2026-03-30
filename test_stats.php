<?php
/**
 * Script para probar el endpoint de estadísticas
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Curso;

echo "=== PRUEBA DE ESTADÍSTICAS DE CURSOS ===\n\n";

$cursos = Curso::all();

foreach ($cursos as $curso) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "CURSO: {$curso->titulo} (ID: {$curso->id})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Obtener estudiantes
    $estudiantes = $curso->estudiantes()
        ->withPivot(['estado', 'progreso', 'fecha_inscripcion', 'ultima_actividad'])
        ->get();
    
    echo "\nESTUDIANTES INSCRITOS: " . $estudiantes->count() . "\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    if ($estudiantes->count() > 0) {
        foreach ($estudiantes as $est) {
            $nombre = $est->name . ' ' . ($est->apellido1 ?? '');
            $progreso = $est->pivot->progreso ?? 0;
            $estado = $est->pivot->estado ?? 'N/A';
            $fechaInsc = $est->pivot->fecha_inscripcion 
                ? \Carbon\Carbon::parse($est->pivot->fecha_inscripcion)->format('d/m/Y') 
                : 'N/A';
            
            echo sprintf("  %-30s | %-25s | %3d%% | %-10s | %s\n", 
                substr($nombre, 0, 30),
                substr($est->email, 0, 25),
                $progreso,
                $estado,
                $fechaInsc
            );
        }
    } else {
        echo "  (No hay estudiantes inscritos)\n";
    }
    
    // Obtener actividades
    $actividades = $curso->actividades ?? collect([]);
    echo "\nACTIVIDADES: " . $actividades->count() . "\n";
    echo "  - Tareas: " . $actividades->where('tipo', 'tarea')->count() . "\n";
    echo "  - Quizzes: " . $actividades->where('tipo', 'quiz')->count() . "\n";
    echo "  - Evaluaciones: " . $actividades->where('tipo', 'evaluacion')->count() . "\n";
    echo "  - Proyectos: " . $actividades->where('tipo', 'proyecto')->count() . "\n";
    
    echo "\n";
}

echo "=== FIN DE LA PRUEBA ===\n";
