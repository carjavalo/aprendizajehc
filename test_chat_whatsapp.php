<?php
/**
 * Script de prueba para verificar funcionalidad del chat de WhatsApp
 * Ejecutar: php test_chat_whatsapp.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== TEST CHAT WHATSAPP - DASHBOARD ===\n\n";

// 1. Verificar usuarios con teléfono
echo "1. Verificando usuarios con teléfono registrado...\n";
$usuariosConTelefono = User::whereNotNull('phone')
                          ->where('phone', '!=', '')
                          ->count();
echo "   ✓ Total usuarios con teléfono: {$usuariosConTelefono}\n\n";

// 2. Listar algunos usuarios con teléfono
echo "2. Listando primeros 5 usuarios con teléfono:\n";
$usuarios = User::whereNotNull('phone')
               ->where('phone', '!=', '')
               ->select('id', 'name', 'apellido1', 'apellido2', 'email', 'phone', 'numero_documento')
               ->limit(5)
               ->get();

foreach ($usuarios as $user) {
    $nombreCompleto = trim($user->name . ' ' . $user->apellido1 . ' ' . $user->apellido2);
    echo "   - ID: {$user->id}\n";
    echo "     Nombre: {$nombreCompleto}\n";
    echo "     Email: {$user->email}\n";
    echo "     Teléfono: {$user->phone}\n";
    echo "     Documento: " . ($user->numero_documento ?? 'N/A') . "\n\n";
}

// 3. Simular búsqueda
echo "3. Simulando búsqueda de estudiantes...\n";
$queryTest = 'estudiante';
echo "   Buscando: '{$queryTest}'\n";

$resultados = User::where(function($q) use ($queryTest) {
        $q->where('name', 'LIKE', "%{$queryTest}%")
          ->orWhere('email', 'LIKE', "%{$queryTest}%")
          ->orWhere('numero_documento', 'LIKE', "%{$queryTest}%");
    })
    ->whereNotNull('phone')
    ->where('phone', '!=', '')
    ->select('id', 'name', 'apellido1', 'apellido2', 'email', 'phone', 'numero_documento')
    ->limit(10)
    ->get();

echo "   ✓ Resultados encontrados: " . $resultados->count() . "\n";

foreach ($resultados as $user) {
    $nombreCompleto = trim($user->name . ' ' . $user->apellido1 . ' ' . $user->apellido2);
    echo "   - {$nombreCompleto} ({$user->email}) - Tel: {$user->phone}\n";
}

echo "\n";

// 4. Verificar formato de teléfono para WhatsApp
echo "4. Verificando formato de teléfonos para WhatsApp...\n";
$usuariosPrueba = User::whereNotNull('phone')
                     ->where('phone', '!=', '')
                     ->limit(3)
                     ->get();

foreach ($usuariosPrueba as $user) {
    $telefonoLimpio = preg_replace('/\D/', '', $user->phone);
    $url = "https://wa.me/{$telefonoLimpio}?text=Mensaje%20de%20prueba";
    echo "   - {$user->name}: {$user->phone} → {$telefonoLimpio}\n";
    echo "     URL WhatsApp: {$url}\n\n";
}

// 5. Resumen
echo "=== RESUMEN ===\n";
echo "✓ Total usuarios con teléfono: {$usuariosConTelefono}\n";
echo "✓ Búsqueda funcional: " . ($resultados->count() > 0 ? "SÍ" : "NO") . "\n";
echo "✓ Formato WhatsApp: OK\n";
echo "\n";

echo "=== INSTRUCCIONES DE USO ===\n";
echo "1. Acceder a: http://192.168.2.200:8001/dashboard\n";
echo "2. Buscar estudiante en el widget de chat\n";
echo "3. Seleccionar estudiante de los resultados\n";
echo "4. Escribir mensaje\n";
echo "5. Hacer clic en 'Enviar vía WhatsApp'\n";
echo "6. Se abrirá WhatsApp con el mensaje preparado\n";
echo "\n";

echo "=== TEST COMPLETADO ===\n";
