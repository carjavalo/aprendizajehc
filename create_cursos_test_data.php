<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Curso;
use App\Models\Area;
use App\Models\User;
use App\Models\Categoria;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎓 CREACIÓN DE DATOS DE PRUEBA PARA CURSOS\n";
echo "============================================\n\n";

try {
    // 1. Verificar que existan las tablas necesarias
    echo "1. 🔍 Verificando tablas necesarias...\n";
    
    $tables = ['categorias', 'areas', 'users', 'cursos'];
    foreach ($tables as $table) {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            throw new Exception("La tabla '{$table}' no existe");
        }
        echo "   ✅ Tabla '{$table}' existe\n";
    }
    
    // 2. Verificar/crear categorías
    echo "\n2. 📋 Verificando categorías...\n";
    $categoriaCount = Categoria::count();
    if ($categoriaCount == 0) {
        $categorias = [
            ['descripcion' => 'Medicina General'],
            ['descripcion' => 'Especialidades Médicas'],
            ['descripcion' => 'Enfermería'],
            ['descripcion' => 'Administración Hospitalaria'],
        ];
        
        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
        echo "   ✅ Creadas 4 categorías de prueba\n";
    } else {
        echo "   ✅ Ya existen {$categoriaCount} categorías\n";
    }
    
    // 3. Verificar/crear áreas
    echo "\n3. 🏢 Verificando áreas...\n";
    $areaCount = Area::count();
    if ($areaCount == 0) {
        $categorias = Categoria::all();
        $areas = [
            ['descripcion' => 'Consulta Externa', 'cod_categoria' => $categorias->first()->id],
            ['descripcion' => 'Urgencias', 'cod_categoria' => $categorias->first()->id],
            ['descripcion' => 'Hospitalización', 'cod_categoria' => $categorias->skip(1)->first()->id],
            ['descripcion' => 'Cirugía', 'cod_categoria' => $categorias->skip(1)->first()->id],
            ['descripcion' => 'Cuidados Intensivos', 'cod_categoria' => $categorias->skip(2)->first()->id],
        ];
        
        foreach ($areas as $area) {
            Area::create($area);
        }
        echo "   ✅ Creadas 5 áreas de prueba\n";
    } else {
        echo "   ✅ Ya existen {$areaCount} áreas\n";
    }
    
    // 4. Verificar usuarios instructores
    echo "\n4. 👨‍🏫 Verificando usuarios instructores...\n";
    $userCount = User::count();
    if ($userCount == 0) {
        echo "   ⚠️  No hay usuarios en el sistema\n";
        echo "   💡 Crea al menos un usuario para poder asignar como instructor\n";
        
        // Crear un usuario de prueba
        $user = User::create([
            'name' => 'Dr. Juan',
            'apellido1' => 'Pérez',
            'apellido2' => 'García',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password'),
            'role' => 'Docente',
            'email_verified_at' => now(),
        ]);
        echo "   ✅ Usuario instructor creado: {$user->email}\n";
    } else {
        echo "   ✅ Existen {$userCount} usuarios en el sistema\n";
    }
    
    // 5. Crear cursos de prueba
    echo "\n5. 📚 Creando cursos de prueba...\n";
    $cursoCount = Curso::count();
    
    if ($cursoCount == 0) {
        $areas = Area::all();
        $instructor = User::first();
        
        if (!$instructor) {
            throw new Exception("No hay usuarios disponibles para asignar como instructor");
        }
        
        $cursos = [
            [
                'titulo' => 'Introducción a la Medicina de Urgencias',
                'descripcion' => 'Curso básico sobre atención médica de urgencias, protocolos de triaje y manejo inicial de pacientes críticos.',
                'id_area' => $areas->where('descripcion', 'Urgencias')->first()->id ?? $areas->first()->id,
                'instructor_id' => $instructor->id,
                'fecha_inicio' => now()->addDays(7),
                'fecha_fin' => now()->addDays(37),
                'estado' => 'activo',
                'max_estudiantes' => 30,
                'objetivos' => 'Al finalizar el curso, los participantes serán capaces de: 1) Aplicar protocolos de triaje, 2) Manejar situaciones de emergencia, 3) Trabajar en equipo bajo presión.',
                'requisitos' => 'Título profesional en medicina o enfermería. Experiencia mínima de 1 año en atención clínica.',
                'duracion_horas' => 40,
            ],
            [
                'titulo' => 'Gestión de Calidad en Servicios de Salud',
                'descripcion' => 'Curso avanzado sobre implementación de sistemas de gestión de calidad en instituciones de salud.',
                'id_area' => $areas->where('descripcion', 'Consulta Externa')->first()->id ?? $areas->first()->id,
                'instructor_id' => $instructor->id,
                'fecha_inicio' => now()->addDays(14),
                'fecha_fin' => now()->addDays(44),
                'estado' => 'activo',
                'max_estudiantes' => 25,
                'objetivos' => 'Desarrollar competencias en: 1) Auditoría de calidad, 2) Indicadores de gestión, 3) Mejora continua de procesos.',
                'requisitos' => 'Experiencia en gestión hospitalaria o administración en salud.',
                'duracion_horas' => 60,
            ],
            [
                'titulo' => 'Cuidados Intensivos Pediátricos',
                'descripcion' => 'Especialización en cuidados críticos para pacientes pediátricos, incluyendo ventilación mecánica y monitoreo avanzado.',
                'id_area' => $areas->where('descripcion', 'Cuidados Intensivos')->first()->id ?? $areas->first()->id,
                'instructor_id' => $instructor->id,
                'fecha_inicio' => now()->addDays(21),
                'fecha_fin' => now()->addDays(81),
                'estado' => 'borrador',
                'max_estudiantes' => 15,
                'objetivos' => 'Capacitar en: 1) Manejo de ventiladores pediátricos, 2) Monitoreo hemodinámico, 3) Farmacología pediátrica crítica.',
                'requisitos' => 'Especialización en pediatría o medicina crítica. Certificación en RCP pediátrico.',
                'duracion_horas' => 80,
            ],
            [
                'titulo' => 'Técnicas Quirúrgicas Mínimamente Invasivas',
                'descripcion' => 'Curso práctico sobre cirugía laparoscópica y técnicas endoscópicas avanzadas.',
                'id_area' => $areas->where('descripcion', 'Cirugía')->first()->id ?? $areas->first()->id,
                'instructor_id' => $instructor->id,
                'fecha_inicio' => now()->addDays(30),
                'fecha_fin' => now()->addDays(90),
                'estado' => 'activo',
                'max_estudiantes' => 12,
                'objetivos' => 'Dominar: 1) Técnicas laparoscópicas básicas, 2) Manejo de complicaciones, 3) Selección de pacientes.',
                'requisitos' => 'Especialización en cirugía general. Experiencia mínima de 2 años en cirugía.',
                'duracion_horas' => 100,
            ],
            [
                'titulo' => 'Enfermería en Hospitalización',
                'descripcion' => 'Actualización en cuidados de enfermería para pacientes hospitalizados, incluyendo administración de medicamentos y cuidados especializados.',
                'id_area' => $areas->where('descripcion', 'Hospitalización')->first()->id ?? $areas->first()->id,
                'instructor_id' => $instructor->id,
                'fecha_inicio' => now()->subDays(10),
                'fecha_fin' => now()->addDays(20),
                'estado' => 'activo',
                'max_estudiantes' => 40,
                'objetivos' => 'Actualizar conocimientos en: 1) Administración segura de medicamentos, 2) Cuidados post-operatorios, 3) Prevención de infecciones.',
                'requisitos' => 'Título profesional en enfermería. Experiencia en hospitalización.',
                'duracion_horas' => 30,
            ],
        ];
        
        foreach ($cursos as $cursoData) {
            $curso = Curso::create($cursoData);
            echo "   ✅ Curso creado: {$curso->titulo} (ID: {$curso->id}, Código: {$curso->codigo_acceso})\n";
        }
        
        echo "   🎉 Se crearon " . count($cursos) . " cursos de prueba\n";
    } else {
        echo "   ✅ Ya existen {$cursoCount} cursos en el sistema\n";
    }
    
    // 6. Mostrar resumen
    echo "\n6. 📊 Resumen del sistema:\n";
    echo "   - Categorías: " . Categoria::count() . "\n";
    echo "   - Áreas: " . Area::count() . "\n";
    echo "   - Usuarios: " . User::count() . "\n";
    echo "   - Cursos: " . Curso::count() . "\n";
    
    // 7. Mostrar cursos creados
    echo "\n7. 📚 Cursos disponibles:\n";
    $cursos = Curso::with(['area', 'instructor'])->get();
    foreach ($cursos as $curso) {
        echo "   📖 {$curso->titulo}\n";
        echo "      - Área: {$curso->area->descripcion}\n";
        echo "      - Instructor: {$curso->instructor->name} {$curso->instructor->apellido1}\n";
        echo "      - Estado: {$curso->estado}\n";
        echo "      - Código: {$curso->codigo_acceso}\n";
        echo "      - URL: http://127.0.0.1:8000/capacitaciones/cursos/{$curso->id}/classroom\n\n";
    }
    
    echo "🎉 ¡DATOS DE PRUEBA CREADOS EXITOSAMENTE!\n\n";
    echo "🌐 ACCEDER AL SISTEMA:\n";
    echo "   - Lista de cursos: http://127.0.0.1:8000/capacitaciones/cursos\n";
    echo "   - Primer curso: http://127.0.0.1:8000/capacitaciones/cursos/1/classroom\n\n";
    
    echo "👤 USUARIO DE PRUEBA:\n";
    echo "   - Email: instructor@test.com\n";
    echo "   - Password: password\n\n";
    
    echo "💡 PRÓXIMOS PASOS:\n";
    echo "   1. Acceder al sistema con el usuario de prueba\n";
    echo "   2. Explorar la lista de cursos\n";
    echo "   3. Entrar al classroom de cualquier curso\n";
    echo "   4. Probar subir materiales (como instructor)\n";
    echo "   5. Crear posts en los foros\n";
    echo "   6. Inscribir estudiantes a los cursos\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
