<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Obtener información de la columna role
$result = DB::select("SHOW COLUMNS FROM users WHERE Field = 'role'");

if (!empty($result)) {
    $column = $result[0];
    echo "Información de la columna 'role':\n";
    echo "================================\n";
    echo "Campo: " . $column->Field . "\n";
    echo "Tipo: " . $column->Type . "\n";
    echo "Nulo: " . $column->Null . "\n";
    echo "Clave: " . $column->Key . "\n";
    echo "Default: " . ($column->Default ?? 'NULL') . "\n";
    echo "Extra: " . $column->Extra . "\n";
} else {
    echo "La columna 'role' no existe en la tabla users\n";
}
