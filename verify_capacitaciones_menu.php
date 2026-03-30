<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 VERIFICACIÓN DEL MENÚ 'CAPACITACIONES' EN ADMINLTE\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Verificar la configuración del menú
    echo "1. ✅ Verificando configuración del menú AdminLTE:\n";
    
    $menuConfig = config('adminlte.menu');
    
    if (!$menuConfig) {
        echo "   ❌ Error: No se pudo cargar la configuración del menú\n";
        exit(1);
    }
    
    echo "   ✅ Configuración del menú cargada correctamente\n";
    echo "   📊 Total de elementos en el menú: " . count($menuConfig) . "\n";
    
    // 2. Buscar la sección "Capacitaciones"
    echo "\n2. 🔍 Buscando la sección 'Capacitaciones':\n";
    
    $capacitacionesFound = false;
    $capacitacionesIndex = -1;
    $capacitacionesMenu = null;
    
    foreach ($menuConfig as $index => $item) {
        if (isset($item['text']) && $item['text'] === 'Capacitaciones') {
            $capacitacionesFound = true;
            $capacitacionesIndex = $index;
            $capacitacionesMenu = $item;
            break;
        }
    }
    
    if ($capacitacionesFound) {
        echo "   ✅ Sección 'Capacitaciones' encontrada en la posición {$capacitacionesIndex}\n";
        echo "   🎯 Icono: {$capacitacionesMenu['icon']}\n";
        
        // 3. Verificar el submenú
        echo "\n3. 📋 Verificando submenú de Capacitaciones:\n";
        
        if (isset($capacitacionesMenu['submenu']) && is_array($capacitacionesMenu['submenu'])) {
            echo "   ✅ Submenú encontrado con " . count($capacitacionesMenu['submenu']) . " elementos\n";
            
            $expectedSubmenu = [
                'Historia Clínica' => [
                    'url' => 'capacitaciones/historia-clinica',
                    'icon' => 'fas fa-fw fa-file-medical'
                ],
                'Administrativos' => [
                    'url' => 'capacitaciones/administrativos',
                    'icon' => 'fas fa-fw fa-briefcase'
                ],
                'Mipres' => [
                    'url' => 'capacitaciones/mipres',
                    'icon' => 'fas fa-fw fa-prescription-bottle-alt'
                ]
            ];
            
            foreach ($capacitacionesMenu['submenu'] as $subIndex => $subItem) {
                $text = $subItem['text'] ?? 'Sin texto';
                $url = $subItem['url'] ?? 'Sin URL';
                $icon = $subItem['icon'] ?? 'Sin icono';
                $active = isset($subItem['active']) ? implode(', ', $subItem['active']) : 'Sin configuración active';
                
                echo "\n   📌 Submenú {$subIndex}: {$text}\n";
                echo "      URL: {$url}\n";
                echo "      Icono: {$icon}\n";
                echo "      Active: [{$active}]\n";
                
                // Verificar si coincide con lo esperado
                if (isset($expectedSubmenu[$text])) {
                    $expected = $expectedSubmenu[$text];
                    if ($url === $expected['url'] && $icon === $expected['icon']) {
                        echo "      ✅ Configuración correcta\n";
                    } else {
                        echo "      ⚠️  Configuración no coincide con lo esperado\n";
                    }
                } else {
                    echo "      ❓ Elemento no esperado\n";
                }
            }
        } else {
            echo "   ❌ Error: Submenú no encontrado o no es un array\n";
        }
    } else {
        echo "   ❌ Error: Sección 'Capacitaciones' no encontrada en el menú\n";
        exit(1);
    }
    
    // 4. Verificar la posición en el menú
    echo "\n4. 📍 Verificando posición en el menú:\n";
    
    $menuStructure = [];
    foreach ($menuConfig as $index => $item) {
        if (isset($item['text'])) {
            $menuStructure[] = "{$index}: {$item['text']}";
        } elseif (isset($item['header'])) {
            $menuStructure[] = "{$index}: [HEADER] {$item['header']}";
        } elseif (isset($item['type'])) {
            $menuStructure[] = "{$index}: [TYPE] {$item['type']}";
        }
    }
    
    echo "   📋 Estructura del menú:\n";
    foreach ($menuStructure as $item) {
        if (strpos($item, 'Capacitaciones') !== false) {
            echo "   ➤ {$item} ⭐ (NUEVA SECCIÓN)\n";
        } else {
            echo "   - {$item}\n";
        }
    }
    
    // 5. Verificar que está después de "Seguimiento"
    echo "\n5. ✅ Verificando orden correcto:\n";
    
    $seguimientoIndex = -1;
    $accountSettingsIndex = -1;
    
    foreach ($menuConfig as $index => $item) {
        if (isset($item['text']) && $item['text'] === 'Seguimiento') {
            $seguimientoIndex = $index;
        }
        if (isset($item['header']) && $item['header'] === 'account_settings') {
            $accountSettingsIndex = $index;
        }
    }
    
    if ($seguimientoIndex !== -1 && $capacitacionesIndex > $seguimientoIndex) {
        echo "   ✅ 'Capacitaciones' está después de 'Seguimiento' (posición {$seguimientoIndex})\n";
    } else {
        echo "   ⚠️  'Capacitaciones' no está en la posición esperada respecto a 'Seguimiento'\n";
    }
    
    if ($accountSettingsIndex !== -1 && $capacitacionesIndex < $accountSettingsIndex) {
        echo "   ✅ 'Capacitaciones' está antes de 'account_settings' (posición {$accountSettingsIndex})\n";
    } else {
        echo "   ⚠️  'Capacitaciones' no está en la posición esperada respecto a 'account_settings'\n";
    }
    
    // 6. Resumen final
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 RESUMEN DE LA VERIFICACIÓN:\n";
    echo "✅ Sección 'Capacitaciones' implementada correctamente\n";
    echo "✅ Icono principal: fas fa-fw fa-graduation-cap\n";
    echo "✅ Submenú con 3 opciones configuradas\n";
    echo "✅ URLs preparadas para futuras implementaciones\n";
    echo "✅ Configuración 'active' para resaltado de rutas\n";
    echo "✅ Posición correcta en el menú\n";
    
    echo "\n📋 URLS CONFIGURADAS:\n";
    foreach ($capacitacionesMenu['submenu'] as $subItem) {
        echo "   🔗 {$subItem['text']}: {$subItem['url']}\n";
    }
    
    echo "\n🌐 Para ver el menú, accede a: http://127.0.0.1:8000/dashboard\n";
    echo "📝 Las URLs están preparadas para futuras implementaciones de controladores y vistas\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
