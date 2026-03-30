<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Area;
use App\Models\Categoria;
use Carbon\Carbon;

echo "🏗️ GENERADOR DE DATOS DE PRUEBA PARA ÁREAS\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Verificar que existan categorías
    $categorias = Categoria::all();
    
    if ($categorias->isEmpty()) {
        echo "❌ Error: No hay categorías disponibles. Primero debe crear categorías.\n";
        echo "💡 Ejecute: php generate_categorias_test_data.php\n";
        exit(1);
    }
    
    echo "✅ Categorías disponibles: " . $categorias->count() . "\n\n";
    
    // Definir áreas de prueba por categoría
    $areasPorCategoria = [
        'Medicina General' => [
            'Consulta Externa',
            'Urgencias',
            'Hospitalización',
            'Medicina Preventiva',
            'Medicina Familiar'
        ],
        'Pediatría' => [
            'Neonatología',
            'Pediatría General',
            'Urgencias Pediátricas',
            'Cuidados Intensivos Pediátricos',
            'Desarrollo Infantil'
        ],
        'Ginecología' => [
            'Consulta Ginecológica',
            'Obstetricia',
            'Planificación Familiar',
            'Cirugía Ginecológica',
            'Medicina Reproductiva'
        ],
        'Cardiología' => [
            'Cardiología Clínica',
            'Electrofisiología',
            'Hemodinamia',
            'Cirugía Cardiovascular',
            'Rehabilitación Cardíaca'
        ],
        'Neurología' => [
            'Neurología Clínica',
            'Neurocirugía',
            'Neuropsicología',
            'Electroencefalografía',
            'Rehabilitación Neurológica'
        ],
        'Dermatología' => [
            'Dermatología General',
            'Dermatología Pediátrica',
            'Cirugía Dermatológica',
            'Dermatopatología',
            'Cosmiatría'
        ],
        'Oftalmología' => [
            'Consulta Oftalmológica',
            'Cirugía Oftalmológica',
            'Retina y Vítreo',
            'Glaucoma',
            'Oftalmología Pediátrica'
        ],
        'Traumatología' => [
            'Traumatología General',
            'Cirugía Ortopédica',
            'Medicina Deportiva',
            'Rehabilitación Física',
            'Columna Vertebral'
        ],
        'Psiquiatría' => [
            'Psiquiatría General',
            'Psiquiatría Infantil',
            'Psicología Clínica',
            'Terapia Familiar',
            'Adicciones'
        ],
        'Radiología' => [
            'Radiología Convencional',
            'Tomografía Computarizada',
            'Resonancia Magnética',
            'Ultrasonografía',
            'Medicina Nuclear'
        ]
    ];
    
    echo "📊 Creando áreas de prueba...\n";
    
    $totalCreadas = 0;
    $totalExistentes = 0;
    
    foreach ($areasPorCategoria as $categoriaDesc => $areas) {
        // Buscar la categoría
        $categoria = $categorias->where('descripcion', $categoriaDesc)->first();
        
        if (!$categoria) {
            echo "   ⚠️  Categoría '{$categoriaDesc}' no encontrada, saltando...\n";
            continue;
        }
        
        echo "\n   📋 Procesando categoría: {$categoriaDesc} (ID: {$categoria->id})\n";
        
        foreach ($areas as $areaDesc) {
            // Verificar si ya existe
            $existeArea = Area::where('descripcion', $areaDesc)
                             ->where('cod_categoria', $categoria->id)
                             ->first();
            
            if (!$existeArea) {
                // Crear fecha aleatoria en los últimos 3 meses
                $fechaCreacion = Carbon::now()->subDays(rand(1, 90));
                
                Area::create([
                    'descripcion' => $areaDesc,
                    'cod_categoria' => $categoria->id,
                    'created_at' => $fechaCreacion,
                    'updated_at' => $fechaCreacion,
                ]);
                
                echo "      ✅ Área creada: {$areaDesc}\n";
                $totalCreadas++;
            } else {
                echo "      ⚠️  Área ya existe: {$areaDesc}\n";
                $totalExistentes++;
            }
        }
    }
    
    echo "\n📈 RESUMEN DE CREACIÓN:\n";
    echo str_repeat("-", 40) . "\n";
    echo "✅ Áreas creadas: {$totalCreadas}\n";
    echo "⚠️  Áreas existentes: {$totalExistentes}\n";
    echo "📊 Total de áreas en el sistema: " . Area::count() . "\n";
    
    // Mostrar estadísticas por categoría
    echo "\n📊 DISTRIBUCIÓN POR CATEGORÍA:\n";
    echo str_repeat("-", 40) . "\n";
    
    $estadisticas = Area::join('categorias', 'areas.cod_categoria', '=', 'categorias.id')
        ->selectRaw('categorias.descripcion as categoria, COUNT(*) as total')
        ->groupBy('categorias.id', 'categorias.descripcion')
        ->orderBy('total', 'desc')
        ->get();
    
    foreach ($estadisticas as $stat) {
        echo sprintf("%-20s: %d áreas\n", $stat->categoria, $stat->total);
    }
    
    // Mostrar distribución por mes
    echo "\n📅 DISTRIBUCIÓN POR MES:\n";
    echo str_repeat("-", 40) . "\n";
    
    $distribucionMes = Area::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
        ->groupBy('año', 'mes')
        ->orderBy('año', 'desc')
        ->orderBy('mes', 'desc')
        ->get();
    
    foreach ($distribucionMes as $item) {
        $nombreMes = Carbon::create($item->año, $item->mes, 1)->format('F Y');
        echo sprintf("%-20s: %d áreas\n", $nombreMes, $item->total);
    }
    
    echo "\n🎉 ¡Datos de prueba para áreas generados exitosamente!\n";
    echo "🌐 Puedes acceder al sistema en: http://127.0.0.1:8000/capacitaciones/areas\n";
    echo "📋 Total de áreas disponibles: " . Area::count() . "\n";
    echo "📋 Total de categorías con áreas: " . $estadisticas->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la generación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
