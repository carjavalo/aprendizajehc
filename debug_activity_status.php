<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Simulate logged in user (assuming ID 1 or the first user, or I need to know who the user is. 
// Since I can't easily know the session user from CLI, I'll check for the user associated with the course enrollment or just check all users)
// Let's assume user ID 2 (student) or check for any completion.

$actividadId = 15;
$actividad = App\Models\CursoActividad::find($actividadId);

if (!$actividad) {
    die("Activity $actividadId not found\n");
}

echo "Activity: {$actividad->titulo} (ID: {$actividad->id})\n";
echo "Course ID: {$actividad->curso_id}\n";
echo "Type: {$actividad->tipo}\n";
echo "Enabled: " . ($actividad->habilitado ? 'Yes' : 'No') . "\n";
echo "Open Date: {$actividad->fecha_apertura}\n";
echo "Close Date: {$actividad->fecha_cierre}\n";
echo "Current Time: " . now() . "\n";

$isOpen = !$actividad->fecha_cierre || now() <= $actividad->fecha_cierre;
echo "Is Open (Calculated): " . ($isOpen ? 'Yes' : 'No') . "\n";

// Check completions
$completions = DB::table('curso_actividad_entrega')
    ->where('actividad_id', $actividadId)
    ->get();

echo "Completions found: " . $completions->count() . "\n";
foreach ($completions as $completion) {
    echo "- User ID: {$completion->user_id}, Status: {$completion->estado}, Date: {$completion->created_at}\n";
}
