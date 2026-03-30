<?php
/**
 * Script de Prueba: Inscripción a Curso
 * 
 * Verifica que la ruta de inscripción funcione correctamente
 * 
 * Uso: php test_inscripcion_curso.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PRUEBA: Ruta de Inscripción a Curso\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Verificar que la ruta existe
echo "1️⃣  Verificando ruta de inscripción...\n";

$routeName = 'academico.curso.inscribirse';
$route = Route::getRoutes()->getByName($routeName);

if ($route) {
    echo "   ✅ Ruta '{$routeName}' encontrada\n";
    echo "   URI: {$route->uri()}\n";
    echo "   Métodos: " . implode(', ', $route->methods()) . "\n";
    echo "   Acción: {$route->getActionName()}\n";
} else {
    echo "   ❌ Ruta '{$routeName}' NO encontrada\n";
}
echo "\n";

// 2. Verificar métodos HTTP permitidos
echo "2️⃣  Verificando métodos HTTP...\n";

if ($route) {
    $methods = $route->methods();
    
    $expectedMethods = ['GET', 'POST', 'HEAD'];
    $hasAllMethods = true;
    
    foreach ($expectedMethods as $method) {
        if (in_array($method, $methods)) {
            echo "   ✅ Método {$method} permitido\n";
        } else {
            echo "   ❌ Método {$method} NO permitido\n";
            $hasAllMethods = false;
        }
    }
    
    if ($hasAllMethods) {
        echo "   ✅ Todos los métodos necesarios están configurados\n";
    } else {
        echo "   ⚠️  Faltan métodos necesarios\n";
    }
} else {
    echo "   ❌ No se puede verificar métodos (ruta no encontrada)\n";
}
echo "\n";

// 3. Generar URL de prueba
echo "3️⃣  Generando URL de inscripción...\n";

try {
    $cursoId = 18;
    $url = route('academico.curso.inscribirse', $cursoId);
    echo "   ✅ URL generada correctamente\n";
    echo "   URL: {$url}\n";
    
    // Verificar que la URL contiene el ID del curso
    if (strpos($url, (string)$cursoId) !== false) {
        echo "   ✅ URL contiene el ID del curso ({$cursoId})\n";
    } else {
        echo "   ⚠️  URL no contiene el ID del curso\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error al generar URL: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Verificar controlador
echo "4️⃣  Verificando controlador...\n";

$controllerClass = 'App\Http\Controllers\AcademicoController';
$controllerMethod = 'inscribirseCurso';

if (class_exists($controllerClass)) {
    echo "   ✅ Controlador {$controllerClass} existe\n";
    
    if (method_exists($controllerClass, $controllerMethod)) {
        echo "   ✅ Método {$controllerMethod} existe\n";
        
        // Verificar parámetros del método
        $reflection = new ReflectionMethod($controllerClass, $controllerMethod);
        $parameters = $reflection->getParameters();
        
        echo "   Parámetros del método:\n";
        foreach ($parameters as $param) {
            $type = $param->getType() ? $param->getType()->getName() : 'mixed';
            echo "      - \${$param->getName()} ({$type})\n";
        }
    } else {
        echo "   ❌ Método {$controllerMethod} NO existe\n";
    }
} else {
    echo "   ❌ Controlador {$controllerClass} NO existe\n";
}
echo "\n";

// 5. Verificar middleware
echo "5️⃣  Verificando middleware de la ruta...\n";

if ($route) {
    $middleware = $route->gatherMiddleware();
    
    if (count($middleware) > 0) {
        echo "   Middleware aplicado:\n";
        foreach ($middleware as $mw) {
            echo "      - {$mw}\n";
        }
        
        // Verificar que tenga autenticación
        $hasAuth = false;
        foreach ($middleware as $mw) {
            if (strpos($mw, 'auth') !== false) {
                $hasAuth = true;
                break;
            }
        }
        
        if ($hasAuth) {
            echo "   ✅ Ruta protegida con autenticación\n";
        } else {
            echo "   ⚠️  Ruta NO tiene middleware de autenticación\n";
        }
    } else {
        echo "   ⚠️  No hay middleware aplicado\n";
    }
} else {
    echo "   ❌ No se puede verificar middleware (ruta no encontrada)\n";
}
echo "\n";

// Resumen
echo "═══════════════════════════════════════════════════════════════\n";
echo "RESUMEN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($route && in_array('GET', $route->methods()) && in_array('POST', $route->methods())) {
    echo "✅ CORRECCIÓN EXITOSA\n\n";
    echo "La ruta de inscripción ahora acepta:\n";
    echo "  • Método GET: Para enlaces directos desde correos\n";
    echo "  • Método POST: Para peticiones AJAX desde la interfaz\n\n";
    echo "El usuario puede:\n";
    echo "  1. Hacer clic en el enlace del correo de asignación\n";
    echo "  2. Inscribirse desde la vista de cursos disponibles\n\n";
    echo "Ambos métodos funcionarán correctamente.\n";
} else {
    echo "❌ PROBLEMA DETECTADO\n\n";
    echo "La ruta no está configurada correctamente.\n";
    echo "Verifica que la ruta acepte GET y POST.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "PRUEBA MANUAL RECOMENDADA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Registrar un nuevo usuario\n";
echo "2. Verificar que reciba el correo de asignación\n";
echo "3. Hacer clic en el botón 'Inscribirme Ahora' del correo\n";
echo "4. Verificar que se inscriba correctamente\n";
echo "5. Verificar redirección a cursos disponibles\n";
echo "6. Verificar mensaje de éxito\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
