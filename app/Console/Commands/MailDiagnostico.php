<?php

namespace App\Console\Commands;

use App\Services\MailFailure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailDiagnostico extends Command
{
    protected $signature = 'mail:diagnostico {destino? : Dirección a la que enviar el correo de prueba}';

    protected $description = 'Verifica la configuración SMTP: conexión, autenticación y envío real.';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $smtp = config('mail.mailers.smtp');

        $this->line('');
        $this->info('Configuración activa');
        $this->table(['Parámetro', 'Valor'], [
            ['MAIL_MAILER', $mailer],
            ['MAIL_HOST', $smtp['host'] ?? '—'],
            ['MAIL_PORT', $smtp['port'] ?? '—'],
            ['MAIL_USERNAME', $smtp['username'] ?: '(vacío)'],
            ['MAIL_PASSWORD', $smtp['password'] ? '(definida, ' . strlen($smtp['password']) . ' caracteres)' : '(vacía)'],
            ['MAIL_SCHEME', $smtp['scheme'] ?: '(auto)'],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
        ]);

        if ($mailer !== 'smtp') {
            $this->warn("MAIL_MAILER está en \"{$mailer}\", no en \"smtp\". No se probará el servidor SMTP.");

            return self::SUCCESS;
        }

        // Paso 1: conexión + autenticación, sin enviar nada todavía.
        $this->line('');
        $this->info('1) Conexión y autenticación con el servidor SMTP');

        $transport = new EsmtpTransport(
            $smtp['host'],
            (int) $smtp['port'],
            $smtp['scheme'] === 'smtps'
        );
        $transport->setUsername((string) $smtp['username']);
        $transport->setPassword((string) $smtp['password']);

        try {
            $transport->start();
            $transport->stop();
            $this->line('   <fg=green>OK</> — el servidor aceptó las credenciales.');
        } catch (\Throwable $e) {
            $this->line('   <fg=red>FALLÓ</> (' . MailFailure::tipo($e) . ')');
            $this->line('   ' . str_replace("\n", "\n   ", trim($e->getMessage())));
            $this->line('');
            $this->warn(MailFailure::diagnostico($e));

            return self::FAILURE;
        }

        // Paso 2: envío real, solo si se indicó un destinatario.
        $destino = $this->argument('destino');

        if (! $destino) {
            $this->line('');
            $this->comment('Para probar además un envío real: php artisan mail:diagnostico tu@correo.com');

            return self::SUCCESS;
        }

        $this->line('');
        $this->info("2) Envío de prueba a {$destino}");

        try {
            Mail::raw(
                'Correo de prueba de AprendizajeHC. Si lo recibes, la configuración SMTP funciona.',
                fn ($m) => $m->to($destino)->subject('Prueba de configuración SMTP - AprendizajeHC')
            );
            $this->line('   <fg=green>OK</> — correo entregado al servidor. Revisa la bandeja de entrada y spam.');
        } catch (\Throwable $e) {
            $this->line('   <fg=red>FALLÓ</> (' . MailFailure::tipo($e) . ')');
            $this->line('   ' . str_replace("\n", "\n   ", trim($e->getMessage())));
            $this->line('');
            $this->warn(MailFailure::diagnostico($e));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
