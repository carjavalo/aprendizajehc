<?php

namespace App\Models;

use App\Services\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PlantillaCertificado extends Model
{
    protected $fillable = [
        'nombre',
        'fondo_path',
        'elementos_json',
        'html_content',
    ];

    protected $casts = [
        'elementos_json' => 'array',
    ];

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'plantilla_certificado_id');
    }

    /**
     * Obtener la URL del fondo del certificado.
     * El fondo llega como base64 dentro de elementos_json; la primera vez se
     * guarda como archivo en el almacenamiento (S3) y luego se sirve desde allí.
     * Si algo falla se devuelve el base64 como data URI, que siempre funciona.
     */
    public function getFondoUrlAttribute(): ?string
    {
        $base64 = $this->elementos_json['fondo_base64'] ?? null;

        try {
            if ($this->fondo_path && MediaStorage::exists($this->fondo_path)) {
                return MediaStorage::url($this->fondo_path);
            }

            if (!$base64) {
                return null;
            }

            // Decodificar base64
            $imageData = $base64;
            if (str_contains($base64, ',')) {
                $imageData = substr($base64, strpos($base64, ',') + 1);
            }

            $decodedImage = base64_decode($imageData);
            if ($decodedImage === false) {
                return $base64; // Fallback al base64 original
            }

            // Detectar extensión real del formato de imagen
            $extension = 'png';
            if (str_contains($base64, 'data:image/jpeg') || str_contains($base64, 'data:image/jpg')) {
                $extension = 'jpg';
            } elseif (str_contains($base64, 'data:image/webp')) {
                $extension = 'webp';
            } elseif (str_contains($base64, 'data:image/gif')) {
                $extension = 'gif';
            } elseif (strlen($decodedImage) >= 2 && str_starts_with($decodedImage, "\xFF\xD8")) {
                // Magic bytes de JPEG
                $extension = 'jpg';
            }

            $relativePath = 'certificados/fondos/plantilla_' . $this->id . '.' . $extension;

            // Guardar como archivo
            MediaStorage::put($relativePath, $decodedImage);
            $this->update(['fondo_path' => $relativePath]);

            return MediaStorage::url($relativePath);
        } catch (\Throwable $e) {
            Log::warning("PlantillaCertificado #{$this->id}: Error fondo URL - " . $e->getMessage());

            // Fallback: base64 siempre funciona
            return $base64;
        }
    }

    /**
     * Elimina el archivo de fondo guardado y lo vuelve a generar desde el base64.
     */
    public function regenerarFondo(): void
    {
        MediaStorage::delete($this->fondo_path);
        $this->update(['fondo_path' => null]);

        $this->fondo_url;
    }
}
