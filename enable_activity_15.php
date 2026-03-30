<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$actividad = App\Models\CursoActividad::find(15);
if ($actividad) {
    $actividad->habilitado = true;
    $actividad->save();
    echo "Activity 15 enabled.\n";
} else {
    echo "Activity 15 not found.\n";
}
