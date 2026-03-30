<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$actividad = App\Models\CursoActividad::find(15);
echo "Type: " . gettype($actividad->contenido_json) . "\n";
if (is_string($actividad->contenido_json)) {
    echo "Value: " . $actividad->contenido_json . "\n";
} else {
    echo "Value: " . json_encode($actividad->contenido_json) . "\n";
}
