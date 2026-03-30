<?php
$f = "app/Http/Controllers/AcademicoController.php";
$c = file_get_contents($f);
$start = strpos($c, "->addColumn(\047instructor_nombre\047,");
if ($start !== false) {
    $end = strpos($c, "})", $start) + 2;
    $part1 = substr($c, 0, $start);
    $part2 = substr($c, $end);
    
    $replacement = "->addColumn(\047instructor_nombre\047, function (\$curso) use (\$user, \$userRole, \$rolesVerTodos) {
                if (!in_array(\$userRole, \$rolesVerTodos)) {
                    \$asignacion = \App\Models\CursoAsignacion::with(\047docente\047)->where(\047curso_id\047, \$curso->id)->where(\047estudiante_id\047, \$user->id)->activas()->first();
                    if (\$asignacion && \$asignacion->docente) {
                        return trim(\$asignacion->docente->name . \047 \047 . \$asignacion->docente->apellido1);
                    }
                }
                return \$curso->instructor->full_name ?? \047Sin docente asignado\047;
            })";
    
    $c = $part1 . $replacement . $part2;
    file_put_contents($f, $c);
    echo "Done for real";
} else {
    echo "Not found";
}

