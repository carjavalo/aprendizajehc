<?php
/**
 * Diagnóstico específico: ¿Por qué el Operador no ve Reportes?
 * URL: https://localhost/AprendizajeHC/debug_operador_reportes.php
 * ELIMINAR DESPUÉS DE USAR.
 */

$basePath = realpath(__DIR__ . '/..');
if (!$basePath || !file_exists($basePath . '/vendor/autoload.php')) {
    $basePath = __DIR__;
}

require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;

echo "<html><head><meta charset='utf-8'><title>Debug Operador</title>
<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:0 20px;}
.ok{color:green;}.err{color:red;}.warn{color:orange;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#2c4370;color:#fff;}</style></head><body>";

echo "<h2>🔍 Diagnóstico: Operador + Reportes</h2><hr>";

// ═══════════ 1. Verificar permisos en BD ═══════════
echo "<h3>1. Permisos de Reportes en la BD</h3>";
$reportesPerms = DB::table('permissions')->where('group', 'Reportes')->get();
echo "<table><tr><th>ID</th><th>name</th><th>display_name</th></tr>";
foreach ($reportesPerms as $p) {
    echo "<tr><td>{$p->id}</td><td>{$p->name}</td><td>{$p->display_name}</td></tr>";
}
echo "</table>";

// ═══════════ 2. Verificar asignaciones del Operador ═══════════
echo "<h3>2. Permisos asignados al rol 'Operador' (todos)</h3>";
$operadorPerms = DB::table('role_permissions')
    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
    ->where('role_permissions.role_name', 'Operador')
    ->get(['permissions.id', 'permissions.name', 'permissions.display_name', 'permissions.group']);

echo "<table><tr><th>ID</th><th>name</th><th>display_name</th><th>group</th></tr>";
$operadorHasReportes = false;
foreach ($operadorPerms as $p) {
    $isReporte = $p->group === 'Reportes';
    $style = $isReporte ? "style='background:#d4edda;font-weight:bold;'" : "";
    echo "<tr $style><td>{$p->id}</td><td>{$p->name}</td><td>{$p->display_name}</td><td>{$p->group}</td></tr>";
    if ($isReporte) $operadorHasReportes = true;
}
echo "</table>";

if (!$operadorHasReportes) {
    echo "<p class='err'><strong>❌ EL OPERADOR NO TIENE NINGÚN PERMISO DE REPORTES EN LA BD.</strong></p>";
    echo "<p>Esto significa que al hacer clic en 'Guardar Permisos' en /permisos, los permisos de Reportes del Operador no se guardaron correctamente.</p>";
    
    echo "<h3>🔧 Reparación automática: Insertando permisos de Reportes para Operador...</h3>";
    $now = now();
    $reportesIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
    $inserted = 0;
    foreach ($reportesIds as $pid) {
        $exists = DB::table('role_permissions')
            ->where('role_name', 'Operador')
            ->where('permission_id', $pid)
            ->exists();
        if (!$exists) {
            DB::table('role_permissions')->insert([
                'role_name' => 'Operador',
                'permission_id' => $pid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }
    }
    echo "<p class='ok'>✅ Se insertaron $inserted permisos de Reportes para Operador.</p>";
} else {
    echo "<p class='ok'>✅ El Operador SÍ tiene permisos de Reportes en la BD.</p>";
}

// ═══════════ 3. Verificar asignaciones específicas de Reportes por ROL ═══════════
echo "<h3>3. Asignaciones de Reportes por Rol</h3>";
$reportesAssignments = DB::table('role_permissions')
    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
    ->where('permissions.group', 'Reportes')
    ->orderBy('role_permissions.role_name')
    ->get(['role_permissions.role_name', 'permissions.name']);

echo "<table><tr><th>Rol</th><th>Permiso</th></tr>";
foreach ($reportesAssignments as $a) {
    $style = $a->role_name === 'Operador' ? "style='background:#d4edda;font-weight:bold;'" : "";
    echo "<tr $style><td>{$a->role_name}</td><td>{$a->name}</td></tr>";
}
echo "</table>";

// ═══════════ 4. Test Gate para un usuario Operador ═══════════
echo "<h3>4. Test de Gate para usuario con rol 'Operador'</h3>";
$operadorUser = DB::table('users')->where('role', 'Operador')->first();
if ($operadorUser) {
    echo "<p>Usuario encontrado: <strong>{$operadorUser->name}</strong> (ID: {$operadorUser->id}, role: {$operadorUser->role})</p>";
    
    $userModel = \App\Models\User::find($operadorUser->id);
    $gates = ['reportes.view', 'reportes.edit', 'reportes.delete', 'reportes.print', 'reportes.export'];
    echo "<table><tr><th>Gate</th><th>Resultado</th></tr>";
    foreach ($gates as $gate) {
        $allowed = Gate::forUser($userModel)->allows($gate);
        $icon = $allowed ? '✅ Permitido' : '❌ Denegado';
        $class = $allowed ? 'ok' : 'err';
        echo "<tr><td>{$gate}</td><td class='{$class}'>{$icon}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warn'>⚠️ No se encontró ningún usuario con role='Operador'.</p>";
}

// ═══════════ 5. Limpiar cachés ═══════════
echo "<h3>5. Limpieza de Cachés</h3>";
foreach (['cache:clear', 'config:clear', 'route:clear', 'view:clear'] as $cmd) {
    try { Artisan::call($cmd); echo "<p class='ok'>✅ $cmd</p>"; } 
    catch (\Exception $e) { echo "<p class='warn'>⚠️ $cmd: {$e->getMessage()}</p>"; }
}

// Eliminar archivos de caché manualmente
foreach (['bootstrap/cache/config.php', 'bootstrap/cache/routes-v7.php'] as $cf) {
    if (file_exists($basePath . '/' . $cf)) {
        @unlink($basePath . '/' . $cf);
        echo "<p class='ok'>🗑️ Eliminado: $cf</p>";
    }
}

// ═══════════ 6. Verificación final ═══════════
echo "<hr><h3>📊 Resumen</h3>";
$finalCount = DB::table('role_permissions')
    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
    ->where('role_permissions.role_name', 'Operador')
    ->where('permissions.group', 'Reportes')
    ->count();

if ($finalCount > 0) {
    echo "<p class='ok' style='font-size:1.2em;'>✅ El Operador tiene $finalCount permisos de Reportes. Debe poder ver el menú Consultas > Reportes.</p>";
    echo "<p>Pide al usuario Operador que recargue la página con <strong>Ctrl+Shift+R</strong> o que cierre sesión y vuelva a entrar.</p>";
} else {
    echo "<p class='err' style='font-size:1.2em;'>❌ El Operador aún no tiene permisos de Reportes.</p>";
}

echo "<p class='err' style='margin-top:20px;'><strong>⚠️ ELIMINA este archivo del servidor.</strong></p>";
echo "</body></html>";
