<?php
/**
 * Script de Diagnóstico: Cursos Disponibles
 * 
 * Verifica por qué los cursos asignados no aparecen en la vista
 * 
 * Uso: php diagnostico_cursos_disponibles.php [user_id]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Curso;
use App\Models\CursoAsignacion;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  DIAGNÓSTICO: Cursos Disponibles\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Obtener ID de usuario desde argumentos o usar el último estudiante
$userId = $argv[1] ?? null;

if (!$userId) {
    $ultimoEstudiante = User::where('role', 'Estudiante')->latest()->first();
    if ($ultimoEstudiante) {
        $userId = $ultimoEstudiante->id;
        echo "ℹ️  Usando último estudiante registrado: {$ultimoEstudiante->name} (ID: {$userId})\n\n";
    } else {
        echo "❌ No hay estudiantes en la base de datos\n";
        exit(1);
    }
} else {
    echo "ℹ️  Usando usuario ID: {$userId}\n\n";
}

$user = User::find($userId);

if (!$user) {
    echo "❌ Usuario no encontrado\n";
    exit(1);
}

echo "👤 INFORMACIÓN DEL USUARIO\n";
echo "   Nombre: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Rol: {$user->role}\n";
echo "   Email verificado: " . ($user->email_verified_at ? 'Sí' : 'No') . "\n";
echo "\n";

// 1. Verificar asignaciones en curso_asignaciones
echo "1️⃣  ASIGNACIONES EN TABLA curso_asignaciones\n";

$asignaciones = DB::table('curso_asignaciones')
    ->where('estudiante_id', $user->id)
    ->get();

echo "   Total de asignaciones: {$asignaciones->count()}\n\n";

if ($asignaciones->count() > 0) {
    echo "   Detalle de asignaciones:\n";
    foreach ($asignaciones as $asignacion) {
        $curso = Curso::find($asignacion->curso_id);
        $cursoNombre = $curso ? $curso->titulo : 'Curso no encontrado';
        $cursoEstado = $curso ? $curso->estado : 'N/A';
        
        echo "   ┌─ Asignación ID: {$asignacion->id}\n";
        echo "   │  Curso ID: {$asignacion->curso_id} - {$cursoNombre}\n";
        echo "   │  Estado asignación: {$asignacion->estado}\n";
        echo "   │  Estado curso: {$cursoEstado}\n";
        echo "   │  Fecha asignación: {$asignacion->fecha_asignacion}\n";
        echo "   │  Fecha expiración: " . ($asignacion->fecha_expiracion ?? 'Sin expiración') . "\n";
        
        // Verificar si es activa según el scope
        $esActiva = $asignacion->estado === 'activo' && 
                    (!$asignacion->fecha_expiracion || $asignacion->fecha_expiracion > now());
        
        echo "   │  ¿Es activa? " . ($esActiva ? '✅ Sí' : '❌ No') . "\n";
        echo "   └─\n";
    }
} else {
    echo "   ⚠️  No tiene asignaciones\n";
}
echo "\n";

// 2. Verificar asignaciones activas usando el scope
echo "2️⃣  ASIGNACIONES ACTIVAS (usando scope)\n";

$asignacionesActivas = CursoAsignacion::where('estudiante_id', $user->id)
    ->activas()
    ->get();

echo "   Total de asignaciones activas: {$asignacionesActivas->count()}\n\n";

if ($asignacionesActivas->count() > 0) {
    echo "   Cursos asignados activos:\n";
    foreach ($asignacionesActivas as $asignacion) {
        $curso = $asignacion->curso;
        echo "   • ID {$curso->id}: {$curso->titulo} (Estado: {$curso->estado})\n";
    }
} else {
    echo "   ⚠️  No tiene asignaciones activas\n";
}
echo "\n";

// 3. Simular la consulta del controlador
echo "3️⃣  SIMULACIÓN DE CONSULTA DEL CONTROLADOR\n";

$rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];

if (in_array($user->role, $rolesVerTodos)) {
    echo "   ℹ️  Usuario con rol privilegiado - Ve TODOS los cursos activos\n";
    $cursosQuery = Curso::where('estado', 'activo');
} else {
    echo "   ℹ️  Usuario estudiante/docente - Ve solo cursos asignados\n";
    
    // Obtener IDs de cursos asignados
    $cursosAsignadosIds = CursoAsignacion::where('estudiante_id', $user->id)
        ->activas()
        ->pluck('curso_id')
        ->toArray();
    
    echo "   IDs de cursos asignados: " . implode(', ', $cursosAsignadosIds) . "\n";
    
    $cursosQuery = Curso::where('estado', 'activo')
        ->whereIn('id', $cursosAsignadosIds);
}

$cursos = $cursosQuery->get();

echo "   Total de cursos que debería ver: {$cursos->count()}\n\n";

if ($cursos->count() > 0) {
    echo "   Lista de cursos:\n";
    foreach ($cursos as $curso) {
        echo "   • ID {$curso->id}: {$curso->titulo}\n";
        echo "     Estado: {$curso->estado}\n";
        echo "     Área: " . ($curso->area->descripcion ?? 'Sin área') . "\n";
        echo "     Instructor: " . ($curso->instructor->name ?? 'Sin instructor') . "\n";
        echo "\n";
    }
} else {
    echo "   ⚠️  No hay cursos para mostrar\n";
}

// 4. Verificar curso ID 18 específicamente
echo "4️⃣  VERIFICACIÓN ESPECÍFICA DEL CURSO ID 18\n";

$curso18 = Curso::find(18);

if ($curso18) {
    echo "   ✅ Curso ID 18 existe\n";
    echo "   Título: {$curso18->titulo}\n";
    echo "   Estado: {$curso18->estado}\n";
    
    // Verificar si el usuario tiene asignación
    $asignacionCurso18 = CursoAsignacion::where('estudiante_id', $user->id)
        ->where('curso_id', 18)
        ->first();
    
    if ($asignacionCurso18) {
        echo "   ✅ Usuario tiene asignación al curso 18\n";
        echo "   Estado asignación: {$asignacionCurso18->estado}\n";
        echo "   Fecha expiración: " . ($asignacionCurso18->fecha_expiracion ?? 'Sin expiración') . "\n";
        
        // Verificar si es activa
        $esActiva = $asignacionCurso18->estaActiva();
        echo "   ¿Es activa? " . ($esActiva ? '✅ Sí' : '❌ No') . "\n";
        
        if (!$esActiva) {
            echo "   ⚠️  PROBLEMA: La asignación NO está activa\n";
            if ($asignacionCurso18->estado !== 'activo') {
                echo "      Razón: Estado es '{$asignacionCurso18->estado}' (debe ser 'activo')\n";
            }
            if ($asignacionCurso18->fecha_expiracion && $asignacionCurso18->fecha_expiracion < now()) {
                echo "      Razón: Fecha de expiración pasada\n";
            }
        }
    } else {
        echo "   ❌ Usuario NO tiene asignación al curso 18\n";
    }
} else {
    echo "   ❌ Curso ID 18 no existe\n";
}
echo "\n";

// 5. Verificar inscripciones (tabla curso_estudiante)
echo "5️⃣  INSCRIPCIONES (tabla curso_estudiante)\n";

$inscripciones = DB::table('curso_estudiante')
    ->where('user_id', $user->id)
    ->get();

echo "   Total de inscripciones: {$inscripciones->count()}\n";

if ($inscripciones->count() > 0) {
    echo "   Cursos inscritos:\n";
    foreach ($inscripciones as $inscripcion) {
        $curso = Curso::find($inscripcion->curso_id);
        $cursoNombre = $curso ? $curso->titulo : 'Curso no encontrado';
        echo "   • Curso ID {$inscripcion->curso_id}: {$cursoNombre} (Estado: {$inscripcion->estado})\n";
    }
}
echo "\n";

// Resumen y recomendaciones
echo "═══════════════════════════════════════════════════════════════\n";
echo "DIAGNÓSTICO Y RECOMENDACIONES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$problemas = [];

if ($asignaciones->count() === 0) {
    $problemas[] = "Usuario no tiene asignaciones en curso_asignaciones";
}

if ($asignacionesActivas->count() === 0 && !in_array($user->role, $rolesVerTodos)) {
    $problemas[] = "Usuario no tiene asignaciones ACTIVAS";
}

if ($curso18 && $curso18->estado !== 'activo') {
    $problemas[] = "Curso ID 18 no está activo (estado: {$curso18->estado})";
}

if ($asignacionCurso18 && !$asignacionCurso18->estaActiva()) {
    $problemas[] = "Asignación al curso 18 no está activa";
}

if (count($problemas) > 0) {
    echo "❌ PROBLEMAS DETECTADOS:\n\n";
    foreach ($problemas as $i => $problema) {
        echo "   " . ($i + 1) . ". {$problema}\n";
    }
    echo "\n";
    
    echo "💡 SOLUCIONES RECOMENDADAS:\n\n";
    
    if ($asignaciones->count() === 0) {
        echo "   • Asignar curso manualmente desde /configuracion/asignacion-cursos\n";
        echo "   • O ejecutar: INSERT INTO curso_asignaciones (curso_id, estudiante_id, asignado_por, estado, fecha_asignacion) VALUES (18, {$user->id}, 1, 'activo', NOW());\n";
    }
    
    if ($asignacionCurso18 && $asignacionCurso18->estado !== 'activo') {
        echo "   • Actualizar estado de asignación: UPDATE curso_asignaciones SET estado='activo' WHERE id={$asignacionCurso18->id};\n";
    }
    
    if ($curso18 && $curso18->estado !== 'activo') {
        echo "   • Activar curso: UPDATE cursos SET estado='activo' WHERE id=18;\n";
    }
} else {
    echo "✅ NO SE DETECTARON PROBLEMAS\n\n";
    echo "El usuario debería ver {$cursos->count()} curso(s) en /academico/cursos-disponibles\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
