<?php
/**
 * Script de setup para cPanel.
 * Acceder: http://aprendizajehc.huv.gov.co/setup_servidor.php
 * ELIMINAR después de usar.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

$baseDir = dirname(__DIR__);

echo "<h2>Setup del Servidor</h2>";

// 1. Crear carpetas de storage faltantes
echo "<h3>1. Creando carpetas de storage</h3>";
$dirs = [
    $baseDir . '/storage/framework',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "✅ Creada: " . str_replace($baseDir . '/', '', $dir) . "<br>";
        } else {
            echo "❌ No se pudo crear: " . str_replace($baseDir . '/', '', $dir) . "<br>";
        }
    } else {
        echo "⏩ Ya existe: " . str_replace($baseDir . '/', '', $dir) . "<br>";
    }
}

// Crear .gitignore en storage/framework
$gitignoreContent = "*\n!.gitignore\n";
foreach (['sessions', 'views', 'cache'] as $sub) {
    $gitignorePath = $baseDir . "/storage/framework/$sub/.gitignore";
    if (!file_exists($gitignorePath)) {
        file_put_contents($gitignorePath, $gitignoreContent);
    }
}

// 2. Verificar/instalar vendor
echo "<h3>2. Verificando vendor/</h3>";
if (file_exists($baseDir . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php ya existe<br>";
} else {
    echo "❌ vendor/ NO existe. Intentando composer install...<br>";
    
    // Intentar encontrar composer
    $composerPaths = [
        'composer',
        '/usr/local/bin/composer',
        '/usr/bin/composer',
        '/opt/cpanel/composer/bin/composer',
        $baseDir . '/composer.phar',
    ];
    
    $composerCmd = null;
    foreach ($composerPaths as $path) {
        $output = [];
        @exec("$path --version 2>&1", $output, $code);
        if ($code === 0) {
            $composerCmd = $path;
            echo "✅ Composer encontrado: $path (" . implode(' ', $output) . ")<br>";
            break;
        }
    }
    
    if ($composerCmd) {
        echo "⏳ Ejecutando composer install (puede tardar unos minutos)...<br>";
        flush();
        ob_flush();
        
        $cmd = "cd " . escapeshellarg($baseDir) . " && $composerCmd install --no-dev --optimize-autoloader 2>&1";
        $output = [];
        exec($cmd, $output, $returnCode);
        
        echo "<pre style='background:#f5f5f5;padding:10px;max-height:300px;overflow:auto;font-size:12px'>";
        echo htmlspecialchars(implode("\n", $output));
        echo "</pre>";
        
        if (file_exists($baseDir . '/vendor/autoload.php')) {
            echo "✅ composer install completado exitosamente<br>";
        } else {
            echo "❌ composer install falló (código: $returnCode)<br>";
        }
    } else {
        echo "❌ Composer no encontrado en el servidor.<br>";
        echo "<br><strong>Opciones para resolver:</strong><br>";
        echo "A) Subir la carpeta <code>vendor/</code> completa desde tu máquina local vía FTP/File Manager.<br>";
        echo "B) Descargar composer.phar al servidor:<br>";
        echo "<pre>cd " . htmlspecialchars($baseDir) . "\ncurl -sS https://getcomposer.org/installer | php\nphp composer.phar install --no-dev --optimize-autoloader</pre>";
    }
}

// 3. Verificación final
echo "<h3>3. Verificación final</h3>";
$checks = [
    'vendor/autoload.php' => $baseDir . '/vendor/autoload.php',
    'storage/framework/sessions' => $baseDir . '/storage/framework/sessions',
    'storage/framework/views' => $baseDir . '/storage/framework/views',
    'storage/framework/cache' => $baseDir . '/storage/framework/cache',
    'storage/logs' => $baseDir . '/storage/logs',
];

$allOk = true;
foreach ($checks as $label => $path) {
    $ok = file_exists($path);
    $allOk = $allOk && $ok;
    echo ($ok ? '✅' : '❌') . " $label<br>";
}

if ($allOk) {
    echo "<br><strong style='color:green'>✅ Todo listo. Recarga la página principal.</strong><br>";
} else {
    echo "<br><strong style='color:red'>⚠️ Aún faltan componentes. Revisa los errores arriba.</strong><br>";
}

echo "<br><small>⚠️ Elimina este archivo después de usarlo.</small>";
