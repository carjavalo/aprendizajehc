<?php

require_once 'vendor/autoload.php';

use App\Models\Categoria;
use Carbon\Carbon;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Generando datos de prueba para el sistema de categorías...\n\n";

try {
    // Datos de prueba para categorías
    $categorias = [
        'Medicina General',
        'Pediatría',
        'Ginecología',
        'Cardiología',
        'Neurología',
        'Dermatología',
        'Oftalmología',
        'Traumatología',
        'Psiquiatría',
        'Radiología',
        'Laboratorio Clínico',
        'Enfermería',
        'Farmacia',
        'Administración Hospitalaria',
        'Gestión de Calidad',
        'Seguridad del Paciente',
        'Bioseguridad',
        'Manejo de Residuos',
        'Atención al Usuario',
        'Sistemas de Información',
        'Recursos Humanos',
        'Contabilidad y Finanzas',
        'Auditoría Médica',
        'Epidemiología',
        'Salud Pública'
    ];
    
    echo "📊 Creando categorías de prueba...\n";
    
    $count = 0;
    foreach ($categorias as $descripcion) {
        // Verificar si ya existe
        $existeCategoria = Categoria::where('descripcion', $descripcion)->first();
        
        if (!$existeCategoria) {
            // Crear fecha aleatoria en los últimos 6 meses
            $fechaCreacion = Carbon::now()->subDays(rand(1, 180));
            
            Categoria::create([
                'descripcion' => $descripcion,
                'created_at' => $fechaCreacion,
                'updated_at' => $fechaCreacion,
            ]);
            
            echo "   ✅ Categoría creada: {$descripcion}\n";
            $count++;
        } else {
            echo "   ⚠️  Categoría ya existe: {$descripcion}\n";
        }
    }
    
    echo "\n📈 ESTADÍSTICAS GENERADAS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stats = [
        'Total de categorías' => Categoria::count(),
        'Categorías nuevas creadas' => $count,
        'Categorías más recientes' => Categoria::orderBy('created_at', 'desc')->limit(3)->pluck('descripcion')->implode(', '),
        'Categorías más antiguas' => Categoria::orderBy('created_at', 'asc')->limit(3)->pluck('descripcion')->implode(', '),
    ];
    
    foreach ($stats as $label => $value) {
        echo sprintf("%-30s: %s\n", $label, $value);
    }
    
    // Mostrar distribución por mes
    echo "\n📅 DISTRIBUCIÓN POR MES:\n";
    echo str_repeat("-", 40) . "\n";
    
    $distribucionMes = Categoria::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
        ->groupBy('año', 'mes')
        ->orderBy('año', 'desc')
        ->orderBy('mes', 'desc')
        ->get();
    
    foreach ($distribucionMes as $item) {
        $nombreMes = Carbon::create($item->año, $item->mes, 1)->format('F Y');
        echo sprintf("%-20s: %d categorías\n", $nombreMes, $item->total);
    }
    
    echo "\n🎉 ¡Datos de prueba para categorías generados exitosamente!\n";
    echo "🌐 Puedes acceder al sistema en: http://127.0.0.1:8000/capacitaciones/categorias\n";
    echo "📋 Total de categorías disponibles: " . Categoria::count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
