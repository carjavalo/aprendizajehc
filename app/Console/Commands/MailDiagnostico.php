<?php

namespace App\Console\Commands;

use App\Services\MailFailure;
use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailDiagnostico extends Command
{
    protected $signature = 'mail:diagnostico {destino? : Dirección a la que enviar el correo de prueba}';

    protected $description = 'Verifica la configuración SMTP: caché, salida de red, autenticación y envío real.';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $smtp = config('mail.mailers.smtp');

        $this->line('');
        $this->info('Configuración activa');
        $this->table(['Parámetro', 'Valor'], [
            ['Leída desde', app()->configurationIsCached() ? 'caché (bootstrap/cache/config.php)' : '.env'],
            ['MAIL_MAILER', $mailer],
            ['MAIL_HOST', $smtp['host'] ?? '—'],
            ['MAIL_PORT', $smtp['port'] ?? '—'],
            ['MAIL_USERNAME', $smtp['username'] ?: '(vacío)'],
            ['MAIL_PASSWORD', $smtp['password'] ? '(definida, ' . strlen($smtp['password']) . ' caracteres)' : '(vacía)'],
            ['MAIL_SCHEME', $smtp['scheme'] ?: '(auto)'],
            ['MAIL_TIMEOUT', ($smtp['timeout'] ?? ini_get('default_socket_timeout')) . ' s'],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
        ]);

        $this->revisarCache($mailer, $smtp);

        if ($mailer !== 'smtp') {
            $this->warn("MAIL_MAILER está en \"{$mailer}\", no en \"smtp\". No se probará el servidor SMTP.");

            return self::SUCCESS;
        }

        // Paso 1: salida de red, sin autenticarse.
        $this->line('');
        $this->info('1) Salida de red hacia el servidor SMTP');
        $this->revisarRed((string) $smtp['host'], (int) $smtp['port']);

        // Paso 2: conexión + autenticación con el mismo transporte que usa la aplicación.
        $this->line('');
        $this->info('2) Conexión y autenticación con el servidor SMTP');

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        try {
            $transport->start();
            $transport->stop();
            $this->line('   <fg=green>OK</> — el servidor aceptó las credenciales.');
        } catch (\Throwable $e) {
            $this->reportarFallo($e);

            return self::FAILURE;
        }

        // Paso 3: envío real, solo si se indicó un destinatario.
        $destino = $this->argument('destino');

        if (! $destino) {
            $this->line('');
            $this->comment('Para probar además un envío real: php artisan mail:diagnostico tu@correo.com');

            return self::SUCCESS;
        }

        $this->line('');
        $this->info("3) Envío de prueba a {$destino}");

        try {
            Mail::raw(
                'Correo de prueba de AprendizajeHC. Si lo recibes, la configuración SMTP funciona.',
                fn ($m) => $m->to($destino)->subject('Prueba de configuración SMTP - AprendizajeHC')
            );
            $this->line('   <fg=green>OK</> — correo entregado al servidor. Revisa la bandeja de entrada y spam.');
        } catch (\Throwable $e) {
            $this->reportarFallo($e);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Con la configuración en caché Laravel no lee el .env: un .env corregido sin
     * "php artisan config:clear" sigue enviando con los datos anteriores.
     */
    private function revisarCache(string $mailer, array $smtp): void
    {
        if (! app()->configurationIsCached()) {
            return;
        }

        $this->line('');
        $this->warn('La configuración está en caché: la aplicación NO está leyendo el .env.');

        try {
            $env = Dotenv::parse((string) @file_get_contents(app()->environmentFilePath()));
        } catch (\Throwable $e) {
            $this->warn('Además el .env tiene un error de sintaxis que tumbará el sitio al limpiar la caché: '
                . $e->getMessage());

            return;
        }

        $enUso = [
            'MAIL_MAILER' => $mailer,
            'MAIL_HOST' => $smtp['host'] ?? null,
            'MAIL_PORT' => $smtp['port'] ?? null,
            'MAIL_USERNAME' => $smtp['username'] ?? null,
            'MAIL_PASSWORD' => $smtp['password'] ?? null,
        ];

        $distintos = array_keys(array_filter(
            $enUso,
            fn ($valor, $clave) => array_key_exists($clave, $env)
                && (strtolower((string) $env[$clave]) === 'null' ? '' : (string) $env[$clave]) !== (string) $valor,
            ARRAY_FILTER_USE_BOTH
        ));

        if ($distintos) {
            $this->warn('El .env tiene otros valores en ' . implode(', ', $distintos) . ': se están usando los de la caché.');
        }

        $this->warn('Para que el .env tenga efecto ejecuta: php artisan config:clear');
    }

    /**
     * Abre conexiones sin autenticarse para separar un bloqueo del hosting de un
     * problema de credenciales. El saludo del servidor delata si el hosting
     * redirige el SMTP saliente a su propio servidor de correo.
     */
    private function revisarRed(string $host, int $puerto): void
    {
        $alterno = $puerto === 465 ? 587 : 465;
        $responde = [];

        foreach ([$puerto, $alterno] as $p) {
            [$ok, $detalle] = $this->sondear($host, $host, $p);
            $interceptado = $ok ? ! $this->saludoEsDe($host, $detalle) : MailFailure::esIntercepcion($detalle);
            $responde[$p] = $ok && ! $interceptado;

            $estado = match (true) {
                $interceptado => '<fg=red>OTRO SERVIDOR</>',
                $ok => '<fg=green>OK</>',
                default => '<fg=red>FALLÓ</>',
            };
            $this->line("   Puerto {$p}: {$estado} — {$detalle}");

            if ($interceptado) {
                $this->line('');
                $this->warn("Respondió un servidor que no es {$host}: el hosting redirige el SMTP saliente a su "
                    . 'propio servidor de correo (cPanel/WHM "SMTP Restrictions" o CSF con SMTP_BLOCK). Pide al '
                    . 'proveedor que lo desactive o que autorice a este usuario a usar SMTP externo.');

                return;
            }
        }

        if ($responde[$puerto]) {
            return;
        }

        $this->line('');

        if ($responde[$alterno]) {
            $esquema = $alterno === 465 ? 'smtps' : 'null';
            $this->warn("El puerto {$puerto} no responde pero el {$alterno} sí: usa MAIL_PORT={$alterno} y "
                . "MAIL_SCHEME={$esquema} en el .env.");

            return;
        }

        // Si por nombre falla pero la IPv4 directa responde, lo que falla es la salida IPv6.
        $ipv4 = gethostbyname($host);

        if ($ipv4 !== $host && $this->sondear($host, $ipv4, $puerto)[0]) {
            $this->warn("Por IPv4 ({$ipv4}) el puerto {$puerto} sí responde: el servidor intenta primero IPv6 "
                . 'y esa salida no funciona. Pide al proveedor corregir o desactivar la salida IPv6.');

            return;
        }

        $this->warn("El servidor no tiene salida hacia {$host} por los puertos {$puerto} ni {$alterno}: "
            . 'pide al proveedor o al área de TI que permita la salida SMTP.');
    }

    /**
     * Conecta sin autenticarse y devuelve el saludo del servidor. En el 465 el TLS
     * es implícito, así que el certificado se valida contra $host al conectar.
     *
     * @return array{0: bool, 1: string}
     */
    private function sondear(string $host, string $destino, int $puerto): array
    {
        $avisos = [];
        set_error_handler(function (int $nivel, string $mensaje) use (&$avisos) {
            $avisos[] = preg_replace('/^stream_socket_client\(\): /', '', $mensaje);

            return true;
        });

        try {
            $socket = stream_socket_client(
                ($puerto === 465 ? 'ssl://' : 'tcp://') . "{$destino}:{$puerto}",
                $codigo,
                $error,
                8,
                STREAM_CLIENT_CONNECT,
                stream_context_create(['ssl' => ['peer_name' => $host]])
            );
        } finally {
            restore_error_handler();
        }

        if (! $socket) {
            return [false, implode(' | ', $avisos) ?: trim($error) ?: 'sin detalle'];
        }

        stream_set_timeout($socket, 8);
        $saludo = trim((string) fgets($socket, 512));
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return $saludo === '' ? [false, 'conectó, pero el servidor no envió saludo'] : [true, $saludo];
    }

    /**
     * El saludo SMTP ("220 smtp.gmail.com ESMTP ...") nombra al servidor; si no
     * contiene el dominio de MAIL_HOST, respondió otro servidor.
     */
    private function saludoEsDe(string $host, string $saludo): bool
    {
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        $dominio = implode('.', array_slice(explode('.', $host), -2));

        return str_contains(strtolower($saludo), strtolower($dominio));
    }

    private function reportarFallo(\Throwable $e): void
    {
        $this->line('   <fg=red>FALLÓ</> (' . MailFailure::tipo($e) . ')');
        $this->line('   ' . str_replace("\n", "\n   ", trim($e->getMessage())));
        $this->line('');
        $this->warn(MailFailure::diagnostico($e));
    }
}
