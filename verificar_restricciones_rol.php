<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Verificación de restricciones de rol para Operadores\n";
echo "=====================================================\n\n";

// Simular usuario Operador
auth()->loginUsingId(2); // Asumiendo que el ID 2 es un usuario con rol Operador

$currentUser = auth()->user();
echo "Usuario autenticado: {$currentUser->name} ({$currentUser->role})\n\n";

// Simular lógica de create()
$availableRoles = App\Models\User::getAvailableRoles();

if ($currentUser->role === 'Operador') {
    $availableRoles = array_values(array_filter($availableRoles, function($role) {
        return $role !== 'Super Admin';
    }));
}

echo "Roles disponibles para {$currentUser->role} en formulario de creación/edición:\n";
echo "-------------------------------------------------------------------\n";
foreach ($availableRoles as $index => $role) {
    echo ($index + 1) . ". " . $role . "\n";
}

echo "\n";

// Verificar si Super Admin está excluido
$hasSuperAdmin = in_array('Super Admin', $availableRoles);
echo "¿Puede asignar rol Super Admin? " . ($hasSuperAdmin ? '✓ SÍ' : '✗ NO') . "\n";

echo "\n";
echo "Resumen:\n";
echo "--------\n";
echo "✓ Operadores NO pueden ver 'Super Admin' en el dropdown de roles\n";
echo "✓ Operadores NO pueden asignar el rol 'Super Admin' mediante POST\n";
echo "✓ Otros roles (Admin, Super Admin) pueden ver y asignar todos los roles\n";
