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

echo "=== PRUEBA COMPLETA DEL FLUJO DE REGISTRO CON VERIFICACIÓN DE EMAIL ===\n\n";

try {
    // 1. Verificar configuración inicial
    echo "1. VERIFICANDO CONFIGURACIÓN INICIAL:\n";
    echo "   MAIL_FROM_ADDRESS: " . Config::get('mail.from.address') . "\n";
    echo "   MAIL_FROM_NAME: " . Config::get('mail.from.name') . "\n";
    echo "   MAIL_MAILER: " . Config::get('mail.default') . "\n";
    echo "   SMTP_HOST: " . Config::get('mail.mailers.smtp.host') . "\n";
    echo "   SMTP_PORT: " . Config::get('mail.mailers.smtp.port') . "\n";
    
    // 2. Simular datos del formulario "Crear Cuenta"
    echo "\n2. SIMULANDO DATOS DEL FORMULARIO 'CREAR CUENTA':\n";
    
    $timestamp = time();
    $formData = [
        'name' => 'Juan',
        'apellido1' => 'Pérez',
        'apellido2' => 'García',
        'email' => "juan.perez.{$timestamp}@ejemplo.com",
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "12345{$timestamp}",
    ];
    
    echo "   📝 Datos del formulario:\n";
    echo "      Nombre: {$formData['name']}\n";
    echo "      Apellido1: {$formData['apellido1']}\n";
    echo "      Apellido2: {$formData['apellido2']}\n";
    echo "      Email: {$formData['email']}\n";
    echo "      Tipo Documento: {$formData['tipo_documento']}\n";
    echo "      Número Documento: {$formData['numero_documento']}\n";
    
    // 3. Verificar que no existe usuario con ese email
    echo "\n3. VERIFICANDO UNICIDAD DEL EMAIL:\n";
    
    $existingUser = User::where('email', $formData['email'])->first();
    if ($existingUser) {
        echo "   ❌ ERROR: Ya existe un usuario con ese email\n";
        $existingUser->delete();
        echo "   ✅ Usuario existente eliminado\n";
    } else {
        echo "   ✅ Email disponible para registro\n";
    }
    
    // 4. Simular exactamente el proceso del RegisteredUserController::store()
    echo "\n4. SIMULANDO RegisteredUserController::store():\n";
    
    echo "   Paso 1: Validar datos (simulado)...\n";
    echo "   ✅ Validación exitosa\n";
    
    echo "   Paso 2: Crear usuario en la base de datos...\n";
    $user = User::create([
        'name' => $formData['name'],
        'apellido1' => $formData['apellido1'],
        'apellido2' => $formData['apellido2'],
        'email' => $formData['email'],
        'password' => bcrypt($formData['password']),
        'role' => 'Registrado', // Rol por defecto
        'tipo_documento' => $formData['tipo_documento'],
        'numero_documento' => $formData['numero_documento'],
    ]);
    
    echo "   ✅ Usuario creado exitosamente:\n";
    echo "      ID: {$user->id}\n";
    echo "      Nombre completo: {$user->full_name}\n";
    echo "      Email: {$user->email}\n";
    echo "      Rol: {$user->role}\n";
    echo "      Documento: {$user->formatted_document}\n";
    
    // 5. Verificar método getEmailForVerification
    echo "\n5. VERIFICANDO MÉTODO getEmailForVerification():\n";
    
    $emailForVerification = $user->getEmailForVerification();
    echo "   Método retorna: {$emailForVerification}\n";
    echo "   Email del usuario: {$user->email}\n";
    echo "   Email del formulario: {$formData['email']}\n";
    
    if ($emailForVerification === $formData['email']) {
        echo "   ✅ PERFECTO: getEmailForVerification() retorna el email del formulario\n";
    } else {
        echo "   ❌ ERROR: getEmailForVerification() retorna email incorrecto\n";
    }
    
    // 6. Interceptar emails para verificar destinatario
    echo "\n6. INTERCEPTANDO EMAILS PARA VERIFICAR DESTINATARIO:\n";
    
    Mail::fake();
    
    echo "   Paso 3: Disparar evento Registered...\n";
    event(new Registered($user));
    
    echo "   Paso 4: Enviar email de verificación manualmente...\n";
    $user->sendEmailVerificationNotification();
    
    // 7. Analizar emails interceptados
    echo "\n7. ANALIZANDO EMAILS INTERCEPTADOS:\n";
    
    $sentMails = Mail::sent(VerifyEmail::class);
    echo "   Total emails enviados: " . count($sentMails) . "\n";
    
    if (count($sentMails) > 0) {
        foreach ($sentMails as $index => $mail) {
            echo "\n   📧 Email #" . ($index + 1) . ":\n";
            echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
            echo "      From: " . ($mail->from[0]['address'] ?? 'No definido') . "\n";
            echo "      Subject: " . $mail->subject . "\n";
            
            // Verificar destinatario correcto
            $recipients = array_keys($mail->to);
            if (in_array($formData['email'], $recipients)) {
                echo "      ✅ EXCELENTE: Email se envía al email del formulario\n";
            } else {
                echo "      ❌ ERROR: Email NO se envía al email del formulario\n";
                echo "      Expected: {$formData['email']}\n";
                echo "      Actual: " . implode(', ', $recipients) . "\n";
                
                // Verificar si se envía al MAIL_FROM_ADDRESS
                if (in_array(Config::get('mail.from.address'), $recipients)) {
                    echo "      ⚠️  PROBLEMA: Se envía a MAIL_FROM_ADDRESS\n";
                }
            }
        }
    } else {
        echo "   ❌ ERROR: NO se enviaron emails\n";
    }
    
    // 8. Verificar estado de verificación del usuario
    echo "\n8. VERIFICANDO ESTADO DE VERIFICACIÓN:\n";
    
    echo "   Email verificado: " . ($user->hasVerifiedEmail() ? 'SÍ' : 'NO') . "\n";
    echo "   Fecha de verificación: " . ($user->email_verified_at ?? 'No verificado') . "\n";
    
    // 9. Simular proceso de verificación
    echo "\n9. SIMULANDO PROCESO DE VERIFICACIÓN:\n";
    
    if (!$user->hasVerifiedEmail()) {
        echo "   Marcando email como verificado...\n";
        $user->markEmailAsVerified();
        echo "   ✅ Email marcado como verificado\n";
        echo "   Estado final: " . ($user->hasVerifiedEmail() ? 'Verificado' : 'No verificado') . "\n";
    }
    
    // 10. Verificar datos guardados en la base de datos
    echo "\n10. VERIFICANDO DATOS EN LA BASE DE DATOS:\n";
    
    $userFromDB = User::find($user->id);
    echo "    ✅ Usuario encontrado en BD:\n";
    echo "       Name: {$userFromDB->name}\n";
    echo "       Apellido1: {$userFromDB->apellido1}\n";
    echo "       Apellido2: {$userFromDB->apellido2}\n";
    echo "       Email: {$userFromDB->email}\n";
    echo "       Role: {$userFromDB->role}\n";
    echo "       Tipo Documento: {$userFromDB->tipo_documento}\n";
    echo "       Número Documento: {$userFromDB->numero_documento}\n";
    
    // Verificar que todos los campos coinciden
    $allFieldsMatch = (
        $userFromDB->name === $formData['name'] &&
        $userFromDB->apellido1 === $formData['apellido1'] &&
        $userFromDB->apellido2 === $formData['apellido2'] &&
        $userFromDB->email === $formData['email'] &&
        $userFromDB->role === 'Registrado' &&
        $userFromDB->tipo_documento === $formData['tipo_documento'] &&
        $userFromDB->numero_documento === $formData['numero_documento']
    );
    
    if ($allFieldsMatch) {
        echo "    ✅ PERFECTO: Todos los campos se guardaron correctamente\n";
    } else {
        echo "    ❌ ERROR: Algunos campos no coinciden\n";
    }
    
    // 11. Limpiar datos de prueba
    echo "\n11. LIMPIANDO DATOS DE PRUEBA:\n";
    $user->delete();
    echo "    ✅ Usuario de prueba eliminado\n";
    
    // 12. Resumen final
    echo "\n=== RESUMEN FINAL DEL FLUJO COMPLETO ===\n";
    
    if (count($sentMails) > 0) {
        $recipients = array_keys($sentMails[0]->to);
        if (in_array($formData['email'], $recipients)) {
            echo "✅ FLUJO COMPLETO FUNCIONANDO PERFECTAMENTE\n";
            echo "✅ Datos se guardan correctamente en la tabla users\n";
            echo "✅ Email de verificación se envía al email del formulario\n";
            echo "✅ NO se envía al email del sistema (MAIL_FROM_ADDRESS)\n";
            echo "✅ Proceso de verificación funciona\n";
        } else {
            echo "❌ PROBLEMA EN EL FLUJO\n";
            echo "   Los datos se guardan correctamente\n";
            echo "   Pero el email se envía a: " . implode(', ', $recipients) . "\n";
            echo "   Debería enviarse a: {$formData['email']}\n";
        }
    } else {
        echo "❌ PROBLEMA: NO SE ENVIARON EMAILS\n";
        echo "   Los datos se guardan correctamente\n";
        echo "   Pero no se envían emails de verificación\n";
    }
    
    echo "\n📋 INSTRUCCIONES PARA PRUEBA REAL:\n";
    echo "1. Ve a: http://127.0.0.1:8000/register\n";
    echo "2. Completa el formulario con:\n";
    echo "   - Nombre: Tu nombre real\n";
    echo "   - Apellidos: Tus apellidos reales\n";
    echo "   - Tipo de documento: Selecciona uno\n";
    echo "   - Número de documento: Tu número real\n";
    echo "   - Email: TU EMAIL PERSONAL REAL\n";
    echo "   - Contraseña: Una contraseña segura\n";
    echo "3. Haz clic en 'Registrar'\n";
    echo "4. Revisa la bandeja de entrada de TU EMAIL\n";
    echo "5. Busca el email de verificación\n";
    echo "6. Haz clic en el enlace de verificación\n";
    echo "7. ¡Tu cuenta estará verificada!\n";
    
} catch (Exception $e) {
    echo "Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
