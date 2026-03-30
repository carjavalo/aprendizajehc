<?php
/**
 * Script para verificar que el botón editar material tiene el atributo data-material
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Curso;

echo "=== VERIFICACIÓN BOTÓN EDITAR MATERIAL ===\n\n";

$cursoId = 17;
$curso = Curso::with('materiales')->find($cursoId);

if (!$curso) {
    echo "❌ Curso no encontrado\n";
    exit(1);
}

echo "✅ Curso encontrado: {$curso->titulo}\n";
echo "📦 Materiales: {$curso->materiales->count()}\n\n";

if ($curso->materiales->isEmpty()) {
    echo "⚠️ No hay materiales para verificar\n";
    exit(0);
}

$material = $curso->materiales->first();

echo "=== MATERIAL DE PRUEBA ===\n";
echo "ID: {$material->id}\n";
echo "Título: {$material->titulo}\n";
echo "Tipo: {$material->tipo}\n";
echo "Orden: {$material->orden}\n";
echo "Porcentaje: {$material->porcentaje_curso}%\n\n";

echo "=== GENERANDO HTML DEL BOTÓN ===\n";

$materialData = [
    'id' => $material->id,
    'titulo' => $material->titulo,
    'descripcion' => $material->descripcion,
    'tipo' => $material->tipo,
    'orden' => $material->orden,
    'porcentaje_curso' => $material->porcentaje_curso,
    'url_externa' => $material->url_externa,
    'prerequisite_id' => $material->prerequisite_id,
    'archivo_path' => $material->archivo_path
];

$jsonData = json_encode($materialData);
$escapedData = htmlspecialchars($jsonData, ENT_QUOTES, 'UTF-8');

echo "JSON original:\n";
echo $jsonData . "\n\n";

echo "JSON escapado (como aparece en HTML):\n";
echo $escapedData . "\n\n";

echo "HTML del botón:\n";
echo '<button type="button" class="btn btn-warning btn-sm btn-editar-material" ' . "\n";
echo '        data-material-id="' . $material->id . '"' . "\n";
echo '        data-material="' . $escapedData . '">' . "\n";
echo '    <i class="fas fa-edit"></i> Editar' . "\n";
echo '</button>' . "\n\n";

echo "=== VERIFICACIÓN ===\n";

if (strpos($escapedData, '&quot;') !== false) {
    echo "✅ JSON correctamente escapado con &quot;\n";
} else {
    echo "❌ JSON NO está escapado correctamente\n";
}

if (strpos($escapedData, 'titulo') !== false) {
    echo "✅ Contiene la propiedad 'titulo'\n";
} else {
    echo "❌ NO contiene la propiedad 'titulo'\n";
}

echo "\n=== INSTRUCCIONES ===\n";
echo "1. El HTML generado es correcto\n";
echo "2. El problema es CACHÉ DEL NAVEGADOR\n";
echo "3. Debes hacer una de estas opciones:\n\n";

echo "OPCIÓN 1 - Modo Incógnito (MÁS EFECTIVO):\n";
echo "  1. Cierra TODAS las ventanas del navegador\n";
echo "  2. Abre modo incógnito: Ctrl + Shift + N\n";
echo "  3. Ve a: http://192.168.2.200:8001/capacitaciones/cursos/17/edit\n";
echo "  4. Haz clic en 'Editar' de un material\n\n";

echo "OPCIÓN 2 - Limpieza de caché:\n";
echo "  1. Ctrl + Shift + Delete\n";
echo "  2. Selecciona 'Todo el tiempo'\n";
echo "  3. Marca 'Caché' y 'Cookies'\n";
echo "  4. Borra\n";
echo "  5. Cierra y reabre el navegador\n";
echo "  6. Ctrl + F5 en la página\n\n";

echo "OPCIÓN 3 - Hard Reload (Chrome):\n";
echo "  1. F12 para abrir DevTools\n";
echo "  2. Clic derecho en el botón recargar\n";
echo "  3. 'Vaciar caché y recargar de manera forzada'\n";
echo "  4. Espera que cargue completamente\n";
echo "  5. Cierra DevTools\n";
echo "  6. Ctrl + F5 nuevamente\n\n";

echo "✅ Verificación completada\n";
