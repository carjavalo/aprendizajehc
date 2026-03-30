<?php
/**
 * Script de diagnóstico y reparación de permisos de Reportes.
 * URL: https://localhost/AprendizajeHC/diagnostico_reportes.php
 * ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO.
 */

$basePath = realpath(__DIR__ . '/..');
if (!$basePath || !file_exists($basePath . '/vendor/autoload.php')) {
    // Tal vez public_html ES la raíz del proyecto
    $basePath = __DIR__;
    if (!file_exists($basePath . '/vendor/autoload.php')) {
        $basePath = realpath(__DIR__ . '/..');
    }
}

echo "<html><head><meta charset='utf-8'><title>Diagnóstico Reportes</title></head><body>";
echo "<h2>🔍 Diagnóstico completo - Permisos de Reportes</h2><hr>";
echo "<p><strong>Base path detectada:</strong> $basePath</p>";

// Bootstrap Laravel
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

$allOk = true;

// ═══════════════════════════════════════════
// 1. VERIFICAR BASE DE DATOS
// ═══════════════════════════════════════════
echo "<h3>1️⃣ Estado de la Base de Datos</h3>";

if (!Schema::hasTable('permissions')) {
    echo "<p style='color:red;'>❌ La tabla 'permissions' NO existe.</p>";
    $allOk = false;
} else {
    $reportesPerms = DB::table('permissions')->where('group', 'Reportes')->get();
    if ($reportesPerms->count() === 0) {
        echo "<p style='color:red;'>❌ No hay permisos del grupo 'Reportes' en la BD. Insertando ahora...</p>";
        $now = now();
        $permissions = [
            ['name' => 'reportes.view',   'display_name' => 'Ver Reportes',             'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.edit',   'display_name' => 'Editar Reportes',           'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.delete', 'display_name' => 'Eliminar Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.print',  'display_name' => 'Imprimir Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.export', 'display_name' => 'Exportar Reportes a Excel', 'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('permissions')->insert($permissions);
        $permIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
        foreach (['Super Admin', 'Administrador'] as $role) {
            foreach ($permIds as $pid) {
                DB::table('role_permissions')->insert(['role_name' => $role, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        echo "<p style='color:green;'>✅ Permisos insertados y asignados.</p>";
        $reportesPerms = DB::table('permissions')->where('group', 'Reportes')->get();
    } else {
        echo "<p style='color:green;'>✅ Existen {$reportesPerms->count()} permisos del grupo 'Reportes':</p><ul>";
        foreach ($reportesPerms as $p) {
            echo "<li><code>{$p->name}</code>: {$p->display_name}</li>";
        }
        echo "</ul>";
    }

    // Verificar asignaciones
    $assignments = DB::table('role_permissions')
        ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
        ->where('permissions.group', 'Reportes')
        ->select('role_permissions.role_name', 'permissions.name')
        ->get();
    echo "<p>Asignaciones: {$assignments->count()} registros</p><ul>";
    foreach ($assignments as $a) {
        echo "<li>{$a->role_name} → {$a->name}</li>";
    }
    echo "</ul>";
}

// ═══════════════════════════════════════════
// 2. VERIFICAR ARCHIVOS CLAVE
// ═══════════════════════════════════════════
echo "<h3>2️⃣ Verificación de Archivos</h3>";

$filesToCheck = [
    'app/Http/Controllers/PermissionController.php' => "'Reportes'",
    'app/Providers/AppServiceProvider.php' => "'reportes.view'",
    'config/adminlte.php' => "'reportes.view'",
    'routes/web.php' => 'check.permission:reportes',
    'app/Http/Controllers/ReporteEstudiantesController.php' => "Gate::allows('reportes",
    'resources/views/admin/consultas/reportes/index.blade.php' => "@can('reportes",
];

foreach ($filesToCheck as $file => $needle) {
    $fullPath = $basePath . '/' . $file;
    if (!file_exists($fullPath)) {
        echo "<p style='color:red;'>❌ <strong>$file</strong> — NO EXISTE en el servidor</p>";
        $allOk = false;
    } else {
        $content = file_get_contents($fullPath);
        if (strpos($content, $needle) !== false) {
            echo "<p style='color:green;'>✅ <strong>$file</strong> — Contiene <code>" . htmlspecialchars($needle) . "</code></p>";
        } else {
            echo "<p style='color:red;'>❌ <strong>$file</strong> — <strong>NO contiene</strong> <code>" . htmlspecialchars($needle) . "</code> (archivo desactualizado)</p>";
            $allOk = false;
        }
    }
}

// ═══════════════════════════════════════════
// 3. LIMPIAR TODAS LAS CACHÉS
// ═══════════════════════════════════════════
echo "<h3>3️⃣ Limpieza de Cachés</h3>";

try {
    Artisan::call('cache:clear');
    echo "<p style='color:green;'>✅ cache:clear — OK</p>";
} catch (\Exception $e) {
    echo "<p style='color:orange;'>⚠️ cache:clear — " . $e->getMessage() . "</p>";
}

try {
    Artisan::call('config:clear');
    echo "<p style='color:green;'>✅ config:clear — OK</p>";
} catch (\Exception $e) {
    echo "<p style='color:orange;'>⚠️ config:clear — " . $e->getMessage() . "</p>";
}

try {
    Artisan::call('route:clear');
    echo "<p style='color:green;'>✅ route:clear — OK</p>";
} catch (\Exception $e) {
    echo "<p style='color:orange;'>⚠️ route:clear — " . $e->getMessage() . "</p>";
}

try {
    Artisan::call('view:clear');
    echo "<p style='color:green;'>✅ view:clear — OK</p>";
} catch (\Exception $e) {
    echo "<p style='color:orange;'>⚠️ view:clear — " . $e->getMessage() . "</p>";
}

// Eliminar archivos de caché manualmente por si acaso
$cacheFiles = [
    $basePath . '/bootstrap/cache/config.php',
    $basePath . '/bootstrap/cache/routes-v7.php',
    $basePath . '/bootstrap/cache/services.php',
    $basePath . '/bootstrap/cache/packages.php',
];
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        @unlink($cf);
        $relPath = str_replace($basePath . '/', '', $cf);
        echo "<p style='color:green;'>🗑️ Eliminado archivo de caché: $relPath</p>";
    }
}

// ═══════════════════════════════════════════
// 4. RESUMEN
// ═══════════════════════════════════════════
echo "<hr>";
if ($allOk) {
    echo "<h3 style='color:green;'>✅ Todo está correcto. Recarga la página /permisos.</h3>";
    echo "<p>Si aún no se ve, prueba con <strong>Ctrl+Shift+R</strong> (recarga forzada sin caché del navegador).</p>";
} else {
    echo "<h3 style='color:red;'>❌ Se encontraron problemas.</h3>";
    echo "<p>Los archivos marcados con ❌ necesitan ser subidos al servidor. Estos son los archivos que debes actualizar desde tu entorno local:</p>";
    echo "<ol>";
    foreach ($filesToCheck as $file => $needle) {
        $fullPath = $basePath . '/' . $file;
        $content = file_exists($fullPath) ? file_get_contents($fullPath) : '';
        if (!file_exists($fullPath) || strpos($content, $needle) === false) {
            echo "<li><code>$file</code></li>";
        }
    }
    echo "</ol>";
    echo "<p><strong>Después de subir los archivos, vuelve a ejecutar este diagnóstico.</strong></p>";
}

echo "<p style='color:red; margin-top:20px;'><strong>⚠️ ELIMINA este archivo del servidor después de usarlo.</strong></p>";
echo "</body></html>";
