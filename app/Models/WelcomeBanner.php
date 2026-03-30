<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeBanner extends Model
{
    use HasFactory;

    protected $table = 'welcome_banners';

    protected $fillable = [
        'banner_titulo',
        'banner_subtitulo',
        'banner_color_fondo',
        'banner_color_texto',
        'media_tipo',
        'media_archivo',
        'media_url',
        'media_titulo',
        'activo',
        'orden',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Obtener solo los banners activos ordenados.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    /**
     * Obtener banners activos y vigentes por fecha.
     */
    public function scopeVigentes($query)
    {
        $hoy = now()->toDateString();
        return $query->where('activo', true)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy);
            })
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
            })
            ->orderBy('orden');
    }

    /**
     * Verificar si el banner está vigente por fechas.
     */
    public function estaVigente()
    {
        $hoy = now()->toDateString();
        if ($this->fecha_inicio && $this->fecha_inicio->toDateString() > $hoy) return false;
        if ($this->fecha_fin && $this->fecha_fin->toDateString() < $hoy) return false;
        return true;
    }

    /**
     * Obtener la URL completa del media.
     */
    public function getMediaUrlCompleta()
    {
        if ($this->media_url) {
            return $this->media_url;
        }

        if ($this->media_archivo) {
            return asset('storage/' . $this->media_archivo);
        }

        return null;
    }

    /**
     * Verificar si es un video de YouTube.
     */
    public function esYoutube()
    {
        if (!$this->media_url) return false;
        return str_contains($this->media_url, 'youtube.com') || str_contains($this->media_url, 'youtu.be');
    }

    /**
     * Obtener el embed URL de YouTube.
     */
    public function getYoutubeEmbedUrl()
    {
        if (!$this->esYoutube()) return null;

        $url = $this->media_url;

        // youtube.com/watch?v=ID
        if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // youtu.be/ID
        if (preg_match('/youtu\.be\/([^?&]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Already embed URL
        if (str_contains($url, '/embed/')) {
            return $url;
        }

        return null;
    }
}
