<?php
/**
 * Script para inscribir estudiantes de prueba en los cursos
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Curso;

echo "=== INSCRIPCIÓN DE ESTUDIANTES DE PRUEBA ===\n\n";

// Obtener usuarios (excepto admin)
$usuarios = User::where('role', '!=', 'Admin')->take(10)->get();
$cursos = Curso::all();

if ($usuarios->isEmpty()) {
    echo "No hay usuarios disponibles para inscribir.\n";
    exit;
}

if ($cursos->isEmpty()) {
    echo "No hay cursos disponibles.\n";
    exit;
}

echo "Usuarios disponibles: " . $usuarios->count() . "\n";
echo "Cursos disponibles: " . $cursos->count() . "\n\n";

$inscripciones = 0;

foreach ($cursos as $curso) {
    echo "Curso: {$curso->titulo} (ID: {$curso->id})\n";
    
    // Inscribir usuarios aleatorios en cada curso
    $usuariosParaCurso = $usuarios->random(min(5, $usuarios->count()));
    
    foreach ($usuariosParaCurso as $usuario) {
        // Verificar si ya está inscrito
        $existe = DB::table('curso_estudiantes')
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', $usuario->id)
            ->exists();
        
        if (!$existe) {
            DB::table('curso_estudiantes')->insert([
                'curso_id' => $curso->id,
                'estudiante_id' => $usuario->id,
                'estado' => 'activo',
                'progreso' => rand(0, 100),
                'fecha_inscripcion' => now()->subDays(rand(1, 30)),
                'ultima_actividad' => now()->subHours(rand(1, 72)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "  ✓ Inscrito: {$usuario->name} ({$usuario->email})\n";
            $inscripciones++;
        } else {
            echo "  - Ya inscrito: {$usuario->name}\n";
        }
    }
    echo "\n";
}

echo "=== RESUMEN ===\n";
echo "Total inscripciones realizadas: {$inscripciones}\n";
echo "Total registros en curso_estudiantes: " . DB::table('curso_estudiantes')->count() . "\n";
