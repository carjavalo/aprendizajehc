<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\CursoAsignacion;

echo "=== INSCRIBIR ESTUDIANTES ASIGNADOS ===\n\n";

$asignaciones = CursoAsignacion::where('estado', 'activo')->get();
echo "Asignaciones activas: " . $asignaciones->count() . "\n\n";

$inscritos = 0;
foreach ($asignaciones as $a) {
    $existe = DB::table('curso_estudiantes')
        ->where('curso_id', $a->curso_id)
        ->where('estudiante_id', $a->estudiante_id)
        ->exists();
    
    if (!$existe) {
        DB::table('curso_estudiantes')->insert([
            'curso_id' => $a->curso_id,
            'estudiante_id' => $a->estudiante_id,
            'estado' => 'activo',
            'progreso' => rand(10, 95),
            'fecha_inscripcion' => now(),
            'ultima_actividad' => now()->subHours(rand(1, 48)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inscritos++;
        echo "✓ Inscrito estudiante {$a->estudiante_id} en curso {$a->curso_id}\n";
    }
}

echo "\n=== RESULTADO ===\n";
echo "Nuevos inscritos: {$inscritos}\n";
echo "Total en curso_estudiantes: " . DB::table('curso_estudiantes')->count() . "\n";
