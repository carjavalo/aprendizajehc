<?php
/**
 * Script de Verificación de Correcciones
 * Verifica que todos los cambios se aplicaron correctamente
 * 
 * Ejecutar: php verificar_correcciones.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE CORRECCIONES - SISTEMA CLASSROOM          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errores = 0;
$advertencias = 0;
$exitos = 0;

// Archivos a verificar
$archivos = [
    'resources/views/admin/capacitaciones/cursos/classroom/participantes.blade.php',
    'resources/views/admin/capacitaciones/cursos/classroom/foros.blade.php',
    'resources/views/admin/capacitaciones/cursos/classroom/actividades.blade.php',
    'resources/views/admin/capacitaciones/cursos/classroom/materiales.blade.php',
    'resources/views/admin/capacitaciones/cursos/classroom/entregas.blade.php',
    'resources/views/admin/capacitaciones/cursos/edit.blade.php',
    'resources/views/academico/curso/aula-virtual.blade.php',
    'resources/views/admin/configuracion/publicidad-productos/index.blade.php',
];

echo "📋 Verificando archivos modificados...\n\n";

foreach ($archivos as $archivo) {
    if (!file_exists($archivo)) {
        echo "❌ ERROR: Archivo no encontrado: $archivo\n";
        $errores++;
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    $nombre = basename($archivo);
    
    // Verificar que no haya @json problemáticos
    $countJsonProblematico = preg_match_all('/@json\([^)]+\)/', $contenido, $matches);
    
    if ($countJsonProblematico > 0) {
        echo "⚠️  ADVERTENCIA: $nombre contiene $countJsonProblematico uso(s) de @json()\n";
        echo "   Ubicaciones: " . implode(', ', array_slice($matches[0], 0, 3)) . "\n";
        $advertencias++;
    }
    
    // Verificar que no haya referencias a user-default.png
    if (strpos($contenido, 'user-default.png') !== false) {
        echo "⚠️  ADVERTENCIA: $nombre contiene referencias a user-default.png\n";
        $advertencias++;
    }
    
    // Verificar que tenga json_encode con flags (si aplica)
    if (strpos($contenido, 'json_encode') !== false) {
        if (strpos($contenido, 'JSON_HEX_TAG') !== false) {
            echo "✅ OK: $nombre usa json_encode con flags de seguridad\n";
            $exitos++;
        }
    }
    
    // Verificar íconos FontAwesome
    if (strpos($contenido, 'fa-user-circle') !== false) {
        echo "✅ OK: $nombre usa íconos FontAwesome para avatares\n";
        $exitos++;
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Éxitos:        $exitos\n";
echo "⚠️  Advertencias:  $advertencias\n";
echo "❌ Errores:       $errores\n";
echo "\n";

if ($errores > 0) {
    echo "❌ RESULTADO: HAY ERRORES QUE REQUIEREN ATENCIÓN\n";
    exit(1);
} elseif ($advertencias > 0) {
    echo "⚠️  RESULTADO: HAY ADVERTENCIAS (revisar si es necesario)\n";
    echo "   Nota: Algunos usos de @json() pueden ser seguros en contextos específicos\n";
    exit(0);
} else {
    echo "✅ RESULTADO: TODAS LAS CORRECCIONES APLICADAS CORRECTAMENTE\n";
    exit(0);
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📝 PRÓXIMOS PASOS:\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "1. Limpiar caché del navegador (Ctrl+Shift+Delete)\n";
echo "2. Ir a: http://192.168.2.200:8001/capacitaciones/cursos/18/classroom\n";
echo "3. Abrir consola del navegador (F12)\n";
echo "4. Navegar por todas las pestañas\n";
echo "5. Hacer clic en 'Editar' en una actividad\n";
echo "6. Verificar que no hay errores en consola\n";
echo "\n";
echo "📚 Documentación:\n";
echo "   - CORRECCION_ERRORES_CLASSROOM.md (detalles técnicos)\n";
echo "   - RESUMEN_CORRECCION_FINAL.md (resumen ejecutivo)\n";
echo "\n";
