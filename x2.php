<?php
$f = "resources/views/academico/cursos-disponibles/index.blade.php";
$c = file_get_contents($f);
$c = str_replace("<th>Instructor</th>", "<th>Docente</th>", $c);
$c = str_replace("<strong>Instructor:</strong>", "<strong>Docente:</strong>", $c);
file_put_contents($f, $c);
echo "View updated";

