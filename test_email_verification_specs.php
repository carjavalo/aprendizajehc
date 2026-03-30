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

echo "=== VERIFICACIÓN DE ESPECIFICACIONES TÉCNICAS DE EMAIL ===\n\n";

try {
    // 1. VERIFICAR REMITENTE DEL EMAIL (FROM)
    echo "1. VERIFICANDO REMITENTE DEL EMAIL (FROM):\n";
    
    $mailFromAddress = Config::get('mail.from.address');
    $mailFromName = Config::get('mail.from.name');
    
    echo "   📧 MAIL_FROM_ADDRESS: {$mailFromAddress}\n";
    echo "   📧 MAIL_FROM_NAME: {$mailFromName}\n";
    
    if ($mailFromAddress === 'carjavalosistem@gmail.com') {
        echo "   ✅ CORRECTO: Email se enviará DESDE carjavalosistem@gmail.com\n";
    } else {
        echo "   ❌ ERROR: Email NO se enviará desde carjavalosistem@gmail.com\n";
        echo "   Expected: carjavalosistem@gmail.com\n";
        echo "   Actual: {$mailFromAddress}\n";
    }
    
    // 2. VERIFICAR CONFIGURACIÓN SMTP
    echo "\n2. VERIFICANDO CONFIGURACIÓN SMTP:\n";
    
    $smtpHost = Config::get('mail.mailers.smtp.host');
    $smtpPort = Config::get('mail.mailers.smtp.port');
    $smtpUsername = Config::get('mail.mailers.smtp.username');
    
    echo "   🔧 SMTP Host: {$smtpHost}\n";
    echo "   🔧 SMTP Port: {$smtpPort}\n";
    echo "   🔧 SMTP Username: {$smtpUsername}\n";
    
    if ($smtpHost === 'smtp.gmail.com' && $smtpPort == 587 && $smtpUsername === 'carjavalosistem@gmail.com') {
        echo "   ✅ CONFIGURACIÓN SMTP CORRECTA\n";
    } else {
        echo "   ❌ ERROR EN CONFIGURACIÓN SMTP\n";
    }
    
    // 3. SIMULAR DATOS DEL FORMULARIO DE REGISTRO
    echo "\n3. SIMULANDO DATOS DEL FORMULARIO DE REGISTRO:\n";
    
    $timestamp = time();
    $userFormData = [
        'name' => 'Ana',
        'apellido1' => 'García',
        'apellido2' => 'López',
        'email' => "ana.garcia.{$timestamp}@ejemplo.com", // Email que ingresa el usuario
        'password' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "98765{$timestamp}",
    ];
    
    echo "   📝 Email ingresado por el usuario: {$userFormData['email']}\n";
    echo "   📝 Este debe ser el DESTINATARIO (TO) del email\n";
    
    // 4. VERIFICAR DESTINATARIO DEL EMAIL (TO)
    echo "\n4. VERIFICANDO DESTINATARIO DEL EMAIL (TO):\n";
    
    // Limpiar usuario existente
    $existingUser = User::where('email', $userFormData['email'])->first();
    if ($existingUser) {
        $existingUser->delete();
        echo "   🧹 Usuario existente eliminado\n";
    }
    
    // Crear usuario como lo hace el formulario
    $user = User::create([
        'name' => $userFormData['name'],
        'apellido1' => $userFormData['apellido1'],
        'apellido2' => $userFormData['apellido2'],
        'email' => $userFormData['email'],
        'password' => bcrypt($userFormData['password']),
        'role' => 'Registrado',
        'tipo_documento' => $userFormData['tipo_documento'],
        'numero_documento' => $userFormData['numero_documento'],
    ]);
    
    echo "   ✅ Usuario creado en tabla users:\n";
    echo "      ID: {$user->id}\n";
    echo "      Email en BD: {$user->email}\n";
    
    // Verificar método getEmailForVerification()
    $emailForVerification = $user->getEmailForVerification();
    echo "   📧 getEmailForVerification(): {$emailForVerification}\n";
    
    if ($emailForVerification === $userFormData['email']) {
        echo "   ✅ CORRECTO: getEmailForVerification() retorna el email del usuario\n";
    } else {
        echo "   ❌ ERROR: getEmailForVerification() NO retorna el email del usuario\n";
    }
    
    // 5. PROBAR FLUJO TÉCNICO COMPLETO
    echo "\n5. PROBANDO FLUJO TÉCNICO COMPLETO:\n";
    
    echo "   Paso 1: Usuario completa formulario en /register ✅\n";
    echo "   Paso 2: Datos se guardan en tabla users ✅\n";
    echo "   Paso 3: Sistema envía email de verificación...\n";
    
    // Interceptar emails para verificar FROM y TO
    Mail::fake();
    
    // Simular el proceso del RegisteredUserController
    echo "   Ejecutando: event(new Registered(\$user))...\n";
    event(new Registered($user));
    
    echo "   Ejecutando: \$user->sendEmailVerificationNotification()...\n";
    $user->sendEmailVerificationNotification();
    
    // 6. ANALIZAR EMAILS INTERCEPTADOS
    echo "\n6. ANALIZANDO EMAILS INTERCEPTADOS:\n";
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   📧 Total emails interceptados: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $index => $mail) {
            echo "\n   📧 Email " . ($index + 1) . ":\n";
            
            // Verificar FROM (remitente)
            $fromAddress = $mail->from[0]['address'] ?? 'No definido';
            $fromName = $mail->from[0]['name'] ?? 'No definido';
            echo "      FROM Address: {$fromAddress}\n";
            echo "      FROM Name: {$fromName}\n";
            
            if ($fromAddress === 'carjavalosistem@gmail.com') {
                echo "      ✅ CORRECTO: Email se envía DESDE carjavalosistem@gmail.com\n";
            } else {
                echo "      ❌ ERROR: Email NO se envía desde carjavalosistem@gmail.com\n";
                echo "      Expected FROM: carjavalosistem@gmail.com\n";
                echo "      Actual FROM: {$fromAddress}\n";
            }
            
            // Verificar TO (destinatario)
            $recipients = array_keys($mail->to);
            echo "      TO: " . implode(', ', $recipients) . "\n";
            
            if (in_array($userFormData['email'], $recipients)) {
                echo "      ✅ CORRECTO: Email se envía HACIA el email del usuario\n";
            } else {
                echo "      ❌ ERROR: Email NO se envía al email del usuario\n";
                echo "      Expected TO: {$userFormData['email']}\n";
                echo "      Actual TO: " . implode(', ', $recipients) . "\n";
            }
            
            // Verificar que NO se envía al sistema como destinatario
            if (in_array('carjavalosistem@gmail.com', $recipients)) {
                echo "      ❌ ERROR CRÍTICO: Email se envía a carjavalosistem@gmail.com como DESTINATARIO\n";
            } else {
                echo "      ✅ CORRECTO: Email NO se envía a carjavalosistem@gmail.com como destinatario\n";
            }
            
            echo "      Subject: " . $mail->subject . "\n";
        }
    } else {
        echo "   ❌ NO se interceptaron emails\n";
    }
    
    // 7. VALIDACIÓN FINAL DE ESPECIFICACIONES
    echo "\n7. VALIDACIÓN FINAL DE ESPECIFICACIONES:\n";
    
    $allSpecsMet = true;
    
    // Validar remitente
    if ($mailFromAddress === 'carjavalosistem@gmail.com') {
        echo "   ✅ REMITENTE: Email se envía DESDE carjavalosistem@gmail.com\n";
    } else {
        echo "   ❌ REMITENTE: Email NO se envía desde carjavalosistem@gmail.com\n";
        $allSpecsMet = false;
    }
    
    // Validar destinatario
    if (count($sentMails) > 0) {
        $recipients = array_keys($sentMails[0]->to);
        if (in_array($userFormData['email'], $recipients)) {
            echo "   ✅ DESTINATARIO: Email se envía HACIA el email del usuario\n";
        } else {
            echo "   ❌ DESTINATARIO: Email NO se envía al email del usuario\n";
            $allSpecsMet = false;
        }
        
        if (!in_array('carjavalosistem@gmail.com', $recipients)) {
            echo "   ✅ VALIDACIÓN: Email NO se envía a carjavalosistem@gmail.com como destinatario\n";
        } else {
            echo "   ❌ VALIDACIÓN: Email se envía incorrectamente a carjavalosistem@gmail.com\n";
            $allSpecsMet = false;
        }
    } else {
        echo "   ❌ NO se enviaron emails para validar\n";
        $allSpecsMet = false;
    }
    
    // Validar guardado en BD
    $userFromDB = User::find($user->id);
    if ($userFromDB && $userFromDB->email === $userFormData['email']) {
        echo "   ✅ BASE DE DATOS: Datos se guardan correctamente en tabla users\n";
    } else {
        echo "   ❌ BASE DE DATOS: Datos NO se guardan correctamente\n";
        $allSpecsMet = false;
    }
    
    // Validar getEmailForVerification
    if ($emailForVerification === $userFormData['email']) {
        echo "   ✅ MÉTODO: getEmailForVerification() retorna \$this->email\n";
    } else {
        echo "   ❌ MÉTODO: getEmailForVerification() NO retorna \$this->email\n";
        $allSpecsMet = false;
    }
    
    // 8. LIMPIAR DATOS DE PRUEBA
    echo "\n8. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "   🧹 Usuario de prueba eliminado\n";
    
    // 9. RESULTADO FINAL
    echo "\n=== RESULTADO FINAL DE ESPECIFICACIONES ===\n";
    
    if ($allSpecsMet) {
        echo "🎉 TODAS LAS ESPECIFICACIONES TÉCNICAS SE CUMPLEN CORRECTAMENTE\n\n";
        
        echo "✅ REMITENTE: carjavalosistem@gmail.com (FROM)\n";
        echo "✅ DESTINATARIO: Email del usuario del formulario (TO)\n";
        echo "✅ FLUJO TÉCNICO: Funcionando según especificaciones\n";
        echo "✅ VALIDACIONES: Todas las validaciones pasaron\n";
        
        echo "\n🚀 SISTEMA LISTO PARA PRUEBA CON EMAIL REAL\n";
        
    } else {
        echo "❌ ALGUNAS ESPECIFICACIONES NO SE CUMPLEN\n";
        echo "🔧 REVISAR CONFIGURACIÓN Y CORREGIR PROBLEMAS\n";
    }
    
    echo "\n📋 INSTRUCCIONES PARA PRUEBA REAL:\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Ingresa TU EMAIL PERSONAL en el campo 'email'\n";
    echo "3. Completa el resto del formulario\n";
    echo "4. Haz clic en 'Registrar'\n";
    echo "5. Revisa tu bandeja de entrada\n";
    echo "6. Confirma que el email llegó desde 'carjavalosistem@gmail.com'\n";
    echo "7. Confirma que el email llegó a TU dirección personal\n";
    echo "8. Haz clic en el enlace de verificación\n";
    
} catch (Exception $e) {
    echo "Error durante la verificación: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
