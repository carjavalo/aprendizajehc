<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\UserLogin;
use Carbon\Carbon;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Generando datos de prueba para el sistema de seguimiento de ingresos...\n\n";

try {
    // Obtener usuarios existentes
    $users = User::all();
    
    if ($users->isEmpty()) {
        echo "❌ No hay usuarios en la base de datos. Creando usuarios de prueba...\n";
        
        // Crear usuarios de prueba
        $testUsers = [
            [
                'name' => 'Juan',
                'apellido1' => 'Pérez',
                'apellido2' => 'García',
                'email' => 'juan.perez@test.com',
                'password' => bcrypt('password123'),
                'role' => 'Administrador',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'María',
                'apellido1' => 'González',
                'apellido2' => 'López',
                'email' => 'maria.gonzalez@test.com',
                'password' => bcrypt('password123'),
                'role' => 'Docente',
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321',
                'email_verified_at' => null, // Sin verificar
            ],
            [
                'name' => 'Carlos',
                'apellido1' => 'Rodríguez',
                'apellido2' => 'Martín',
                'email' => 'carlos.rodriguez@test.com',
                'password' => bcrypt('password123'),
                'role' => 'Estudiante',
                'tipo_documento' => 'Pasaporte',
                'numero_documento' => 'AB123456',
                'email_verified_at' => now(),
            ],
        ];
        
        foreach ($testUsers as $userData) {
            User::create($userData);
            echo "   ✅ Usuario creado: {$userData['email']}\n";
        }
        
        $users = User::all();
    }
    
    echo "\n📊 Generando registros de login de prueba...\n";
    
    $ips = [
        '192.168.1.100',
        '10.0.0.50',
        '172.16.0.25',
        '203.0.113.10',
        '198.51.100.5'
    ];
    
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    ];
    
    $count = 0;
    
    // Generar registros de los últimos 30 días
    for ($i = 30; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        
        // Generar entre 2 y 8 intentos por día
        $attemptsPerDay = rand(2, 8);
        
        for ($j = 0; $j < $attemptsPerDay; $j++) {
            $user = $users->random();
            $isSuccess = rand(1, 10) > 2; // 80% de éxito
            
            // Hora aleatoria del día
            $attemptTime = $date->copy()->addHours(rand(8, 22))->addMinutes(rand(0, 59));
            
            UserLogin::create([
                'user_id' => $isSuccess ? $user->id : (rand(1, 10) > 3 ? $user->id : null),
                'email' => $isSuccess ? $user->email : ($user->email . (rand(1, 10) > 7 ? '.fake' : '')),
                'ip_address' => $ips[array_rand($ips)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'status' => $isSuccess ? 'success' : 'failed',
                'email_verified' => $user->email_verified_at ? 'verified' : 'unverified',
                'failure_reason' => $isSuccess ? null : (rand(1, 10) > 5 ? 'Credenciales inválidas' : 'Email no verificado'),
                'attempted_at' => $attemptTime,
                'created_at' => $attemptTime,
                'updated_at' => $attemptTime,
            ]);
            
            $count++;
        }
    }
    
    // Generar algunos intentos fallidos con emails inexistentes
    for ($i = 0; $i < 10; $i++) {
        $fakeEmails = [
            'hacker@malicious.com',
            'test@nonexistent.com',
            'admin@fake.com',
            'user@invalid.org',
            'spam@bot.net'
        ];
        
        UserLogin::create([
            'user_id' => null,
            'email' => $fakeEmails[array_rand($fakeEmails)],
            'ip_address' => $ips[array_rand($ips)],
            'user_agent' => $userAgents[array_rand($userAgents)],
            'status' => 'failed',
            'email_verified' => 'unverified',
            'failure_reason' => 'Usuario no encontrado',
            'attempted_at' => Carbon::now()->subDays(rand(1, 15))->addHours(rand(0, 23)),
        ]);
        
        $count++;
    }
    
    echo "   ✅ {$count} registros de login generados\n";
    
    // Mostrar estadísticas
    echo "\n📈 ESTADÍSTICAS GENERADAS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stats = [
        'Total de registros' => UserLogin::count(),
        'Ingresos exitosos' => UserLogin::where('status', 'success')->count(),
        'Ingresos fallidos' => UserLogin::where('status', 'failed')->count(),
        'Usuarios verificados' => UserLogin::where('email_verified', 'verified')->count(),
        'Usuarios sin verificar' => UserLogin::where('email_verified', 'unverified')->count(),
        'Registros de hoy' => UserLogin::whereDate('attempted_at', Carbon::today())->count(),
        'Registros de esta semana' => UserLogin::where('attempted_at', '>=', Carbon::now()->startOfWeek())->count(),
    ];
    
    foreach ($stats as $label => $value) {
        echo sprintf("%-25s: %d\n", $label, $value);
    }
    
    echo "\n🎉 ¡Datos de prueba generados exitosamente!\n";
    echo "🌐 Puedes acceder al sistema de seguimiento en: http://127.0.0.1:8000/tracking/logins\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}
