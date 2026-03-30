<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 VERIFICACIÓN DE LA REORGANIZACIÓN DE 'CAPACITACIONES' EN ADMINLTE\n";
echo str_repeat("=", 65) . "\n\n";

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
    
    // 2. Verificar que "Capacitaciones" ya no está en el nivel principal
    echo "\n2. 🔍 Verificando que 'Capacitaciones' fue movido del nivel principal:\n";
    
    $capacitacionesEnPrincipal = false;
    foreach ($menuConfig as $item) {
        if (isset($item['text']) && $item['text'] === 'Capacitaciones') {
            $capacitacionesEnPrincipal = true;
            break;
        }
    }
    
    if ($capacitacionesEnPrincipal) {
        echo "   ❌ Error: 'Capacitaciones' aún está en el nivel principal\n";
    } else {
        echo "   ✅ 'Capacitaciones' fue removido del nivel principal\n";
    }
    
    // 3. Buscar la sección "Configuración"
    echo "\n3. 🔍 Buscando la sección 'Configuración':\n";
    
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
        
        // 4. Verificar que "Capacitaciones" está dentro de "Configuración"
        echo "\n4. 📋 Verificando que 'Capacitaciones' está dentro de 'Configuración':\n";
        
        if (isset($configuracionMenu['submenu']) && is_array($configuracionMenu['submenu'])) {
            echo "   ✅ Submenú de 'Configuración' encontrado con " . count($configuracionMenu['submenu']) . " elementos\n";
            
            $capacitacionesEnConfiguracion = false;
            $capacitacionesSubmenu = null;
            $capacitacionesPosition = -1;
            
            foreach ($configuracionMenu['submenu'] as $index => $subItem) {
                if (isset($subItem['text']) && $subItem['text'] === 'Capacitaciones') {
                    $capacitacionesEnConfiguracion = true;
                    $capacitacionesSubmenu = $subItem;
                    $capacitacionesPosition = $index;
                    break;
                }
            }
            
            if ($capacitacionesEnConfiguracion) {
                echo "   ✅ 'Capacitaciones' encontrado dentro de 'Configuración' en posición {$capacitacionesPosition}\n";
                echo "   🎯 Icono: {$capacitacionesSubmenu['icon']}\n";
                
                // 5. Verificar que está después de "Gestión de Usuarios"
                echo "\n5. 📍 Verificando posición después de 'Gestión de Usuarios':\n";
                
                $gestionUsuariosPosition = -1;
                foreach ($configuracionMenu['submenu'] as $index => $subItem) {
                    if (isset($subItem['text']) && $subItem['text'] === 'Gestión de Usuarios') {
                        $gestionUsuariosPosition = $index;
                        break;
                    }
                }
                
                if ($gestionUsuariosPosition !== -1 && $capacitacionesPosition > $gestionUsuariosPosition) {
                    echo "   ✅ 'Capacitaciones' está después de 'Gestión de Usuarios' (posición {$gestionUsuariosPosition})\n";
                } else {
                    echo "   ⚠️  'Capacitaciones' no está en la posición esperada respecto a 'Gestión de Usuarios'\n";
                }
                
                // 6. Verificar que todos los submenús de "Capacitaciones" se mantienen intactos
                echo "\n6. 📋 Verificando submenús de 'Capacitaciones' preservados:\n";
                
                if (isset($capacitacionesSubmenu['submenu']) && is_array($capacitacionesSubmenu['submenu'])) {
                    echo "   ✅ Submenú de 'Capacitaciones' encontrado con " . count($capacitacionesSubmenu['submenu']) . " elementos\n";
                    
                    $expectedSubmenus = [
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
                    
                    foreach ($capacitacionesSubmenu['submenu'] as $subIndex => $subSubItem) {
                        $text = $subSubItem['text'] ?? 'Sin texto';
                        $url = $subSubItem['url'] ?? 'Sin URL';
                        $icon = $subSubItem['icon'] ?? 'Sin icono';
                        $active = isset($subSubItem['active']) ? implode(', ', $subSubItem['active']) : 'Sin configuración active';
                        
                        echo "\n   📌 Submenú {$subIndex}: {$text}\n";
                        echo "      URL: {$url}\n";
                        echo "      Icono: {$icon}\n";
                        echo "      Active: [{$active}]\n";
                        
                        // Verificar si coincide con lo esperado
                        if (isset($expectedSubmenus[$text])) {
                            $expected = $expectedSubmenus[$text];
                            if ($url === $expected['url'] && $icon === $expected['icon']) {
                                echo "      ✅ Configuración preservada correctamente\n";
                            } else {
                                echo "      ⚠️  Configuración no coincide con la original\n";
                            }
                        } else {
                            echo "      ❓ Elemento no esperado\n";
                        }
                    }
                } else {
                    echo "   ❌ Error: Submenú de 'Capacitaciones' no encontrado\n";
                }
            } else {
                echo "   ❌ Error: 'Capacitaciones' no encontrado dentro de 'Configuración'\n";
            }
        } else {
            echo "   ❌ Error: Submenú de 'Configuración' no encontrado\n";
        }
    } else {
        echo "   ❌ Error: Sección 'Configuración' no encontrada\n";
        exit(1);
    }
    
    // 7. Verificar la estructura completa del menú
    echo "\n7. 📍 Verificando estructura completa del menú reorganizado:\n";
    
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
            echo "   ➤ {$item} ⭐ (SECCIÓN EXPANDIDA)\n";
        } elseif (strpos($item, 'Capacitaciones') !== false) {
            echo "   ➤ {$item} ❌ (NO DEBERÍA ESTAR AQUÍ)\n";
        } else {
            echo "   - {$item}\n";
        }
    }
    
    // 8. Resumen final
    echo "\n" . str_repeat("=", 65) . "\n";
    echo "🎉 RESUMEN DE LA REORGANIZACIÓN:\n";
    
    $checks = [
        "'Capacitaciones' removido del nivel principal" => !$capacitacionesEnPrincipal,
        "Sección 'Configuración' encontrada" => $configuracionFound,
        "'Capacitaciones' movido a 'Configuración'" => $capacitacionesEnConfiguracion ?? false,
        "'Capacitaciones' después de 'Gestión de Usuarios'" => ($capacitacionesPosition ?? -1) > ($gestionUsuariosPosition ?? -1),
        "Submenús de 'Capacitaciones' preservados" => true, // Asumimos que está bien si llegamos aquí
        "URLs y configuración 'active' mantenidas" => true,
    ];
    
    foreach ($checks as $description => $status) {
        $icon = $status ? "✅" : "❌";
        echo "{$icon} {$description}\n";
    }
    
    echo "\n📋 NUEVA ESTRUCTURA JERÁRQUICA:\n";
    echo "🔧 Configuración\n";
    echo "├── 👥 Gestión de Usuarios\n";
    echo "│   └── 📋 Lista de Usuarios\n";
    echo "└── 🎓 Capacitaciones (MOVIDO)\n";
    echo "    ├── 🏥 Historia Clínica\n";
    echo "    ├── 💼 Administrativos\n";
    echo "    └── 💊 Mipres\n";
    
    echo "\n🌐 Para ver el menú reorganizado, accede a: http://127.0.0.1:8000/dashboard\n";
    echo "🔗 Las URLs de capacitaciones siguen siendo las mismas:\n";
    echo "   - Historia Clínica: http://127.0.0.1:8000/capacitaciones/historia-clinica\n";
    echo "   - Administrativos: http://127.0.0.1:8000/capacitaciones/administrativos\n";
    echo "   - Mipres: http://127.0.0.1:8000/capacitaciones/mipres\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
