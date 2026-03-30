<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
     * Intenta guardar el base64 como archivo para eficiencia, pero si el
     * enlace simbólico storage/ no funciona (ej. cPanel), devuelve el
     * base64 directamente como data URI que siempre funciona.
     */
    public function getFondoUrlAttribute(): ?string
    {
        try {
            // Extraer el base64 del JSON
            $base64 = $this->elementos_json['fondo_base64'] ?? null;
            if (!$base64) {
                return null;
            }

            // Verificar que el symlink public/storage existe y es accesible
            $storageLinkPath = public_path('storage');
            $storageLinkWorks = is_dir($storageLinkPath) || is_link($storageLinkPath);

            if (!$storageLinkWorks) {
                // Sin symlink (típico en cPanel) → devolver base64 directamente
                return $base64;
            }

            // Detectar extensión real del formato de imagen
            $extension = 'png';
            if (str_contains($base64, 'data:image/jpeg') || str_contains($base64, 'data:image/jpg')) {
                $extension = 'jpg';
            } elseif (str_contains($base64, 'data:image/webp')) {
                $extension = 'webp';
            } elseif (str_contains($base64, 'data:image/gif')) {
                $extension = 'gif';
            }

            // Ruta relativa del archivo de fondo (con extensión correcta)
            $relativePath = 'certificados/fondos/plantilla_' . $this->id . '.' . $extension;

            // Si ya existe con extensión correcta Y es accesible públicamente
            if (Storage::disk('public')->exists($relativePath)) {
                $publicFile = public_path('storage/' . $relativePath);
                if (file_exists($publicFile)) {
                    return asset('storage/' . $relativePath);
                }
                // El symlink existe pero el archivo no es accesible → usar base64
                return $base64;
            }

            // Limpiar archivos con extensión incorrecta
            foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
                $oldPath = 'certificados/fondos/plantilla_' . $this->id . '.' . $ext;
                if ($ext !== $extension && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
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

            // Detectar formato real por magic bytes si extensión sigue como png
            if ($extension === 'png' && strlen($decodedImage) >= 2) {
                $firstBytes = unpack('C2', $decodedImage);
                if ($firstBytes[1] === 0xFF && $firstBytes[2] === 0xD8) {
                    $extension = 'jpg';
                    $relativePath = 'certificados/fondos/plantilla_' . $this->id . '.' . $extension;
                }
            }

            // Guardar como archivo
            Storage::disk('public')->put($relativePath, $decodedImage);
            $this->update(['fondo_path' => $relativePath]);

            // Verificar que el archivo público sea accesible
            $publicFile = public_path('storage/' . $relativePath);
            if (file_exists($publicFile)) {
                return asset('storage/' . $relativePath);
            }

            // Archivo guardado pero no accesible públicamente → usar base64
            return $base64;
        } catch (\Throwable $e) {
            Log::warning("PlantillaCertificado #{$this->id}: Error fondo URL - " . $e->getMessage());

            // Fallback: base64 siempre funciona
            return $this->elementos_json['fondo_base64'] ?? null;
        }
    }
}
