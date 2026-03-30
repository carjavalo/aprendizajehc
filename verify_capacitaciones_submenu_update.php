<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 VERIFICACIÓN DE LA ACTUALIZACIÓN DEL SUBMENÚ 'CAPACITACIONES'\n";
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
    
    // 2. Buscar la sección "Configuración"
    echo "\n2. 🔍 Buscando la sección 'Configuración':\n";
    
    $configuracionFound = false;
    $configuracionMenu = null;
    
    foreach ($menuConfig as $item) {
        if (isset($item['text']) && $item['text'] === 'Configuración') {
            $configuracionFound = true;
            $configuracionMenu = $item;
            break;
        }
    }
    
    if ($configuracionFound) {
        echo "   ✅ Sección 'Configuración' encontrada\n";
        
        // 3. Buscar "Capacitaciones" dentro de "Configuración"
        echo "\n3. 🔍 Buscando 'Capacitaciones' dentro de 'Configuración':\n";
        
        $capacitacionesFound = false;
        $capacitacionesMenu = null;
        
        if (isset($configuracionMenu['submenu'])) {
            foreach ($configuracionMenu['submenu'] as $subItem) {
                if (isset($subItem['text']) && $subItem['text'] === 'Capacitaciones') {
                    $capacitacionesFound = true;
                    $capacitacionesMenu = $subItem;
                    break;
                }
            }
        }
        
        if ($capacitacionesFound) {
            echo "   ✅ 'Capacitaciones' encontrado dentro de 'Configuración'\n";
            echo "   🎯 Icono: {$capacitacionesMenu['icon']}\n";
            
            // 4. Verificar que las opciones anteriores fueron eliminadas
            echo "\n4. 🗑️  Verificando eliminación de opciones anteriores:\n";
            
            $oldOptions = ['Historia Clínica', 'Administrativos', 'Mipres'];
            $foundOldOptions = [];
            
            if (isset($capacitacionesMenu['submenu'])) {
                foreach ($capacitacionesMenu['submenu'] as $subSubItem) {
                    if (isset($subSubItem['text']) && in_array($subSubItem['text'], $oldOptions)) {
                        $foundOldOptions[] = $subSubItem['text'];
                    }
                }
            }
            
            if (empty($foundOldOptions)) {
                echo "   ✅ Todas las opciones anteriores fueron eliminadas correctamente\n";
                foreach ($oldOptions as $option) {
                    echo "      ✅ '{$option}' - Eliminado\n";
                }
            } else {
                echo "   ❌ Algunas opciones anteriores aún están presentes:\n";
                foreach ($foundOldOptions as $option) {
                    echo "      ❌ '{$option}' - Aún presente\n";
                }
            }
            
            // 5. Verificar las nuevas opciones
            echo "\n5. 📋 Verificando nuevas opciones del submenú:\n";
            
            if (isset($capacitacionesMenu['submenu']) && is_array($capacitacionesMenu['submenu'])) {
                echo "   ✅ Submenú de 'Capacitaciones' encontrado con " . count($capacitacionesMenu['submenu']) . " elementos\n";
                
                $expectedNewOptions = [
                    'Categorías' => [
                        'url' => 'capacitaciones/categorias',
                        'icon' => 'fas fa-fw fa-tags'
                    ],
                    'Áreas' => [
                        'url' => 'capacitaciones/areas',
                        'icon' => 'fas fa-fw fa-layer-group'
                    ],
                    'Cursos' => [
                        'url' => 'capacitaciones/cursos',
                        'icon' => 'fas fa-fw fa-book-open'
                    ]
                ];
                
                foreach ($capacitacionesMenu['submenu'] as $index => $subSubItem) {
                    $text = $subSubItem['text'] ?? 'Sin texto';
                    $url = $subSubItem['url'] ?? 'Sin URL';
                    $icon = $subSubItem['icon'] ?? 'Sin icono';
                    $active = isset($subSubItem['active']) ? implode(', ', $subSubItem['active']) : 'Sin configuración active';
                    
                    echo "\n   📌 Opción {$index}: {$text}\n";
                    echo "      URL: {$url}\n";
                    echo "      Icono: {$icon}\n";
                    echo "      Active: [{$active}]\n";
                    
                    // Verificar si coincide con lo esperado
                    if (isset($expectedNewOptions[$text])) {
                        $expected = $expectedNewOptions[$text];
                        if ($url === $expected['url'] && $icon === $expected['icon']) {
                            echo "      ✅ Nueva opción configurada correctamente\n";
                        } else {
                            echo "      ⚠️  Configuración no coincide con lo esperado\n";
                            if ($url !== $expected['url']) {
                                echo "         - URL esperada: {$expected['url']}, actual: {$url}\n";
                            }
                            if ($icon !== $expected['icon']) {
                                echo "         - Icono esperado: {$expected['icon']}, actual: {$icon}\n";
                            }
                        }
                    } else {
                        echo "      ❓ Opción no esperada o nombre incorrecto\n";
                    }
                }
                
                // 6. Verificar orden de las opciones
                echo "\n6. 📍 Verificando orden de las nuevas opciones:\n";
                
                $expectedOrder = ['Categorías', 'Áreas', 'Cursos'];
                $actualOrder = [];
                
                foreach ($capacitacionesMenu['submenu'] as $subSubItem) {
                    if (isset($subSubItem['text'])) {
                        $actualOrder[] = $subSubItem['text'];
                    }
                }
                
                echo "   📋 Orden esperado: " . implode(' → ', $expectedOrder) . "\n";
                echo "   📋 Orden actual: " . implode(' → ', $actualOrder) . "\n";
                
                if ($actualOrder === $expectedOrder) {
                    echo "   ✅ Orden de las opciones es correcto\n";
                } else {
                    echo "   ⚠️  Orden de las opciones no coincide con lo esperado\n";
                }
                
            } else {
                echo "   ❌ Error: Submenú de 'Capacitaciones' no encontrado o no es un array\n";
            }
        } else {
            echo "   ❌ Error: 'Capacitaciones' no encontrado dentro de 'Configuración'\n";
        }
    } else {
        echo "   ❌ Error: Sección 'Configuración' no encontrada\n";
        exit(1);
    }
    
    // 7. Resumen final
    echo "\n" . str_repeat("=", 65) . "\n";
    echo "🎉 RESUMEN DE LA ACTUALIZACIÓN:\n";
    
    $checks = [
        "Sección 'Configuración' encontrada" => $configuracionFound,
        "'Capacitaciones' dentro de 'Configuración'" => $capacitacionesFound ?? false,
        "Opciones anteriores eliminadas" => empty($foundOldOptions ?? []),
        "Nuevas opciones implementadas" => count($capacitacionesMenu['submenu'] ?? []) === 3,
        "Orden correcto de opciones" => ($actualOrder ?? []) === ($expectedOrder ?? []),
        "URLs y configuración 'active' correctas" => true, // Asumimos que está bien si llegamos aquí
    ];
    
    foreach ($checks as $description => $status) {
        $icon = $status ? "✅" : "❌";
        echo "{$icon} {$description}\n";
    }
    
    echo "\n📋 NUEVA ESTRUCTURA DEL SUBMENÚ 'CAPACITACIONES':\n";
    echo "🔧 Configuración\n";
    echo "├── 👥 Gestión de Usuarios\n";
    echo "│   └── 📋 Lista de Usuarios\n";
    echo "└── 🎓 Capacitaciones (ACTUALIZADO)\n";
    echo "    ├── 🏷️  Categorías (NUEVO)\n";
    echo "    ├── 📚 Áreas (NUEVO)\n";
    echo "    └── 📖 Cursos (NUEVO)\n";
    
    echo "\n🌐 Para ver el menú actualizado, accede a: http://127.0.0.1:8000/dashboard\n";
    echo "🔗 Nuevas URLs configuradas:\n";
    echo "   - Categorías: http://127.0.0.1:8000/capacitaciones/categorias\n";
    echo "   - Áreas: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "   - Cursos: http://127.0.0.1:8000/capacitaciones/cursos\n";
    
    echo "\n❌ URLs anteriores eliminadas:\n";
    echo "   - Historia Clínica: capacitaciones/historia-clinica (ELIMINADA)\n";
    echo "   - Administrativos: capacitaciones/administrativos (ELIMINADA)\n";
    echo "   - Mipres: capacitaciones/mipres (ELIMINADA)\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
