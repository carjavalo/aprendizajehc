<?php
/**
 * Script de diagnóstico para cPanel.
 * Subir al servidor, acceder desde: http://aprendizajehc.huv.gov.co/diagnostico_servidor.php
 * ELIMINAR después de usar.
 */

echo "<h2>Diagnóstico del servidor</h2>";

// 1. Versión de PHP
echo "<h3>1. PHP</h3>";
echo "Versión: " . phpversion() . "<br>";
echo "Extensiones cargadas: " . implode(', ', get_loaded_extensions()) . "<br><br>";

// 2. Verificar extensiones necesarias para Laravel
echo "<h3>2. Extensiones requeridas por Laravel</h3>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'bcmath'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌ FALTA';
    echo "$ext: $status<br>";
}

// 3. Verificar permisos de carpetas
echo "<h3>3. Permisos de carpetas</h3>";
$dirs = [
    __DIR__ . '/storage',
    __DIR__ . '/storage/framework',
    __DIR__ . '/storage/framework/sessions',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/storage/framework/cache',
    __DIR__ . '/storage/logs',
    __DIR__ . '/bootstrap/cache',
];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✅ escribible' : '❌ NO escribible';
        echo "$dir: $writable (permisos: " . substr(sprintf('%o', fileperms($dir)), -4) . ")<br>";
    } else {
        echo "$dir: ❌ NO EXISTE<br>";
    }
}

// 4. Verificar .env
echo "<h3>4. Archivo .env</h3>";
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ .env existe<br>";
} else {
    echo "❌ .env NO EXISTE<br>";
}

// 5. Verificar vendor
echo "<h3>5. Vendor/autoload</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php existe<br>";
} else {
    echo "❌ vendor/autoload.php NO EXISTE - Ejecuta: composer install<br>";
}

// 6. Conexión a la base de datos
echo "<h3>6. Conexión a MySQL</h3>";
try {
    // Leer credenciales del .env
    $envFile = file_get_contents(__DIR__ . '/.env');
    preg_match('/DB_HOST=(.+)/', $envFile, $host);
    preg_match('/DB_PORT=(.+)/', $envFile, $port);
    preg_match('/DB_DATABASE=(.+)/', $envFile, $db);
    preg_match('/DB_USERNAME=(.+)/', $envFile, $user);
    preg_match('/DB_PASSWORD=(.+)/', $envFile, $pass);
    
    $h = trim($host[1] ?? 'localhost');
    $p = trim($port[1] ?? '3306');
    $d = trim($db[1] ?? '');
    $u = trim($user[1] ?? '');
    $pw = trim($pass[1] ?? '');
    
    $pdo = new PDO("mysql:host=$h;port=$p;dbname=$d", $u, $pw);
    echo "✅ Conexión exitosa a MySQL ($d en $h)<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// 7. Mostrar último log de Laravel
echo "<h3>7. Último error en log de Laravel</h3>";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -30);
    echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:400px'>";
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
} else {
    echo "No se encontró archivo de log.<br>";
}
