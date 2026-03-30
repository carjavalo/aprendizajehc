<?php

/**
 * Script de diagnóstico para verificar el envío y recepción de mensajes del chat
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\MensajeChat;

echo "=== DIAGNÓSTICO DE MENSAJES DEL CHAT ===\n\n";

// 1. Verificar tabla y estructura
echo "1. Verificando tabla mensajes_chat...\n";
try {
    $count = DB::table('mensajes_chat')->count();
    echo "   ✓ Tabla existe. Total de mensajes: {$count}\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verificar últimos mensajes
echo "\n2. Últimos 10 mensajes en la base de datos:\n";
$mensajes = DB::table('mensajes_chat')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

if ($mensajes->isEmpty()) {
    echo "   ⚠ No hay mensajes en la base de datos\n";
} else {
    foreach ($mensajes as $mensaje) {
        $remitente = User::find($mensaje->remitente_id);
        $destinatario = User::find($mensaje->destinatario_id);
        
        $remitenteNombre = $remitente ? $remitente->full_name : "Usuario #{$mensaje->remitente_id}";
        $destinatarioNombre = $destinatario ? $destinatario->full_name : "Usuario #{$mensaje->destinatario_id}";
        
        echo "\n   ID: {$mensaje->id}\n";
        echo "   De: {$remitenteNombre} (ID: {$mensaje->remitente_id})\n";
        echo "   Para: {$destinatarioNombre} (ID: {$mensaje->destinatario_id})\n";
        echo "   Tipo: {$mensaje->tipo}\n";
        if ($mensaje->grupo_destinatario) {
            echo "   Grupo: {$mensaje->grupo_destinatario}\n";
        }
        echo "   Mensaje: " . substr($mensaje->mensaje, 0, 100) . (strlen($mensaje->mensaje) > 100 ? '...' : '') . "\n";
        echo "   Leído: " . ($mensaje->leido ? 'Sí' : 'No') . "\n";
        echo "   Fecha: {$mensaje->created_at}\n";
        echo "   " . str_repeat('-', 70) . "\n";
    }
}

// 3. Verificar usuarios de prueba
echo "\n3. Verificando usuarios de prueba:\n";
$estudiante = User::where('role', 'Estudiante')->first();
$docente = User::where('role', 'Docente')->first();
$operador = User::where('role', 'Operador')->first();

if ($estudiante) {
    echo "   ✓ Estudiante: {$estudiante->full_name} (ID: {$estudiante->id})\n";
    $mensajesEnviados = MensajeChat::where('remitente_id', $estudiante->id)->count();
    $mensajesRecibidos = MensajeChat::where('destinatario_id', $estudiante->id)->count();
    echo "     - Mensajes enviados: {$mensajesEnviados}\n";
    echo "     - Mensajes recibidos: {$mensajesRecibidos}\n";
}

if ($docente) {
    echo "   ✓ Docente: {$docente->full_name} (ID: {$docente->id})\n";
    $mensajesEnviados = MensajeChat::where('remitente_id', $docente->id)->count();
    $mensajesRecibidos = MensajeChat::where('destinatario_id', $docente->id)->count();
    echo "     - Mensajes enviados: {$mensajesEnviados}\n";
    echo "     - Mensajes recibidos: {$mensajesRecibidos}\n";
}

if ($operador) {
    echo "   ✓ Operador: {$operador->full_name} (ID: {$operador->id})\n";
    $mensajesEnviados = MensajeChat::where('remitente_id', $operador->id)->count();
    $mensajesRecibidos = MensajeChat::where('destinatario_id', $operador->id)->count();
    echo "     - Mensajes enviados: {$mensajesEnviados}\n";
    echo "     - Mensajes recibidos: {$mensajesRecibidos}\n";
}

// 4. Probar envío de mensaje de prueba
echo "\n4. Probando envío de mensaje de prueba...\n";
if ($estudiante && $operador) {
    try {
        $mensajePrueba = MensajeChat::create([
            'remitente_id' => $estudiante->id,
            'destinatario_id' => $operador->id,
            'mensaje' => 'Mensaje de prueba automático - ' . date('Y-m-d H:i:s'),
            'tipo' => 'individual',
            'leido' => false,
        ]);
        
        echo "   ✓ Mensaje de prueba creado exitosamente\n";
        echo "   ID del mensaje: {$mensajePrueba->id}\n";
        echo "   De: {$estudiante->full_name}\n";
        echo "   Para: {$operador->full_name}\n";
        
        // Verificar que se guardó
        $verificar = MensajeChat::find($mensajePrueba->id);
        if ($verificar) {
            echo "   ✓ Mensaje verificado en la base de datos\n";
        } else {
            echo "   ✗ Error: Mensaje no se encuentra en la base de datos\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Error al crear mensaje: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠ No se puede probar: faltan usuarios de prueba\n";
}

// 5. Verificar rutas
echo "\n5. Verificando rutas del chat...\n";
try {
    $buscarUrl = route('chat.buscar-usuarios');
    echo "   ✓ Ruta buscar-usuarios: {$buscarUrl}\n";
    
    $enviarUrl = route('chat.enviar');
    echo "   ✓ Ruta enviar: {$enviarUrl}\n";
    
    $mensajesUrl = route('chat.mensajes');
    echo "   ✓ Ruta mensajes: {$mensajesUrl}\n";
} catch (Exception $e) {
    echo "   ✗ Error con rutas: " . $e->getMessage() . "\n";
}

// 6. Verificar logs de Laravel
echo "\n6. Verificando logs recientes...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Últimas 50 líneas
    
    $chatErrors = array_filter($recentLines, function($line) {
        return stripos($line, 'chat') !== false || 
               stripos($line, 'mensaje') !== false ||
               stripos($line, 'ChatController') !== false;
    });
    
    if (empty($chatErrors)) {
        echo "   ✓ No hay errores relacionados con el chat en los logs recientes\n";
    } else {
        echo "   ⚠ Errores encontrados en logs:\n";
        foreach ($chatErrors as $error) {
            echo "   " . substr($error, 0, 150) . "\n";
        }
    }
} else {
    echo "   ⚠ Archivo de log no encontrado\n";
}

// 7. Verificar CSRF token
echo "\n7. Verificando configuración de sesión...\n";
$sessionDriver = config('session.driver');
echo "   Driver de sesión: {$sessionDriver}\n";

$sessionLifetime = config('session.lifetime');
echo "   Tiempo de vida de sesión: {$sessionLifetime} minutos\n";

// 8. Estadísticas generales
echo "\n8. Estadísticas generales:\n";
$totalMensajes = MensajeChat::count();
$mensajesIndividuales = MensajeChat::where('tipo', 'individual')->count();
$mensajesGrupales = MensajeChat::where('tipo', 'grupal')->count();
$mensajesNoLeidos = MensajeChat::where('leido', false)->count();
$mensajesHoy = MensajeChat::whereDate('created_at', today())->count();

echo "   Total de mensajes: {$totalMensajes}\n";
echo "   Mensajes individuales: {$mensajesIndividuales}\n";
echo "   Mensajes grupales: {$mensajesGrupales}\n";
echo "   Mensajes no leídos: {$mensajesNoLeidos}\n";
echo "   Mensajes de hoy: {$mensajesHoy}\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
echo "\nPara probar el envío desde el navegador:\n";
echo "1. Abre la consola del navegador (F12)\n";
echo "2. Ve a la pestaña 'Network' o 'Red'\n";
echo "3. Intenta enviar un mensaje\n";
echo "4. Busca la petición POST a '/chat/enviar'\n";
echo "5. Revisa la respuesta del servidor\n";
echo "6. Si hay errores, cópialos y repórtalos\n";

echo "\n";
