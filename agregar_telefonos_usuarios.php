<?php
/**
 * Script para agregar teléfonos a usuarios existentes
 * Ejecutar: php agregar_telefonos_usuarios.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== AGREGAR TELÉFONOS A USUARIOS ===\n\n";

// Obtener usuarios sin teléfono
$usuariosSinTelefono = User::where(function($q) {
    $q->whereNull('phone')
      ->orWhere('phone', '');
})->limit(10)->get();

echo "Usuarios sin teléfono: " . $usuariosSinTelefono->count() . "\n\n";

if ($usuariosSinTelefono->count() === 0) {
    echo "✓ Todos los usuarios ya tienen teléfono registrado\n";
    exit(0);
}

// Teléfonos de ejemplo (formato internacional)
$telefonosEjemplo = [
    '+51987654321',
    '+51987654322',
    '+51987654323',
    '+51987654324',
    '+51987654325',
    '+51987654326',
    '+51987654327',
    '+51987654328',
    '+51987654329',
    '+51987654330',
];

$contador = 0;
foreach ($usuariosSinTelefono as $index => $user) {
    $telefono = $telefonosEjemplo[$index] ?? '+51987654' . str_pad($index + 331, 3, '0', STR_PAD_LEFT);
    
    $user->phone = $telefono;
    $user->save();
    
    echo "✓ Usuario actualizado:\n";
    echo "  ID: {$user->id}\n";
    echo "  Nombre: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Teléfono: {$telefono}\n\n";
    
    $contador++;
}

echo "=== RESUMEN ===\n";
echo "✓ Total usuarios actualizados: {$contador}\n";
echo "\nAhora puedes probar el chat de WhatsApp en el dashboard.\n";
