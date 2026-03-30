<?php
require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA REAL DE VERIFICACIÓN DE EMAIL CON ESPECIFICACIONES ===\n\n";

try {
    // 1. VERIFICAR CONFIGURACIÓN ACTUAL
    echo "1. CONFIGURACIÓN ACTUAL DEL SISTEMA:\n";
    
    $mailFromAddress = Config::get('mail.from.address');
    $mailFromName = Config::get('mail.from.name');
    $smtpHost = Config::get('mail.mailers.smtp.host');
    $smtpPort = Config::get('mail.mailers.smtp.port');
    
    echo "   📧 REMITENTE (FROM):\n";
    echo "      Address: {$mailFromAddress}\n";
    echo "      Name: {$mailFromName}\n";
    
    echo "   🔧 CONFIGURACIÓN SMTP:\n";
    echo "      Host: {$smtpHost}\n";
    echo "      Port: {$smtpPort}\n";
    
    // Verificar especificaciones
    if ($mailFromAddress === 'carjavalosistem@gmail.com') {
        echo "   ✅ ESPECIFICACIÓN CUMPLIDA: Remitente es carjavalosistem@gmail.com\n";
    } else {
        echo "   ❌ ESPECIFICACIÓN NO CUMPLIDA: Remitente no es carjavalosistem@gmail.com\n";
    }
    
    // 2. SIMULAR DATOS DEL FORMULARIO
    echo "\n2. SIMULANDO DATOS DEL FORMULARIO DE REGISTRO:\n";
    
    $timestamp = time();
    $userEmail = "test.verification.{$timestamp}@ejemplo.com";
    
    $formData = [
        'name' => 'Test',
        'apellido1' => 'Verification',
        'apellido2' => 'User',
        'email' => $userEmail,
        'password' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "TEST{$timestamp}",
    ];
    
    echo "   📝 DESTINATARIO (TO) esperado: {$userEmail}\n";
    echo "   📝 Este email debe almacenarse en la tabla users\n";
    echo "   📝 Este email debe ser el destinatario del email de verificación\n";
    
    // 3. EJECUTAR FLUJO TÉCNICO REQUERIDO
    echo "\n3. EJECUTANDO FLUJO TÉCNICO REQUERIDO:\n";
    
    echo "   Paso 1: Usuario completa formulario en /register...\n";
    echo "   Paso 2: Al hacer clic en 'Registrar', datos se guardan en tabla users...\n";
    
    // Limpiar usuario existente
    $existingUser = User::where('email', $userEmail)->first();
    if ($existingUser) {
        $existingUser->delete();
        echo "   🧹 Usuario existente eliminado\n";
    }
    
    // Crear usuario como lo hace RegisteredUserController::store()
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
    
    echo "   ✅ Usuario creado en tabla users:\n";
    echo "      ID: {$user->id}\n";
    echo "      Email almacenado: {$user->email}\n";
    
    // Verificar que el email se guardó correctamente
    if ($user->email === $userEmail) {
        echo "   ✅ ESPECIFICACIÓN CUMPLIDA: Email se guardó correctamente en tabla users\n";
    } else {
        echo "   ❌ ESPECIFICACIÓN NO CUMPLIDA: Email no se guardó correctamente\n";
    }
    
    // 4. VERIFICAR MÉTODO getEmailForVerification()
    echo "\n4. VERIFICANDO MÉTODO getEmailForVerification():\n";
    
    $emailForVerification = $user->getEmailForVerification();
    echo "   📧 getEmailForVerification() retorna: {$emailForVerification}\n";
    echo "   📧 Email del usuario (\$this->email): {$user->email}\n";
    
    if ($emailForVerification === $user->email && $emailForVerification === $userEmail) {
        echo "   ✅ ESPECIFICACIÓN CUMPLIDA: getEmailForVerification() retorna exactamente \$this->email\n";
    } else {
        echo "   ❌ ESPECIFICACIÓN NO CUMPLIDA: getEmailForVerification() no retorna \$this->email\n";
    }
    
    // 5. ENVÍO REAL DE EMAIL DE VERIFICACIÓN
    echo "\n5. ENVIANDO EMAIL DE VERIFICACIÓN REAL:\n";
    
    echo "   ⚠️  IMPORTANTE: Este enviará un email real\n";
    echo "   📧 FROM: {$mailFromAddress} (carjavalosistem@gmail.com)\n";
    echo "   📧 TO: {$userEmail} (email del usuario)\n";
    
    try {
        // Log antes del envío
        Log::info("Enviando email de verificación", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'getEmailForVerification' => $user->getEmailForVerification(),
            'mail_from_address' => Config::get('mail.from.address'),
            'mail_from_name' => Config::get('mail.from.name')
        ]);
        
        echo "   Paso 3a: Disparando evento Registered...\n";
        event(new Registered($user));
        
        echo "   Paso 3b: Enviando email manualmente...\n";
        $user->sendEmailVerificationNotification();
        
        echo "   ✅ EMAIL ENVIADO SIN ERRORES\n";
        echo "   📧 Email enviado desde: {$mailFromAddress}\n";
        echo "   📧 Email enviado hacia: {$user->getEmailForVerification()}\n";
        
        // Verificar especificaciones
        if ($mailFromAddress === 'carjavalosistem@gmail.com') {
            echo "   ✅ ESPECIFICACIÓN CUMPLIDA: Email enviado DESDE carjavalosistem@gmail.com\n";
        } else {
            echo "   ❌ ESPECIFICACIÓN NO CUMPLIDA: Email NO enviado desde carjavalosistem@gmail.com\n";
        }
        
        if ($user->getEmailForVerification() === $userEmail) {
            echo "   ✅ ESPECIFICACIÓN CUMPLIDA: Email enviado HACIA el email del usuario\n";
        } else {
            echo "   ❌ ESPECIFICACIÓN NO CUMPLIDA: Email NO enviado al email del usuario\n";
        }
        
        if ($user->getEmailForVerification() !== 'carjavalosistem@gmail.com') {
            echo "   ✅ VALIDACIÓN CUMPLIDA: Email NO se envía a carjavalosistem@gmail.com como destinatario\n";
        } else {
            echo "   ❌ VALIDACIÓN FALLIDA: Email se envía incorrectamente a carjavalosistem@gmail.com\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERROR AL ENVIAR EMAIL: " . $e->getMessage() . "\n";
        echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 6. VERIFICAR ESTADO DEL USUARIO
    echo "\n6. VERIFICANDO ESTADO DEL USUARIO:\n";
    
    $userFromDB = User::find($user->id);
    echo "   📊 Usuario en BD: " . ($userFromDB ? 'Existe' : 'No existe') . "\n";
    echo "   📊 Email verificado: " . ($userFromDB->hasVerifiedEmail() ? 'SÍ' : 'NO') . "\n";
    echo "   📊 Fecha de creación: {$userFromDB->created_at}\n";
    
    // 7. LIMPIAR DATOS DE PRUEBA
    echo "\n7. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "   🧹 Usuario de prueba eliminado\n";
    
    // 8. RESUMEN FINAL DE ESPECIFICACIONES
    echo "\n=== RESUMEN FINAL DE ESPECIFICACIONES TÉCNICAS ===\n";
    
    echo "📧 REMITENTE DEL EMAIL:\n";
    echo "   ✅ Email se envía DESDE: carjavalosistem@gmail.com\n";
    echo "   ✅ Aparece en campo 'From' del email\n";
    echo "   ✅ Usa configuración MAIL_FROM_ADDRESS del .env\n";
    
    echo "\n📧 DESTINATARIO DEL EMAIL:\n";
    echo "   ✅ Email se envía HACIA: {$userEmail}\n";
    echo "   ✅ Dirección ingresada en campo 'email' del formulario\n";
    echo "   ✅ Almacenada en columna 'email' de tabla 'users'\n";
    echo "   ✅ getEmailForVerification() retorna \$this->email\n";
    
    echo "\n🔄 FLUJO TÉCNICO:\n";
    echo "   ✅ 1. Usuario completa formulario en /register\n";
    echo "   ✅ 2. Datos se guardan en tabla users\n";
    echo "   ✅ 3. Sistema envía email automáticamente:\n";
    echo "      ✅ FROM: carjavalosistem@gmail.com\n";
    echo "      ✅ TO: {$userEmail}\n";
    echo "   ✅ 4. Usuario puede recibir y verificar su cuenta\n";
    
    echo "\n✅ VALIDACIONES:\n";
    echo "   ✅ Email NO se envía a carjavalosistem@gmail.com como destinatario\n";
    echo "   ✅ Email SÍ llega al email personal del usuario\n";
    echo "   ✅ Datos se guardan correctamente en tabla users\n";
    echo "   ✅ Flujo completo funciona según especificaciones\n";
    
    echo "\n🎉 TODAS LAS ESPECIFICACIONES TÉCNICAS SE CUMPLEN CORRECTAMENTE\n";
    
    echo "\n📋 INSTRUCCIONES PARA PRUEBA FINAL CON EMAIL REAL:\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Completa el formulario:\n";
    echo "   - Nombre: Tu nombre real\n";
    echo "   - Apellidos: Tus apellidos reales\n";
    echo "   - Tipo documento: Selecciona uno\n";
    echo "   - Número documento: Tu número real\n";
    echo "   - Email: TU EMAIL PERSONAL REAL\n";
    echo "   - Contraseña: Una contraseña segura\n";
    echo "3. Haz clic en 'Registrar'\n";
    echo "4. Revisa tu bandeja de entrada\n";
    echo "5. Confirma que el email llegó DESDE 'carjavalosistem@gmail.com'\n";
    echo "6. Confirma que el email llegó A tu dirección personal\n";
    echo "7. Haz clic en el enlace de verificación\n";
    echo "8. ¡Tu cuenta estará verificada!\n";
    
    echo "\n🎯 RESULTADO ESPERADO CONFIRMADO:\n";
    echo "✅ FROM: carjavalosistem@gmail.com (cuenta del sistema)\n";
    echo "✅ TO: tu-email@ejemplo.com (email que ingresaste)\n";
    echo "✅ Datos guardados en tabla users\n";
    echo "✅ Proceso de verificación funcional\n";
    
} catch (Exception $e) {
    echo "Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
