<?php

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'SHC';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si ya existe el usuario
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@test.com']);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "✅ Usuario de prueba ya existe:\n";
        echo "   Email: admin@test.com\n";
        echo "   Nombre: {$existingUser['name']} {$existingUser['apellido1']} {$existingUser['apellido2']}\n";
        echo "   Documento: {$existingUser['tipo_documento']}: {$existingUser['numero_documento']}\n";
        echo "   Rol: {$existingUser['role']}\n";
    } else {
        // Crear usuario de prueba
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, apellido1, apellido2, email, password, role, tipo_documento, numero_documento, email_verified_at, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Administrador',
            'Sistema',
            'Test',
            'admin@test.com',
            $hashedPassword,
            'Super Admin',
            'DNI',
            '12345678',
            $now,
            $now,
            $now
        ]);
        
        echo "✅ Usuario de prueba creado exitosamente:\n";
        echo "   Email: admin@test.com\n";
        echo "   Contraseña: password123\n";
        echo "   Nombre: Administrador Sistema Test\n";
        echo "   Documento: DNI: 12345678\n";
        echo "   Rol: Super Admin\n";
        echo "   Verificado: SÍ\n";
    }
    
    echo "\n🌐 Ahora puedes acceder a:\n";
    echo "   URL: http://127.0.0.1:8000/\n";
    echo "   Email: admin@test.com\n";
    echo "   Contraseña: password123\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
