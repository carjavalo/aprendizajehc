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

        // Los códigos SMTP se buscan con el formato de Symfony (code "535") y no
        // como número suelto: el nombre de un servidor de hosting (server535...)
        // haría pasar un fallo de conexión por uno de credenciales.
        if (str_contains($mensaje, 'Failed to authenticate')
            || str_contains($mensaje, 'BadCredentials')
            || preg_match('/code "53[45]"/', $mensaje)) {
            return self::AUTENTICACION;
        }

        if (str_contains($mensaje, 'Connection could not be established')
            || str_contains($mensaje, 'Connection refused')
            || str_contains($mensaje, 'timed out')
            || str_contains($mensaje, 'getaddrinfo')
            || str_contains($mensaje, 'SSL')
            || str_contains($mensaje, 'STARTTLS')
            || str_contains($mensaje, 'certificate')
            || str_contains($mensaje, 'stream_socket')) {
            return self::CONEXION;
        }

        if (preg_match('/code "55[0-3]"/', $mensaje)
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
            // Reintentar no sirve si las credenciales están mal: se pide acudir al administrador.
            self::AUTENTICACION => 'No fue posible enviar el correo porque la cuenta de correo del '
                . 'sistema no está bien configurada. Tu cuenta quedó creada: contacta al '
                . 'administrador para que corrija el envío de correos.',
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
            self::CONEXION => self::diagnosticoConexion($e->getMessage()),
            self::DESTINATARIO => 'El servidor SMTP rechazó la dirección de destino.',
            default => 'Fallo no clasificado al enviar el correo.',
        };
    }

    /**
     * El certificado TLS recibido no es el del servidor pedido: la conexión llegó
     * a otro servidor (típico de un cPanel que redirige el SMTP saliente).
     */
    public static function esIntercepcion(string $mensaje): bool
    {
        return str_contains($mensaje, 'did not match expected CN');
    }

    /**
     * Distingue las causas de conexión típicas de un hosting cPanel, que exigen
     * acciones distintas (cambiar el .env o pedir un cambio al proveedor).
     */
    private static function diagnosticoConexion(string $mensaje): string
    {
        if (self::esIntercepcion($mensaje)) {
            return 'Respondió un servidor distinto a MAIL_HOST (su certificado TLS no corresponde): '
                . 'el hosting está redirigiendo el SMTP saliente a su propio servidor de correo. En '
                . 'cPanel/WHM es la opción "SMTP Restrictions" (o CSF con SMTP_BLOCK). Pide al '
                . 'proveedor que la desactive o que autorice a este usuario a usar SMTP externo.';
        }

        if (str_contains($mensaje, 'certificate verify failed')) {
            return 'No se pudo validar el certificado TLS del servidor SMTP: o el hosting redirige la '
                . 'conexión a otro servidor, o PHP no tiene los certificados raíz (openssl.cafile).';
        }

        if (str_contains($mensaje, 'timed out')) {
            return 'La conexión con MAIL_HOST expiró sin respuesta: un firewall está descartando el '
                . 'tráfico saliente a ese puerto (en cPanel suele ser CSF/SMTP_BLOCK o el firewall del '
                . 'proveedor). Prueba MAIL_PORT=465 con MAIL_SCHEME=smtps o pide abrir el puerto.';
        }

        if (str_contains($mensaje, 'Connection refused')) {
            return 'La conexión con MAIL_HOST fue rechazada: el puerto está bloqueado a la salida. '
                . 'Prueba MAIL_PORT=465 con MAIL_SCHEME=smtps o pide al proveedor abrir el puerto.';
        }

        if (str_contains($mensaje, 'getaddrinfo') || str_contains($mensaje, 'Name or service not known')) {
            return 'No se pudo resolver MAIL_HOST en DNS: revisa que esté bien escrito.';
        }

        return 'No se pudo abrir la conexión con el servidor SMTP. Revisa MAIL_HOST, MAIL_PORT y '
            . 'que el firewall del servidor permita la salida.';
    }
}
