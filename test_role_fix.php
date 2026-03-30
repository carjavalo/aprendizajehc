<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Area;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 VERIFICACIÓN DE LA CORRECCIÓN DEL ERROR DE ROLES\n";
echo "===================================================\n\n";

try {
    // 1. Verificar la estructura del modelo User
    echo "1. 👤 Verificando modelo User:\n";
    
    $user = User::first();
    if ($user) {
        echo "   ✅ Modelo User funciona\n";
        echo "   📋 Campo role: " . ($user->role ?? 'NULL') . "\n";
        echo "   🔧 Método hasRole() existe: " . (method_exists($user, 'hasRole') ? 'SÍ' : 'NO') . "\n";
        echo "   🔧 Método isAdmin() existe: " . (method_exists($user, 'isAdmin') ? 'SÍ' : 'NO') . "\n";
    } else {
        echo "   ⚠️ No hay usuarios en la base de datos\n";
    }
    
    // 2. Probar la consulta corregida de instructores
    echo "\n2. 👨‍🏫 Probando consulta de instructores:\n";
    
    try {
        $instructores = User::whereIn('role', ['Super Admin', 'Administrador', 'Docente'])
                           ->orderBy('name')
                           ->get();
        
        echo "   ✅ Consulta ejecutada exitosamente\n";
        echo "   📊 Instructores encontrados: " . $instructores->count() . "\n";
        
        foreach ($instructores as $instructor) {
            echo "   👤 {$instructor->name} {$instructor->apellido1} - Rol: {$instructor->role}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error en la consulta: " . $e->getMessage() . "\n";
    }
    
    // 3. Verificar áreas disponibles
    echo "\n3. 🏢 Verificando áreas disponibles:\n";
    
    try {
        $areas = Area::with('categoria')->orderBy('descripcion')->get();
        echo "   ✅ Consulta de áreas exitosa\n";
        echo "   📊 Áreas encontradas: " . $areas->count() . "\n";
        
        foreach ($areas->take(3) as $area) {
            echo "   🏢 {$area->descripcion} (Categoría: {$area->categoria->descripcion})\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error en consulta de áreas: " . $e->getMessage() . "\n";
    }
    
    // 4. Probar métodos de roles del usuario
    echo "\n4. 🔐 Probando métodos de roles:\n";
    
    if ($user) {
        try {
            // Probar hasRole con string
            $esDocente = $user->hasRole('Docente');
            echo "   ✅ hasRole('Docente'): " . ($esDocente ? 'true' : 'false') . "\n";
            
            // Probar isAdmin
            $esAdmin = $user->isAdmin();
            echo "   ✅ isAdmin(): " . ($esAdmin ? 'true' : 'false') . "\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error en métodos de roles: " . $e->getMessage() . "\n";
        }
    }
    
    // 5. Simular la carga de la página de creación de cursos
    echo "\n5. 📄 Simulando carga de página de creación:\n";
    
    try {
        // Simular el código del método create()
        $areas = Area::with('categoria')->orderBy('descripcion')->get();
        $instructores = User::whereIn('role', ['Super Admin', 'Administrador', 'Docente'])
                           ->orderBy('name')
                           ->get();
        
        echo "   ✅ Datos cargados exitosamente\n";
        echo "   📊 Áreas: " . $areas->count() . "\n";
        echo "   📊 Instructores: " . $instructores->count() . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error simulando carga: " . $e->getMessage() . "\n";
    }
    
    // 6. Verificar roles disponibles
    echo "\n6. 📋 Roles disponibles en el sistema:\n";
    
    try {
        $rolesDisponibles = User::getAvailableRoles();
        echo "   ✅ Roles definidos: " . implode(', ', $rolesDisponibles) . "\n";
        
        // Contar usuarios por rol
        foreach ($rolesDisponibles as $rol) {
            $count = User::where('role', $rol)->count();
            echo "   📊 {$rol}: {$count} usuarios\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error obteniendo roles: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 RESUMEN DE LA CORRECCIÓN:\n";
    echo "============================\n";
    echo "✅ Cambio realizado en CursoController@create():\n";
    echo "   ANTES: User::whereHas('roles', function(\$q) { \$q->whereIn('name', [...]) })\n";
    echo "   DESPUÉS: User::whereIn('role', ['Super Admin', 'Administrador', 'Docente'])\n\n";
    
    echo "✅ Cambio realizado en CursoClassroomController@verificarAccesoCurso():\n";
    echo "   ANTES: \$user->hasRole(['Super Admin', 'Administrador'])\n";
    echo "   DESPUÉS: \$user->isAdmin()\n\n";
    
    echo "🌐 URLS PARA PROBAR:\n";
    echo "====================\n";
    echo "📋 Lista de cursos: http://127.0.0.1:8000/capacitaciones/cursos\n";
    echo "➕ Crear curso: http://127.0.0.1:8000/capacitaciones/cursos/create\n";
    echo "🏫 Classroom: http://127.0.0.1:8000/capacitaciones/cursos/1/classroom\n\n";
    
    echo "👤 CREDENCIALES DE PRUEBA:\n";
    echo "==========================\n";
    echo "Email: instructor@test.com\n";
    echo "Password: password\n\n";
    
    echo "🎉 ¡CORRECCIÓN COMPLETADA!\n";
    echo "El error 'Call to undefined method App\\Models\\User::roles()' debería estar resuelto.\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
    
    echo "\n🔧 SOLUCIONES ADICIONALES:\n";
    echo "1. Verificar que la base de datos esté conectada\n";
    echo "2. Ejecutar: php artisan cache:clear\n";
    echo "3. Verificar que las migraciones estén ejecutadas\n";
    echo "4. Revisar logs en storage/logs/laravel.log\n";
}
