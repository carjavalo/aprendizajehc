<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$availableRoles = App\Models\User::getAvailableRoles();
$filtered = array_values(array_filter($availableRoles, function($role) {
    return in_array($role, ["Estudiante", "Registrado", "Docente", "Operador", "Consultor Agesoc", "Consultor Asstracud"]);
}));
print_r($filtered);

