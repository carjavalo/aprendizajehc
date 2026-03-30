<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Extend activities 15 and 16 to end of year
$ids = [15, 16];
$affected = App\Models\CursoActividad::whereIn('id', $ids)
    ->update(['fecha_cierre' => '2025-12-31 23:59:59']);

echo "Se actualizaron las fechas de cierre para {$affected} actividades.\n";
