<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Roles disponibles en el sistema:\n";
echo "================================\n";
$roles = App\Models\User::getAvailableRoles();
foreach ($roles as $index => $role) {
    echo ($index + 1) . ". " . $role . "\n";
}
echo "\n¿Está 'Operador' en la lista? " . (in_array('Operador', $roles) ? 'SÍ ✓' : 'NO ✗') . "\n";
