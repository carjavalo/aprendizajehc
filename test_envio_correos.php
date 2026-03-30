<?php
/**
 * Script de Prueba: Sistema de Envío de Correos
 * 
 * Este script prueba el envío de todos los tipos de correos implementados.
 * 
 * Uso: php test_envio_correos.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Curso;
use App\Mail\VerificarCuenta;
use App\Mail\RecuperarPassword;
use App\Mail\InscripcionCurso;
use App\Mail\AsignacionCurso;
use App\Mail\BienvenidaUsuario;
use Illuminate\Support\Facades\Mail;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PRUEBA DE SISTEMA DE ENVÍO DE CORREOS ELECTRÓNICOS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Verificar configuración de correo
echo "📧 Verificando configuración de correo...\n";
echo "   MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "   MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "   MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "   MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "   MAIL_FROM_NAME: " . config('mail.from.name') . "\n\n";

// Obtener usuario de prueba
echo "👤 Buscando usuario de prueba...\n";
$user = User::first();

if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    echo "   Por favor, crea al menos un usuario para realizar las pruebas.\n";
    exit(1);
}

echo "✅ Usuario encontrado:\n";
echo "   Nombre: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Rol: {$user->role}\n\n";

// Preguntar si desea continuar
echo "⚠️  IMPORTANTE: Se enviarán correos de prueba a: {$user->email}\n";
echo "¿Deseas continuar? (s/n): ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) != 's' && trim($line) != 'S'){
    echo "Prueba cancelada.\n";
    exit(0);
}
fclose($handle);

echo "\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "INICIANDO PRUEBAS DE ENVÍO\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$errores = 0;
$exitosos = 0;

// 1. Probar correo de verificación de cuenta
echo "1️⃣  Correo de Verificación de Cuenta\n";
echo "   Preparando...\n";
try {
    $verificationUrl = url('/email/verify/' . $user->id . '/test-hash');
    Mail::to($user->email)->send(new VerificarCuenta($user, $verificationUrl));
    echo "   ✅ Enviado exitosamente\n";
    echo "   📬 Revisa la bandeja de: {$user->email}\n";
    $exitosos++;
} catch (\Exception $e) {
    echo "   ❌ Error al enviar: " . $e->getMessage() . "\n";
    $errores++;
}
echo "\n";

sleep(2); // Esperar 2 segundos entre envíos

// 2. Probar correo de recuperación de contraseña
echo "2️⃣  Correo de Recuperación de Contraseña\n";
echo "   Preparando...\n";
try {
    $resetUrl = url('/password/reset/test-token?email=' . urlencode($user->email));
    Mail::to($user->email)->send(new RecuperarPassword($user, $resetUrl));
    echo "   ✅ Enviado exitosamente\n";
    echo "   📬 Revisa la bandeja de: {$user->email}\n";
    $exitosos++;
} catch (\Exception $e) {
    echo "   ❌ Error al enviar: " . $e->getMessage() . "\n";
    $errores++;
}
echo "\n";

sleep(2);

// 3. Probar correo de bienvenida
echo "3️⃣  Correo de Bienvenida\n";
echo "   Preparando...\n";
try {
    $dashboardUrl = url('/dashboard');
    Mail::to($user->email)->send(new BienvenidaUsuario($user, $dashboardUrl));
    echo "   ✅ Enviado exitosamente\n";
    echo "   📬 Revisa la bandeja de: {$user->email}\n";
    $exitosos++;
} catch (\Exception $e) {
    echo "   ❌ Error al enviar: " . $e->getMessage() . "\n";
    $errores++;
}
echo "\n";

sleep(2);

// 4. Probar correo de inscripción a curso (si hay cursos)
echo "4️⃣  Correo de Inscripción a Curso\n";
$curso = Curso::first();

if (!$curso) {
    echo "   ⚠️  No hay cursos en la base de datos\n";
    echo "   Saltando esta prueba...\n\n";
} else {
    echo "   Curso: {$curso->nombre}\n";
    echo "   Preparando...\n";
    try {
        $cursoUrl = url('/academico/curso/' . $curso->id . '/aula-virtual');
        Mail::to($user->email)->send(new InscripcionCurso($user, $curso, $cursoUrl));
        echo "   ✅ Enviado exitosamente\n";
        echo "   📬 Revisa la bandeja de: {$user->email}\n";
        $exitosos++;
    } catch (\Exception $e) {
        echo "   ❌ Error al enviar: " . $e->getMessage() . "\n";
        $errores++;
    }
    echo "\n";
    
    sleep(2);
    
    // 5. Probar correo de asignación de curso
    echo "5️⃣  Correo de Asignación de Curso\n";
    echo "   Curso: {$curso->nombre}\n";
    echo "   Preparando...\n";
    try {
        $inscripcionUrl = url('/academico/cursos/' . $curso->id . '/inscribir');
        $fechaLimite = $curso->fecha_inicio ? 
            \Carbon\Carbon::parse($curso->fecha_inicio)->subDays(3)->format('d/m/Y') : 
            null;
        Mail::to($user->email)->send(new AsignacionCurso($user, $curso, $inscripcionUrl, $fechaLimite));
        echo "   ✅ Enviado exitosamente\n";
        echo "   📬 Revisa la bandeja de: {$user->email}\n";
        $exitosos++;
    } catch (\Exception $e) {
        echo "   ❌ Error al enviar: " . $e->getMessage() . "\n";
        $errores++;
    }
    echo "\n";
}

// Resumen
echo "═══════════════════════════════════════════════════════════════\n";
echo "RESUMEN DE PRUEBAS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Correos enviados exitosamente: {$exitosos}\n";
echo "❌ Errores encontrados: {$errores}\n\n";

if ($errores === 0) {
    echo "🎉 ¡TODAS LAS PRUEBAS PASARON EXITOSAMENTE!\n\n";
    echo "📬 Revisa la bandeja de entrada de: {$user->email}\n";
    echo "   Deberías ver {$exitosos} correos nuevos.\n\n";
    echo "💡 Consejos:\n";
    echo "   - Revisa también la carpeta de SPAM/Correo no deseado\n";
    echo "   - Los correos pueden tardar 1-2 minutos en llegar\n";
    echo "   - Verifica que el diseño se vea correctamente\n";
    echo "   - Prueba los enlaces en los correos\n\n";
} else {
    echo "⚠️  ALGUNAS PRUEBAS FALLARON\n\n";
    echo "Posibles causas:\n";
    echo "   - Credenciales de Gmail incorrectas en .env\n";
    echo "   - Contraseña de aplicación inválida\n";
    echo "   - Verificación en 2 pasos no activada\n";
    echo "   - Firewall bloqueando puerto 587\n";
    echo "   - Límite de envío de Gmail alcanzado\n\n";
    echo "Soluciones:\n";
    echo "   1. Verifica las credenciales en el archivo .env\n";
    echo "   2. Genera una nueva contraseña de aplicación\n";
    echo "   3. Verifica que el puerto 587 esté abierto\n";
    echo "   4. Revisa los logs en storage/logs/laravel.log\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
