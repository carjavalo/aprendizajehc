<?php
/**
 * Script para sincronizar asignaciones con inscripciones
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\CursoAsignacion;

echo "=== SINCRONIZACIÓN DE ASIGNACIONES E INSCRIPCIONES ===\n\n";

// Obtener todas las inscripciones actuales
$inscripciones = DB::table('curso_estudiantes')->get();

echo "Inscripciones encontradas: " . $inscripciones->count() . "\n";
echo "Asignaciones activas: " . CursoAsignacion::where('estado', 'activo')->count() . "\n\n";

$creadas = 0;
$reactivadas = 0;

foreach ($inscripciones as $insc) {
    // Verificar si existe asignación
    $asignacion = CursoAsignacion::where('curso_id', $insc->curso_id)
        ->where('estudiante_id', $insc->estudiante_id)
        ->first();
    
    if (!$asignacion) {
        // Crear nueva asignación
        CursoAsignacion::create([
            'curso_id' => $insc->curso_id,
            'estudiante_id' => $insc->estudiante_id,
            'asignado_por' => 1, // Admin
            'estado' => 'activo',
            'fecha_asignacion' => $insc->fecha_inscripcion ?? now(),
        ]);
        $creadas++;
        echo "✓ Creada asignación: Curso {$insc->curso_id} - Estudiante {$insc->estudiante_id}\n";
    } elseif ($asignacion->estado !== 'activo') {
        // Reactivar asignación
        $asignacion->update(['estado' => 'activo']);
        $reactivadas++;
        echo "↻ Reactivada asignación: Curso {$insc->curso_id} - Estudiante {$insc->estudiante_id}\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Asignaciones creadas: {$creadas}\n";
echo "Asignaciones reactivadas: {$reactivadas}\n";
echo "Total asignaciones activas ahora: " . CursoAsignacion::where('estado', 'activo')->count() . "\n";
