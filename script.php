<?php
$c = file_get_contents('app/Http/Controllers/AcademicoController.php');
$search = "->addColumn('instructor_nombre', function (\) {\n                  return \->instructor->full_name ?? 'Sin instructor';\n              })";
$search2 = "->addColumn('instructor_nombre', function (\) {\r\n                  return \->instructor->full_name ?? 'Sin instructor';\r\n              })";
$replace = "->addColumn('instructor_nombre', function (\) use (\, \, \) {\n                  if (!in_array(\, \)) {\n                      \ = \App\Models\CursoAsignacion::with('docente')\n                          ->where('curso_id', \->id)\n                          ->where('estudiante_id', \->id)\n                          ->activas()\n                          ->first();\n                      if (\ && \->docente) {\n                          return trim(\->docente->name . ' ' . \->docente->apellido1);\n                      }\n                  }\n                  return \->instructor->full_name ?? 'Sin docente asignado';\n              })";
$c = str_replace($search, $replace, $c);
$c = str_replace($search2, $replace, $c);
file_put_contents('app/Http/Controllers/AcademicoController.php', $c);
echo "Replaced in AcademicoController.php\n";
