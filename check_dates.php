<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$activities = App\Models\CursoActividad::where('curso_id', 8)->get();
$now = now();

$output = "Current Time: " . $now . "\n";
$output .= "ID | Titulo | Apertura | Cierre | Estado Calculado\n";
$output .= "---|---|---|---|---\n";


foreach ($activities as $activity) {
    $estado = 'Abierta';
    if ($activity->fecha_apertura && $now->lt($activity->fecha_apertura)) {
        $estado = 'Pendiente (Futuro)';
    } elseif ($activity->fecha_cierre && $now->gt($activity->fecha_cierre)) {
        $estado = 'Cerrada (Pasado)';
    }
    
    $output .= "{$activity->id} | {$activity->titulo} | {$activity->fecha_apertura} | {$activity->fecha_cierre} | {$estado}\n";
}
file_put_contents('dates_output.txt', $output);

