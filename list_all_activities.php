<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$activities = App\Models\CursoActividad::all();
$output = "ID | Curso ID | Titulo | Apertura | Cierre\n";
foreach ($activities as $activity) {
    $output .= "{$activity->id} | {$activity->curso_id} | {$activity->titulo} | {$activity->fecha_apertura} | {$activity->fecha_cierre}\n";
}
file_put_contents('all_activities.txt', $output);
