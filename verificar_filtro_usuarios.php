<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Verificación del filtro de usuarios por rol\n";
echo "============================================\n\n";

// Simular usuario Operador
$operador = App\Models\User::where('role', 'Operador')->first();

if ($operador) {
    echo "✓ Usuario Operador encontrado: {$operador->name}\n\n";
    
    // Simular la lógica del controlador
    $usersForOperador = App\Models\User::where('role', '!=', 'Super Admin')->get();
    
    echo "Usuarios que vería el Operador ({$usersForOperador->count()} total):\n";
    echo "-----------------------------------------------------------\n";
    foreach ($usersForOperador as $user) {
        echo "- {$user->name} ({$user->role})\n";
    }
    
    echo "\n";
    
    // Verificar Super Admins excluidos
    $superAdmins = App\Models\User::where('role', 'Super Admin')->get();
    echo "Super Admins EXCLUIDOS ({$superAdmins->count()} total):\n";
    echo "-----------------------------------------------------------\n";
    foreach ($superAdmins as $user) {
        echo "- {$user->name} (Super Admin) ✗ NO VISIBLE\n";
    }
} else {
    echo "⚠ No se encontró ningún usuario con rol Operador\n";
    echo "Crea un usuario con rol Operador para probar el filtro.\n";
}

echo "\n";

// Verificar todos los usuarios
$allUsers = App\Models\User::all();
echo "Total de usuarios en el sistema: {$allUsers->count()}\n";
echo "Usuarios que vería un Super Admin o Administrador: {$allUsers->count()}\n";
echo "Usuarios que vería un Operador: " . App\Models\User::where('role', '!=', 'Super Admin')->count() . "\n";
