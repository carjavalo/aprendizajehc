<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 AUDITORÍA COMPLETA DE CONSISTENCIA DE NAVBAR EN EL SISTEMA SHC\n";
echo str_repeat("=", 70) . "\n\n";

try {
    // 1. Identificar todas las vistas del sistema
    echo "1. 📄 IDENTIFICANDO TODAS LAS VISTAS DEL SISTEMA:\n";
    echo str_repeat("-", 50) . "\n";
    
    $viewsDirectory = 'resources/views';
    $allViews = [];
    
    // Función recursiva para encontrar archivos .blade.php
    function findBladeFiles($directory, &$files, $basePath = '') {
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
            $relativePath = $basePath ? $basePath . '/' . $item : $item;
            
            if (is_dir($fullPath)) {
                findBladeFiles($fullPath, $files, $relativePath);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php' && 
                      strpos($item, '.blade.') !== false) {
                $files[] = $relativePath;
            }
        }
    }
    
    findBladeFiles($viewsDirectory, $allViews);
    
    echo "   📊 Total de vistas encontradas: " . count($allViews) . "\n\n";
    
    // 2. Analizar cada vista para identificar el layout usado
    echo "2. 🔍 ANALIZANDO LAYOUTS UTILIZADOS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $layoutAnalysis = [
        'admin.layouts.master' => [],
        'adminlte::page' => [],
        'layouts.app' => [],
        'layouts.guest' => [],
        'x-guest-layout' => [],
        'adminlte::master' => [],
        'adminlte::auth-page' => [],
        'other' => [],
        'no_extends' => []
    ];
    
    foreach ($allViews as $view) {
        $filePath = $viewsDirectory . '/' . $view;
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $firstLine = strtok($content, "\n");
            
            // Buscar @extends
            if (preg_match('/@extends\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches)) {
                $layout = $matches[1];
                if (isset($layoutAnalysis[$layout])) {
                    $layoutAnalysis[$layout][] = $view;
                } else {
                    $layoutAnalysis['other'][] = $view . ' (Layout: ' . $layout . ')';
                }
            }
            // Buscar <x-layout>
            elseif (preg_match('/<x-([^>]+)>/', $firstLine, $matches)) {
                $layout = 'x-' . $matches[1];
                if (isset($layoutAnalysis[$layout])) {
                    $layoutAnalysis[$layout][] = $view;
                } else {
                    $layoutAnalysis['other'][] = $view . ' (Component: ' . $layout . ')';
                }
            } else {
                $layoutAnalysis['no_extends'][] = $view;
            }
        }
    }
    
    // Mostrar análisis de layouts
    foreach ($layoutAnalysis as $layout => $views) {
        if (!empty($views)) {
            echo "   📋 {$layout}: " . count($views) . " vistas\n";
            foreach ($views as $view) {
                echo "      - {$view}\n";
            }
            echo "\n";
        }
    }
    
    // 3. Verificar consistencia de navbar
    echo "3. 🔧 VERIFICANDO CONSISTENCIA DE NAVBAR:\n";
    echo str_repeat("-", 50) . "\n";
    
    // Vista de referencia (dashboard)
    $referenceView = 'dashboard.blade.php';
    $referenceLayout = 'admin.layouts.master';
    
    echo "   📌 Vista de referencia: {$referenceView}\n";
    echo "   📌 Layout de referencia: {$referenceLayout}\n\n";
    
    // Verificar vistas que usan admin.layouts.master
    $adminViews = $layoutAnalysis['admin.layouts.master'];
    echo "   ✅ Vistas que usan el layout correcto ({$referenceLayout}):\n";
    foreach ($adminViews as $view) {
        echo "      ✅ {$view}\n";
    }
    echo "\n";
    
    // Identificar vistas inconsistentes
    $inconsistentViews = [];
    foreach ($layoutAnalysis as $layout => $views) {
        if ($layout !== 'admin.layouts.master' && $layout !== 'no_extends' && !empty($views)) {
            foreach ($views as $view) {
                // Excluir vistas de autenticación que deben usar otros layouts
                if (!preg_match('/auth\/|vendor\/adminlte\/auth\//', $view)) {
                    $inconsistentViews[] = ['view' => $view, 'layout' => $layout];
                }
            }
        }
    }
    
    if (!empty($inconsistentViews)) {
        echo "   ⚠️  Vistas con layouts inconsistentes:\n";
        foreach ($inconsistentViews as $item) {
            echo "      ❌ {$item['view']} (usa: {$item['layout']})\n";
        }
    } else {
        echo "   ✅ Todas las vistas administrativas usan el layout correcto\n";
    }
    echo "\n";
    
    // 4. Verificar archivos de navbar
    echo "4. 📁 VERIFICANDO ARCHIVOS DE NAVBAR:\n";
    echo str_repeat("-", 50) . "\n";
    
    $navbarFiles = [
        'Layout principal' => 'resources/views/admin/layouts/master.blade.php',
        'Navbar personalizado' => 'resources/views/admin/partials/navbar-user-menu.blade.php',
        'CSS personalizado' => 'public/css/custom.css',
        'Navbar AdminLTE' => 'resources/views/vendor/adminlte/partials/navbar/navbar.blade.php',
        'User menu AdminLTE' => 'resources/views/vendor/adminlte/partials/navbar/menu-item-dropdown-user-menu.blade.php'
    ];
    
    foreach ($navbarFiles as $description => $file) {
        if (file_exists($file)) {
            echo "   ✅ {$description}: {$file}\n";
        } else {
            echo "   ❌ {$description}: {$file} - NO ENCONTRADO\n";
        }
    }
    echo "\n";
    
    // 5. Verificar contenido del layout principal
    echo "5. 🔍 VERIFICANDO CONTENIDO DEL LAYOUT PRINCIPAL:\n";
    echo str_repeat("-", 50) . "\n";
    
    $masterLayoutPath = 'resources/views/admin/layouts/master.blade.php';
    if (file_exists($masterLayoutPath)) {
        $masterContent = file_get_contents($masterLayoutPath);
        
        // Verificar elementos clave
        $keyElements = [
            "@extends('adminlte::page')" => 'Extiende AdminLTE',
            "@include('admin.partials.navbar-user-menu')" => 'Incluye navbar personalizado',
            'custom.css' => 'Incluye CSS personalizado',
            'content_top_nav_right' => 'Sección de navbar derecha'
        ];
        
        foreach ($keyElements as $element => $description) {
            if (strpos($masterContent, $element) !== false) {
                echo "   ✅ {$description}\n";
            } else {
                echo "   ❌ {$description} - NO ENCONTRADO\n";
            }
        }
    } else {
        echo "   ❌ Layout principal no encontrado\n";
    }
    echo "\n";
    
    // 6. Verificar navbar personalizado
    echo "6. 👤 VERIFICANDO NAVBAR PERSONALIZADO:\n";
    echo str_repeat("-", 50) . "\n";
    
    $navbarPath = 'resources/views/admin/partials/navbar-user-menu.blade.php';
    if (file_exists($navbarPath)) {
        $navbarContent = file_get_contents($navbarPath);
        
        // Verificar implementación actual
        $navbarElements = [
            'fas fa-user' => 'Icono de usuario',
            'class="d-none"' => 'Texto oculto',
            'logout-form' => 'Funcionalidad de logout',
            'fa-power-off' => 'Icono de salir'
        ];
        
        foreach ($navbarElements as $element => $description) {
            if (strpos($navbarContent, $element) !== false) {
                echo "   ✅ {$description}\n";
            } else {
                echo "   ❌ {$description} - NO ENCONTRADO\n";
            }
        }
    } else {
        echo "   ❌ Navbar personalizado no encontrado\n";
    }
    echo "\n";
    
    // 7. Resumen y recomendaciones
    echo "7. 📋 RESUMEN Y RECOMENDACIONES:\n";
    echo str_repeat("-", 50) . "\n";
    
    $totalAdminViews = count($adminViews);
    $totalInconsistent = count($inconsistentViews);
    
    echo "   📊 ESTADÍSTICAS:\n";
    echo "      - Total de vistas: " . count($allViews) . "\n";
    echo "      - Vistas administrativas consistentes: {$totalAdminViews}\n";
    echo "      - Vistas inconsistentes: {$totalInconsistent}\n";
    echo "      - Porcentaje de consistencia: " . 
         round(($totalAdminViews / ($totalAdminViews + $totalInconsistent)) * 100, 2) . "%\n\n";
    
    if ($totalInconsistent > 0) {
        echo "   🔧 ACCIONES REQUERIDAS:\n";
        foreach ($inconsistentViews as $item) {
            echo "      1. Cambiar {$item['view']} de '{$item['layout']}' a 'admin.layouts.master'\n";
        }
        echo "\n";
    } else {
        echo "   ✅ SISTEMA CONSISTENTE: Todas las vistas administrativas usan el layout correcto\n\n";
    }
    
    echo "   🌐 URLS PARA VERIFICAR:\n";
    echo "      - Dashboard: http://127.0.0.1:8000/dashboard\n";
    echo "      - Usuarios: http://127.0.0.1:8000/users\n";
    echo "      - Categorías: http://127.0.0.1:8000/capacitaciones/categorias\n";
    echo "      - Tracking: http://127.0.0.1:8000/tracking/logins\n";
    
    echo "\n🎉 ¡Auditoría de navbar completada!\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la auditoría: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
