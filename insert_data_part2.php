<?php
/**
 * Script para insertar datos - Parte 2 (Materiales y Actividades)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INSERCIÓN DE DATOS - PARTE 2 ===\n\n";

try {
    DB::beginTransaction();
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    // CURSO_MATERIALES
    echo "Insertando datos en 'curso_materiales'...\n";
    DB::table('curso_materiales')->truncate();
    
    $materiales = [
        ['id' => 39, 'curso_id' => 13, 'titulo' => 'introducción curso de soporte vital básico HUV', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://drive.google.com/file/d/1tzum92RP6Na5h6j8_p3CZzyZUki1xhWw/view?usp=sharing', 'orden' => 1, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 03:18:42'],
        ['id' => 40, 'curso_id' => 13, 'titulo' => 'Generalidades de la rcp y soporte vital básico adulto', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://drive.google.com/file/d/1u-zHrzfLMZL5s7i0TXJRinSENVDl1MNv/view', 'orden' => 2, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 03:18:42'],
        ['id' => 41, 'curso_id' => 13, 'titulo' => 'Ovace', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://drive.google.com/file/d/1U4yZoT0GhJyKVe93NuqIHiLAEG3MEYs1/view', 'orden' => 3, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 03:18:42'],
        ['id' => 42, 'curso_id' => 13, 'titulo' => 'Video Dea', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://drive.google.com/file/d/18q365bkXNDzhUfZ14ECqkdGcT1XMb5Sc/view', 'orden' => 4, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 03:18:42'],
        ['id' => 43, 'curso_id' => 13, 'titulo' => 'Soporte vital básico pediátrico', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://drive.google.com/file/d/1yT2LA4yEK42z5HU3uhYjkr1DP4N7Qrhw/view', 'orden' => 5, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 03:18:42', 'updated_at' => '2026-01-06 03:18:42'],
        ['id' => 44, 'curso_id' => 14, 'titulo' => 'MODULO 1', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/67f834bd1a857fdd10bbbe14/interactive-content-modulo-1-hemato', 'orden' => 1, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 18:51:49', 'updated_at' => '2026-01-06 18:51:49'],
        ['id' => 45, 'curso_id' => 14, 'titulo' => 'MODULO 2', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/67f8373820540ffde531a7f7/interactive-content-modulo-2-hemato', 'orden' => 2, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 18:51:49', 'updated_at' => '2026-01-06 18:51:49'],
        ['id' => 46, 'curso_id' => 14, 'titulo' => 'MODULO 3', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/67f83819285f3787bbefc6b3/interactive-content-modulo-3-hemato', 'orden' => 3, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 18:51:49', 'updated_at' => '2026-01-06 18:51:49'],
        ['id' => 47, 'curso_id' => 14, 'titulo' => 'MODULO 4', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/67f838b7f15f85cbc97cb840/interactive-content-modulo-4-hemato', 'orden' => 4, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 18:51:49', 'updated_at' => '2026-01-06 18:51:49'],
        ['id' => 48, 'curso_id' => 15, 'titulo' => '1) Direccionamiento Estratégico', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65847b54c60dcb00144860aa/interactive-content-direccionamiento-estrategico', 'orden' => 1, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 49, 'curso_id' => 15, 'titulo' => '2) Gestión Calidad', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65848554c60dcb00144d93e6/interactive-content-gestion-calidad', 'orden' => 2, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 50, 'curso_id' => 15, 'titulo' => '3) Coordinación Académica', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65848393cbb59a001482f35f/interactive-content-coordinacion-academica', 'orden' => 3, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 51, 'curso_id' => 15, 'titulo' => '5) Política de Gestión del Conocimiento y la Innovación', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65b7c10320d46c0014fc41f2/interactive-content-politica-de-gestion-del-conocimiento-y-la-innovacion', 'orden' => 4, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 52, 'curso_id' => 15, 'titulo' => '6) Política de Humanización', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/66d5c3491f0c039ac5a77f86/interactive-content-politica-de-humanizacion', 'orden' => 5, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 53, 'curso_id' => 15, 'titulo' => '7) Daruma', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65858260899e670015866159/interactive-content-daruma', 'orden' => 6, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 54, 'curso_id' => 15, 'titulo' => '8) Derechos Y Deberes Del Paciente', 'descripcion' => '', 'tipo' => 'documento', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://www.calameo.com/read/006141493fcc60112b715', 'orden' => 7, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 55, 'curso_id' => 15, 'titulo' => '9) Seguridad Del paciente', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65f85fc0bb328200149b9d62/interactive-content-seguridad-del-paciente', 'orden' => 8, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 56, 'curso_id' => 15, 'titulo' => '10) Control de Infecciones', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/6691937cbf1e2c0d70d02a64/interactive-content-control-de-infecciones', 'orden' => 9, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 57, 'curso_id' => 15, 'titulo' => '11) Seguridad y Salud en el Trabajo', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/658487bfc5af6d0013a17017/interactive-content-salud-ocupacional', 'orden' => 10, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 58, 'curso_id' => 15, 'titulo' => '12) Gestión Ambiental', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/65848901c5af6d0013a224a9/interactive-content-gestion-ambiental', 'orden' => 11, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 59, 'curso_id' => 15, 'titulo' => '13) Atención Quirúrgica', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/66df6e2bc9379e1c924c706d/interactive-content-atencion-quirurgica', 'orden' => 12, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 60, 'curso_id' => 15, 'titulo' => '14) Programas Sociales', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/658580a378f80a0015b854ab/interactive-content-programas-sociales', 'orden' => 13, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 61, 'curso_id' => 15, 'titulo' => '15) Trabajo Mimhos', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/6585bd42899e6700159fe0be/interactive-content-trabajo-mimhos', 'orden' => 14, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
        ['id' => 62, 'curso_id' => 15, 'titulo' => '16) Lactancia Materna', 'descripcion' => '', 'tipo' => 'video', 'archivo_path' => NULL, 'archivo_nombre' => NULL, 'archivo_extension' => NULL, 'archivo_size' => NULL, 'url_externa' => 'https://view.genially.com/6585bc35fa7c870015622fd4/interactive-content-lactancia-materna', 'orden' => 15, 'fecha_inicio' => NULL, 'fecha_fin' => NULL, 'es_publico' => 1, 'created_at' => '2026-01-06 21:13:51', 'updated_at' => '2026-01-06 21:13:51'],
    ];
    
    foreach ($materiales as $m) {
        DB::table('curso_materiales')->insert($m);
    }
    echo "  ✓ curso_materiales: " . count($materiales) . " registros\n";

    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    DB::commit();
    
    echo "\n=== PARTE 2 COMPLETADA ===\n";

} catch (Exception $e) {
    DB::rollBack();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
