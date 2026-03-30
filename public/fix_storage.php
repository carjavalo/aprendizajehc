<?php
/**
 * Script de diagnóstico y reparación para Laravel en cPanel.
 * Abre: https://localhost/AprendizajeHC/public/fix_storage.php
 * ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO.
 */

echo "<h2>🔧 Diagnóstico y Reparación Laravel - cPanel</h2>";
echo "<hr>";

$basePath = realpath(__DIR__ . '/..');

echo "<h3>📂 Ruta base detectada: $basePath</h3>";

// 1. Crear carpetas necesarias
echo "<h3>1. Creando carpetas de caché...</h3>";
$directories = [
    $basePath . '/storage',
    $basePath . '/storage/app',
    $basePath . '/storage/app/public',
    $basePath . '/storage/framework',
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/framework/views',
    $basePath . '/storage/logs',
    $basePath . '/bootstrap/cache',
];

foreach ($directories as $dir) {
    $relative = str_replace($basePath . '/', '', $dir);
    if (!is_dir($dir)) {
        if (@mkdir($dir, 0775, true)) {
            echo "✅ Creada: $relative<br>";
        } else {
            echo "❌ Error al crear: $relative<br>";
        }
    } else {
        @chmod($dir, 0775);
        echo "✔️ Ya existe: $relative<br>";
    }
}

// 2. Crear .gitignore en carpetas
$gitignoreContent = "*\n!.gitignore\n";
$gitignoreDirs = [
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/framework/views',
    $basePath . '/storage/logs',
    $basePath . '/bootstrap/cache',
];
foreach ($gitignoreDirs as $dir) {
    $path = $dir . '/.gitignore';
    if (!file_exists($path)) {
        @file_put_contents($path, $gitignoreContent);
    }
}

// 3. Verificar archivos clave
echo "<h3>2. Verificando archivos clave...</h3>";
$files = [
    '.env' => $basePath . '/.env',
    '.htaccess (raiz)' => $basePath . '/.htaccess',
    'public/.htaccess' => $basePath . '/public/.htaccess',
    'public/index.php' => $basePath . '/public/index.php',
    'artisan' => $basePath . '/artisan',
    'composer.json' => $basePath . '/composer.json',
    'vendor/autoload.php' => $basePath . '/vendor/autoload.php',
];
foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name<br>";
    } else {
        echo "❌ FALTA: $name<br>";
    }
}

// 4. Verificar .env
echo "<h3>3. Verificando .env...</h3>";
$envFile = $basePath . '/.env';
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    $keys = ['APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_URL'];
    foreach ($keys as $key) {
        if (preg_match("/^{$key}=(.+)$/m", $env, $m)) {
            $val = trim($m[1]);
            if ($key === 'DB_USERNAME' || $key === 'APP_KEY') {
                echo "✅ $key = (configurado)<br>";
            } else {
                echo "✅ $key = $val<br>";
            }
        } else {
            echo "❌ $key no encontrado en .env<br>";
        }
    }
} else {
    echo "❌ Archivo .env NO EXISTE. Debes crearlo.<br>";
}

// 5. Verificar permisos
echo "<h3>4. Permisos de carpetas...</h3>";
$checkDirs = ['storage', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
foreach ($checkDirs as $d) {
    $full = $basePath . '/' . $d;
    if (is_dir($full)) {
        $perms = substr(sprintf('%o', fileperms($full)), -4);
        $writable = is_writable($full) ? '✅ Escribible' : '❌ NO escribible';
        echo "$d: permisos=$perms $writable<br>";
    }
}

// 6. Limpiar caché de vistas
echo "<h3>5. Limpiando caché de vistas...</h3>";
$viewsPath = $basePath . '/storage/framework/views';
if (is_dir($viewsPath)) {
    $files = glob($viewsPath . '/*.php');
    $count = count($files);
    foreach ($files as $file) {
        @unlink($file);
    }
    echo "✅ $count archivos de caché de vistas eliminados<br>";
}

echo "<hr>";
echo "<h3 style='color:green;'>✅ Reparación completada. Recarga tu aplicación.</h3>";
echo "<p style='color:red;'><strong>⚠️ ELIMINA este archivo (fix_storage.php) después de usarlo.</strong></p>";
