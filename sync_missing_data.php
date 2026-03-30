<?php
/**
 * Script para sincronizar datos faltantes en la base de datos
 * Solo inserta registros que no existen
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Sincronización de datos faltantes ===\n\n";

try {
    DB::beginTransaction();

    // 1. Verificar y crear tablas de componentes si no existen
    echo "1. Verificando tablas de componentes...\n";
    
    // Tabla servicios_areas
    if (!DB::getSchemaBuilder()->hasTable('servicios_areas')) {
        DB::statement("CREATE TABLE servicios_areas (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");
        echo "   - Tabla servicios_areas creada\n";
    }

    // Tabla vinculacion_contrato
    if (!DB::getSchemaBuilder()->hasTable('vinculacion_contrato')) {
        DB::statement("CREATE TABLE vinculacion_contrato (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");
        echo "   - Tabla vinculacion_contrato creada\n";
    }

    // Tabla sedes
    if (!DB::getSchemaBuilder()->hasTable('sedes')) {
        DB::statement("CREATE TABLE sedes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");
        echo "   - Tabla sedes creada\n";
    }

    // 2. Insertar datos en servicios_areas si no existen
    echo "\n2. Sincronizando servicios_areas...\n";
    $serviciosAreas = [
        'Administrativo', 'Consulta Externa', 'Hospitalizacion', 
        'Sala de Operaciones', 'Uci', 'Urgencias', 
        'Banco de Sangre', 'Unidad Renal', 'Otro'
    ];
    
    foreach ($serviciosAreas as $nombre) {
        $exists = DB::table('servicios_areas')->where('nombre', $nombre)->exists();
        if (!$exists) {
            DB::table('servicios_areas')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: $nombre\n";
        }
    }

    // 3. Insertar datos en vinculacion_contrato si no existen
    echo "\n3. Sincronizando vinculacion_contrato...\n";
    $vinculaciones = [
        'Nomina', 'Agesoc', 'Asstracud', 'Estudiante', 
        'Docente', 'Unidad Renal', 'Otro'
    ];
    
    foreach ($vinculaciones as $nombre) {
        $exists = DB::table('vinculacion_contrato')->where('nombre', $nombre)->exists();
        if (!$exists) {
            DB::table('vinculacion_contrato')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: $nombre\n";
        }
    }

    // 4. Insertar datos en sedes si no existen
    echo "\n4. Sincronizando sedes...\n";
    $sedes = ['HUV-CALI', 'HUV-CARTAGO'];
    
    foreach ($sedes as $nombre) {
        $exists = DB::table('sedes')->where('nombre', $nombre)->exists();
        if (!$exists) {
            DB::table('sedes')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: $nombre\n";
        }
    }

    // 5. Sincronizar categorías
    echo "\n5. Sincronizando categorías...\n";
    $categorias = [
        ['id' => 28, 'descripcion' => 'Extensión Académica'],
        ['id' => 29, 'descripcion' => 'Coordinación Académica'],
    ];
    
    foreach ($categorias as $cat) {
        $exists = DB::table('categorias')->where('id', $cat['id'])->exists();
        if (!$exists) {
            DB::table('categorias')->insert([
                'id' => $cat['id'],
                'descripcion' => $cat['descripcion'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: {$cat['descripcion']}\n";
        }
    }

    // 6. Sincronizar áreas
    echo "\n6. Sincronizando áreas...\n";
    $areas = [
        ['id' => 9, 'descripcion' => 'Inducción Institucional', 'cod_categoria' => 29],
        ['id' => 10, 'descripcion' => 'Cursos', 'cod_categoria' => 28],
        ['id' => 11, 'descripcion' => 'Diplomados', 'cod_categoria' => 28],
    ];
    
    foreach ($areas as $area) {
        $exists = DB::table('areas')->where('id', $area['id'])->exists();
        if (!$exists) {
            DB::table('areas')->insert([
                'id' => $area['id'],
                'descripcion' => $area['descripcion'],
                'cod_categoria' => $area['cod_categoria'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: {$area['descripcion']}\n";
        }
    }

    // 7. Sincronizar usuarios principales
    echo "\n7. Sincronizando usuarios...\n";
    $users = [
        ['id' => 1, 'name' => 'Carlos Jairton', 'apellido1' => 'Valderrama', 'apellido2' => 'Orobio', 
         'email' => 'carjavalosistem@gmail.com', 'role' => 'Super Admin', 'tipo_documento' => 'DNI', 
         'numero_documento' => '121424443', 'password' => '$2y$12$EnOfSKid6Q0GxBR0ncZjde2okJWsrZIr999R7/gzJAEcAZJ2IIvPq'],
        ['id' => 36, 'name' => 'Estudiante', 'apellido1' => 'uno', 'apellido2' => 'uno',
         'email' => 'uno@estudiante.com', 'role' => 'Estudiante', 'tipo_documento' => 'Pasaporte',
         'numero_documento' => '6427785448', 'password' => '$2y$12$StNFxknxExNgSqNAjmUm7.HS70qnEEojUDTx3nV74SK0ojFU0p3AK'],
        ['id' => 45, 'name' => 'DocenteCurso', 'apellido1' => 'Prueba', 'apellido2' => 'Prueba',
         'email' => 'uno@docente.com', 'role' => 'Docente', 'tipo_documento' => 'Cédula',
         'numero_documento' => '987654321123', 'password' => '$2y$12$MXkIdaF70ayAirlxuhBJie.8UqI.fo5gm0tXPW8b.KSOkY.m9iaWi'],
    ];
    
    foreach ($users as $user) {
        $exists = DB::table('users')->where('id', $user['id'])->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'id' => $user['id'],
                'name' => $user['name'],
                'apellido1' => $user['apellido1'],
                'apellido2' => $user['apellido2'],
                'email' => $user['email'],
                'role' => $user['role'],
                'tipo_documento' => $user['tipo_documento'],
                'numero_documento' => $user['numero_documento'],
                'email_verified_at' => now(),
                'password' => $user['password'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado usuario: {$user['name']} ({$user['email']})\n";
        }
    }

    // 8. Sincronizar cursos
    echo "\n8. Sincronizando cursos...\n";
    $cursos = [
        ['id' => 13, 'titulo' => 'Reanimación Cardiopulmonar(RCP)', 'id_area' => 10, 'instructor_id' => 45, 
         'estado' => 'activo', 'codigo_acceso' => 'AKOQD7', 'duracion_horas' => 20],
        ['id' => 14, 'titulo' => 'Hemato Oncología', 'id_area' => 11, 'instructor_id' => 45,
         'estado' => 'activo', 'codigo_acceso' => 'K3HJMJ', 'duracion_horas' => 115],
        ['id' => 15, 'titulo' => 'Inducción 2026', 'id_area' => 9, 'instructor_id' => 45,
         'estado' => 'activo', 'codigo_acceso' => '0DPCON', 'duracion_horas' => 12],
    ];
    
    foreach ($cursos as $curso) {
        $exists = DB::table('cursos')->where('id', $curso['id'])->exists();
        if (!$exists) {
            DB::table('cursos')->insert([
                'id' => $curso['id'],
                'titulo' => $curso['titulo'],
                'id_area' => $curso['id_area'],
                'instructor_id' => $curso['instructor_id'],
                'estado' => $curso['estado'],
                'codigo_acceso' => $curso['codigo_acceso'],
                'duracion_horas' => $curso['duracion_horas'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado curso: {$curso['titulo']}\n";
        }
    }

    // 9. Sincronizar materiales de cursos
    echo "\n9. Sincronizando materiales de cursos...\n";
    $materiales = [
        // Curso RCP (13)
        ['id' => 39, 'curso_id' => 13, 'titulo' => 'introducción curso de soporte vital básico HUV', 'tipo' => 'video', 
         'url_externa' => 'https://drive.google.com/file/d/1tzum92RP6Na5h6j8_p3CZzyZUki1xhWw/view?usp=sharing', 'orden' => 1],
        ['id' => 40, 'curso_id' => 13, 'titulo' => 'Generalidades de la rcp y soporte vital básico adulto', 'tipo' => 'video',
         'url_externa' => 'https://drive.google.com/file/d/1u-zHrzfLMZL5s7i0TXJRinSENVDl1MNv/view', 'orden' => 2],
        ['id' => 41, 'curso_id' => 13, 'titulo' => 'Ovace', 'tipo' => 'video',
         'url_externa' => 'https://drive.google.com/file/d/1U4yZoT0GhJyKVe93NuqIHiLAEG3MEYs1/view', 'orden' => 3],
        ['id' => 42, 'curso_id' => 13, 'titulo' => 'Video Dea', 'tipo' => 'video',
         'url_externa' => 'https://drive.google.com/file/d/18q365bkXNDzhUfZ14ECqkdGcT1XMb5Sc/view', 'orden' => 4],
        ['id' => 43, 'curso_id' => 13, 'titulo' => 'Soporte vital básico pediátrico', 'tipo' => 'video',
         'url_externa' => 'https://drive.google.com/file/d/1yT2LA4yEK42z5HU3uhYjkr1DP4N7Qrhw/view', 'orden' => 5],
        // Curso Hemato (14)
        ['id' => 44, 'curso_id' => 14, 'titulo' => 'MODULO 1', 'tipo' => 'video',
         'url_externa' => 'https://view.genially.com/67f834bd1a857fdd10bbbe14/interactive-content-modulo-1-hemato', 'orden' => 1],
        ['id' => 45, 'curso_id' => 14, 'titulo' => 'MODULO 2', 'tipo' => 'video',
         'url_externa' => 'https://view.genially.com/67f8373820540ffde531a7f7/interactive-content-modulo-2-hemato', 'orden' => 2],
        ['id' => 46, 'curso_id' => 14, 'titulo' => 'MODULO 3', 'tipo' => 'video',
         'url_externa' => 'https://view.genially.com/67f83819285f3787bbefc6b3/interactive-content-modulo-3-hemato', 'orden' => 3],
        ['id' => 47, 'curso_id' => 14, 'titulo' => 'MODULO 4', 'tipo' => 'video',
         'url_externa' => 'https://view.genially.com/67f838b7f15f85cbc97cb840/interactive-content-modulo-4-hemato', 'orden' => 4],
    ];
    
    foreach ($materiales as $mat) {
        $exists = DB::table('curso_materiales')->where('id', $mat['id'])->exists();
        if (!$exists) {
            DB::table('curso_materiales')->insert([
                'id' => $mat['id'],
                'curso_id' => $mat['curso_id'],
                'titulo' => $mat['titulo'],
                'tipo' => $mat['tipo'],
                'url_externa' => $mat['url_externa'],
                'orden' => $mat['orden'],
                'es_publico' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado material: {$mat['titulo']}\n";
        }
    }

    // 10. Materiales del curso Inducción 2026 (15)
    echo "\n10. Sincronizando materiales de Inducción 2026...\n";
    $materialesInduccion = [
        ['id' => 48, 'titulo' => '1) Direccionamiento Estratégico', 'url' => 'https://view.genially.com/65847b54c60dcb00144860aa/interactive-content-direccionamiento-estrategico'],
        ['id' => 49, 'titulo' => '2) Gestión Calidad', 'url' => 'https://view.genially.com/65848554c60dcb00144d93e6/interactive-content-gestion-calidad'],
        ['id' => 50, 'titulo' => '3) Coordinación Académica', 'url' => 'https://view.genially.com/65848393cbb59a001482f35f/interactive-content-coordinacion-academica'],
        ['id' => 51, 'titulo' => '5) Política de Gestión del Conocimiento y la Innovación', 'url' => 'https://view.genially.com/65b7c10320d46c0014fc41f2/interactive-content-politica-de-gestion-del-conocimiento-y-la-innovacion'],
        ['id' => 52, 'titulo' => '6) Política de Humanización', 'url' => 'https://view.genially.com/66d5c3491f0c039ac5a77f86/interactive-content-politica-de-humanizacion'],
        ['id' => 53, 'titulo' => '7) Daruma', 'url' => 'https://view.genially.com/65858260899e670015866159/interactive-content-daruma'],
        ['id' => 54, 'titulo' => '8) Derechos Y Deberes Del Paciente', 'url' => 'https://www.calameo.com/read/006141493fcc60112b715', 'tipo' => 'documento'],
        ['id' => 55, 'titulo' => '9) Seguridad Del paciente', 'url' => 'https://view.genially.com/65f85fc0bb328200149b9d62/interactive-content-seguridad-del-paciente'],
        ['id' => 56, 'titulo' => '10) Control de Infecciones', 'url' => 'https://view.genially.com/6691937cbf1e2c0d70d02a64/interactive-content-control-de-infecciones'],
        ['id' => 57, 'titulo' => '11) Seguridad y Salud en el Trabajo', 'url' => 'https://view.genially.com/658487bfc5af6d0013a17017/interactive-content-salud-ocupacional'],
        ['id' => 58, 'titulo' => '12) Gestión Ambiental', 'url' => 'https://view.genially.com/65848901c5af6d0013a224a9/interactive-content-gestion-ambiental'],
        ['id' => 59, 'titulo' => '13) Atención Quirúrgica', 'url' => 'https://view.genially.com/66df6e2bc9379e1c924c706d/interactive-content-atencion-quirurgica'],
        ['id' => 60, 'titulo' => '14) Programas Sociales', 'url' => 'https://view.genially.com/658580a378f80a0015b854ab/interactive-content-programas-sociales'],
        ['id' => 61, 'titulo' => '15) Trabajo Mimhos', 'url' => 'https://view.genially.com/6585bd42899e6700159fe0be/interactive-content-trabajo-mimhos'],
        ['id' => 62, 'titulo' => '16) Lactancia Materna', 'url' => 'https://view.genially.com/6585bc35fa7c870015622fd4/interactive-content-lactancia-materna'],
    ];
    
    $orden = 1;
    foreach ($materialesInduccion as $mat) {
        $exists = DB::table('curso_materiales')->where('id', $mat['id'])->exists();
        if (!$exists) {
            DB::table('curso_materiales')->insert([
                'id' => $mat['id'],
                'curso_id' => 15,
                'titulo' => $mat['titulo'],
                'tipo' => $mat['tipo'] ?? 'video',
                'url_externa' => $mat['url'],
                'orden' => $orden,
                'es_publico' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   + Insertado: {$mat['titulo']}\n";
        }
        $orden++;
    }

    // 11. Sincronizar foro de bienvenida
    echo "\n11. Sincronizando foros...\n";
    $foroExists = DB::table('curso_foros')->where('id', 12)->exists();
    if (!$foroExists) {
        DB::table('curso_foros')->insert([
            'id' => 12,
            'curso_id' => 15,
            'usuario_id' => 45,
            'titulo' => '¡Bienvenidos al hospital!',
            'contenido' => 'Nos complace iniciar con ustedes este proceso de inducción, en el cual conocerán los lineamientos, normas y procedimientos que orientan nuestra labor asistencial y académica.',
            'es_anuncio' => 1,
            'es_fijado' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "   + Insertado foro de bienvenida\n";
    }

    DB::commit();
    echo "\n=== Sincronización completada exitosamente ===\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
