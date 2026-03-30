<?php
/**
 * Script de reparación automática para permisos de Reportes en cPanel.
 * Parchea los archivos del servidor y la BD automáticamente.
 * URL: https://localhost/AprendizajeHC/fix_reportes.php
 * ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO.
 */

$basePath = realpath(__DIR__ . '/..');
if (!$basePath || !file_exists($basePath . '/vendor/autoload.php')) {
    $basePath = __DIR__;
}

echo "<html><head><meta charset='utf-8'><title>Fix Reportes</title>
<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:0 20px;}
.ok{color:green;}.err{color:red;}.warn{color:orange;}.patch{color:blue;}
code{background:#f0f0f0;padding:2px 6px;border-radius:3px;font-size:0.9em;}
pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;overflow-x:auto;}</style>
</head><body>";
echo "<h2>🔧 Reparación Automática - Permisos de Reportes</h2><hr>";
echo "<p>Base path: <code>$basePath</code></p>";

// Bootstrap Laravel
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

$fixes = 0;
$errors = 0;

// ═══════════════════════════════════════════════
// 1. BASE DE DATOS
// ═══════════════════════════════════════════════
echo "<h3>1️⃣ Base de Datos</h3>";

if (Schema::hasTable('permissions')) {
    $count = DB::table('permissions')->where('group', 'Reportes')->count();
    if ($count === 0) {
        $now = now();
        DB::table('permissions')->insert([
            ['name' => 'reportes.view',   'display_name' => 'Ver Reportes',             'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.edit',   'display_name' => 'Editar Reportes',           'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.delete', 'display_name' => 'Eliminar Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.print',  'display_name' => 'Imprimir Reportes',         'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.export', 'display_name' => 'Exportar Reportes a Excel', 'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $permIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
        foreach (['Super Admin', 'Administrador'] as $role) {
            foreach ($permIds as $pid) {
                DB::table('role_permissions')->insertOrIgnore(['role_name' => $role, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        echo "<p class='ok'>✅ Permisos de Reportes insertados y asignados.</p>";
        $fixes++;
    } else {
        echo "<p class='ok'>✅ BD OK — $count permisos del grupo Reportes ya existen.</p>";
    }
} else {
    echo "<p class='err'>❌ La tabla permissions no existe.</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 2. PARCHEAR PermissionController.php
// ═══════════════════════════════════════════════
echo "<h3>2️⃣ PermissionController.php</h3>";

$file = $basePath . '/app/Http/Controllers/PermissionController.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, "'Reportes'") !== false) {
        echo "<p class='ok'>✅ Ya contiene 'Reportes' en el grupo de orden.</p>";
    } else {
        // Buscar la línea 'Publicidad y Productos' y agregar 'Reportes' después
        $search = "'Publicidad y Productos',\n            'Componentes',";
        $replace = "'Publicidad y Productos',\n            'Reportes',\n            'Componentes',";
        
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregó 'Reportes' al \$groupOrder.</p>";
            $fixes++;
        } else {
            // Intentar variante sin tilde
            $search2 = "'Publicidad y Productos',";
            $pos = strpos($content, $search2);
            if ($pos !== false) {
                // Encontrar la siguiente línea con 'Componentes' o cualquier grupo
                $content = str_replace($search2, $search2 . "\n            'Reportes',", $content);
                file_put_contents($file, $content);
                echo "<p class='patch'>🔧 Parcheado (variante): Se agregó 'Reportes' después de 'Publicidad y Productos'.</p>";
                $fixes++;
            } else {
                echo "<p class='err'>❌ No se pudo encontrar el punto de inserción en PermissionController.</p>";
                $errors++;
            }
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 3. PARCHEAR AppServiceProvider.php
// ═══════════════════════════════════════════════
echo "<h3>3️⃣ AppServiceProvider.php</h3>";

$file = $basePath . '/app/Providers/AppServiceProvider.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, "'reportes.view'") !== false) {
        echo "<p class='ok'>✅ Ya contiene los gates de reportes.</p>";
    } else {
        // Buscar la última línea de gates conocidos y agregar los de reportes
        $search = "'publicidad.view', 'publicidad.create', 'publicidad.edit', 'publicidad.delete', 'publicidad.banner',\n        ];";
        $replace = "'publicidad.view', 'publicidad.create', 'publicidad.edit', 'publicidad.delete', 'publicidad.banner',\n            'reportes.view', 'reportes.edit', 'reportes.delete', 'reportes.print', 'reportes.export',\n        ];";
        
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregaron los gates de reportes.</p>";
            $fixes++;
        } else {
            // Variante con espacios diferentes
            if (preg_match("/'publicidad\.banner',\s*\];/", $content)) {
                $content = preg_replace(
                    "/'publicidad\.banner',(\s*)\];/",
                    "'publicidad.banner',\n            'reportes.view', 'reportes.edit', 'reportes.delete', 'reportes.print', 'reportes.export',\$1];",
                    $content
                );
                file_put_contents($file, $content);
                echo "<p class='patch'>🔧 Parcheado (regex): Se agregaron los gates de reportes.</p>";
                $fixes++;
            } else {
                echo "<p class='err'>❌ No se pudo parchear AppServiceProvider. Punto de inserción no encontrado.</p>";
                echo "<p>Buscar manualmente la línea con 'publicidad.banner' y agregar después:<br><code>'reportes.view', 'reportes.edit', 'reportes.delete', 'reportes.print', 'reportes.export',</code></p>";
                $errors++;
            }
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 4. PARCHEAR config/adminlte.php
// ═══════════════════════════════════════════════
echo "<h3>4️⃣ config/adminlte.php (menú lateral)</h3>";

$file = $basePath . '/config/adminlte.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    
    // Verificar si ya tiene el 'can' => 'reportes.view' en Consultas
    if (strpos($content, "'reportes.view'") !== false) {
        echo "<p class='ok'>✅ Ya contiene el gate 'reportes.view' en el menú.</p>";
    } else {
        // Buscar el bloque de Consultas sin 'can'
        $search = "'text'    => 'Consultas',\n            'icon'    => 'fas fa-fw fa-search',\n            'submenu' => [";
        $replace = "'text'    => 'Consultas',\n            'icon'    => 'fas fa-fw fa-search',\n            'can'     => 'reportes.view',\n            'submenu' => [";
        
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            
            // También agregar 'can' al submenú Reportes
            $searchSub = "'text' => 'Reportes',\n                    'url'  => 'consultas/reportes',\n                    'icon' => 'fas fa-fw fa-file-alt',\n                    'active' => ['consultas/reportes', 'consultas/reportes/*'],\n                ],";
            $replaceSub = "'text' => 'Reportes',\n                    'url'  => 'consultas/reportes',\n                    'icon' => 'fas fa-fw fa-file-alt',\n                    'active' => ['consultas/reportes', 'consultas/reportes/*'],\n                    'can' => 'reportes.view',\n                ],";
            
            if (strpos($content, $searchSub) !== false) {
                $content = str_replace($searchSub, $replaceSub, $content);
            }
            
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregó 'can' => 'reportes.view' al menú Consultas.</p>";
            $fixes++;
        } else {
            echo "<p class='warn'>⚠️ Patrón de menú Consultas no encontrado. Verificar manualmente.</p>";
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 5. PARCHEAR routes/web.php
// ═══════════════════════════════════════════════
echo "<h3>5️⃣ routes/web.php (middleware de permisos)</h3>";

$file = $basePath . '/routes/web.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, "check.permission:reportes") !== false) {
        echo "<p class='ok'>✅ Ya contiene middleware de permisos en rutas de reportes.</p>";
    } else {
        // Buscar las rutas sin middleware
        $oldRoutes = "Route::get('/', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'index'])->name('index');";
        $newRoutes = "Route::get('/', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'index'])->middleware('check.permission:reportes.view')->name('index');";
        
        if (strpos($content, $oldRoutes) !== false) {
            // Reemplazar todas las rutas de reportes
            $replacements = [
                ["Route::get('/', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'index'])->name('index');",
                 "Route::get('/', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'index'])->middleware('check.permission:reportes.view')->name('index');"],
                ["Route::get('/data', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'getData'])->name('data');",
                 "Route::get('/data', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'getData'])->middleware('check.permission:reportes.view')->name('data');"],
                ["Route::get('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'show'])->name('show');",
                 "Route::get('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'show'])->middleware('check.permission:reportes.view')->name('show');"],
                ["Route::get('/{id}/edit', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'edit'])->name('edit');",
                 "Route::get('/{id}/edit', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'edit'])->middleware('check.permission:reportes.edit')->name('edit');"],
                ["Route::put('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'update'])->name('update');",
                 "Route::put('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'update'])->middleware('check.permission:reportes.edit')->name('update');"],
                ["Route::delete('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'destroy'])->name('destroy');",
                 "Route::delete('/{id}', [\\App\\Http\\Controllers\\ReporteEstudiantesController::class, 'destroy'])->middleware('check.permission:reportes.delete')->name('destroy');"],
            ];
            
            foreach ($replacements as $r) {
                $content = str_replace($r[0], $r[1], $content);
            }
            
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregó middleware de permisos a las rutas de reportes.</p>";
            $fixes++;
        } else {
            echo "<p class='warn'>⚠️ Las rutas ya tienen middleware o el patrón no coincide.</p>";
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 6. PARCHEAR ReporteEstudiantesController.php
// ═══════════════════════════════════════════════
echo "<h3>6️⃣ ReporteEstudiantesController.php (botones de acción)</h3>";

$file = $basePath . '/app/Http/Controllers/ReporteEstudiantesController.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, "Gate::allows('reportes") !== false) {
        echo "<p class='ok'>✅ Ya contiene verificación de permisos en botones de acción.</p>";
    } else {
        // Agregar use Gate si no existe
        if (strpos($content, 'use Illuminate\\Support\\Facades\\Gate;') === false) {
            $content = str_replace(
                'use Illuminate\\Support\\Facades\\DB;',
                "use Illuminate\\Support\\Facades\\DB;\nuse Illuminate\\Support\\Facades\\Gate;",
                $content
            );
        }
        
        // Reemplazar la columna de action
        $oldAction = "\$btn = '<div class=\"btn-group\">';\n                \$btn .= '<button type=\"button\" class=\"btn btn-sm btn-info viewRecord\"";
        
        if (strpos($content, $oldAction) !== false) {
            $oldBlock = <<<'PHP'
$btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-sm btn-info viewRecord" data-id="'.$row->id.'" title="Ver"><i class="fas fa-eye text-white"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-primary editRecord" data-id="'.$row->id.'" title="Editar"><i class="fas fa-edit"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-danger deleteRecord" data-id="'.$row->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
PHP;

            $newBlock = <<<'PHP'
$btn = '<div class="btn-group">';
                if (Gate::allows('reportes.view')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-info viewRecord" data-id="'.$row->id.'" title="Ver"><i class="fas fa-eye text-white"></i></button>';
                }
                if (Gate::allows('reportes.edit')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary editRecord" data-id="'.$row->id.'" title="Editar"><i class="fas fa-edit"></i></button>';
                }
                if (Gate::allows('reportes.delete')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger deleteRecord" data-id="'.$row->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                }
                $btn .= '</div>';
PHP;
            $content = str_replace($oldBlock, $newBlock, $content);
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregaron verificaciones Gate a los botones de acción.</p>";
            $fixes++;
        } else {
            echo "<p class='warn'>⚠️ Patrón de botones no encontrado. Puede tener formato diferente.</p>";
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 7. PARCHEAR vista reportes/index.blade.php
// ═══════════════════════════════════════════════
echo "<h3>7️⃣ Vista reportes/index.blade.php (botones export/print)</h3>";

$file = $basePath . '/resources/views/admin/consultas/reportes/index.blade.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    if (strpos($content, "@can('reportes") !== false) {
        echo "<p class='ok'>✅ Ya contiene directivas @can para reportes.</p>";
    } else {
        // Reemplazar botones de DataTable
        $oldButtons = "{ extend: 'excel', text: '<i class=\"fas fa-file-excel\"></i> Excel', className: 'btn btn-success btn-sm' },\n                { extend: 'pdf', text: '<i class=\"fas fa-file-pdf\"></i> PDF', className: 'btn btn-danger btn-sm' },\n                { extend: 'print', text: '<i class=\"fas fa-print\"></i> Imprimir', className: 'btn btn-info btn-sm' }";
        
        $newButtons = "@can('reportes.export')\n                { extend: 'excel', text: '<i class=\"fas fa-file-excel\"></i> Excel', className: 'btn btn-success btn-sm' },\n                { extend: 'pdf', text: '<i class=\"fas fa-file-pdf\"></i> PDF', className: 'btn btn-danger btn-sm' },\n                @endcan\n                @can('reportes.print')\n                { extend: 'print', text: '<i class=\"fas fa-print\"></i> Imprimir', className: 'btn btn-info btn-sm' }\n                @endcan";
        
        if (strpos($content, $oldButtons) !== false) {
            $content = str_replace($oldButtons, $newButtons, $content);
            file_put_contents($file, $content);
            echo "<p class='patch'>🔧 Parcheado: Se agregaron directivas @can a botones de export/print.</p>";
            $fixes++;
        } else {
            echo "<p class='warn'>⚠️ Patrón de botones DataTable no encontrado. Puede tener formato diferente.</p>";
        }
    }
} else {
    echo "<p class='err'>❌ Archivo no encontrado: $file</p>";
    $errors++;
}

// ═══════════════════════════════════════════════
// 8. LIMPIAR CACHÉS
// ═══════════════════════════════════════════════
echo "<h3>8️⃣ Limpieza de Cachés</h3>";

$commands = ['cache:clear', 'config:clear', 'route:clear', 'view:clear'];
foreach ($commands as $cmd) {
    try {
        Artisan::call($cmd);
        echo "<p class='ok'>✅ $cmd — OK</p>";
    } catch (\Exception $e) {
        echo "<p class='warn'>⚠️ $cmd — " . $e->getMessage() . "</p>";
    }
}

// Eliminar cachés manuales
foreach (['bootstrap/cache/config.php', 'bootstrap/cache/routes-v7.php', 'bootstrap/cache/services.php', 'bootstrap/cache/packages.php'] as $cf) {
    $fullCf = $basePath . '/' . $cf;
    if (file_exists($fullCf)) {
        @unlink($fullCf);
        echo "<p class='ok'>🗑️ Eliminado: $cf</p>";
    }
}

// ═══════════════════════════════════════════════
// 9. VERIFICACIÓN FINAL
// ═══════════════════════════════════════════════
echo "<hr><h3>📊 Verificación Final</h3>";

$allGood = true;
$checks = [
    'app/Http/Controllers/PermissionController.php' => "'Reportes'",
    'app/Providers/AppServiceProvider.php' => "'reportes.view'",
    'config/adminlte.php' => "'reportes.view'",
    'routes/web.php' => 'check.permission:reportes',
    'app/Http/Controllers/ReporteEstudiantesController.php' => "Gate::allows('reportes",
    'resources/views/admin/consultas/reportes/index.blade.php' => "@can('reportes",
];

foreach ($checks as $f => $needle) {
    $fp = $basePath . '/' . $f;
    if (file_exists($fp) && strpos(file_get_contents($fp), $needle) !== false) {
        echo "<p class='ok'>✅ $f</p>";
    } else {
        echo "<p class='err'>❌ $f — Aún falta la corrección</p>";
        $allGood = false;
    }
}

$dbCount = DB::table('permissions')->where('group', 'Reportes')->count();
echo "<p class='" . ($dbCount == 5 ? 'ok' : 'err') . "'>" . ($dbCount == 5 ? '✅' : '❌') . " BD: $dbCount permisos de Reportes</p>";

echo "<hr>";
if ($allGood && $dbCount == 5) {
    echo "<h3 class='ok'>✅ ¡Todo reparado! ($fixes correcciones aplicadas)</h3>";
    echo "<p>Recarga <a href='/permisos'>/permisos</a> con <strong>Ctrl+Shift+R</strong>. Deberías ver la sección <strong>Reportes</strong>.</p>";
} else {
    echo "<h3 class='err'>❌ Aún hay problemas ($errors errores). Contacta soporte.</h3>";
}

echo "<p class='err' style='margin-top:20px;'><strong>⚠️ ELIMINA este archivo del servidor después de usarlo.</strong></p>";
echo "</body></html>";
