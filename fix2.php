<?php
$file = 'app/Http/Controllers/AcademicoController.php';
$content = file_get_contents($file);
$pattern = "/->addColumn\('instructor_nombre', function \\\(\\\\\\) \\\{[\s\r\n]*return \\\->instructor->full_name \?\? 'Sin instructor';[\s\r\n]*\\\}/";
$replacement = "->addColumn('instructor_nombre', function (\) use (\, \, \) {\n                  if (!in_array(\, \)) {\n                      \ = \App\Models\CursoAsignacion::with('docente')->where('curso_id', \->id)->where('estudiante_id', \->id)->activas()->first();\n                      if (\ && \->docente) {\n                          return trim(\->docente->name . ' ' . \->docente->apellido1);\n                      }\n                  }\n                  return \->instructor->full_name ?? 'Sin docente asignado';\n              }";
$new_content = preg_replace($pattern, $replacement, $content);
if ($new_content !== $content && $new_content !== null) {
    file_put_contents($file, $new_content);
    echo "¡Modificado con Regex exitosamente!";
} else {
    echo "No se encontró el patrón para modificar.";
}
