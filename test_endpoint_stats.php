<?php
/**
 * Script para probar el endpoint getStats directamente
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\CursoController;
use App\Models\Curso;
use Illuminate\Http\Request;

echo "=== PRUEBA DEL ENDPOINT getStats ===\n\n";

$controller = new CursoController();
$curso = Curso::find(15);

if (!$curso) {
    echo "Curso no encontrado\n";
    exit;
}

echo "Probando curso: {$curso->titulo} (ID: {$curso->id})\n\n";

try {
    $response = $controller->getStats($curso);
    $data = json_decode($response->getContent(), true);
    
    echo "=== RESPUESTA DEL ENDPOINT ===\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
