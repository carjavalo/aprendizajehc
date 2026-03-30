<?php
/**
 * Script para agregar permisos de Reportes en producción (cPanel).
 * URL: https://aprendizajehc.huv.gov.co/public/add_reportes_permissions.php
 * ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO.
 */

echo "<h2>🔧 Agregar Permisos de Reportes</h2>";
echo "<hr>";

$basePath = realpath(__DIR__ . '/..');

// Bootstrap Laravel
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Verificar que la tabla permissions existe
    if (!Schema::hasTable('permissions')) {
        echo "<p style='color:red;'>❌ La tabla 'permissions' no existe. Ejecuta primero la migración principal.</p>";
        exit;
    }

    // Verificar si ya existen los permisos de Reportes
    $existing = DB::table('permissions')->where('group', 'Reportes')->count();
    if ($existing > 0) {
        echo "<p style='color:orange;'>⚠️ Los permisos de Reportes ya existen en la base de datos ($existing permisos).</p>";
        echo "<h3>Permisos existentes:</h3>";
        $perms = DB::table('permissions')->where('group', 'Reportes')->get();
        echo "<ul>";
        foreach ($perms as $p) {
            echo "<li><strong>{$p->name}</strong>: {$p->display_name}</li>";
        }
        echo "</ul>";

        // Verificar asignaciones
        $assignments = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.group', 'Reportes')
            ->get(['role_permissions.role_name', 'permissions.name']);
        
        echo "<h3>Asignaciones actuales:</h3>";
        echo "<ul>";
        foreach ($assignments as $a) {
            echo "<li>{$a->role_name} → {$a->name}</li>";
        }
        echo "</ul>";
        echo "<p style='color:green;'>✅ No se requiere acción adicional. Los permisos ya están configurados.</p>";
        echo "<p><strong>⚠️ ELIMINA este archivo del servidor después de verificar.</strong></p>";
        exit;
    }

    // Insertar los permisos de Reportes
    echo "<h3>1. Insertando permisos de Reportes...</h3>";
    $now = now();
    $permissions = [
        ['name' => 'reportes.view',   'display_name' => 'Ver Reportes',             'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'reportes.edit',   'display_name' => 'Editar Reportes',           'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'reportes.delete', 'display_name' => 'Eliminar Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'reportes.print',  'display_name' => 'Imprimir Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ['name' => 'reportes.export', 'display_name' => 'Exportar Reportes a Excel', 'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
    ];

    DB::table('permissions')->insert($permissions);
    echo "<p style='color:green;'>✅ 5 permisos insertados correctamente.</p>";

    // Asignar a Super Admin y Administrador
    echo "<h3>2. Asignando permisos a Super Admin y Administrador...</h3>";
    $permIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
    $inserts = [];
    foreach (['Super Admin', 'Administrador'] as $roleName) {
        foreach ($permIds as $permId) {
            $inserts[] = [
                'role_name'     => $roleName,
                'permission_id' => $permId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }
    }
    DB::table('role_permissions')->insert($inserts);
    echo "<p style='color:green;'>✅ Permisos asignados a Super Admin y Administrador (" . count($inserts) . " asignaciones).</p>";

    // Marcar la migración como ejecutada (para que artisan migrate no la vuelva a correr)
    echo "<h3>3. Registrando migración en la tabla migrations...</h3>";
    $migrationName = '2026_03_19_163847_add_reportes_permissions';
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
    if (!$exists) {
        $batch = DB::table('migrations')->max('batch') ?? 0;
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch'     => $batch + 1,
        ]);
        echo "<p style='color:green;'>✅ Migración registrada (batch " . ($batch + 1) . ").</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ La migración ya estaba registrada.</p>";
    }

    // Resumen final
    echo "<hr>";
    echo "<h3>✅ ¡Proceso completado exitosamente!</h3>";
    echo "<p>Los permisos de <strong>Reportes</strong> ahora aparecerán en la vista <a href='/permisos'>/permisos</a>.</p>";
    echo "<p>Permisos creados:</p>";
    echo "<ul>";
    echo "<li><strong>reportes.view</strong> — Ver Reportes</li>";
    echo "<li><strong>reportes.edit</strong> — Editar Reportes</li>";
    echo "<li><strong>reportes.delete</strong> — Eliminar Reportes</li>";
    echo "<li><strong>reportes.print</strong> — Imprimir Reportes</li>";
    echo "<li><strong>reportes.export</strong> — Exportar Reportes a Excel</li>";
    echo "</ul>";
    echo "<p style='color:red; font-weight:bold;'>⚠️ IMPORTANTE: ELIMINA este archivo (add_reportes_permissions.php) del servidor después de verificar.</p>";

} catch (\Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
