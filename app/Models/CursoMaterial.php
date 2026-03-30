<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoMaterial extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'curso_materiales';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'curso_id',
        'titulo',
        'descripcion',
        'tipo',
        'archivo_path',
        'archivo_nombre',
        'archivo_extension',
        'archivo_size',
        'url_externa',
        'orden',
        'es_publico',
        'fecha_inicio',
        'fecha_fin',
        'prerequisite_id',
        'porcentaje_curso',
        'nota_minima_aprobacion',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'archivo_size' => 'integer',
        'orden' => 'integer',
        'es_publico' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'prerequisite_id' => 'integer',
        'porcentaje_curso' => 'decimal:2',
        'nota_minima_aprobacion' => 'decimal:2',
    ];

    /**
     * Relación con el curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Relación con el material prerrequisito
     */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(CursoMaterial::class, 'prerequisite_id');
    }

    /**
     * Materiales que dependen de este como prerrequisito
     */
    public function dependientes(): HasMany
    {
        return $this->hasMany(CursoMaterial::class, 'prerequisite_id');
    }

    /**
     * Accessors
     */
    public function getArchivoUrlAttribute(): ?string
    {
        if ($this->archivo_path && file_exists(public_path('storage/' . $this->archivo_path))) {
            return asset('storage/' . $this->archivo_path);
        }
        
        return $this->url_externa;
    }

    public function getArchivoSizeFormattedAttribute(): string
    {
        if (!$this->archivo_size) {
            return 'N/A';
        }

        $bytes = $this->archivo_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTipoIconAttribute(): string
    {
        $icons = [
            'archivo' => 'fas fa-file',
            'video' => 'fas fa-video',
            'imagen' => 'fas fa-image',
            'documento' => 'fas fa-file-alt',
            'clase_en_linea' => 'fas fa-video',
        ];

        return $icons[$this->tipo] ?? 'fas fa-file';
    }

    public function getTipoBadgeAttribute(): string
    {
        $badges = [
            'archivo' => '<span class="badge badge-info">Archivo</span>',
            'video' => '<span class="badge badge-danger">Video</span>',
            'imagen' => '<span class="badge badge-success">Imagen</span>',
            'documento' => '<span class="badge badge-primary">Documento</span>',
            'clase_en_linea' => '<span class="badge badge-warning">Clase en Línea</span>',
        ];

        return $badges[$this->tipo] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }

    /**
     * Scopes
     */
    public function scopePublicos($query)
    {
        return $query->where('es_publico', true);
    }

    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Relación con las actividades del material
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(CursoActividad::class, 'material_id');
    }

    /**
     * Obtener el porcentaje total asignado a actividades de este material
     */
    public function getPorcentajeActividadesAsignadoAttribute(): float
    {
        return $this->actividades()->sum('porcentaje_curso') ?? 0;
    }

    /**
     * Obtener el porcentaje disponible para nuevas actividades
     * Los porcentajes de actividades son relativos al material (suman hasta 100%)
     */
    public function getPorcentajeDisponibleActividadesAttribute(): float
    {
        return 100 - $this->porcentaje_actividades_asignado;
    }

    /**
     * Validar que los porcentajes de actividades no excedan 100%
     * Los porcentajes de actividades representan su peso DENTRO del material (0-100%)
     */
    public function validarPorcentajeActividades(float $nuevoPorcentaje, ?int $actividadIdExcluir = null): bool
    {
        $porcentajeActual = $this->actividades()
            ->when($actividadIdExcluir, function($query) use ($actividadIdExcluir) {
                return $query->where('id', '!=', $actividadIdExcluir);
            })
            ->sum('porcentaje_curso');
        
        return ($porcentajeActual + $nuevoPorcentaje) <= 100;
    }

    /**
     * Calcular la nota de un estudiante en este material
     * La nota se calcula sumando las notas ponderadas de todas las actividades
     * 
     * Los porcentaje_curso de las actividades representan su peso DENTRO del material
     * (suman 100% entre las actividades de un mismo material), NO son porcentajes
     * absolutos del curso. Por eso se normaliza dividiendo entre la suma total
     * de porcentajes de actividades del material.
     */
    public function calcularNotaEstudiante($userId): float
    {
        if ($this->porcentaje_curso == 0) {
            return 0;
        }

        $notaMaterial = 0;
        
        // Calcular el total de pesos de actividades para normalizar correctamente
        $totalPesoActividades = $this->actividades->sum('porcentaje_curso');
        
        if ($totalPesoActividades <= 0) {
            return 0;
        }
        
        foreach ($this->actividades as $actividad) {
            $notaActividad = $actividad->calcularNotaEstudiante($userId);
            // La nota de la actividad ya está en escala 0-5.0
            // Ponderamos según el porcentaje de la actividad respecto al total de actividades del material
            // Ej: si actividad tiene 20% y total actividades es 100%, pesoRelativo = 0.20
            $pesoRelativo = floatval($actividad->porcentaje_curso ?? 0) / $totalPesoActividades;
            $notaMaterial += ($notaActividad * $pesoRelativo);
        }
        
        return min(round($notaMaterial, 2), 5.0);
    }

    /**
     * Verificar si un estudiante aprobó el material
     */
    public function estudianteAprobo($userId): bool
    {
        $nota = $this->calcularNotaEstudiante($userId);
        return $nota >= $this->nota_minima_aprobacion;
    }

    /**
     * Obtener resumen de actividades de un estudiante en este material
     */
    public function getResumenActividadesEstudiante($userId): array
    {
        $resumen = [];
        
        foreach ($this->actividades as $actividad) {
            $resumen[] = [
                'id' => $actividad->id,
                'titulo' => $actividad->titulo,
                'tipo' => $actividad->tipo,
                'porcentaje_curso' => $actividad->porcentaje_curso,
                'nota_obtenida' => $actividad->calcularNotaEstudiante($userId),
            ];
        }
        
        return $resumen;
    }
}
