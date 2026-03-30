<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$config = config('adminlte.menu');

echo "Verificación de permisos del menú para rol 'Operador'\n";
echo "=====================================================\n\n";

foreach ($config as $item) {
    if (isset($item['text'])) {
        $text = $item['text'];
        $hasRole = isset($item['role']);
        
        if ($hasRole) {
            $roles = is_array($item['role']) ? $item['role'] : [$item['role']];
            $hasOperador = in_array('Operador', $roles);
            
            echo "📋 {$text}\n";
            echo "   Roles permitidos: " . implode(', ', $roles) . "\n";
            echo "   ¿Operador tiene acceso? " . ($hasOperador ? '✓ SÍ' : '✗ NO') . "\n\n";
        } else {
            echo "📋 {$text}\n";
            echo "   Sin restricciones de rol (acceso para todos)\n\n";
        }
    }
}
