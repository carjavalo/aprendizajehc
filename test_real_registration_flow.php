<?php
require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

echo "=== PRUEBA REAL DEL FLUJO DE REGISTRO ===\n\n";

try {
    // 1. Mostrar configuración actual
    echo "1. CONFIGURACIÓN ACTUAL:\n";
    echo "   MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
    echo "   MAIL_FROM_NAME: " . Config::get('mail.from.name') . "\n";
    echo "   MAIL_MAILER: " . Config::get('mail.default') . "\n";
    
    // 2. Simular datos del formulario de registro
    echo "\n2. SIMULANDO DATOS DEL FORMULARIO:\n";
    
    $timestamp = time();
    $formData = [
        'name' => 'Usuario',
        'apellido1' => 'Prueba',
        'apellido2' => 'Real',
        'email' => "usuario.real.{$timestamp}@ejemplo.com",
        'password' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "REAL{$timestamp}",
    ];
    
    echo "   Email ingresado en el formulario: {$formData['email']}\n";
    
    // 3. Simular exactamente lo que hace RegisteredUserController::store()
    echo "\n3. SIMULANDO RegisteredUserController::store():\n";
    
    echo "   Paso 1: Crear usuario...\n";
    $user = User::create([
        'name' => $formData['name'],
        'apellido1' => $formData['apellido1'],
        'apellido2' => $formData['apellido2'],
        'email' => $formData['email'],
        'password' => bcrypt($formData['password']),
        'role' => 'Registrado',
        'tipo_documento' => $formData['tipo_documento'],
        'numero_documento' => $formData['numero_documento'],
    ]);
    
    echo "   ✅ Usuario creado con ID: {$user->id}\n";
    echo "   ✅ Email del usuario: {$user->email}\n";
    
    // 4. Interceptar emails para ver exactamente qué pasa
    echo "\n4. INTERCEPTANDO EMAILS:\n";
    
    Mail::fake();
    
    echo "   Paso 2: Disparar evento Registered...\n";
    event(new Registered($user));
    
    echo "   Paso 3: Enviar email manualmente...\n";
    $user->sendEmailVerificationNotification();
    
    // 5. Analizar emails interceptados
    echo "\n5. ANALIZANDO EMAILS INTERCEPTADOS:\n";
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Total emails enviados: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $index => $mail) {
            echo "\n   📧 Email #{$index + 1}:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            echo "      From: " . ($mail->from[0]['address'] ?? 'No definido') . "\n";
            echo "      Subject: " . $mail->subject . "\n";
            
            // Verificar destinatario
            $recipients = array_keys($mail->to);
            if (in_array($formData['email'], $recipients)) {
                echo "      ✅ CORRECTO: Email se envía al usuario del formulario\n";
            } else {
                echo "      ❌ ERROR: Email NO se envía al usuario del formulario\n";
                echo "      Expected: {$formData['email']}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
                
                // Verificar si se envía al MAIL_FROM_ADDRESS
                if (in_array(Config::get('mail.from.address'), $recipients)) {
                    echo "      ⚠️  PROBLEMA CONFIRMADO: Se envía a MAIL_FROM_ADDRESS\n";
                }
            }
        }
    } else {
        echo "   ❌ NO SE ENVIARON EMAILS\n";
    }
    
    // 6. Verificar método getEmailForVerification
    echo "\n6. VERIFICANDO getEmailForVerification():\n";
    
    $emailForVerification = $user->getEmailForVerification();
    echo "   Método retorna: {$emailForVerification}\n";
    echo "   Email del formulario: {$formData['email']}\n";
    echo "   ¿Son iguales? " . ($emailForVerification === $formData['email'] ? 'SÍ' : 'NO') . "\n";
    
    // 7. Probar notificación directa
    echo "\n7. PROBANDO NOTIFICACIÓN DIRECTA:\n";
    
    Mail::fake();
    
    echo "   Creando notificación VerifyEmail directamente...\n";
    $notification = new VerifyEmail();
    
    echo "   Enviando notificación al usuario...\n";
    $user->notify($notification);
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Emails de notificación directa: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   📧 Notificación directa:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            
            $recipients = array_keys($mail->to);
            if (in_array($formData['email'], $recipients)) {
                echo "      ✅ CORRECTO: Notificación directa funciona\n";
            } else {
                echo "      ❌ ERROR: Notificación directa también falla\n";
                echo "      Expected: {$formData['email']}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
            }
        }
    }
    
    // 8. Verificar si hay algún middleware o configuración especial
    echo "\n8. VERIFICANDO CONFIGURACIÓN ESPECIAL:\n";
    
    // Verificar si hay alguna configuración de testing
    echo "   APP_ENV: " . Config::get('app.env') . "\n";
    echo "   MAIL_MAILER: " . Config::get('mail.default') . "\n";
    
    // Verificar si hay alguna configuración de queue
    echo "   QUEUE_CONNECTION: " . Config::get('queue.default') . "\n";
    
    // 9. Limpiar datos de prueba
    echo "\n9. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "   ✅ Usuario eliminado\n";
    
    echo "\n=== DIAGNÓSTICO FINAL ===\n";
    
    if (count($sentMails) > 0) {
        $recipients = array_keys($sentMails[0]->to);
        if (in_array($formData['email'], $recipients)) {
            echo "✅ SISTEMA FUNCIONANDO CORRECTAMENTE\n";
            echo "   Los emails se envían al usuario del formulario\n";
        } else {
            echo "❌ PROBLEMA CONFIRMADO\n";
            echo "   Los emails NO se envían al usuario del formulario\n";
            echo "   Se envían a: " . implode(', ', $recipients) . "\n";
            echo "   Deberían enviarse a: {$formData['email']}\n";
            
            echo "\n🔍 POSIBLES SOLUCIONES:\n";
            echo "   1. Verificar configuración de entorno de desarrollo\n";
            echo "   2. Revisar middleware de mail\n";
            echo "   3. Verificar configuración de mailer\n";
            echo "   4. Revisar logs de Laravel para más detalles\n";
        }
    } else {
        echo "❌ NO SE ENVIARON EMAILS\n";
        echo "   Verificar configuración SMTP\n";
    }
    
    echo "\n📋 PARA PROBAR EN VIVO:\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Usa el email: {$formData['email']}\n";
    echo "3. Completa el resto del formulario\n";
    echo "4. Verifica dónde llega el email de verificación\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
