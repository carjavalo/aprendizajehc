<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Curso;
use App\Models\User;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 DIAGNÓSTICO Y CORRECCIÓN DEL BOTÓN 'SUBIR MATERIAL'\n";
echo "======================================================\n\n";

try {
    // 1. Verificar si existe el curso ID 6
    echo "1. 📚 Verificando curso ID 6:\n";
    
    $curso = Curso::find(6);
    if ($curso) {
        echo "   ✅ Curso encontrado: {$curso->titulo}\n";
        echo "   👨‍🏫 Instructor: {$curso->instructor_nombre}\n";
        echo "   📊 Estado: {$curso->estado}\n";
    } else {
        echo "   ❌ Curso con ID 6 no existe\n";
        echo "   📋 Cursos disponibles:\n";
        
        $cursos = Curso::all();
        foreach ($cursos as $c) {
            echo "      - ID: {$c->id}, Título: {$c->titulo}\n";
        }
        
        if ($cursos->count() > 0) {
            $curso = $cursos->first();
            echo "   🔄 Usando curso ID {$curso->id} para las pruebas\n";
        } else {
            echo "   ⚠️ No hay cursos en el sistema\n";
            return;
        }
    }
    
    // 2. Verificar rutas
    echo "\n2. 🛣️ Verificando rutas:\n";
    
    try {
        $materialesUrl = route('capacitaciones.cursos.classroom.materiales', $curso->id);
        echo "   ✅ Ruta materiales: {$materialesUrl}\n";
        
        $storeUrl = route('capacitaciones.cursos.classroom.materiales.store', $curso->id);
        echo "   ✅ Ruta store: {$storeUrl}\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error en rutas: " . $e->getMessage() . "\n";
    }
    
    // 3. Verificar controlador
    echo "\n3. 🎮 Verificando controlador:\n";
    
    if (method_exists('App\Http\Controllers\CursoClassroomController', 'subirMaterial')) {
        echo "   ✅ Método subirMaterial existe\n";
    } else {
        echo "   ❌ Método subirMaterial NO existe\n";
    }
    
    if (method_exists('App\Http\Controllers\CursoClassroomController', 'materiales')) {
        echo "   ✅ Método materiales existe\n";
    } else {
        echo "   ❌ Método materiales NO existe\n";
    }
    
    // 4. Verificar modelo CursoMaterial
    echo "\n4. 🏗️ Verificando modelo CursoMaterial:\n";
    
    if (class_exists('App\Models\CursoMaterial')) {
        echo "   ✅ Modelo CursoMaterial existe\n";
        
        $material = new \App\Models\CursoMaterial();
        echo "   📋 Tabla: " . $material->getTable() . "\n";
        echo "   📝 Fillable: " . implode(', ', $material->getFillable()) . "\n";
        
    } else {
        echo "   ❌ Modelo CursoMaterial NO existe\n";
    }
    
    // 5. Verificar tabla curso_materiales
    echo "\n5. 🗄️ Verificando tabla curso_materiales:\n";
    
    if (\Illuminate\Support\Facades\Schema::hasTable('curso_materiales')) {
        echo "   ✅ Tabla curso_materiales existe\n";
        
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('curso_materiales');
        echo "   📋 Columnas: " . implode(', ', $columns) . "\n";
        
        $count = \Illuminate\Support\Facades\DB::table('curso_materiales')->count();
        echo "   📊 Registros: {$count}\n";
        
    } else {
        echo "   ❌ Tabla curso_materiales NO existe\n";
    }
    
    // 6. Verificar archivos de vista
    echo "\n6. 📄 Verificando archivos de vista:\n";
    
    $vistas = [
        'resources/views/admin/capacitaciones/cursos/classroom/index.blade.php' => 'Vista principal',
        'resources/views/admin/capacitaciones/cursos/classroom/materiales.blade.php' => 'Vista materiales'
    ];
    
    foreach ($vistas as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "   ✅ {$descripcion}: {$archivo}\n";
        } else {
            echo "   ❌ {$descripcion}: {$archivo} - NO EXISTE\n";
        }
    }
    
    // 7. Verificar permisos de directorio
    echo "\n7. 📁 Verificando permisos de almacenamiento:\n";
    
    $storageDir = storage_path('app/public');
    if (is_dir($storageDir)) {
        echo "   ✅ Directorio storage/app/public existe\n";
        
        if (is_writable($storageDir)) {
            echo "   ✅ Directorio es escribible\n";
        } else {
            echo "   ❌ Directorio NO es escribible\n";
        }
    } else {
        echo "   ❌ Directorio storage/app/public NO existe\n";
    }
    
    // 8. Crear datos de prueba si es necesario
    echo "\n8. 🧪 Creando datos de prueba:\n";
    
    // Verificar si hay un usuario instructor
    $instructor = User::whereIn('role', ['Super Admin', 'Administrador', 'Docente'])->first();
    if (!$instructor) {
        echo "   ⚠️ No hay usuarios instructores en el sistema\n";
    } else {
        echo "   ✅ Instructor disponible: {$instructor->name}\n";
        
        // Verificar si el curso tiene instructor asignado
        if (!$curso->instructor_id) {
            $curso->instructor_id = $instructor->id;
            $curso->save();
            echo "   🔄 Instructor asignado al curso\n";
        }
    }
    
    echo "\n🎯 PROBLEMAS IDENTIFICADOS Y SOLUCIONES:\n";
    echo "=========================================\n\n";
    
    echo "📋 PROBLEMA PRINCIPAL:\n";
    echo "La función loadTabContent() está definida dentro de \$(document).ready()\n";
    echo "pero se llama desde la vista de materiales, causando un error de referencia.\n\n";
    
    echo "✅ SOLUCIONES A IMPLEMENTAR:\n";
    echo "1. Mover loadTabContent() al scope global\n";
    echo "2. Verificar que el botón #btn-subir-material tenga el event handler correcto\n";
    echo "3. Asegurar que el modal se abra correctamente\n";
    echo "4. Verificar que el CSRF token esté presente\n";
    echo "5. Confirmar que las rutas estén correctamente configuradas\n\n";
    
    echo "🌐 URLS PARA PROBAR:\n";
    echo "====================\n";
    echo "📋 Classroom: http://127.0.0.1:8000/capacitaciones/cursos/{$curso->id}/classroom\n";
    echo "📁 Materiales: http://127.0.0.1:8000/capacitaciones/cursos/{$curso->id}/classroom/materiales\n\n";
    
    echo "👤 CREDENCIALES DE PRUEBA:\n";
    echo "==========================\n";
    if ($instructor) {
        echo "Email: {$instructor->email}\n";
        echo "Password: password (o la contraseña configurada)\n";
    } else {
        echo "Email: instructor@test.com\n";
        echo "Password: password\n";
    }
    
    echo "\n🔧 PRÓXIMOS PASOS:\n";
    echo "==================\n";
    echo "1. Corregir la función loadTabContent en la vista principal\n";
    echo "2. Verificar el JavaScript del botón 'Subir Material'\n";
    echo "3. Probar la funcionalidad completa\n";
    echo "4. Verificar que los archivos se suban correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el diagnóstico: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
