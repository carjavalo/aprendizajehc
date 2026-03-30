<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Verificar si ya existe un usuario de prueba
    $existingUser = User::where('email', 'admin@test.com')->first();
    
    if ($existingUser) {
        echo "✅ Usuario de prueba ya existe:\n";
        echo "   Email: admin@test.com\n";
        echo "   Nombre: {$existingUser->full_name}\n";
        echo "   Documento: {$existingUser->formatted_document}\n";
        echo "   Rol: {$existingUser->role}\n";
        echo "   Verificado: " . ($existingUser->email_verified_at ? 'SÍ' : 'NO') . "\n";
    } else {
        // Crear usuario de prueba
        $user = User::create([
            'name' => 'Administrador',
            'apellido1' => 'Sistema',
            'apellido2' => 'Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'Super Admin',
            'tipo_documento' => 'DNI',
            'numero_documento' => '12345678',
            'email_verified_at' => now(),
        ]);

        echo "✅ Usuario de prueba creado exitosamente:\n";
        echo "   Email: admin@test.com\n";
        echo "   Contraseña: password123\n";
        echo "   Nombre: {$user->full_name}\n";
        echo "   Documento: {$user->formatted_document}\n";
        echo "   Rol: {$user->role}\n";
        echo "   Verificado: SÍ\n";
    }
    
    echo "\n🌐 Ahora puedes acceder a:\n";
    echo "   URL: http://127.0.0.1:8000/\n";
    echo "   Email: admin@test.com\n";
    echo "   Contraseña: password123\n";
    
} catch (Exception $e) {
    echo "❌ Error al crear usuario: " . $e->getMessage() . "\n";
}
