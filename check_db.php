<?php

// Script simple para verificar el estado de la base de datos
echo "Verificando base de datos...\n";

try {
    // Conectar directamente a MySQL
    $host = '127.0.0.1';
    $dbname = 'SHC';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a base de datos exitosa\n";
    echo "📊 Base de datos: $dbname\n\n";
    
    // Verificar si existen las tablas
    $tablas = ['migrations', 'categorias', 'areas'];
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabla]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$tabla' existe\n";
            
            // Contar registros
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `$tabla`");
            $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "   📊 Registros: $count\n";
            
            // Mostrar estructura
            if ($tabla === 'areas') {
                echo "   🏗️ Estructura de la tabla areas:\n";
                $structStmt = $pdo->query("DESCRIBE `areas`");
                while ($row = $structStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "      - {$row['Field']}: {$row['Type']}\n";
                }
            }
        } else {
            echo "❌ Tabla '$tabla' NO existe\n";
        }
        echo "\n";
    }
    
    // Si la tabla areas no existe, mostrar las migraciones disponibles
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'migrations'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "📋 Migraciones registradas:\n";
        $migStmt = $pdo->query("SELECT migration FROM migrations ORDER BY batch, id");
        while ($row = $migStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['migration']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
