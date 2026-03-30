<?php
/**
 * Script de Prueba: Verificar Cursos Disponibles para Usuario
 * 
 * Uso: php test_usuario_cursos_disponibles.php [email]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Curso;
use App\Models\CursoAsignacion;

$email = $argv[1] ?? null;

if (!$email) {
    echo "Uso: php test_usuario_cursos_disponibles.php [email]\n";
    echo "Ejemplo: php test_usuario_cursos_disponibles.php usuario@correo.com\n\n";
    
    // Mostrar últimos 5 estudiantes
    echo "Últimos 5 estudiantes registrados:\n";
    $estudiantes = User::where('role', 'Estudiante')->latest()->take(5)->get();
    foreach ($estudiantes as $est) {
        echo "  - {$est->email} ({$est->name})\n";
    }
    exit(1);
}

$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ Usuario no encontrado: {$email}\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  CURSOS DISPONIBLES PARA: {$user->name}\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📧 Email: {$user->email}\n";
echo "👤 Rol: {$user->role}\n";
echo "✅ Email verificado: " . ($user->email_verified_at ? 'Sí' : 'No') . "\n\n";

// Obtener asignaciones activas
$asignacionesActivas = CursoAsignacion::where('estudiante_id', $user->id)
    ->activas()
    ->with('curso')
    ->get();

echo "📚 CURSOS ASIGNADOS ACTIVOS: {$asignacionesActivas->count()}\n\n";

if ($asignacionesActivas->count() > 0) {
    foreach ($asignacionesActivas as $asignacion) {
        $curso = $asignacion->curso;
        echo "┌─ Curso ID: {$curso->id}\n";
        echo "│  Título: {$curso->titulo}\n";
        echo "│  Estado: {$curso->estado}\n";
        echo "│  Área: " . ($curso->area->descripcion ?? 'Sin área') . "\n";
        echo "│  Instructor: " . ($curso->instructor->name ?? 'Sin instructor') . "\n";
        echo "│  Fecha asignación: {$asignacion->fecha_asignacion->format('d/m/Y H:i')}\n";
        echo "└─\n\n";
    }
    
    echo "✅ Estos cursos DEBERÍAN aparecer en /academico/cursos-disponibles\n\n";
    
    // Verificar si están inscritos
    echo "📝 ESTADO DE INSCRIPCIÓN:\n\n";
    foreach ($asignacionesActivas as $asignacion) {
        $curso = $asignacion->curso;
        $inscrito = $curso->tieneEstudiante($user->id);
        
        echo "• Curso {$curso->id}: ";
        if ($inscrito) {
            echo "✅ INSCRITO\n";
        } else {
            echo "⚠️  ASIGNADO (pendiente de inscripción)\n";
        }
    }
} else {
    echo "⚠️  No tiene cursos asignados activos\n";
    echo "\nPara asignar cursos:\n";
    echo "1. Ir a http://192.168.2.200:8001/configuracion/asignacion-cursos\n";
    echo "2. Buscar al estudiante: {$user->email}\n";
    echo "3. Seleccionar cursos y asignar\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
