<?php
/**
 * Script de prueba para verificar que el JSON de actividades sea válido
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Curso;

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA DE JSON DE ACTIVIDADES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    // Obtener el curso 18
    $curso = Curso::with('actividades')->find(18);
    
    if (!$curso) {
        echo "❌ ERROR: Curso 18 no encontrado\n";
        exit(1);
    }
    
    echo "✅ Curso encontrado: {$curso->titulo}\n";
    echo "📋 Actividades: {$curso->actividades->count()}\n\n";
    
    // Probar serialización de cada actividad
    foreach ($curso->actividades as $actividad) {
        echo "───────────────────────────────────────────────────────────\n";
        echo "Actividad: {$actividad->titulo}\n";
        echo "Tipo: {$actividad->tipo}\n";
        
        // Intentar serializar a JSON
        try {
            $json = json_encode($actividad, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            
            if ($json === false) {
                echo "❌ ERROR: No se pudo serializar a JSON\n";
                echo "   Error: " . json_last_error_msg() . "\n";
            } else {
                echo "✅ JSON válido (longitud: " . strlen($json) . " bytes)\n";
                
                // Verificar que no contenga caracteres problemáticos sin escapar
                if (preg_match('/[^\\\\]":/', $json)) {
                    echo "⚠️  ADVERTENCIA: Contiene dos puntos sin escapar\n";
                }
                
                // Mostrar primeros 200 caracteres
                echo "   Inicio: " . substr($json, 0, 200) . "...\n";
            }
        } catch (\Exception $e) {
            echo "❌ EXCEPCIÓN: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
    
    // Probar serialización de toda la colección
    echo "═══════════════════════════════════════════════════════════\n";
    echo "PRUEBA DE COLECCIÓN COMPLETA\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    try {
        $json = json_encode($curso->actividades, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        
        if ($json === false) {
            echo "❌ ERROR: No se pudo serializar la colección\n";
            echo "   Error: " . json_last_error_msg() . "\n";
        } else {
            echo "✅ Colección serializada correctamente\n";
            echo "   Longitud: " . strlen($json) . " bytes\n";
            echo "   Actividades: " . $curso->actividades->count() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ EXCEPCIÓN: {$e->getMessage()}\n";
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ PRUEBA COMPLETADA\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR FATAL: {$e->getMessage()}\n";
    echo "   Archivo: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}
