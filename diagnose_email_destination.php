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

echo "=== DIAGNÓSTICO: DESTINATARIO DEL EMAIL DE VERIFICACIÓN ===\n\n";

try {
    // 1. Verificar configuración actual
    echo "1. CONFIGURACIÓN ACTUAL:\n";
    echo "   MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
    echo "   MAIL_FROM_NAME: " . Config::get('mail.from.name') . "\n";
    echo "   MAIL_MAILER: " . Config::get('mail.default') . "\n";
    echo "   SMTP_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
    
    // 2. Crear usuario de prueba
    echo "\n2. CREANDO USUARIO DE PRUEBA:\n";
    
    $timestamp = time();
    $userEmail = "test.destination.{$timestamp}@example.com";
    $documentNumber = "DEST{$timestamp}";
    
    $user = User::create([
        'name' => 'Test',
        'apellido1' => 'Destination',
        'apellido2' => 'Check',
        'email' => $userEmail,
        'password' => bcrypt('password123'),
        'role' => 'Registrado',
        'tipo_documento' => 'DNI',
        'numero_documento' => $documentNumber,
    ]);
    
    echo "   ✅ Usuario creado con email: {$user->email}\n";
    
    // 3. Verificar método getEmailForVerification
    echo "\n3. VERIFICANDO getEmailForVerification():\n";
    
    $emailForVerification = $user->getEmailForVerification();
    echo "   Método retorna: {$emailForVerification}\n";
    echo "   Email del usuario: {$user->email}\n";
    echo "   ¿Son iguales? " . ($emailForVerification === $user->email ? 'SÍ' : 'NO') . "\n";
    
    // 4. Interceptar emails con Mail::fake
    echo "\n4. INTERCEPTANDO EMAILS CON MAIL::FAKE:\n";
    
    Mail::fake();
    
    // Probar notificación directa
    echo "   Enviando notificación VerifyEmail directamente...\n";
    $notification = new VerifyEmail();
    $user->notify($notification);
    
    // Verificar emails interceptados
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Emails interceptados: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   📧 Email interceptado:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            echo "      From: " . ($mail->from[0]['address'] ?? 'No definido') . "\n";
            echo "      Subject: " . $mail->subject . "\n";
            
            // Verificar destinatario
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "      ✅ CORRECTO: Email se envía al usuario\n";
            } else {
                echo "      ❌ ERROR: Email se envía a dirección incorrecta\n";
                echo "      Expected: {$userEmail}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
            }
        }
    }
    
    // 5. Probar método del modelo User
    echo "\n5. PROBANDO MÉTODO DEL MODELO USER:\n";
    
    Mail::fake();
    
    echo "   Llamando sendEmailVerificationNotification()...\n";
    $user->sendEmailVerificationNotification();
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Emails enviados por método del modelo: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   📧 Email del método del modelo:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "      ✅ CORRECTO: Método del modelo envía al usuario\n";
            } else {
                echo "      ❌ ERROR: Método del modelo envía a dirección incorrecta\n";
            }
        }
    }
    
    // 6. Probar evento Registered
    echo "\n6. PROBANDO EVENTO REGISTERED:\n";
    
    Mail::fake();
    
    echo "   Disparando evento Registered...\n";
    event(new Registered($user));
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Emails enviados por evento: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   📧 Email del evento Registered:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "      ✅ CORRECTO: Evento envía al usuario\n";
            } else {
                echo "      ❌ ERROR: Evento envía a dirección incorrecta\n";
                echo "      Expected: {$userEmail}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
            }
        }
    }
    
    // 7. Verificar listeners del evento
    echo "\n7. VERIFICANDO LISTENERS DEL EVENTO REGISTERED:\n";
    
    $eventDispatcher = app('events');
    $listeners = $eventDispatcher->getListeners('Illuminate\Auth\Events\Registered');
    
    echo "   Listeners registrados: " . count($listeners) . "\n";
    foreach ($listeners as $listener) {
        if (is_array($listener) && isset($listener[0])) {
            echo "   - " . get_class($listener[0]) . "\n";
        } elseif (is_string($listener)) {
            echo "   - " . $listener . "\n";
        } else {
            echo "   - " . gettype($listener) . "\n";
        }
    }
    
    // 8. Simular proceso completo del controlador
    echo "\n8. SIMULANDO PROCESO COMPLETO DEL CONTROLADOR:\n";
    
    Mail::fake();
    
    echo "   Paso 1: Disparar evento Registered...\n";
    event(new Registered($user));
    
    echo "   Paso 2: Enviar email manualmente...\n";
    $user->sendEmailVerificationNotification();
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Total emails enviados en proceso completo: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $mail) {
            echo "   📧 Email del proceso completo:\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            
            $recipients = array_keys($mail->to);
            if (in_array($userEmail, $recipients)) {
                echo "      ✅ EXCELENTE: Proceso completo envía al usuario correcto\n";
            } else {
                echo "      ❌ PROBLEMA: Proceso completo envía a dirección incorrecta\n";
                echo "      Expected: {$userEmail}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
            }
        }
    }
    
    // 9. Limpiar
    echo "\n9. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "   ✅ Usuario eliminado\n";
    
    echo "\n=== DIAGNÓSTICO COMPLETO ===\n";
    
    if (count($sentMails) > 0) {
        $recipients = array_keys($sentMails[0]->to);
        if (in_array($userEmail, $recipients)) {
            echo "✅ SISTEMA FUNCIONANDO CORRECTAMENTE\n";
            echo "   El email se envía al usuario registrado\n";
        } else {
            echo "❌ PROBLEMA CONFIRMADO\n";
            echo "   El email se envía a: " . implode(', ', $recipients) . "\n";
            echo "   Debería enviarse a: {$userEmail}\n";
            echo "\n🔍 POSIBLES CAUSAS:\n";
            echo "   1. Configuración de mail forzando destinatario\n";
            echo "   2. Middleware interceptando emails\n";
            echo "   3. Listener personalizado modificando destinatario\n";
            echo "   4. Configuración de entorno de desarrollo\n";
        }
    } else {
        echo "❌ NO SE ENVIARON EMAILS\n";
        echo "   Verificar configuración SMTP\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
