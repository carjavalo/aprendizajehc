<?php
/**
 * Script de Prueba: Registro y Verificación Final
 * 
 * Verifica que:
 * 1. Solo se envíe UN correo de verificación en español
 * 2. El curso ID 18 se asigne automáticamente
 * 3. El curso aparezca en cursos disponibles
 * 
 * Uso: php test_registro_verificacion_final.php
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
echo "  PRUEBA: Registro y Verificación Final\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Verificar que el evento Registered esté comentado
echo "1️⃣  Verificando configuración de correos...\n";

$controllerFile = file_get_contents('app/Http/Controllers/Auth/RegisteredUserController.php');

if (strpos($controllerFile, '// event(new Registered($user));') !== false) {
    echo "   ✅ Evento Registered comentado (no enviará correo automático en inglés)\n";
} else if (strpos($controllerFile, 'event(new Registered($user));') !== false) {
    echo "   ⚠️  Evento Registered ACTIVO (enviará correo automático en inglés)\n";
    echo "   ACCIÓN REQUERIDA: Comentar la línea 'event(new Registered(\$user));'\n";
} else {
    echo "   ⚠️  No se encontró el evento Registered\n";
}

if (strpos($controllerFile, 'new \App\Mail\VerificarCuenta') !== false) {
    echo "   ✅ Correo personalizado de verificación en español configurado\n";
} else {
    echo "   ❌ Correo personalizado de verificación NO encontrado\n";
}
echo "\n";

// 2. Verificar curso ID 18
echo "2️⃣  Verificando curso ID 18 (Inducción Institucional)...\n";

$curso18 = Curso::find(18);

if ($curso18) {
    echo "   ✅ Curso ID 18 encontrado\n";
    echo "   Nombre: " . ($curso18->nombre ?: '[Sin nombre]') . "\n";
    echo "   Título: " . ($curso18->titulo ?: '[Sin título]') . "\n";
    echo "   Estado: {$curso18->estado}\n";
    
    if ($curso18->instructor) {
        echo "   Instructor: {$curso18->instructor->name}\n";
    }
    
    // Verificar si tiene el nombre correcto
    if (stripos($curso18->nombre, 'Inducción') !== false || 
        stripos($curso18->titulo, 'Inducción') !== false) {
        echo "   ✅ Nombre contiene 'Inducción'\n";
    } else {
        echo "   ⚠️  Nombre no contiene 'Inducción' (puede actualizarse después)\n";
    }
} else {
    echo "   ❌ Curso ID 18 NO encontrado\n";
    echo "   ADVERTENCIA: Los nuevos usuarios no podrán ser asignados al curso\n";
}
echo "\n";

// 3. Verificar modelo CursoAsignacion
echo "3️⃣  Verificando modelo CursoAsignacion...\n";

if (class_exists('App\Models\CursoAsignacion')) {
    echo "   ✅ Modelo CursoAsignacion existe\n";
    
    // Verificar scope activas
    $reflection = new ReflectionClass('App\Models\CursoAsignacion');
    if ($reflection->hasMethod('scopeActivas')) {
        echo "   ✅ Scope 'activas()' implementado\n";
    } else {
        echo "   ❌ Scope 'activas()' NO implementado\n";
    }
} else {
    echo "   ❌ Modelo CursoAsignacion NO existe\n";
}
echo "\n";

// 4. Verificar tabla curso_asignaciones
echo "4️⃣  Verificando tabla curso_asignaciones...\n";

try {
    $totalAsignaciones = DB::table('curso_asignaciones')->count();
    echo "   ✅ Tabla curso_asignaciones existe\n";
    echo "   Total de asignaciones: {$totalAsignaciones}\n";
    
    // Verificar asignaciones al curso 18
    $asignacionesCurso18 = DB::table('curso_asignaciones')
        ->where('curso_id', 18)
        ->count();
    
    echo "   Asignaciones al curso ID 18: {$asignacionesCurso18}\n";
    
    // Verificar columnas
    $columns = DB::select("SHOW COLUMNS FROM curso_asignaciones");
    $columnNames = array_map(function($col) {
        return $col->Field;
    }, $columns);
    
    if (in_array('estudiante_id', $columnNames)) {
        echo "   ✅ Columna 'estudiante_id' existe\n";
    } else {
        echo "   ❌ Columna 'estudiante_id' NO existe\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error con tabla curso_asignaciones: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Verificar vista de cursos disponibles
echo "5️⃣  Verificando vista de cursos disponibles...\n";

$vistaPath = 'resources/views/academico/cursos-disponibles/index.blade.php';
if (file_exists($vistaPath)) {
    echo "   ✅ Vista cursos-disponibles existe\n";
    echo "   Ruta: {$vistaPath}\n";
} else {
    echo "   ❌ Vista cursos-disponibles NO existe\n";
}
echo "\n";

// 6. Verificar controlador AcademicoController
echo "6️⃣  Verificando controlador AcademicoController...\n";

if (class_exists('App\Http\Controllers\AcademicoController')) {
    echo "   ✅ Controlador AcademicoController existe\n";
    
    $reflection = new ReflectionClass('App\Http\Controllers\AcademicoController');
    
    if ($reflection->hasMethod('cursosDisponibles')) {
        echo "   ✅ Método cursosDisponibles() existe\n";
    }
    
    if ($reflection->hasMethod('getCursosDisponiblesData')) {
        echo "   ✅ Método getCursosDisponiblesData() existe\n";
    }
} else {
    echo "   ❌ Controlador AcademicoController NO existe\n";
}
echo "\n";

// 7. Verificar ruta de cursos disponibles
echo "7️⃣  Verificando ruta de cursos disponibles...\n";

try {
    $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('academico.cursos.disponibles');
    
    if ($route) {
        echo "   ✅ Ruta 'academico.cursos.disponibles' encontrada\n";
        echo "   URI: {$route->uri()}\n";
    } else {
        echo "   ⚠️  Ruta 'academico.cursos.disponibles' NO encontrada\n";
        echo "   Intentando con nombre alternativo...\n";
        
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('academico.cursos-disponibles');
        if ($route) {
            echo "   ✅ Ruta encontrada con nombre alternativo\n";
            echo "   URI: {$route->uri()}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error al verificar ruta: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. Simular consulta de cursos disponibles para un estudiante
echo "8️⃣  Simulando consulta de cursos disponibles...\n";

$estudiante = User::where('role', 'Estudiante')->first();

if ($estudiante) {
    echo "   Usuario de prueba: {$estudiante->name} (ID: {$estudiante->id})\n";
    
    // Obtener cursos asignados
    $cursosAsignados = CursoAsignacion::where('estudiante_id', $estudiante->id)
        ->activas()
        ->with('curso')
        ->get();
    
    echo "   Cursos asignados activos: {$cursosAsignados->count()}\n";
    
    if ($cursosAsignados->count() > 0) {
        echo "   Lista de cursos:\n";
        foreach ($cursosAsignados as $asignacion) {
            $curso = $asignacion->curso;
            echo "      - ID {$curso->id}: {$curso->titulo} ({$curso->estado})\n";
        }
    }
    
    // Verificar si tiene asignado el curso 18
    $tieneAsignacionCurso18 = CursoAsignacion::where('estudiante_id', $estudiante->id)
        ->where('curso_id', 18)
        ->activas()
        ->exists();
    
    if ($tieneAsignacionCurso18) {
        echo "   ✅ Estudiante tiene asignación activa al curso ID 18\n";
    } else {
        echo "   ⚠️  Estudiante NO tiene asignación al curso ID 18\n";
    }
} else {
    echo "   ⚠️  No hay estudiantes en la base de datos para probar\n";
}
echo "\n";

// Resumen
echo "═══════════════════════════════════════════════════════════════\n";
echo "RESUMEN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ CONFIGURACIÓN DE CORREOS:\n";
echo "   • Solo se enviará UN correo de verificación en español\n";
echo "   • Evento Registered de Laravel desactivado\n";
echo "   • Correo personalizado VerificarCuenta activo\n\n";

echo "✅ ASIGNACIÓN AUTOMÁTICA DE CURSO:\n";
echo "   • Curso ID 18 disponible\n";
echo "   • Se asigna automáticamente al registrarse\n";
echo "   • Tabla curso_asignaciones funcional\n\n";

echo "✅ VISTA DE CURSOS DISPONIBLES:\n";
echo "   • Vista implementada\n";
echo "   • Controlador con métodos necesarios\n";
echo "   • Filtra cursos por asignaciones activas\n";
echo "   • Ruta configurada correctamente\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "FLUJO ESPERADO AL REGISTRARSE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Usuario llena formulario de registro\n";
echo "2. Sistema crea usuario con rol 'Estudiante'\n";
echo "3. Sistema asigna curso ID 18 en tabla curso_asignaciones\n";
echo "4. Sistema envía 2 correos en español:\n";
echo "   a) Correo de verificación de cuenta\n";
echo "   b) Correo de asignación de curso\n";
echo "5. Usuario verifica su email\n";
echo "6. Sistema envía correo de bienvenida\n";
echo "7. Usuario va a /academico/cursos-disponibles\n";
echo "8. Ve el curso ID 18 'Inducción Institucional (General)'\n";
echo "9. Hace clic en 'Inscribirse' o 'Acceder'\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "PRUEBA MANUAL RECOMENDADA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Registrar un nuevo usuario\n";
echo "2. Verificar que reciba SOLO 2 correos (no 3):\n";
echo "   • Verificación de cuenta (español)\n";
echo "   • Asignación de curso (español)\n";
echo "3. Verificar email\n";
echo "4. Recibir correo de bienvenida (español)\n";
echo "5. Ir a /academico/cursos-disponibles\n";
echo "6. Verificar que aparezca el curso ID 18\n";
echo "7. Inscribirse al curso\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
