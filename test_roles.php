<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\CursoAsignacion::all() as $c) {
    if ($c->docente) {
        echo "doc_role: " . $c->docente->role . " (id: " . $c->docente->id . ") \n";
    }
    if ($c->estudiante) {
        echo "est_role: " . $c->estudiante->role . " (id: " . $c->estudiante->id . ") \n";
    }
}
