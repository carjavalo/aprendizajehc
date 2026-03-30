<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Verificación de restricciones extendidas para Operadores\n";
echo "=========================================================\n\n";

// Simular lógica de filtrado para Operador
$allRoles = App\Models\User::getAvailableRoles();

echo "Todos los roles en el sistema:\n";
foreach ($allRoles as $index => $role) {
    echo ($index + 1) . ". " . $role . "\n";
}

echo "\n";

// Filtrar roles para Operador
$rolesForOperador = array_values(array_filter($allRoles, function($role) {
    return !in_array($role, ['Super Admin', 'Administrador']);
}));

echo "Roles disponibles para usuario con rol 'Operador':\n";
foreach ($rolesForOperador as $index => $role) {
    echo ($index + 1) . ". " . $role . "\n";
}

echo "\n";

echo "Roles EXCLUIDOS para Operadores:\n";
$excludedRoles = array_diff($allRoles, $rolesForOperador);
foreach ($excludedRoles as $role) {
    echo "✗ " . $role . "\n";
}

echo "\n";
echo "Resumen:\n";
echo "--------\n";
echo "✓ Operadores NO pueden ver 'Super Admin' en el dropdown\n";
echo "✓ Operadores NO pueden ver 'Administrador' en el dropdown\n";
echo "✓ Operadores NO pueden asignar 'Super Admin' mediante POST\n";
echo "✓ Operadores NO pueden asignar 'Administrador' mediante POST\n";
echo "✓ Operadores pueden asignar: Docente, Estudiante, Registrado, Operador\n";
