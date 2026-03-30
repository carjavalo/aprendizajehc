<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CertificadoEmitido extends Model
{
    use HasFactory;

    protected $table = 'certificados_emitidos';

    protected $fillable = [
        'curso_id',
        'estudiante_id',
        'plantilla_id',
        'codigo_verificacion',
        'nota_final',
        'fecha_emision',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'nota_final' => 'decimal:2',
    ];

    /**
     * Generar un código de verificación único.
     * Formato: SHC-XXXXXXXX-XXXX (alfanumérico, 16 caracteres útiles)
     */
    public static function generarCodigo(): string
    {
        do {
            $codigo = 'SHC-' . strtoupper(Str::random(8)) . '-' . strtoupper(Str::random(4));
        } while (self::where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    /**
     * Obtener o crear el certificado para un estudiante en un curso.
     */
    public static function obtenerOCrear(int $cursoId, int $estudianteId, float $notaFinal, ?int $plantillaId = null): self
    {
        return self::firstOrCreate(
            [
                'curso_id' => $cursoId,
                'estudiante_id' => $estudianteId,
            ],
            [
                'plantilla_id' => $plantillaId,
                'codigo_verificacion' => self::generarCodigo(),
                'nota_final' => $notaFinal,
                'fecha_emision' => now(),
            ]
        );
    }

    /**
     * Obtener la URL pública de verificación.
     * Usa el host real de la solicitud actual para funcionar tanto en red local como pública.
     */
    public function getUrlVerificacionAttribute(): string
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        return $baseUrl . '/verificar-certificado/' . $this->codigo_verificacion;
    }

    // ── Relaciones ──

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function plantilla()
    {
        return $this->belongsTo(PlantillaCertificado::class, 'plantilla_id');
    }
}
