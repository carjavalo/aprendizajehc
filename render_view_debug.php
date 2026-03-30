<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Login as a user (e.g., ID 2)
auth()->loginUsingId(2);

$cursoId = 9;
$curso = App\Models\Curso::find($cursoId);

if (!$curso) {
    die("Course $cursoId not found\n");
}

// Mimic controller logic
$query = $curso->actividades()->orderBy('fecha_apertura');
echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";
$actividades = $query->get();
echo "Activities found (all): " . $actividades->count() . "\n";
$actividades = $curso->actividades()->where('habilitado', true)->orderBy('fecha_apertura')->get();
echo "Activities found (enabled): " . $actividades->count() . "\n";
exit;

$estudiante = $curso->estudiantes()->where('estudiante_id', auth()->id())->first();
$actividadesCompletadas = $estudiante ? ($estudiante->pivot->actividades_completadas ?? []) : [];

// Ensure it's an array
if (is_string($actividadesCompletadas)) {
    $actividadesCompletadas = json_decode($actividadesCompletadas, true) ?? [];
}

// Render view
$view = view('academico.curso.actividades', compact('curso', 'actividades', 'actividadesCompletadas'))->render();

echo $view;
