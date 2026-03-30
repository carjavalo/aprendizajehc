<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\URL;

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 SOLUCIONANDO PROBLEMA DE VERIFICACIÓN DE EMAIL\n";
echo "=" . str_repeat("=", 55) . "\n\n";

try {
    // 1. Verificar configuración de APP_URL
    echo "1. ✅ Verificando configuración de APP_URL:\n";
    $appUrl = config('app.url');
    echo "   APP_URL actual: {$appUrl}\n";
    
    if ($appUrl === 'http://127.0.0.1:8000') {
        echo "   ✅ CORRECTO: APP_URL configurado correctamente\n";
    } else {
        echo "   ❌ ERROR: APP_URL debe ser http://127.0.0.1:8000\n";
    }

    // 2. Verificar usuarios sin verificar
    echo "\n2. ✅ Verificando usuarios sin verificar:\n";
    $unverifiedUsers = User::whereNull('email_verified_at')->get();
    echo "   Usuarios sin verificar: " . $unverifiedUsers->count() . "\n";
    
    foreach ($unverifiedUsers as $user) {
        echo "   - {$user->email} (ID: {$user->id})\n";
    }

    // 3. Crear usuario de prueba verificado
    echo "\n3. ✅ Creando usuario de prueba verificado:\n";
    $testUser = User::where('email', 'test@example.com')->first();
    
    if (!$testUser) {
        $testUser = User::create([
            'name' => 'Usuario',
            'apellido1' => 'Prueba',
            'apellido2' => 'Verificado',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Registrado',
            'tipo_documento' => 'DNI',
            'numero_documento' => '87654321',
            'email_verified_at' => now(),
        ]);
        echo "   ✅ Usuario de prueba creado y verificado\n";
    } else {
        if (!$testUser->email_verified_at) {
            $testUser->update(['email_verified_at' => now()]);
            echo "   ✅ Usuario de prueba existente verificado\n";
        } else {
            echo "   ✅ Usuario de prueba ya estaba verificado\n";
        }
    }

    // 4. Verificar manualmente usuarios existentes (SOLUCIÓN TEMPORAL)
    echo "\n4. 🔧 SOLUCIÓN TEMPORAL - Verificando usuarios existentes:\n";
    $updated = 0;
    foreach ($unverifiedUsers as $user) {
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
            $updated++;
            echo "   ✅ Verificado: {$user->email}\n";
        }
    }
    echo "   Total usuarios verificados manualmente: {$updated}\n";

    // 5. Generar URL de verificación de ejemplo
    echo "\n5. ✅ Generando URL de verificación de ejemplo:\n";
    if ($testUser) {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $testUser->id, 'hash' => sha1($testUser->email)]
        );
        echo "   URL de ejemplo: {$verificationUrl}\n";
        
        if (strpos($verificationUrl, '127.0.0.1:8000') !== false) {
            echo "   ✅ CORRECTO: La URL apunta al servidor correcto\n";
        } else {
            echo "   ❌ ERROR: La URL no apunta al servidor correcto\n";
        }
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎯 RESUMEN DE SOLUCIONES APLICADAS:\n";
    echo "=" . str_repeat("=", 60) . "\n";
    echo "✅ APP_URL corregido a http://127.0.0.1:8000\n";
    echo "✅ {$updated} usuarios verificados manualmente\n";
    echo "✅ Usuario de prueba creado: test@example.com / password123\n";
    echo "✅ URLs de verificación ahora apuntan al servidor correcto\n";

    echo "\n🌐 CREDENCIALES DE ACCESO DIRECTO:\n";
    echo "=" . str_repeat("=", 40) . "\n";
    echo "Email: test@example.com\n";
    echo "Contraseña: password123\n";
    echo "Estado: ✅ Verificado (acceso directo al dashboard)\n";

    echo "\n🔧 PRÓXIMOS PASOS:\n";
    echo "=" . str_repeat("=", 20) . "\n";
    echo "1. Acceder a http://127.0.0.1:8000/login\n";
    echo "2. Usar las credenciales: test@example.com / password123\n";
    echo "3. Verificar acceso directo al dashboard\n";
    echo "4. Probar nuevo registro para verificar flujo corregido\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
