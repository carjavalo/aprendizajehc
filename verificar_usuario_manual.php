<?php

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'SHC';
$username = 'root';
$password = '';

echo "🔧 VERIFICACIÓN MANUAL DE USUARIOS\n";
echo "=" . str_repeat("=", 35) . "\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar todos los usuarios sin verificar
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE users SET email_verified_at = ? WHERE email_verified_at IS NULL");
    $result = $stmt->execute([$now]);
    $affectedRows = $stmt->rowCount();
    
    echo "✅ {$affectedRows} usuarios verificados automáticamente\n\n";
    
    // Mostrar usuarios disponibles
    echo "👥 USUARIOS DISPONIBLES PARA LOGIN:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->query("SELECT name, apellido1, email, role FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "📧 {$user['email']}\n";
        echo "   Nombre: {$user['name']} {$user['apellido1']}\n";
        echo "   Rol: {$user['role']}\n";
        echo "   Contraseña: password123 (o la que usaste al registrarte)\n\n";
    }
    
    echo "🌐 ACCESO:\n";
    echo "URL: http://127.0.0.1:8000/login\n";
    echo "Usa cualquiera de los emails mostrados arriba\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
