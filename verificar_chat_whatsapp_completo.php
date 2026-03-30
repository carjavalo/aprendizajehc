<?php
/**
 * Verificación completa del sistema de chat WhatsApp
 * Ejecutar: php verificar_chat_whatsapp_completo.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Route;

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║     VERIFICACIÓN COMPLETA - CHAT WHATSAPP DASHBOARD             ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$errores = [];
$advertencias = [];
$exitos = [];

// 1. Verificar campo phone en tabla users
echo "1. Verificando campo 'phone' en tabla users...\n";
try {
    $usuario = User::first();
    if ($usuario && isset($usuario->phone)) {
        $exitos[] = "Campo 'phone' existe y es accesible en modelo User";
        echo "   ✓ Campo 'phone' existe y es accesible\n";
    } else {
        // Verificar en fillable
        $fillable = (new User())->getFillable();
        if (in_array('phone', $fillable)) {
            $exitos[] = "Campo 'phone' está en \$fillable del modelo User";
            echo "   ✓ Campo 'phone' en \$fillable\n";
        } else {
            $errores[] = "Campo 'phone' no encontrado en modelo User";
            echo "   ✗ Campo 'phone' no encontrado\n";
        }
    }
} catch (\Exception $e) {
    $errores[] = "Error al verificar campo phone: " . $e->getMessage();
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Verificar usuarios con teléfono
echo "2. Verificando usuarios con teléfono...\n";
$totalUsuarios = User::count();
$usuariosConTelefono = User::whereNotNull('phone')->where('phone', '!=', '')->count();
$porcentaje = $totalUsuarios > 0 ? round(($usuariosConTelefono / $totalUsuarios) * 100, 2) : 0;

echo "   Total usuarios: {$totalUsuarios}\n";
echo "   Con teléfono: {$usuariosConTelefono} ({$porcentaje}%)\n";

if ($usuariosConTelefono > 0) {
    $exitos[] = "{$usuariosConTelefono} usuarios con teléfono registrado";
    echo "   ✓ Hay usuarios con teléfono\n";
} else {
    $advertencias[] = "No hay usuarios con teléfono registrado";
    echo "   ⚠ No hay usuarios con teléfono\n";
    echo "   → Ejecutar: php agregar_telefonos_usuarios.php\n";
}
echo "\n";

// 3. Verificar ruta de búsqueda
echo "3. Verificando ruta de búsqueda...\n";
$rutaExiste = false;
foreach (Route::getRoutes() as $route) {
    if ($route->getName() === 'dashboard.buscar-estudiantes') {
        $rutaExiste = true;
        $exitos[] = "Ruta 'dashboard.buscar-estudiantes' registrada";
        echo "   ✓ Ruta registrada: " . $route->uri() . "\n";
        echo "   ✓ Método: " . implode('|', $route->methods()) . "\n";
        break;
    }
}
if (!$rutaExiste) {
    $errores[] = "Ruta 'dashboard.buscar-estudiantes' no encontrada";
    echo "   ✗ Ruta no encontrada\n";
}
echo "\n";

// 4. Verificar método en DashboardController
echo "4. Verificando DashboardController...\n";
if (method_exists(\App\Http\Controllers\DashboardController::class, 'buscarEstudiantes')) {
    $exitos[] = "Método 'buscarEstudiantes' existe en DashboardController";
    echo "   ✓ Método 'buscarEstudiantes' existe\n";
} else {
    $errores[] = "Método 'buscarEstudiantes' no encontrado";
    echo "   ✗ Método no encontrado\n";
}

if (method_exists(\App\Http\Controllers\DashboardController::class, 'index')) {
    $exitos[] = "Método 'index' existe en DashboardController";
    echo "   ✓ Método 'index' existe\n";
} else {
    $errores[] = "Método 'index' no encontrado";
    echo "   ✗ Método 'index' no encontrado\n";
}
echo "\n";

// 5. Verificar vista dashboard
echo "5. Verificando vista dashboard.blade.php...\n";
$vistaPath = resource_path('views/dashboard.blade.php');
if (file_exists($vistaPath)) {
    $contenido = file_get_contents($vistaPath);
    
    // Verificar widget HTML
    if (strpos($contenido, 'whatsapp-chat-widget') !== false) {
        $exitos[] = "Widget HTML de WhatsApp presente en vista";
        echo "   ✓ Widget HTML presente\n";
    } else {
        $errores[] = "Widget HTML no encontrado en vista";
        echo "   ✗ Widget HTML no encontrado\n";
    }
    
    // Verificar JavaScript
    if (strpos($contenido, 'buscarEstudiantes') !== false) {
        $exitos[] = "Función JavaScript 'buscarEstudiantes' presente";
        echo "   ✓ JavaScript de búsqueda presente\n";
    } else {
        $errores[] = "JavaScript de búsqueda no encontrado";
        echo "   ✗ JavaScript no encontrado\n";
    }
    
    // Verificar función de envío
    if (strpos($contenido, 'enviarWhatsApp') !== false) {
        $exitos[] = "Función JavaScript 'enviarWhatsApp' presente";
        echo "   ✓ JavaScript de envío presente\n";
    } else {
        $errores[] = "JavaScript de envío no encontrado";
        echo "   ✗ JavaScript de envío no encontrado\n";
    }
    
    // Verificar variable totalUsuarios
    if (strpos($contenido, '$totalUsuarios') !== false) {
        $exitos[] = "Variable \$totalUsuarios presente en vista";
        echo "   ✓ Variable \$totalUsuarios presente\n";
    } else {
        $advertencias[] = "Variable \$totalUsuarios no encontrada en vista";
        echo "   ⚠ Variable \$totalUsuarios no encontrada\n";
    }
} else {
    $errores[] = "Vista dashboard.blade.php no encontrada";
    echo "   ✗ Vista no encontrada\n";
}
echo "\n";

// 6. Probar búsqueda
echo "6. Probando funcionalidad de búsqueda...\n";
if ($usuariosConTelefono > 0) {
    $query = 'estudiante';
    $resultados = User::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('numero_documento', 'LIKE', "%{$query}%");
        })
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->limit(10)
        ->get();
    
    echo "   Query: '{$query}'\n";
    echo "   Resultados: " . $resultados->count() . "\n";
    
    if ($resultados->count() > 0) {
        $exitos[] = "Búsqueda funcional con {$resultados->count()} resultados";
        echo "   ✓ Búsqueda funcional\n";
        
        foreach ($resultados->take(3) as $user) {
            $nombreCompleto = trim($user->name . ' ' . $user->apellido1 . ' ' . $user->apellido2);
            echo "     - {$nombreCompleto} ({$user->phone})\n";
        }
    } else {
        $advertencias[] = "Búsqueda no retorna resultados para '{$query}'";
        echo "   ⚠ Sin resultados para '{$query}'\n";
    }
} else {
    $advertencias[] = "No se puede probar búsqueda sin usuarios con teléfono";
    echo "   ⚠ No hay usuarios para probar búsqueda\n";
}
echo "\n";

// 7. Verificar formato WhatsApp
echo "7. Verificando formato de URL WhatsApp...\n";
if ($usuariosConTelefono > 0) {
    $usuario = User::whereNotNull('phone')->where('phone', '!=', '')->first();
    $telefono = preg_replace('/\D/', '', $usuario->phone);
    $mensaje = "Mensaje de prueba";
    $url = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
    
    echo "   Usuario: {$usuario->name}\n";
    echo "   Teléfono: {$usuario->phone}\n";
    echo "   Teléfono limpio: {$telefono}\n";
    echo "   URL: {$url}\n";
    
    if (strlen($telefono) >= 10) {
        $exitos[] = "Formato de URL WhatsApp correcto";
        echo "   ✓ Formato correcto\n";
    } else {
        $advertencias[] = "Teléfono muy corto: {$telefono}";
        echo "   ⚠ Teléfono parece incorrecto\n";
    }
} else {
    $advertencias[] = "No se puede verificar formato sin usuarios";
    echo "   ⚠ No hay usuarios para verificar\n";
}
echo "\n";

// 8. Verificar archivos de documentación
echo "8. Verificando documentación...\n";
$archivosDoc = [
    'IMPLEMENTACION_CHAT_WHATSAPP_DASHBOARD.md',
    'RESUMEN_IMPLEMENTACION_CHAT_WHATSAPP.md',
    'INSTRUCCIONES_CHAT_WHATSAPP.txt',
    'EJEMPLOS_USO_CHAT_WHATSAPP.md',
];

foreach ($archivosDoc as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        $exitos[] = "Documentación '{$archivo}' presente";
        echo "   ✓ {$archivo}\n";
    } else {
        $advertencias[] = "Documentación '{$archivo}' no encontrada";
        echo "   ⚠ {$archivo} no encontrado\n";
    }
}
echo "\n";

// RESUMEN FINAL
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                        RESUMEN FINAL                             ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "✓ ÉXITOS: " . count($exitos) . "\n";
foreach ($exitos as $exito) {
    echo "  • {$exito}\n";
}
echo "\n";

if (count($advertencias) > 0) {
    echo "⚠ ADVERTENCIAS: " . count($advertencias) . "\n";
    foreach ($advertencias as $advertencia) {
        echo "  • {$advertencia}\n";
    }
    echo "\n";
}

if (count($errores) > 0) {
    echo "✗ ERRORES: " . count($errores) . "\n";
    foreach ($errores as $error) {
        echo "  • {$error}\n";
    }
    echo "\n";
}

// Estado final
echo "╔══════════════════════════════════════════════════════════════════╗\n";
if (count($errores) === 0) {
    echo "║  ✅ SISTEMA OPERATIVO - LISTO PARA USO                          ║\n";
} else {
    echo "║  ⚠️  SISTEMA CON ERRORES - REVISAR IMPLEMENTACIÓN               ║\n";
}
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "PRÓXIMOS PASOS:\n";
echo "1. Acceder a: http://192.168.2.200:8001/dashboard\n";
echo "2. Verificar que aparece el widget de chat\n";
echo "3. Probar búsqueda de estudiantes\n";
echo "4. Enviar mensaje de prueba\n";
echo "\n";

echo "DOCUMENTACIÓN:\n";
echo "• IMPLEMENTACION_CHAT_WHATSAPP_DASHBOARD.md (técnica)\n";
echo "• RESUMEN_IMPLEMENTACION_CHAT_WHATSAPP.md (ejecutiva)\n";
echo "• INSTRUCCIONES_CHAT_WHATSAPP.txt (uso rápido)\n";
echo "• EJEMPLOS_USO_CHAT_WHATSAPP.md (casos prácticos)\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════════\n";
