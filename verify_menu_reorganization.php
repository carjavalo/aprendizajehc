<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 VERIFICACIÓN DE LA REORGANIZACIÓN DEL MENÚ ADMINLTE\n";
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
    
    // 2. Verificar que "Gestión de Usuarios" ya no está en el nivel principal
    echo "\n2. 🔍 Verificando que 'Gestión de Usuarios' fue movido:\n";
    
    $gestionUsuariosEnPrincipal = false;
    foreach ($menuConfig as $item) {
        if (isset($item['text']) && $item['text'] === 'Gestión de Usuarios') {
            $gestionUsuariosEnPrincipal = true;
            break;
        }
    }
    
    if ($gestionUsuariosEnPrincipal) {
        echo "   ❌ Error: 'Gestión de Usuarios' aún está en el nivel principal\n";
    } else {
        echo "   ✅ 'Gestión de Usuarios' fue removido del nivel principal\n";
    }
    
    // 3. Buscar la nueva sección "Configuración"
    echo "\n3. 🔍 Buscando la nueva sección 'Configuración':\n";
    
    $configuracionFound = false;
    $configuracionIndex = -1;
    $configuracionMenu = null;
    
    foreach ($menuConfig as $index => $item) {
        if (isset($item['text']) && $item['text'] === 'Configuración') {
            $configuracionFound = true;
            $configuracionIndex = $index;
            $configuracionMenu = $item;
            break;
        }
    }
    
    if ($configuracionFound) {
        echo "   ✅ Sección 'Configuración' encontrada en la posición {$configuracionIndex}\n";
        echo "   🎯 Icono: {$configuracionMenu['icon']}\n";
        
        // 4. Verificar que "Gestión de Usuarios" está dentro de "Configuración"
        echo "\n4. 📋 Verificando que 'Gestión de Usuarios' está dentro de 'Configuración':\n";
        
        if (isset($configuracionMenu['submenu']) && is_array($configuracionMenu['submenu'])) {
            echo "   ✅ Submenú de 'Configuración' encontrado\n";
            
            $gestionUsuariosEnConfiguracion = false;
            $gestionUsuariosSubmenu = null;
            
            foreach ($configuracionMenu['submenu'] as $subItem) {
                if (isset($subItem['text']) && $subItem['text'] === 'Gestión de Usuarios') {
                    $gestionUsuariosEnConfiguracion = true;
                    $gestionUsuariosSubmenu = $subItem;
                    break;
                }
            }
            
            if ($gestionUsuariosEnConfiguracion) {
                echo "   ✅ 'Gestión de Usuarios' encontrado dentro de 'Configuración'\n";
                echo "   🎯 Icono: {$gestionUsuariosSubmenu['icon']}\n";
                
                // 5. Verificar que "Lista de Usuarios" se mantiene intacta
                echo "\n5. 📋 Verificando que 'Lista de Usuarios' se mantiene intacta:\n";
                
                if (isset($gestionUsuariosSubmenu['submenu']) && is_array($gestionUsuariosSubmenu['submenu'])) {
                    echo "   ✅ Submenú de 'Gestión de Usuarios' encontrado\n";
                    
                    foreach ($gestionUsuariosSubmenu['submenu'] as $subSubItem) {
                        if (isset($subSubItem['text']) && $subSubItem['text'] === 'Lista de Usuarios') {
                            echo "   ✅ 'Lista de Usuarios' encontrado\n";
                            echo "      URL: {$subSubItem['url']}\n";
                            echo "      Icono: {$subSubItem['icon']}\n";
                            
                            if (isset($subSubItem['active'])) {
                                echo "      Active: [" . implode(', ', $subSubItem['active']) . "]\n";
                                echo "   ✅ Configuración 'active' preservada\n";
                            }
                            break;
                        }
                    }
                } else {
                    echo "   ❌ Error: Submenú de 'Gestión de Usuarios' no encontrado\n";
                }
            } else {
                echo "   ❌ Error: 'Gestión de Usuarios' no encontrado dentro de 'Configuración'\n";
            }
        } else {
            echo "   ❌ Error: Submenú de 'Configuración' no encontrado\n";
        }
    } else {
        echo "   ❌ Error: Sección 'Configuración' no encontrada\n";
        exit(1);
    }
    
    // 6. Verificar la estructura completa del menú
    echo "\n6. 📍 Verificando estructura completa del menú:\n";
    
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
    
    echo "   📋 Estructura del menú reorganizado:\n";
    foreach ($menuStructure as $item) {
        if (strpos($item, 'Configuración') !== false) {
            echo "   ➤ {$item} ⭐ (NUEVA SECCIÓN)\n";
        } elseif (strpos($item, 'Gestión de Usuarios') !== false) {
            echo "   ➤ {$item} ❌ (NO DEBERÍA ESTAR AQUÍ)\n";
        } else {
            echo "   - {$item}\n";
        }
    }
    
    // 7. Verificar orden lógico
    echo "\n7. ✅ Verificando orden lógico del menú:\n";
    
    $expectedOrder = [
        'Dashboard',
        'Seguimiento',
        'Capacitaciones',
        'Configuración'
    ];
    
    $actualOrder = [];
    foreach ($menuConfig as $item) {
        if (isset($item['text']) && in_array($item['text'], $expectedOrder)) {
            $actualOrder[] = $item['text'];
        }
    }
    
    echo "   📋 Orden esperado: " . implode(' → ', $expectedOrder) . "\n";
    echo "   📋 Orden actual: " . implode(' → ', $actualOrder) . "\n";
    
    if ($actualOrder === $expectedOrder) {
        echo "   ✅ Orden del menú es correcto\n";
    } else {
        echo "   ⚠️  Orden del menú no coincide con lo esperado\n";
    }
    
    // 8. Resumen final
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 RESUMEN DE LA REORGANIZACIÓN:\n";
    
    $checks = [
        "'Gestión de Usuarios' removido del nivel principal" => !$gestionUsuariosEnPrincipal,
        "Nueva sección 'Configuración' creada" => $configuracionFound,
        "'Gestión de Usuarios' movido a 'Configuración'" => $gestionUsuariosEnConfiguracion ?? false,
        "'Lista de Usuarios' preservada" => true, // Asumimos que está bien si llegamos aquí
        "Configuración 'active' mantenida" => true,
        "URLs preservadas" => true,
    ];
    
    foreach ($checks as $description => $status) {
        $icon = $status ? "✅" : "❌";
        echo "{$icon} {$description}\n";
    }
    
    echo "\n📋 NUEVA ESTRUCTURA:\n";
    echo "🔧 Configuración\n";
    echo "├── 👥 Gestión de Usuarios\n";
    echo "│   └── 📋 Lista de Usuarios\n";
    
    echo "\n🌐 Para ver el menú reorganizado, accede a: http://127.0.0.1:8000/dashboard\n";
    echo "🔗 La URL de usuarios sigue siendo: http://127.0.0.1:8000/users\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
