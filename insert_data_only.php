<?php
/**
 * Script para insertar SOLO los datos de cada tabla
 * Extrae los INSERT del dump SQL y los ejecuta
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INSERCIÓN DE DATOS EN TABLAS ===\n\n";

try {
    DB::beginTransaction();
    
    // Desactivar verificación de claves foráneas temporalmente
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // 1. CATEGORIAS
    echo "Insertando datos en 'categorias'...\n";
    DB::table('categorias')->truncate();
    DB::table('categorias')->insert([
        ['id' => 28, 'descripcion' => 'Extensión Académica', 'created_at' => '2026-01-05 20:44:47', 'updated_at' => '2026-01-05 20:44:47'],
        ['id' => 29, 'descripcion' => 'Coordinación Académica', 'created_at' => '2026-01-05 20:45:15', 'updated_at' => '2026-01-05 20:45:15'],
    ]);
    echo "  ✓ categorias: 2 registros\n";

    // 2. AREAS
    echo "Insertando datos en 'areas'...\n";
    DB::table('areas')->truncate();
    DB::table('areas')->insert([
        ['id' => 9, 'descripcion' => 'Inducción Institucional', 'cod_categoria' => 29, 'created_at' => '2026-01-05 21:08:05', 'updated_at' => '2026-01-05 21:11:25'],
        ['id' => 10, 'descripcion' => 'Cursos', 'cod_categoria' => 28, 'created_at' => '2026-01-05 21:13:12', 'updated_at' => '2026-01-05 21:13:12'],
        ['id' => 11, 'descripcion' => 'Diplomados', 'cod_categoria' => 28, 'created_at' => '2026-01-05 21:13:23', 'updated_at' => '2026-01-05 21:13:23'],
    ]);
    echo "  ✓ areas: 3 registros\n";

    // 3. USERS
    echo "Insertando datos en 'users'...\n";
    DB::table('users')->truncate();
    DB::table('users')->insert([
        ['id' => 1, 'name' => 'Carlos Jairton', 'apellido1' => 'Valderrama', 'apellido2' => 'Orobio', 'email' => 'carjavalosistem@gmail.com', 'role' => 'Super Admin', 'tipo_documento' => 'DNI', 'numero_documento' => '121424443', 'email_verified_at' => '2025-06-17 02:36:45', 'password' => '$2y$12$EnOfSKid6Q0GxBR0ncZjde2okJWsrZIr999R7/gzJAEcAZJ2IIvPq', 'remember_token' => NULL, 'created_at' => '2025-06-16 18:26:54', 'updated_at' => '2025-06-17 02:38:57'],
        ['id' => 36, 'name' => 'Estudiante', 'apellido1' => 'uno', 'apellido2' => 'uno', 'email' => 'uno@estudiante.com', 'role' => 'Estudiante', 'tipo_documento' => 'Pasaporte', 'numero_documento' => '6427785448', 'email_verified_at' => '2025-06-17 02:19:51', 'password' => '$2y$12$StNFxknxExNgSqNAjmUm7.HS70qnEEojUDTx3nV74SK0ojFU0p3AK', 'remember_token' => 'gW1f3icZU7SowXyxOQYPFHjemwlOOUqBJAjK4ichtFPHPuBwDIr6xDL9WhR5', 'created_at' => '2025-06-17 02:17:36', 'updated_at' => '2025-12-11 02:37:13'],
        ['id' => 37, 'name' => 'Estudiante', 'apellido1' => 'dos', 'apellido2' => 'dos', 'email' => 'dos@estudiante.com', 'role' => 'Estudiante', 'tipo_documento' => 'Cédula', 'numero_documento' => '1233321', 'email_verified_at' => '2025-06-17 05:04:04', 'password' => '$2y$12$y1RMMw0/bqy4KgVa.L4DRetmOPVCm0ceI38zTioB0tbjlCRjjicYa', 'remember_token' => NULL, 'created_at' => '2025-06-17 02:48:13', 'updated_at' => '2025-11-14 19:42:27'],
        ['id' => 38, 'name' => 'Usuario', 'apellido1' => 'Prueba', 'apellido2' => 'Verificado', 'email' => 'test@example.com', 'role' => 'Registrado', 'tipo_documento' => 'DNI', 'numero_documento' => '87654321', 'email_verified_at' => '2025-06-17 05:04:04', 'password' => '$2y$12$8RjSJS9V/WqEVYGKw8HQAuTI7G73FXCIcfbbCpK4sXUopD1dwyLCi', 'remember_token' => NULL, 'created_at' => '2025-06-17 03:03:26', 'updated_at' => '2025-06-17 03:03:26'],
        ['id' => 44, 'name' => 'Jhon Andres', 'apellido1' => 'Carrillo', 'apellido2' => 'Bolaños', 'email' => 'touma11913@gmail.com', 'role' => 'Operador', 'tipo_documento' => 'Cédula', 'numero_documento' => '1143995780', 'email_verified_at' => '2025-12-12 18:00:34', 'password' => '$2y$12$a8e/Y0JRjQieHsX2QgkUDertB9rhX/hSRx1m7XVpW8Xu6NGA.DdXy', 'remember_token' => 'PoDSylWbxY5peT4lHNjCHgkHUMnXqtCAX3xHH3C8366dcSrtKaaZEvGmFaqI', 'created_at' => '2025-12-12 18:00:32', 'updated_at' => '2026-01-05 19:56:24'],
        ['id' => 45, 'name' => 'DocenteCurso', 'apellido1' => 'Prueba', 'apellido2' => 'Prueba', 'email' => 'uno@docente.com', 'role' => 'Docente', 'tipo_documento' => 'Cédula', 'numero_documento' => '987654321123', 'email_verified_at' => NULL, 'password' => '$2y$12$MXkIdaF70ayAirlxuhBJie.8UqI.fo5gm0tXPW8b.KSOkY.m9iaWi', 'remember_token' => NULL, 'created_at' => '2026-01-05 23:19:46', 'updated_at' => '2026-01-05 23:22:54'],
        ['id' => 46, 'name' => 'Prueba', 'apellido1' => 'D', 'apellido2' => '1', 'email' => 'programasdeextensionhuv@gmail.com', 'role' => 'Estudiante', 'tipo_documento' => 'Cédula', 'numero_documento' => '1143995781', 'email_verified_at' => NULL, 'password' => '$2y$12$Is1gMdQeSHSfRj2AgCz8E.7JoJWIpjSpxuy9.u7QCXjjlwsJf6bGa', 'remember_token' => 'MlKhufImUXyNOEqT5qAM7oc78U7o3qSFAIRnykJRcdg47umbrOAu8UZZtJcF', 'created_at' => '2026-01-06 17:36:28', 'updated_at' => '2026-01-06 17:36:28'],
    ]);
    echo "  ✓ users: 7 registros\n";

    // 4. CURSOS
    echo "Insertando datos en 'cursos'...\n";
    DB::table('cursos')->truncate();
    DB::table('cursos')->insert([
        ['id' => 13, 'titulo' => 'Reanimación Cardiopulmonar(RCP)', 'descripcion' => NULL, 'id_area' => 10, 'instructor_id' => 45, 'fecha_inicio' => '2026-01-05', 'fecha_fin' => NULL, 'estado' => 'activo', 'codigo_acceso' => 'AKOQD7', 'max_estudiantes' => NULL, 'imagen_portada' => 'cursos/portadas/fnNjsPQQ8xtazUNrAmbuQkRi3PMi4hXkDuMjDayy.png', 'objetivos' => NULL, 'requisitos' => NULL, 'duracion_horas' => 20, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 18:56:41'],
        ['id' => 14, 'titulo' => 'Hemato Oncología', 'descripcion' => NULL, 'id_area' => 11, 'instructor_id' => 45, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'estado' => 'activo', 'codigo_acceso' => 'K3HJMJ', 'max_estudiantes' => NULL, 'imagen_portada' => NULL, 'objetivos' => NULL, 'requisitos' => NULL, 'duracion_horas' => 115, 'created_at' => '2026-01-06 18:51:49', 'updated_at' => '2026-01-06 18:56:21'],
        ['id' => 15, 'titulo' => 'Inducción 2026', 'descripcion' => NULL, 'id_area' => 9, 'instructor_id' => 45, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'estado' => 'activo', 'codigo_acceso' => '0DPCON', 'max_estudiantes' => NULL, 'imagen_portada' => 'cursos/portadas/Ddgh4GR1zz8Pi2Vrg5Mb91Bc1PpSQU70wJT8d1px.png', 'objetivos' => NULL, 'requisitos' => NULL, 'duracion_horas' => 12, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:20:32'],
    ]);
    echo "  ✓ cursos: 3 registros\n";

    // 5. CURSO_ASIGNACIONES
    echo "Insertando datos en 'curso_asignaciones'...\n";
    DB::table('curso_asignaciones')->truncate();
    DB::table('curso_asignaciones')->insert([
        ['id' => 1, 'curso_id' => 13, 'estudiante_id' => 46, 'asignado_por' => 44, 'estado' => 'activo', 'fecha_asignacion' => '2026-01-06 18:52:10', 'fecha_expiracion' => NULL, 'observaciones' => NULL, 'created_at' => '2026-01-06 18:52:10', 'updated_at' => '2026-01-06 18:52:10'],
        ['id' => 2, 'curso_id' => 14, 'estudiante_id' => 46, 'asignado_por' => 44, 'estado' => 'activo', 'fecha_asignacion' => '2026-01-06 18:52:10', 'fecha_expiracion' => NULL, 'observaciones' => NULL, 'created_at' => '2026-01-06 18:52:10', 'updated_at' => '2026-01-06 18:52:10'],
        ['id' => 3, 'curso_id' => 14, 'estudiante_id' => 36, 'asignado_por' => 44, 'estado' => 'activo', 'fecha_asignacion' => '2026-01-06 18:53:16', 'fecha_expiracion' => NULL, 'observaciones' => NULL, 'created_at' => '2026-01-06 18:53:16', 'updated_at' => '2026-01-06 18:53:16'],
        ['id' => 4, 'curso_id' => 13, 'estudiante_id' => 36, 'asignado_por' => 44, 'estado' => 'activo', 'fecha_asignacion' => '2026-01-06 18:56:55', 'fecha_expiracion' => NULL, 'observaciones' => NULL, 'created_at' => '2026-01-06 18:56:55', 'updated_at' => '2026-01-06 18:56:55'],
        ['id' => 5, 'curso_id' => 15, 'estudiante_id' => 36, 'asignado_por' => 44, 'estado' => 'activo', 'fecha_asignacion' => '2026-01-06 21:22:31', 'fecha_expiracion' => NULL, 'observaciones' => NULL, 'created_at' => '2026-01-06 21:22:31', 'updated_at' => '2026-01-06 21:22:31'],
    ]);
    echo "  ✓ curso_asignaciones: 5 registros\n";

    // 6. CURSO_ESTUDIANTES
    echo "Insertando datos en 'curso_estudiantes'...\n";
    DB::table('curso_estudiantes')->truncate();
    DB::table('curso_estudiantes')->insert([
        ['id' => 10, 'curso_id' => 14, 'estudiante_id' => 36, 'fecha_inscripcion' => '2026-01-06 18:58:16', 'estado' => 'activo', 'progreso' => 0, 'ultima_actividad' => '2026-01-06 18:58:16', 'created_at' => '2026-01-06 18:58:16', 'updated_at' => '2026-01-06 18:58:16'],
        ['id' => 11, 'curso_id' => 15, 'estudiante_id' => 36, 'fecha_inscripcion' => '2026-01-06 21:23:18', 'estado' => 'activo', 'progreso' => 0, 'ultima_actividad' => '2026-01-06 21:23:18', 'created_at' => '2026-01-06 21:23:18', 'updated_at' => '2026-01-06 21:23:18'],
    ]);
    echo "  ✓ curso_estudiantes: 2 registros\n";

    // 7. CURSO_FOROS
    echo "Insertando datos en 'curso_foros'...\n";
    DB::table('curso_foros')->truncate();
    DB::table('curso_foros')->insert([
        ['id' => 12, 'curso_id' => 15, 'usuario_id' => 45, 'titulo' => '¡Bienvenidos al hospital!', 'contenido' => 'Nos complace iniciar con ustedes este proceso de inducción, en el cual conocerán los lineamientos, normas y procedimientos que orientan nuestra labor asistencial y académica. Este espacio busca facilitar su integración, fortalecer su desempeño y promover una atención en salud responsable, ética y de calidad. Les deseamos una experiencia formativa exitosa.', 'parent_id' => NULL, 'es_anuncio' => 1, 'es_fijado' => 1, 'likes' => 0, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
    ]);
    echo "  ✓ curso_foros: 1 registro\n";

    // 8. CURSO_MATERIAL_VISTO
    echo "Insertando datos en 'curso_material_visto'...\n";
    DB::table('curso_material_visto')->truncate();
    DB::table('curso_material_visto')->insert([
        ['id' => 23, 'curso_id' => 14, 'material_id' => 44, 'user_id' => 36, 'visto_at' => '2026-01-06 19:27:06', 'created_at' => NULL, 'updated_at' => '2026-01-06 19:27:06'],
    ]);
    echo "  ✓ curso_material_visto: 1 registro\n";

    // Reactivar verificación de claves foráneas
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    DB::commit();
    
    echo "\n=== INSERCIÓN COMPLETADA EXITOSAMENTE ===\n";

} catch (Exception $e) {
    DB::rollBack();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
