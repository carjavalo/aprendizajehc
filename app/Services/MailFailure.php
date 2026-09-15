<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Traduce un fallo de envío de correo a un mensaje entendible para el usuario
 * y a un diagnóstico preciso para el administrador.
 *
 * Antes se mostraba siempre "la configuración SMTP de credenciales de Google es
 * incorrecta", incluso cuando la causa era otra (host inalcanzable, buzón
 * rechazado, plantilla rota...). Aquí se distingue el motivo real.
 */
class MailFailure
{
    public const AUTENTICACION = 'autenticacion';
    public const CONEXION = 'conexion';
    public const DESTINATARIO = 'destinatario';
    public const DESCONOCIDO = 'desconocido';

    /**
     * Clasifica la excepción en uno de los tipos anteriores.
     */
    public static function tipo(\Throwable $e): string
    {
        $mensaje = $e->getMessage();

        if (str_contains($mensaje, 'Failed to authenticate')
            || str_contains($mensaje, '535')
            || str_contains($mensaje, '534')
            || str_contains($mensaje, 'BadCredentials')) {
            return self::AUTENTICACION;
        }

        if (str_contains($mensaje, 'Connection could not be established')
            || str_contains($mensaje, 'Connection refused')
            || str_contains($mensaje, 'Connection timed out')
            || str_contains($mensaje, 'getaddrinfo')
            || str_contains($mensaje, 'SSL')
            || str_contains($mensaje, 'stream_socket_client')) {
            return self::CONEXION;
        }

        if (str_contains($mensaje, '550')
            || str_contains($mensaje, '553')
            || str_contains($mensaje, 'Recipient')
            || str_contains($mensaje, 'Address')) {
            return self::DESTINATARIO;
        }

        return $e instanceof TransportExceptionInterface ? self::CONEXION : self::DESCONOCIDO;
    }

    /**
     * Mensaje que se muestra al usuario final. No expone detalles técnicos.
     */
    public static function mensaje(\Throwable $e): string
    {
        return match (self::tipo($e)) {
            self::DESTINATARIO => 'No pudimos entregar el correo en la dirección indicada. '
                . 'Verifica que esté bien escrita o contacta al administrador.',
            default => 'No fue posible enviar el correo en este momento porque el servidor de '
                . 'correo no está disponible. Tu cuenta quedó creada: intenta solicitar el '
                . 'enlace nuevamente en unos minutos o contacta al administrador.',
        };
    }

    /**
     * Diagnóstico para el log / administrador, con la acción concreta a tomar.
     */
    public static function diagnostico(\Throwable $e): string
    {
        return match (self::tipo($e)) {
            self::AUTENTICACION => 'El servidor SMTP rechazó las credenciales (MAIL_USERNAME / '
                . 'MAIL_PASSWORD). Si se usa Gmail o Google Workspace, la contraseña de aplicación '
                . 'expiró o fue revocada: genera una nueva y actualiza MAIL_PASSWORD en el .env.',
            self::CONEXION => 'No se pudo abrir la conexión con el servidor SMTP. Revisa '
                . 'MAIL_HOST, MAIL_PORT y que el firewall del servidor permita la salida.',
            self::DESTINATARIO => 'El servidor SMTP rechazó la dirección de destino.',
            default => 'Fallo no clasificado al enviar el correo.',
        };
    }
}
