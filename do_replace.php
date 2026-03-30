<?php
\ = "app/Http/Controllers/AcademicoController.php";
\ = file(\);

for (\ = 0; \ < count(\); \++) {
    if (strpos(\[\], "->addColumn('instructor_nombre', function (\) {") !== false) {
        \[\] = "            ->addColumn('instructor_nombre', function (\) use (\, \, \) {\n";
        \[\ + 1] = "                if (!in_array(\, \)) {\n                    \ = \App\Models\CursoAsignacion::with('docente')->where('curso_id', \->id)->where('estudiante_id', \->id)->activas()->first();\n                    if (\ && \->docente) {\n                        return trim(\->docente->name . ' ' . \->docente->apellido1);\n                    }\n                }\n                return \->instructor->full_name ?? 'Sin docente';\n            })\n";
        \[\ + 2] = "";
    }
}

file_put_contents(\, implode("", \));
echo "Por fin logrado!";
