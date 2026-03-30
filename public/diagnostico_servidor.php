<?php
/**
 * Diagnóstico para cPanel - colocar en public/
 * Acceder: http://aprendizajehc.huv.gov.co/diagnostico_servidor.php
 * ELIMINAR después de usar.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico del Servidor</h2>";

// 1. PHP
echo "<h3>1. PHP</h3>";
echo "Versión: " . phpversion() . "<br>";

// 2. Extensiones requeridas
echo "<h3>2. Extensiones requeridas por Laravel</h3>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'bcmath'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌ FALTA';
    echo "$ext: $status<br>";
}

// 3. Rutas
echo "<h3>3. Rutas del servidor</h3>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "__DIR__: " . __DIR__ . "<br>";
echo "Parent dir: " . dirname(__DIR__) . "<br>";

// 4. Archivos críticos
echo "<h3>4. Archivos críticos</h3>";
$files = [
    dirname(__DIR__) . '/.env' => '.env',
    dirname(__DIR__) . '/vendor/autoload.php' => 'vendor/autoload.php',
    dirname(__DIR__) . '/bootstrap/app.php' => 'bootstrap/app.php',
    dirname(__DIR__) . '/artisan' => 'artisan',
    __DIR__ . '/index.php' => 'public/index.php',
    __DIR__ . '/.htaccess' => 'public/.htaccess',
];
foreach ($files as $path => $label) {
    $exists = file_exists($path) ? '✅' : '❌ NO EXISTE';
    echo "$label: $exists<br>";
}

// 5. Permisos
echo "<h3>5. Permisos de carpetas</h3>";
$dirs = [
    dirname(__DIR__) . '/storage',
    dirname(__DIR__) . '/storage/framework',
    dirname(__DIR__) . '/storage/framework/sessions',
    dirname(__DIR__) . '/storage/framework/views',
    dirname(__DIR__) . '/storage/framework/cache',
    dirname(__DIR__) . '/storage/framework/cache/data',
    dirname(__DIR__) . '/storage/logs',
    dirname(__DIR__) . '/bootstrap/cache',
];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $w = is_writable($dir) ? '✅ escribible' : '❌ NO escribible';
        echo basename(dirname($dir)) . "/" . basename($dir) . ": $w (" . substr(sprintf('%o', fileperms($dir)), -4) . ")<br>";
    } else {
        echo str_replace(dirname(__DIR__) . '/', '', $dir) . ": ❌ NO EXISTE<br>";
    }
}

// 6. Conexión a BD
echo "<h3>6. Conexión a MySQL</h3>";
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/^DB_HOST=(.+)$/m', $envContent, $host);
    preg_match('/^DB_PORT=(.+)$/m', $envContent, $port);
    preg_match('/^DB_DATABASE=(.+)$/m', $envContent, $db);
    preg_match('/^DB_USERNAME=(.+)$/m', $envContent, $user);
    preg_match('/^DB_PASSWORD=(.+)$/m', $envContent, $pass);

    $h = trim($host[1] ?? 'localhost');
    $p = trim($port[1] ?? '3306');
    $d = trim($db[1] ?? '');
    $u = trim($user[1] ?? '');
    $pw = trim($pass[1] ?? '');

    echo "Host: $h | DB: $d | User: $u<br>";
    try {
        $pdo = new PDO("mysql:host=$h;port=$p;dbname=$d", $u, $pw);
        echo "✅ Conexión exitosa a MySQL<br>";
        
        // Verificar tablas
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tablas encontradas: " . count($tables) . "<br>";
        if (count($tables) === 0) {
            echo "⚠️ La BD está vacía. Necesitas importar el SQL o ejecutar migraciones.<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
} else {
    echo "❌ No se pudo leer .env<br>";
}

// 7. Log de Laravel
echo "<h3>7. Último error en log</h3>";
$logFile = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lastChunk = substr($content, -3000);
    echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:400px;font-size:12px'>";
    echo htmlspecialchars($lastChunk);
    echo "</pre>";
} else {
    echo "No hay archivo de log.<br>";
}

// 8. Intentar cargar Laravel
echo "<h3>8. Prueba de carga de Laravel</h3>";
try {
    if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
        require dirname(__DIR__) . '/vendor/autoload.php';
        echo "✅ Autoload cargado<br>";
        
        if (file_exists(dirname(__DIR__) . '/bootstrap/app.php')) {
            $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
            echo "✅ App bootstrapped<br>";
        }
    }
} catch (Throwable $e) {
    echo "❌ Error al cargar Laravel: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
