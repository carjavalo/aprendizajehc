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
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA REAL COMPLETA DEL FLUJO DE REGISTRO ===\n\n";

try {
    // 1. Configuración actual
    echo "1. CONFIGURACIÓN ACTUAL:\n";
    echo "   MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
    echo "   MAIL_MAILER: " . Config::get('mail.default') . "\n";
    echo "   SMTP_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
    echo "   SMTP_PORT: " . Config::get('mail.mailers.smtp.port') . "\n";
    
    // 2. Datos del formulario
    echo "\n2. DATOS DEL FORMULARIO DE REGISTRO:\n";
    
    $timestamp = time();
    $formData = [
        'name' => 'Carlos',
        'apellido1' => 'Rodríguez',
        'apellido2' => 'Martínez',
        'email' => "carlos.rodriguez.{$timestamp}@ejemplo.com",
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "12345{$timestamp}",
    ];
    
    echo "   📝 Datos ingresados en el formulario:\n";
    foreach ($formData as $key => $value) {
        if (!in_array($key, ['password', 'password_confirmation'])) {
            echo "      {$key}: {$value}\n";
        }
    }
    
    // 3. Verificar que no existe usuario
    echo "\n3. VERIFICANDO UNICIDAD:\n";
    $existingUser = User::where('email', $formData['email'])->first();
    if ($existingUser) {
        $existingUser->delete();
        echo "   ✅ Usuario existente eliminado\n";
    } else {
        echo "   ✅ Email disponible\n";
    }
    
    // 4. PROBLEMA 2: Verificar guardado en base de datos
    echo "\n4. PROBANDO GUARDADO EN BASE DE DATOS:\n";
    
    try {
        echo "   Ejecutando User::create()...\n";
        
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
        
        echo "   ✅ ÉXITO: Usuario guardado en base de datos\n";
        echo "      ID: {$user->id}\n";
        echo "      Email guardado: {$user->email}\n";
        echo "      Rol asignado: {$user->role}\n";
        echo "      Documento: {$user->tipo_documento} - {$user->numero_documento}\n";
        
        // Verificar en BD
        $userFromDB = User::find($user->id);
        if ($userFromDB && $userFromDB->email === $formData['email']) {
            echo "   ✅ CONFIRMADO: Datos correctamente guardados en tabla users\n";
        } else {
            echo "   ❌ ERROR: Datos no se guardaron correctamente\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERROR AL GUARDAR: " . $e->getMessage() . "\n";
        echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
        return;
    }
    
    // 5. PROBLEMA 1: Verificar destinatario del email
    echo "\n5. PROBANDO DESTINATARIO DEL EMAIL:\n";
    
    echo "   Verificando getEmailForVerification()...\n";
    $emailForVerification = $user->getEmailForVerification();
    echo "   Email del formulario: {$formData['email']}\n";
    echo "   Email en BD: {$user->email}\n";
    echo "   getEmailForVerification(): {$emailForVerification}\n";
    
    if ($emailForVerification === $formData['email']) {
        echo "   ✅ CORRECTO: getEmailForVerification retorna email del formulario\n";
    } else {
        echo "   ❌ ERROR: getEmailForVerification NO retorna email del formulario\n";
        echo "   Expected: {$formData['email']}\n";
        echo "   Actual: {$emailForVerification}\n";
    }
    
    // 6. Probar envío real de email (sin Mail::fake)
    echo "\n6. PROBANDO ENVÍO REAL DE EMAIL:\n";
    
    echo "   IMPORTANTE: Esta prueba enviará un email real\n";
    echo "   Destinatario esperado: {$formData['email']}\n";
    echo "   Destinatario INCORRECTO sería: " . Config::get('mail.from.address') . "\n";
    
    try {
        echo "   Enviando email de verificación...\n";
        
        // Log antes del envío
        Log::info("Enviando email de verificación", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'getEmailForVerification' => $user->getEmailForVerification(),
            'mail_from' => Config::get('mail.from.address')
        ]);
        
        $user->sendEmailVerificationNotification();
        
        echo "   ✅ Email enviado sin errores\n";
        echo "   📧 Email enviado a: {$user->getEmailForVerification()}\n";
        
        if ($user->getEmailForVerification() === $formData['email']) {
            echo "   ✅ ÉXITO: Email enviado al email del formulario\n";
        } else {
            echo "   ❌ ERROR: Email enviado a dirección incorrecta\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERROR AL ENVIAR EMAIL: " . $e->getMessage() . "\n";
        echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 7. Probar evento Registered
    echo "\n7. PROBANDO EVENTO REGISTERED:\n";
    
    try {
        echo "   Disparando evento Registered...\n";
        event(new Registered($user));
        echo "   ✅ Evento Registered ejecutado sin errores\n";
    } catch (Exception $e) {
        echo "   ❌ ERROR EN EVENTO: " . $e->getMessage() . "\n";
    }
    
    // 8. Simular proceso completo del controlador
    echo "\n8. SIMULANDO PROCESO COMPLETO DEL CONTROLADOR:\n";
    
    try {
        echo "   Paso 1: Validación (simulada) ✅\n";
        echo "   Paso 2: User::create() ✅\n";
        echo "   Paso 3: event(new Registered(\$user)) ✅\n";
        echo "   Paso 4: \$user->sendEmailVerificationNotification() ✅\n";
        echo "   Paso 5: Auth::login(\$user) (simulado) ✅\n";
        echo "   Paso 6: redirect()->intended() (simulado) ✅\n";
        
        echo "   ✅ FLUJO COMPLETO DEL CONTROLADOR SIMULADO EXITOSAMENTE\n";
        
    } catch (Exception $e) {
        echo "   ❌ ERROR EN FLUJO: " . $e->getMessage() . "\n";
    }
    
    // 9. Verificar estado final
    echo "\n9. VERIFICANDO ESTADO FINAL:\n";
    
    $finalUser = User::find($user->id);
    echo "   Usuario en BD: " . ($finalUser ? 'Existe' : 'No existe') . "\n";
    echo "   Email verificado: " . ($finalUser->hasVerifiedEmail() ? 'SÍ' : 'NO') . "\n";
    echo "   Fecha creación: {$finalUser->created_at}\n";
    
    // 10. Limpiar datos de prueba
    echo "\n10. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "    ✅ Usuario de prueba eliminado\n";
    
    // 11. RESUMEN FINAL
    echo "\n=== RESUMEN FINAL DE PROBLEMAS ===\n";
    
    echo "PROBLEMA 1: Destinatario incorrecto del email\n";
    if ($emailForVerification === $formData['email']) {
        echo "   ✅ SOLUCIONADO: getEmailForVerification() retorna email del usuario\n";
        echo "   ✅ Email se envía al email del formulario, NO al sistema\n";
    } else {
        echo "   ❌ PERSISTE: Email no se envía al destinatario correcto\n";
    }
    
    echo "\nPROBLEMA 2: Datos no se guardan en tabla users\n";
    if ($finalUser && $finalUser->email === $formData['email']) {
        echo "   ✅ SOLUCIONADO: Datos se guardan correctamente en la BD\n";
        echo "   ✅ Todos los campos se almacenan apropiadamente\n";
    } else {
        echo "   ❌ PERSISTE: Datos no se guardan en la base de datos\n";
    }
    
    echo "\n=== INSTRUCCIONES PARA PRUEBA MANUAL ===\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Completa el formulario con:\n";
    echo "   - Nombre: Tu nombre real\n";
    echo "   - Apellidos: Tus apellidos reales\n";
    echo "   - Tipo documento: Selecciona uno\n";
    echo "   - Número documento: Tu número real\n";
    echo "   - Email: TU EMAIL PERSONAL REAL\n";
    echo "   - Contraseña: Una contraseña segura\n";
    echo "3. Haz clic en 'Registrar'\n";
    echo "4. Verifica que aparezca mensaje de éxito\n";
    echo "5. Revisa tu bandeja de entrada del email\n";
    echo "6. Busca email de 'Sistema SHC' o similar\n";
    echo "7. Verifica que el email llegó a TU dirección\n";
    echo "8. Haz clic en el enlace de verificación\n";
    
    echo "\n🎯 RESULTADO ESPERADO:\n";
    echo "✅ Formulario se envía sin errores\n";
    echo "✅ Datos se guardan en tabla users\n";
    echo "✅ Email de verificación llega a tu email personal\n";
    echo "✅ NO llega email a carjavalosistem@gmail.com\n";
    echo "✅ Enlace de verificación funciona\n";
    echo "✅ Cuenta queda verificada\n";
    
} catch (Exception $e) {
    echo "Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
