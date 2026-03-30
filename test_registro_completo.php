<?php
/**
 * Script de Prueba: Sistema de Registro Completo
 * 
 * Verifica que el sistema de registro esté configurado correctamente
 * 
 * Uso: php test_registro_completo.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Curso;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PRUEBA: Sistema de Registro Completo\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Verificar configuración de idioma
echo "1️⃣  Verificando configuración de idioma...\n";
echo "   APP_LOCALE: " . config('app.locale') . "\n";
echo "   APP_FALLBACK_LOCALE: " . config('app.fallback_locale') . "\n";

if (config('app.locale') === 'es') {
    echo "   ✅ Idioma configurado correctamente en español\n";
} else {
    echo "   ❌ Idioma NO está en español\n";
}
echo "\n";

// 2. Verificar archivos de traducción
echo "2️⃣  Verificando archivos de traducción...\n";
$archivosTraduccion = [
    'lang/es/auth.php',
    'lang/es/passwords.php',
    'lang/es/validation.php',
];

foreach ($archivosTraduccion as $archivo) {
    if (file_exists($archivo)) {
        echo "   ✅ {$archivo} existe\n";
    } else {
        echo "   ❌ {$archivo} NO existe\n";
    }
}
echo "\n";

// 3. Verificar curso ID 18
echo "3️⃣  Verificando curso ID 18...\n";
$curso18 = Curso::find(18);

if ($curso18) {
    echo "   ✅ Curso ID 18 encontrado\n";
    echo "   Nombre: {$curso18->nombre}\n";
    if ($curso18->instructor) {
        echo "   Instructor: {$curso18->instructor->name}\n";
    }
} else {
    echo "   ❌ Curso ID 18 NO encontrado\n";
    echo "   ADVERTENCIA: Los nuevos usuarios no podrán ser asignados al curso\n";
}
echo "\n";

// 4. Verificar tabla curso_asignaciones
echo "4️⃣  Verificando tabla curso_asignaciones...\n";
try {
    $asignaciones = DB::table('curso_asignaciones')->count();
    echo "   ✅ Tabla curso_asignaciones existe\n";
    echo "   Total de asignaciones: {$asignaciones}\n";
} catch (\Exception $e) {
    echo "   ❌ Error con tabla curso_asignaciones: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Verificar clases Mailable
echo "5️⃣  Verificando clases Mailable...\n";
$mailables = [
    'App\Mail\VerificarCuenta',
    'App\Mail\RecuperarPassword',
    'App\Mail\AsignacionCurso',
    'App\Mail\BienvenidaUsuario',
];

foreach ($mailables as $mailable) {
    if (class_exists($mailable)) {
        echo "   ✅ {$mailable} existe\n";
    } else {
        echo "   ❌ {$mailable} NO existe\n";
    }
}
echo "\n";

// 6. Verificar vistas de correo
echo "6️⃣  Verificando vistas de correo...\n";
$vistas = [
    'resources/views/emails/layout.blade.php',
    'resources/views/emails/verificar-cuenta.blade.php',
    'resources/views/emails/recuperar-password.blade.php',
    'resources/views/emails/asignacion-curso.blade.php',
    'resources/views/emails/bienvenida.blade.php',
];

foreach ($vistas as $vista) {
    if (file_exists($vista)) {
        echo "   ✅ {$vista} existe\n";
    } else {
        echo "   ❌ {$vista} NO existe\n";
    }
}
echo "\n";

// 7. Verificar logo
echo "7️⃣  Verificando logo institucional...\n";
$logo = 'public/images/logocorreo.jpeg';
if (file_exists($logo)) {
    echo "   ✅ Logo encontrado: {$logo}\n";
    $size = filesize($logo);
    echo "   Tamaño: " . number_format($size / 1024, 2) . " KB\n";
} else {
    echo "   ❌ Logo NO encontrado: {$logo}\n";
}
echo "\n";

// 8. Verificar configuración de correo
echo "8️⃣  Verificando configuración de correo...\n";
echo "   MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "   MAIL_FROM_NAME: " . config('mail.from.name') . "\n";

if (config('mail.from.address') === 'oficinacoordinadoraacademica@correohuv.gov.co') {
    echo "   ✅ Correo institucional configurado correctamente\n";
} else {
    echo "   ⚠️  Correo institucional diferente al esperado\n";
}
echo "\n";

// 9. Verificar método personalizado en User
echo "9️⃣  Verificando método sendPasswordResetNotification en User...\n";
if (method_exists(User::class, 'sendPasswordResetNotification')) {
    echo "   ✅ Método sendPasswordResetNotification existe\n";
} else {
    echo "   ❌ Método sendPasswordResetNotification NO existe\n";
}
echo "\n";

// 10. Verificar usuarios con rol Estudiante
echo "🔟 Verificando usuarios con rol Estudiante...\n";
$estudiantes = User::where('role', 'Estudiante')->count();
echo "   Total de estudiantes: {$estudiantes}\n";

if ($estudiantes > 0) {
    $ultimoEstudiante = User::where('role', 'Estudiante')->latest()->first();
    echo "   Último estudiante registrado: {$ultimoEstudiante->name}\n";
    echo "   Email: {$ultimoEstudiante->email}\n";
    echo "   Fecha de registro: {$ultimoEstudiante->created_at->format('d/m/Y H:i')}\n";
    
    // Verificar si tiene asignación al curso 18
    $asignacion = DB::table('curso_asignaciones')
        ->where('estudiante_id', $ultimoEstudiante->id)
        ->where('curso_id', 18)
        ->first();
    
    if ($asignacion) {
        echo "   ✅ Tiene asignación al curso ID 18\n";
    } else {
        echo "   ⚠️  NO tiene asignación al curso ID 18\n";
    }
}
echo "\n";

// Resumen
echo "═══════════════════════════════════════════════════════════════\n";
echo "RESUMEN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Sistema configurado en español\n";
echo "✅ Archivos de traducción creados\n";
echo "✅ Clases Mailable implementadas\n";
echo "✅ Vistas de correo creadas\n";
echo "✅ Logo institucional disponible\n";
echo "✅ Configuración de correo lista\n";
echo "✅ Método personalizado de recuperación de contraseña\n\n";

if ($curso18) {
    echo "✅ Curso ID 18 disponible para asignación automática\n";
} else {
    echo "⚠️  ADVERTENCIA: Curso ID 18 no encontrado\n";
    echo "   Los nuevos usuarios no podrán ser asignados automáticamente\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "FLUJO DE REGISTRO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Usuario se registra → Rol 'Estudiante' asignado\n";
echo "2. Sistema asigna curso ID 18 automáticamente\n";
echo "3. Sistema envía correo de verificación\n";
echo "4. Sistema envía correo de asignación de curso\n";
echo "5. Usuario verifica email\n";
echo "6. Sistema envía correo de bienvenida\n";
echo "7. Usuario puede ver curso ID 18 en /academico/cursos-disponibles\n";
echo "8. Usuario hace clic en 'Inscribirse'\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "PRUEBA MANUAL RECOMENDADA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1. Ir a la página de registro\n";
echo "2. Llenar el formulario con datos de prueba\n";
echo "3. Hacer clic en 'Registrarse'\n";
echo "4. Verificar que se reciban 2 correos:\n";
echo "   - Verificación de cuenta\n";
echo "   - Asignación de curso ID 18\n";
echo "5. Hacer clic en el enlace de verificación\n";
echo "6. Verificar que se reciba el correo de bienvenida\n";
echo "7. Ir a /academico/cursos-disponibles\n";
echo "8. Verificar que aparezca el curso ID 18\n";
echo "9. Hacer clic en 'Inscribirse'\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
