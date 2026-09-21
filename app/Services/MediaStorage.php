<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Punto único de acceso a los archivos que suben los usuarios: materiales,
 * entregas, portadas de cursos, banners, publicidad y fondos de certificados.
 *
 * En producción el disco es Amazon S3 y todo vive dentro de la carpeta
 * "aprendizajehc.huv" del bucket (ver config/filesystems.php). Las rutas que se
 * guardan en BD son relativas a esa carpeta (p. ej. "cursos/5/materiales/x.pdf"),
 * por eso no cambian al pasar del disco local a S3.
 */
class MediaStorage
{
    /**
     * Segundos durante los cuales se reutiliza la misma URL firmada, para que
     * el navegador pueda cachear imágenes y videos entre páginas.
     */
    private const URL_WINDOW = 1800;

    public static function diskName(): string
    {
        return (string) config('filesystems.media', 'public');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(static::diskName());
    }

    /**
     * ¿Los archivos están en S3 (bucket privado, se sirven con URLs firmadas)?
     */
    public static function isCloud(): bool
    {
        return config('filesystems.disks.' . static::diskName() . '.driver') === 's3';
    }

    /**
     * Guarda un archivo subido dentro de $directory y devuelve su ruta relativa.
     */
    public static function store(UploadedFile $file, string $directory, ?string $name = null): string
    {
        $path = $name === null
            ? $file->store($directory, static::diskName())
            : $file->storeAs($directory, $name, static::diskName());

        if (!$path) {
            throw new \RuntimeException('No se pudo guardar el archivo "' . $file->getClientOriginalName() . '".');
        }

        return $path;
    }

    /**
     * Escribe contenido (string o recurso) en $path.
     */
    public static function put(string $path, $contents): void
    {
        if (!static::disk()->put($path, $contents)) {
            throw new \RuntimeException("No se pudo guardar el archivo {$path}.");
        }
    }

    public static function exists(?string $path): bool
    {
        if (!$path || static::isExternal($path)) {
            return false;
        }

        try {
            // fileExists: en S3 es una sola petición HEAD (exists() además lista "carpetas")
            return static::disk()->fileExists($path);
        } catch (\Throwable $e) {
            Log::warning("MediaStorage: no se pudo verificar {$path}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina el archivo. Los errores se registran pero no interrumpen la operación.
     */
    public static function delete(?string $path): void
    {
        if (!$path || static::isExternal($path)) {
            return;
        }

        try {
            static::disk()->delete($path);
        } catch (\Throwable $e) {
            Log::warning("MediaStorage: no se pudo eliminar {$path}: " . $e->getMessage());
        }
    }

    /**
     * URL para mostrar el archivo en el navegador.
     * S3: URL firmada temporal. Local: ruta /media/{path}. Las URLs externas
     * (http..., data:...) se devuelven tal cual.
     */
    public static function url(?string $path, array $params = []): ?string
    {
        if (!$path) {
            return null;
        }

        if (static::isExternal($path)) {
            return $path;
        }

        if (!static::isCloud()) {
            return url('media/' . ltrim($path, '/'));
        }

        return static::temporaryUrl($path, $params);
    }

    /**
     * URL firmada de S3. $params acepta parámetros de GetObject como
     * ResponseContentDisposition o ResponseCacheControl.
     *
     * La hora de firma se redondea a URL_WINDOW: la URL es idéntica durante esa
     * ventana (el navegador la cachea) y siempre queda válida al menos
     * "media_url_minutes" minutos desde que se genera.
     */
    public static function temporaryUrl(string $path, array $params = []): string
    {
        if (!static::isSafePath($path)) {
            throw new \InvalidArgumentException("Ruta de archivo no válida: {$path}");
        }

        $minutes = max(5, min((int) config('filesystems.media_url_minutes', 180), 10000));
        $start = intdiv(time(), self::URL_WINDOW) * self::URL_WINDOW;
        $expires = $start + self::URL_WINDOW + $minutes * 60;

        return static::disk()->temporaryUrl($path, $expires, $params + ['start_time' => $start]);
    }

    /**
     * Respuesta para ver en línea un archivo guardado en S3.
     *
     * Los PDF se transmiten desde el servidor: al ser del mismo origen, el visor
     * PDF.js funciona sin configurar CORS en el bucket. El resto (videos,
     * imágenes, otros) redirige a la URL firmada, que soporta rangos para
     * adelantar videos sin pasar los bytes por el servidor.
     */
    public static function cloudResponse(string $path, ?string $filename = null, array $headers = []): Response
    {
        $disposition = static::disposition('inline', $filename ?: basename($path));

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            try {
                $stream = static::disk()->readStream($path);
            } catch (\Throwable $e) {
                Log::warning("MediaStorage: no se pudo leer {$path}: " . $e->getMessage());
                $stream = null;
            }

            if (!$stream) {
                abort(404, 'Archivo no disponible');
            }

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, $headers + [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition,
            ]);
        }

        $params = ['ResponseContentDisposition' => $disposition];
        if (isset($headers['Cache-Control'])) {
            $params['ResponseCacheControl'] = $headers['Cache-Control'];
        }

        return redirect()->away(static::temporaryUrl($path, $params));
    }

    /**
     * Cabecera Content-Disposition segura para nombres con tildes, espacios, etc.
     */
    public static function disposition(string $type, string $filename): string
    {
        $filename = str_replace(['/', '\\'], '-', $filename);
        $fallback = str_replace(['%', '"'], '', Str::ascii($filename)) ?: 'archivo';

        return HeaderUtils::makeDisposition($type, $filename, $fallback);
    }

    /**
     * Rechaza rutas vacías o que intenten salir de la carpeta de la aplicación ("../").
     */
    public static function isSafePath(string $path): bool
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        return $path !== '' && !in_array('..', explode('/', $path), true);
    }

    private static function isExternal(string $path): bool
    {
        return (bool) preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:');
    }
}
