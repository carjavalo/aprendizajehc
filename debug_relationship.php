<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$curso = App\Models\Curso::find(9);
if (!$curso) {
    die("Course 9 not found\n");
}
echo "Course found: {$curso->titulo}\n";

$actividadModel = new App\Models\CursoActividad();
echo "Table: " . $actividadModel->getTable() . "\n";

$query = $curso->actividades();
echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";

$count = $query->count();
echo "Count via relationship: $count\n";

// Direct query
$act = App\Models\CursoActividad::find(15);
echo "ID: {$act->id}\n";
echo "Enabled (var_export): " . var_export($act->habilitado, true) . "\n";
echo "Enabled (ternary): " . ($act->habilitado ? 'Yes' : 'No') . "\n";
exit;

