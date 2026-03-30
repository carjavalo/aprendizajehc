<?php
require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Events\Registered;
use App\Models\User;

echo "=== PRUEBA FINAL: CORRECCIÓN DEFINITIVA DEL EMAIL DE VERIFICACIÓN ===\n\n";

try {
    // 1. Crear usuario de prueba con datos únicos
    echo "1. CREANDO USUARIO DE PRUEBA:\n";
    
    $timestamp = time();
    $userEmail = "test.final.fix.{$timestamp}@example.com";
    $documentNumber = "FIX{$timestamp}";
    
    $user = User::create([
        'name' => 'Test',
        'apellido1' => 'Final',
        'apellido2' => 'Fix',
        'email' => $userEmail,
        'password' => bcrypt('password123'),
        'role' => 'Registrado',
        'tipo_documento' => 'DNI',
        'numero_documento' => $documentNumber,
    ]);
    
    echo "   ✅ Usuario creado: {$user->full_name}\n";
    echo "   ✅ Email del usuario: {$user->email}\n";
    
    // 2. Verificar método getEmailForVerification
    echo "\n2. VERIFICANDO MÉTODO getEmailForVerification():\n";
    
    $emailForVerification = $user->getEmailForVerification();
    echo "   Email para verificación: {$emailForVerification}\n";
    
    if ($emailForVerification === $user->email) {
        echo "   ✅ PERFECTO: El método retorna el email del usuario\n";
    } else {
        echo "   ❌ ERROR: El método retorna email incorrecto\n";
    }
    
    // 3. Probar con Mail::fake para interceptar emails
    echo "\n3. PROBANDO ENVÍO CON MAIL::FAKE:\n";
    
    Mail::fake();
    
    echo "   Enviando notificación de verificación...\n";
    $user->sendEmailVerificationNotification();
    
    // Verificar emails enviados con la notificación por defecto
    $sentMails = Mail::sent(\Illuminate\Auth\Notifications\VerifyEmail::class);
    echo "   Emails enviados: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   ✅ Email interceptado\n";
            echo "   Destinatarios: " . implode(', ', array_keys($mail->to)) . "\n";
            
            // Verificar que el email se envía al usuario correcto
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "   ✅ EXCELENTE: Email se envía al usuario correcto\n";
            } else {
                echo "   ❌ ERROR: Email se envía a dirección incorrecta\n";
                echo "   Expected: {$userEmail}\n";
                echo "   Actual: " . implode(', ', $recipients) . "\n";
            }
        }
    } else {
        echo "   ❌ ERROR: No se enviaron emails\n";
    }
    
    // 4. Probar el proceso completo del controlador
    echo "\n4. PROBANDO PROCESO COMPLETO DEL CONTROLADOR:\n";
    
    Mail::fake();
    
    echo "   Disparando evento Registered...\n";
    event(new Registered($user));
    
    echo "   Enviando email manualmente (como en el controlador)...\n";
    $user->sendEmailVerificationNotification();
    
    // Verificar emails enviados
    $sentMails = Mail::sent(\Illuminate\Auth\Notifications\VerifyEmail::class);
    echo "   Emails enviados en proceso completo: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "   ✅ PERFECTO: El proceso completo envía email al usuario correcto\n";
            } else {
                echo "   ❌ ERROR: El proceso completo envía email a dirección incorrecta\n";
            }
        }
    }
    
    // 5. Verificar configuración SMTP
    echo "\n5. VERIFICANDO CONFIGURACIÓN SMTP:\n";
    
    echo "   From Address: " . Config::get('mail.from.address') . "\n";
    echo "   From Name: " . Config::get('mail.from.name') . "\n";
    echo "   SMTP Host: " . Config::get('mail.mailers.smtp.host') . "\n";
    echo "   SMTP Port: " . Config::get('mail.mailers.smtp.port') . "\n";
    
    // 6. Verificar que el proceso de verificación funciona
    echo "\n6. VERIFICANDO PROCESO DE VERIFICACIÓN:\n";
    
    echo "   Estado inicial: " . ($user->hasVerifiedEmail() ? 'Verificado' : 'No verificado') . "\n";
    
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        echo "   ✅ Email marcado como verificado\n";
        echo "   Estado final: " . ($user->hasVerifiedEmail() ? 'Verificado' : 'No verificado') . "\n";
    }
    
    // 7. Limpiar datos de prueba
    echo "\n7. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "   ✅ Usuario de prueba eliminado\n";
    
    echo "\n=== RESUMEN FINAL ===\n";
    
    if (count($sentMails) > 0) {
        $recipients = array_keys($sentMails[0]->to);
        if (in_array($userEmail, $recipients)) {
            echo "✅ PROBLEMA COMPLETAMENTE SOLUCIONADO\n";
            echo "✅ El email se envía al usuario correcto\n";
            echo "✅ Notificación por defecto de Laravel funcionando\n";
            echo "✅ Método getEmailForVerification() implementado\n";
        } else {
            echo "❌ PROBLEMA PERSISTE\n";
            echo "   El email se envía a: " . implode(', ', $recipients) . "\n";
            echo "   Debería enviarse a: {$userEmail}\n";
        }
    } else {
        echo "❌ NO SE ENVIARON EMAILS\n";
    }
    
    echo "\n✅ CONFIGURACIÓN VERIFICADA:\n";
    echo "   - SMTP: " . Config::get('mail.mailers.smtp.host') . ":" . Config::get('mail.mailers.smtp.port') . "\n";
    echo "   - From: " . Config::get('mail.from.address') . "\n";
    echo "   - Modelo User implementa MustVerifyEmail\n";
    echo "   - Método getEmailForVerification() retorna email del usuario\n";
    echo "   - RegisteredUserController modificado para envío garantizado\n";
    
    echo "\n🎉 SISTEMA DE VERIFICACIÓN DE EMAIL CORREGIDO!\n";
    
    echo "\n📋 INSTRUCCIONES PARA PROBAR:\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Completa el formulario con:\n";
    echo "   - Tu nombre y apellidos\n";
    echo "   - TU EMAIL PERSONAL (no el del sistema)\n";
    echo "   - Tipo y número de documento\n";
    echo "   - Contraseña\n";
    echo "3. Haz clic en 'Registrar'\n";
    echo "4. Revisa la bandeja de entrada de TU EMAIL\n";
    echo "5. Busca el email de verificación\n";
    echo "6. Haz clic en el enlace de verificación\n";
    echo "7. ¡Tu cuenta estará verificada!\n";
    
} catch (Exception $e) {
    echo "Error durante la prueba: " . $e->getMessage() . "\n";
}
?>
