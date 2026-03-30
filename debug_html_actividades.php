<?php
/**
 * Script para generar y guardar el HTML de actividades para inspección
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Curso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "DEBUG HTML DE ACTIVIDADES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    // Simular usuario autenticado (admin)
    $user = \App\Models\User::where('role', 'admin')->first();
    if (!$user) {
        $user = \App\Models\User::first();
    }
    Auth::login($user);
    
    // Obtener curso 17
    $curso = Curso::with(['actividades', 'materiales'])->find(17);
    
    if (!$curso) {
        echo "❌ ERROR: Curso 17 no encontrado\n";
        exit(1);
    }
    
    echo "✅ Curso encontrado: {$curso->titulo}\n";
    echo "📋 Actividades: {$curso->actividades->count()}\n\n";
    
    // Preparar datos para la vista
    $actividades = $curso->actividades()->orderBy('fecha_apertura')->get();
    $esInstructor = true;
    
    // Generar HTML
    echo "Generando HTML...\n";
    $html = View::make('admin.capacitaciones.cursos.classroom.actividades', compact(
        'curso', 'actividades', 'esInstructor'
    ))->render();
    
    // Guardar HTML en archivo
    $filename = 'debug_actividades_output.html';
    file_put_contents($filename, $html);
    
    echo "✅ HTML generado y guardado en: {$filename}\n";
    echo "   Tamaño: " . number_format(strlen($html)) . " bytes\n\n";
    
    // Buscar caracteres problemáticos
    echo "Buscando caracteres problemáticos...\n";
    
    // Buscar dos puntos en contextos problemáticos
    if (preg_match_all('/data-[^=]+=(["\'])[^"\']*:[^"\']*\1/', $html, $matches)) {
        echo "⚠️  ADVERTENCIA: Encontrados " . count($matches[0]) . " atributos data-* con dos puntos\n";
        foreach (array_slice($matches[0], 0, 5) as $match) {
            echo "   → " . substr($match, 0, 100) . "...\n";
        }
        echo "\n";
    }
    
    // Buscar JSON embebido
    if (preg_match_all('/const\s+\w+\s*=\s*\{[^}]{100,}/', $html, $matches)) {
        echo "⚠️  ADVERTENCIA: Encontrados " . count($matches[0]) . " posibles JSON embebidos\n";
        foreach (array_slice($matches[0], 0, 3) as $match) {
            echo "   → " . substr($match, 0, 150) . "...\n";
        }
        echo "\n";
    }
    
    // Buscar base64
    if (preg_match_all('/base64_encode|atob\(/', $html, $matches)) {
        echo "⚠️  ADVERTENCIA: Encontradas " . count($matches[0]) . " referencias a base64\n";
    }
    
    // Buscar scripts con JSON
    if (preg_match_all('/<script[^>]*>.*?<\/script>/s', $html, $matches)) {
        echo "📊 Scripts encontrados: " . count($matches[0]) . "\n";
        
        foreach ($matches[0] as $i => $script) {
            if (strlen($script) > 1000) {
                echo "   Script #" . ($i + 1) . ": " . number_format(strlen($script)) . " bytes\n";
                
                // Buscar JSON grande en el script
                if (preg_match('/\{[^}]{500,}/', $script)) {
                    echo "      ⚠️  Contiene JSON grande\n";
                }
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ DEBUG COMPLETADO\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "\nRevisa el archivo: {$filename}\n";
    echo "Busca en el archivo por:\n";
    echo "  - 'data-actividad'\n";
    echo "  - 'const actividades'\n";
    echo "  - 'const materiales'\n";
    echo "  - Cualquier JSON grande\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR FATAL: {$e->getMessage()}\n";
    echo "   Archivo: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}
