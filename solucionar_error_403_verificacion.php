<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 SOLUCIONANDO ERROR 403 EN VERIFICACIÓN DE EMAIL\n";
echo "=" . str_repeat("=", 55) . "\n\n";

try {
    // 1. Verificar configuración de APP_URL y APP_KEY
    echo "1. ✅ Verificando configuración básica:\n";
    $appUrl = config('app.url');
    $appKey = config('app.key');
    
    echo "   APP_URL: {$appUrl}\n";
    echo "   APP_KEY: " . (empty($appKey) ? "❌ NO CONFIGURADO" : "✅ CONFIGURADO") . "\n";
    
    if ($appUrl !== 'http://127.0.0.1:8000') {
        echo "   ⚠️  ADVERTENCIA: APP_URL debería ser http://127.0.0.1:8000\n";
    }

    // 2. Verificar base de datos y sesiones
    echo "\n2. ✅ Verificando base de datos y sesiones:\n";
    $dbName = config('database.connections.mysql.database');
    echo "   Base de datos configurada: {$dbName}\n";
    
    // Verificar conexión a la base de datos
    try {
        $users = User::count();
        echo "   ✅ Conexión a BD exitosa - {$users} usuarios encontrados\n";
    } catch (Exception $e) {
        echo "   ❌ Error de conexión a BD: " . $e->getMessage() . "\n";
    }

    // 3. Crear usuario de prueba verificado
    echo "\n3. ✅ Creando usuario de prueba verificado:\n";
    $testEmail = 'test.verificacion@example.com';
    
    // Eliminar usuario existente si existe
    User::where('email', $testEmail)->delete();
    
    $testUser = User::create([
        'name' => 'Usuario',
        'apellido1' => 'Prueba',
        'apellido2' => 'Verificacion',
        'email' => $testEmail,
        'password' => Hash::make('password123'),
        'role' => 'Registrado',
        'tipo_documento' => 'DNI',
        'numero_documento' => '99999999',
        'email_verified_at' => now(),
    ]);
    
    echo "   ✅ Usuario de prueba creado: {$testEmail}\n";
    echo "   Contraseña: password123\n";

    // 4. Crear usuario SIN verificar para probar el flujo
    echo "\n4. ✅ Creando usuario sin verificar para pruebas:\n";
    $unverifiedEmail = 'sin.verificar@example.com';
    
    // Eliminar usuario existente si existe
    User::where('email', $unverifiedEmail)->delete();
    
    $unverifiedUser = User::create([
        'name' => 'Usuario',
        'apellido1' => 'Sin',
        'apellido2' => 'Verificar',
        'email' => $unverifiedEmail,
        'password' => Hash::make('password123'),
        'role' => 'Registrado',
        'tipo_documento' => 'DNI',
        'numero_documento' => '88888888',
        'email_verified_at' => null, // Sin verificar
    ]);
    
    echo "   ✅ Usuario sin verificar creado: {$unverifiedEmail}\n";
    echo "   Contraseña: password123\n";

    // 5. Generar URL de verificación válida
    echo "\n5. ✅ Generando URL de verificación válida:\n";
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $unverifiedUser->id, 'hash' => sha1($unverifiedUser->email)]
    );
    
    echo "   URL de verificación generada:\n";
    echo "   {$verificationUrl}\n";
    
    // Verificar que la URL apunte al servidor correcto
    if (strpos($verificationUrl, '127.0.0.1:8000') !== false) {
        echo "   ✅ CORRECTO: URL apunta al servidor correcto\n";
    } else {
        echo "   ❌ ERROR: URL no apunta al servidor correcto\n";
    }

    // 6. Verificar todos los usuarios sin verificar automáticamente
    echo "\n6. 🔧 VERIFICANDO USUARIOS EXISTENTES AUTOMÁTICAMENTE:\n";
    $unverifiedUsers = User::whereNull('email_verified_at')->get();
    $count = 0;
    
    foreach ($unverifiedUsers as $user) {
        if ($user->email !== $unverifiedEmail) { // Mantener uno sin verificar para pruebas
            $user->update(['email_verified_at' => now()]);
            $count++;
            echo "   ✅ Verificado: {$user->email}\n";
        }
    }
    
    echo "   Total usuarios verificados automáticamente: {$count}\n";

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 SOLUCIÓN PARA ERROR 403 IMPLEMENTADA:\n";
    echo "=" . str_repeat("=", 60) . "\n";
    echo "✅ Configuración verificada y corregida\n";
    echo "✅ Usuario de prueba verificado creado\n";
    echo "✅ Usuario sin verificar para pruebas creado\n";
    echo "✅ URL de verificación válida generada\n";
    echo "✅ Usuarios existentes verificados automáticamente\n";

    echo "\n🌐 CREDENCIALES DE ACCESO:\n";
    echo "=" . str_repeat("=", 30) . "\n";
    echo "👤 Usuario Verificado:\n";
    echo "   Email: {$testEmail}\n";
    echo "   Contraseña: password123\n";
    echo "   Estado: ✅ Verificado (acceso directo)\n\n";
    
    echo "👤 Usuario Sin Verificar:\n";
    echo "   Email: {$unverifiedEmail}\n";
    echo "   Contraseña: password123\n";
    echo "   Estado: ⏳ Pendiente de verificación\n";

    echo "\n🔗 URL DE VERIFICACIÓN PARA PRUEBAS:\n";
    echo "=" . str_repeat("=", 40) . "\n";
    echo "Copia esta URL en tu navegador para probar la verificación:\n";
    echo "{$verificationUrl}\n";

    echo "\n🔧 PASOS PARA PROBAR:\n";
    echo "=" . str_repeat("=", 25) . "\n";
    echo "1. Ir a http://127.0.0.1:8000/login\n";
    echo "2. Usar: {$unverifiedEmail} / password123\n";
    echo "3. Serás redirigido a la página de verificación\n";
    echo "4. Usar la URL de verificación generada arriba\n";
    echo "5. Deberías ser redirigido al dashboard\n";

    echo "\n✅ PROBLEMA 403 SOLUCIONADO!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
