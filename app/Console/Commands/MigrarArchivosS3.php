<?php

namespace App\Console\Commands;

use App\Models\PlantillaCertificado;
use App\Services\MediaStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MigrarArchivosS3 extends Command
{
    protected $signature = 'media:migrar-s3
                            {--origen=public : Disco local desde el que se copian los archivos}
                            {--sobrescribir : Vuelve a subir los archivos que ya existen en S3}
                            {--simular : Solo muestra lo que se haría, sin subir nada}';

    protected $description = 'Copia a Amazon S3 (carpeta aprendizajehc.huv) los archivos subidos que están en el almacenamiento local, conservando sus rutas.';

    public function handle(): int
    {
        if (!MediaStorage::isCloud()) {
            $this->error('El disco de archivos no es S3. Configure las variables AWS_* en el .env y ejecute "php artisan config:clear".');

            return self::FAILURE;
        }

        $s3 = config('filesystems.disks.' . MediaStorage::diskName());
        $this->line('');
        $this->info('Destino: s3://' . $s3['bucket'] . '/' . trim($s3['root'] ?? '', '/') . '/');

        try {
            MediaStorage::disk()->fileExists('.conexion');
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar con el bucket: ' . $e->getMessage());

            return self::FAILURE;
        }

        $fallidos = $this->copiarArchivos();

        if (!$this->option('simular')) {
            $this->generarFondosCertificados();
        }

        $faltantes = $this->referenciasFaltantes();

        $this->line('');
        if ($faltantes) {
            $this->warn(count($faltantes) . ' archivos referenciados en la base de datos no existen en S3:');
            $this->table(['Origen', 'Ruta'], $faltantes);
        } else {
            $this->info('Todos los archivos referenciados en la base de datos existen en S3.');
        }

        if (!$this->option('simular') && !$fallidos) {
            $this->line('');
            $this->line('Los archivos locales de storage/app/' . $this->option('origen') . ' no se borraron.');
            $this->line('Puede eliminarlos cuando haya verificado que todo se ve bien en la aplicación.');
        }

        return $fallidos ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Sube al bucket cada archivo del disco local con la misma ruta relativa.
     * Devuelve la cantidad de archivos que fallaron.
     */
    private function copiarArchivos(): int
    {
        $origen = Storage::disk($this->option('origen'));
        $destino = MediaStorage::disk();

        $archivos = array_values(array_filter(
            $origen->allFiles(),
            fn ($path) => !str_starts_with(basename($path), '.')
        ));

        $this->info(count($archivos) . ' archivos encontrados en el disco "' . $this->option('origen') . '".');

        $subidos = $omitidos = 0;
        $errores = [];

        $barra = $this->output->createProgressBar(count($archivos));
        $barra->start();

        foreach ($archivos as $path) {
            try {
                if (!$this->option('sobrescribir') && $destino->fileExists($path)) {
                    $omitidos++;
                } elseif ($this->option('simular')) {
                    $subidos++;
                } else {
                    $stream = $origen->readStream($path);
                    if (!$stream) {
                        throw new \RuntimeException('no se pudo leer el archivo local');
                    }

                    $destino->writeStream($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $subidos++;
                }
            } catch (\Throwable $e) {
                $errores[] = [$path, $e->getMessage()];
            }

            $barra->advance();
        }

        $barra->finish();
        $this->line('');
        $this->line('');

        $this->table(['Resultado', 'Archivos'], [
            [$this->option('simular') ? 'Se subirían' : 'Subidos', $subidos],
            ['Ya existían en S3 (omitidos)', $omitidos],
            ['Con error', count($errores)],
        ]);

        if ($errores) {
            $this->error('Archivos con error:');
            $this->table(['Ruta', 'Error'], $errores);
        }

        return count($errores);
    }

    /**
     * Las plantillas cuyo fondo solo está en base64 lo guardan como archivo en S3.
     */
    private function generarFondosCertificados(): void
    {
        if (!Schema::hasTable('plantilla_certificados')) {
            return;
        }

        $generados = 0;

        foreach (PlantillaCertificado::all() as $plantilla) {
            $antes = $plantilla->fondo_path;
            $plantilla->fondo_url;

            if ($plantilla->fondo_path && $plantilla->fondo_path !== $antes) {
                $generados++;
            }
        }

        if ($generados) {
            $this->info("{$generados} fondos de certificado generados en S3 desde la base de datos.");
        }
    }

    /**
     * Rutas guardadas en BD (y en los JSON de publicidad) que no existen en S3.
     */
    private function referenciasFaltantes(): array
    {
        $columnas = [
            ['curso_materiales', 'archivo_path'],
            ['cursos', 'imagen_portada'],
            ['curso_actividad_entrega', 'archivo_path'],
            ['curso_actividad_entregas', 'archivo_path'],
            ['welcome_banners', 'media_archivo'],
            ['plantilla_certificados', 'fondo_path'],
        ];

        $referencias = [];

        foreach ($columnas as [$tabla, $columna]) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, $columna)) {
                continue;
            }

            foreach (DB::table($tabla)->whereNotNull($columna)->where($columna, '!=', '')->pluck($columna) as $path) {
                $referencias[] = ["{$tabla}.{$columna}", $path];
            }
        }

        $productos = $this->leerJson('publicidad_productos.json');
        foreach ($productos as $producto) {
            if (!empty($producto['imagen'])) {
                $referencias[] = ['publicidad_productos.json', $producto['imagen']];
            }
        }

        $config = $this->leerJson('publicidad_config.json');
        if (!empty($config['banner_imagen'])) {
            $referencias[] = ['publicidad_config.json', $config['banner_imagen']];
        }

        $destino = MediaStorage::disk();
        $faltantes = [];

        foreach ($referencias as [$origen, $path]) {
            if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
                continue;
            }

            try {
                $existe = $destino->fileExists($path);
            } catch (\Throwable $e) {
                $existe = false;
            }

            if (!$existe) {
                $faltantes[] = [$origen, $path];
            }
        }

        return $faltantes;
    }

    private function leerJson(string $archivo): array
    {
        $path = storage_path('app/' . $archivo);

        if (!file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }
}
