<?php
require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNÓSTICO COMPLETO DEL SISTEMA DE REGISTRO ===\n\n";

try {
    // 1. VERIFICAR CONFIGURACIÓN BÁSICA
    echo "1. VERIFICANDO CONFIGURACIÓN BÁSICA:\n";
    echo "   App Name: " . Config::get('app.name') . "\n";
    echo "   App URL: " . Config::get('app.url') . "\n";
    echo "   Database: " . Config::get('database.default') . "\n";
    echo "   Mail Mailer: " . Config::get('mail.default') . "\n";
    echo "   Mail From: " . Config::get('mail.from.address') . "\n";
    
    // 2. VERIFICAR CONEXIÓN A BASE DE DATOS
    echo "\n2. VERIFICANDO CONEXIÓN A BASE DE DATOS:\n";
    try {
        DB::connection()->getPdo();
        echo "   ✅ Conexión a base de datos exitosa\n";
        
        // Verificar tabla users
        $usersTableExists = DB::getSchemaBuilder()->hasTable('users');
        echo "   ✅ Tabla 'users' existe: " . ($usersTableExists ? 'SÍ' : 'NO') . "\n";
        
        if ($usersTableExists) {
            $columns = DB::getSchemaBuilder()->getColumnListing('users');
            echo "   ✅ Columnas en tabla users: " . implode(', ', $columns) . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error de conexión a BD: " . $e->getMessage() . "\n";
    }
    
    // 3. VERIFICAR MODELO USER
    echo "\n3. VERIFICANDO MODELO USER:\n";
    echo "   ✅ Clase User existe: " . (class_exists(User::class) ? 'SÍ' : 'NO') . "\n";
    echo "   ✅ Implementa MustVerifyEmail: " . (in_array('Illuminate\Contracts\Auth\MustVerifyEmail', class_implements(User::class)) ? 'SÍ' : 'NO') . "\n";
    echo "   ✅ Método getEmailForVerification: " . (method_exists(User::class, 'getEmailForVerification') ? 'SÍ' : 'NO') . "\n";
    echo "   ✅ Método sendEmailVerificationNotification: " . (method_exists(User::class, 'sendEmailVerificationNotification') ? 'SÍ' : 'NO') . "\n";
    
    // Verificar fillable
    $user = new User();
    echo "   ✅ Campos fillable: " . implode(', ', $user->getFillable()) . "\n";
    
    // 4. VERIFICAR CONTROLADOR
    echo "\n4. VERIFICANDO REGISTEREDUSER CONTROLLER:\n";
    echo "   ✅ Clase existe: " . (class_exists('App\Http\Controllers\Auth\RegisteredUserController') ? 'SÍ' : 'NO') . "\n";
    
    $controller = new \App\Http\Controllers\Auth\RegisteredUserController();
    echo "   ✅ Método create: " . (method_exists($controller, 'create') ? 'SÍ' : 'NO') . "\n";
    echo "   ✅ Método store: " . (method_exists($controller, 'store') ? 'SÍ' : 'NO') . "\n";
    
    // 5. VERIFICAR RUTAS
    echo "\n5. VERIFICANDO RUTAS:\n";
    $routes = app('router')->getRoutes();
    $registerRoutes = [];
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'register')) {
            $registerRoutes[] = $route->methods()[0] . ' ' . $route->uri();
        }
    }
    echo "   ✅ Rutas de registro encontradas:\n";
    foreach ($registerRoutes as $route) {
        echo "      - {$route}\n";
    }
    
    // 6. SIMULAR DATOS DE FORMULARIO
    echo "\n6. SIMULANDO DATOS DE FORMULARIO:\n";
    
    $timestamp = time();
    $testData = [
        'name' => 'Test',
        'apellido1' => 'Usuario',
        'apellido2' => 'Diagnóstico',
        'email' => "test.diagnostico.{$timestamp}@ejemplo.com",
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'tipo_documento' => 'DNI',
        'numero_documento' => "TEST{$timestamp}",
    ];
    
    echo "   📝 Datos de prueba preparados:\n";
    foreach ($testData as $key => $value) {
        if (!in_array($key, ['password', 'password_confirmation'])) {
            echo "      {$key}: {$value}\n";
        }
    }
    
    // 7. PROBAR CREACIÓN DE USUARIO
    echo "\n7. PROBANDO CREACIÓN DE USUARIO:\n";
    
    try {
        // Verificar si ya existe
        $existingUser = User::where('email', $testData['email'])->first();
        if ($existingUser) {
            $existingUser->delete();
            echo "   ✅ Usuario existente eliminado\n";
        }
        
        $user = User::create([
            'name' => $testData['name'],
            'apellido1' => $testData['apellido1'],
            'apellido2' => $testData['apellido2'],
            'email' => $testData['email'],
            'password' => bcrypt($testData['password']),
            'role' => 'Registrado',
            'tipo_documento' => $testData['tipo_documento'],
            'numero_documento' => $testData['numero_documento'],
        ]);
        
        echo "   ✅ Usuario creado exitosamente:\n";
        echo "      ID: {$user->id}\n";
        echo "      Email: {$user->email}\n";
        echo "      Rol: {$user->role}\n";
        
        // 8. VERIFICAR MÉTODO getEmailForVerification
        echo "\n8. VERIFICANDO getEmailForVerification():\n";
        
        $emailForVerification = $user->getEmailForVerification();
        echo "   Email del usuario: {$user->email}\n";
        echo "   getEmailForVerification(): {$emailForVerification}\n";
        echo "   ¿Son iguales? " . ($emailForVerification === $user->email ? 'SÍ' : 'NO') . "\n";
        
        if ($emailForVerification === $user->email) {
            echo "   ✅ CORRECTO: getEmailForVerification retorna el email del usuario\n";
        } else {
            echo "   ❌ ERROR: getEmailForVerification NO retorna el email del usuario\n";
        }
        
        // 9. PROBAR ENVÍO DE EMAIL
        echo "\n9. PROBANDO ENVÍO DE EMAIL DE VERIFICACIÓN:\n";
        
        // Interceptar emails
        Mail::fake();
        
        try {
            echo "   Intentando enviar email de verificación...\n";
            $user->sendEmailVerificationNotification();
            echo "   ✅ Método sendEmailVerificationNotification ejecutado sin errores\n";
        } catch (Exception $e) {
            echo "   ❌ Error en sendEmailVerificationNotification: " . $e->getMessage() . "\n";
        }
        
        // Verificar emails interceptados
        $sentMails = Mail::sent(VerifyEmail::class);
        echo "   Emails interceptados: " . count($sentMails) . "\n";
        
        if (count($sentMails) > 0) {
            foreach ($sentMails as $index => $mail) {
                echo "   📧 Email " . ($index + 1) . ":\n";
                echo "      To: " . implode(', ', array_keys($mail->to)) . "\n";
                echo "      From: " . ($mail->from[0]['address'] ?? 'No definido') . "\n";
                
                $recipients = array_keys($mail->to);
                if (in_array($testData['email'], $recipients)) {
                    echo "      ✅ CORRECTO: Email se envía al usuario del formulario\n";
                } else {
                    echo "      ❌ ERROR: Email NO se envía al usuario del formulario\n";
                    echo "      Expected: {$testData['email']}\n";
                    echo "      Actual: " . implode(', ', $recipients) . "\n";
                }
            }
        } else {
            echo "   ❌ NO se interceptaron emails\n";
        }
        
        // 10. PROBAR EVENTO REGISTERED
        echo "\n10. PROBANDO EVENTO REGISTERED:\n";
        
        Mail::fake();
        
        try {
            echo "    Disparando evento Registered...\n";
            event(new Registered($user));
            echo "    ✅ Evento Registered disparado sin errores\n";
        } catch (Exception $e) {
            echo "    ❌ Error en evento Registered: " . $e->getMessage() . "\n";
        }
        
        $sentMails = Mail::sent(VerifyEmail::class);
        echo "    Emails por evento: " . count($sentMails) . "\n";
        
        // 11. VERIFICAR LOGS
        echo "\n11. VERIFICANDO LOGS:\n";
        
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            echo "    ✅ Archivo de log existe\n";
            $logContent = file_get_contents($logFile);
            $recentLogs = array_slice(explode("\n", $logContent), -10);
            echo "    Últimas 5 líneas del log:\n";
            foreach (array_slice($recentLogs, -5) as $line) {
                if (!empty(trim($line))) {
                    echo "      " . substr($line, 0, 100) . "...\n";
                }
            }
        } else {
            echo "    ❌ No se encontró archivo de log\n";
        }
        
        // 12. LIMPIAR DATOS DE PRUEBA
        echo "\n12. LIMPIANDO DATOS DE PRUEBA:\n";
        $user->delete();
        echo "    ✅ Usuario de prueba eliminado\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error al crear usuario: " . $e->getMessage() . "\n";
        echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // 13. DIAGNÓSTICO FINAL
    echo "\n=== DIAGNÓSTICO FINAL ===\n";
    
    echo "PROBLEMAS IDENTIFICADOS:\n";
    
    // Verificar vista del controlador
    echo "1. VISTA DEL CONTROLADOR:\n";
    $controllerContent = file_get_contents(app_path('Http/Controllers/Auth/RegisteredUserController.php'));
    if (strpos($controllerContent, "view('auth.register'") !== false) {
        echo "   ❌ PROBLEMA: Controlador usa vista 'auth.register' en lugar de 'adminlte::auth.register'\n";
    } else {
        echo "   ✅ Vista del controlador correcta\n";
    }
    
    // Verificar notificación
    echo "2. NOTIFICACIÓN DE VERIFICACIÓN:\n";
    if (strpos($controllerContent, 'VerifyEmailNotification') !== false) {
        echo "   ❌ PROBLEMA: Referencia a VerifyEmailNotification que no existe\n";
    } else {
        echo "   ✅ Notificación correcta\n";
    }
    
    echo "\nRECOMENDACIONES:\n";
    echo "1. Corregir vista en RegisteredUserController\n";
    echo "2. Usar VerifyEmail en lugar de VerifyEmailNotification\n";
    echo "3. Verificar configuración SMTP\n";
    echo "4. Probar registro con datos reales\n";
    
} catch (Exception $e) {
    echo "Error durante el diagnóstico: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
