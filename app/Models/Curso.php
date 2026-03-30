<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Curso extends Model
{
    use HasFactory;

    /**
     * Nota máxima del curso (constante)
     */
    const NOTA_MAXIMA = 5.0;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'id_area',
        'instructor_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'plantilla_certificado_id',
        'codigo_acceso',
        'max_estudiantes',
        'imagen_portada',
        'objetivos',
        'requisitos',
        'duracion_horas',
        'nota_minima_aprobacion',
        'nota_maxima',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'max_estudiantes' => 'integer',
        'duracion_horas' => 'integer',
        'nota_minima_aprobacion' => 'decimal:2',
        'nota_maxima' => 'decimal:2',
    ];

    /**
     * Boot method para generar código de acceso automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($curso) {
            if (empty($curso->codigo_acceso)) {
                $curso->codigo_acceso = static::generateUniqueCode();
            }
        });
    }

    /**
     * Generar código único de acceso
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('codigo_acceso', $code)->exists());

        return $code;
    }

    /**
     * Relación con el área
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    /**
     * Relación con el instructor
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Verifica si un usuario es docente asignado a este curso vía CursoAsignacion
     */
    public function esDocente(int $userId): bool
    {
        return \App\Models\CursoAsignacion::where('curso_id', $this->id)
            ->where('docente_id', $userId)
            ->where('estado', 'activo')
            ->exists();
    }

    /**
     * Verifica si el usuario puede gestionar este curso:
     * - Es el creador (instructor_id) del curso
     * - Tiene permisos de gestión (Admin/Operador)
     * - Es un docente asignado a este curso vía CursoAsignacion
     */
    public function esGestorODocente(\App\Models\User $user): bool
    {
        return $this->instructor_id === $user->id
            || $user->tienePermisoGestion()
            || $this->esDocente($user->id);
    }

    /**
     * Relación con estudiantes inscritos
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'curso_estudiantes', 'curso_id', 'estudiante_id')
                    ->withPivot(['estado', 'progreso', 'fecha_inscripcion', 'ultima_actividad'])
                    ->withTimestamps();
    }

    /**
     * Relación con materiales del curso
     */
    public function materiales(): HasMany
    {
        return $this->hasMany(CursoMaterial::class)->orderBy('orden');
    }

    /**
     * Relación con foros del curso
     */
    public function foros(): HasMany
    {
        return $this->hasMany(CursoForo::class)->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relación con actividades del curso
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(CursoActividad::class)->orderBy('fecha_apertura');
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('id_area', $areaId);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Accessors
     */
    public function getAreaDescripcionAttribute(): string
    {
        return $this->area ? $this->area->descripcion : 'Sin área';
    }

    public function getInstructorNombreAttribute(): string
    {
        return $this->instructor ? $this->instructor->name . ' ' . $this->instructor->apellido1 : 'Sin instructor';
    }

    public function getEstudiantesCountAttribute(): int
    {
        return $this->estudiantes()->count();
    }

    public function getEstadoBadgeAttribute(): string
    {
        $badges = [
            'borrador' => '<span class="badge badge-secondary">Borrador</span>',
            'activo' => '<span class="badge badge-success">Activo</span>',
            'finalizado' => '<span class="badge badge-primary">Finalizado</span>',
            'archivado' => '<span class="badge badge-dark">Archivado</span>',
        ];

        return $badges[$this->estado] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }

    public function getImagenPortadaUrlAttribute(): string
    {
        if ($this->imagen_portada && file_exists(public_path('storage/' . $this->imagen_portada))) {
            return asset('storage/' . $this->imagen_portada);
        }
        
        return asset('assets/img/default-course.jpg');
    }

    /**
     * Verificar si un usuario está inscrito en el curso
     */
    public function tieneEstudiante($userId): bool
    {
        // Verificar en curso_estudiantes (inscripción directa)
        $inscritoDirecto = $this->estudiantes()->wherePivot('estudiante_id', $userId)->exists();
        
        if ($inscritoDirecto) {
            return true;
        }
        
        // Verificar en curso_asignaciones (asignación por admin)
        $asignado = DB::table('curso_asignaciones')
            ->where('curso_id', $this->id)
            ->where('estudiante_id', $userId)
            ->where('estado', 'activo')
            ->exists();
        
        return $asignado;
    }

    /**
     * Obtener progreso de un estudiante
     */
    public function getProgresoEstudiante($userId): int
    {
        // Calcular progreso basado en materiales vistos y actividades completadas
        $totalMateriales = $this->materiales()->where('es_publico', true)->count();
        $totalActividades = $this->actividades()->count();
        $totalItems = $totalMateriales + $totalActividades;

        if ($totalItems === 0) {
            return 0;
        }

        // Contar materiales vistos
        $materialesVistos = DB::table('curso_material_visto')
            ->where('curso_id', $this->id)
            ->where('user_id', $userId)
            ->count();

        // Contar actividades completadas
        $actividadesCompletadas = DB::table('curso_actividad_entrega')
            ->where('curso_id', $this->id)
            ->where('user_id', $userId)
            ->count();

        $itemsCompletados = $materialesVistos + $actividadesCompletadas;

        return round(($itemsCompletados / $totalItems) * 100);
    }

    /**
     * Actualizar progreso de un estudiante en la tabla pivot
     */
    public function actualizarProgresoEstudiante($userId): void
    {
        $progreso = $this->getProgresoEstudiante($userId);

        // Usar la clave correcta para la relación many-to-many
        $this->estudiantes()->wherePivot('estudiante_id', $userId)->update([
            'progreso' => $progreso,
            'ultima_actividad' => now(),
        ]);
    }

    /**
     * Obtener el porcentaje total asignado a materiales
     */
    public function getPorcentajeMaterialesAsignadoAttribute(): float
    {
        return $this->materiales()->sum('porcentaje_curso') ?? 0;
    }

    /**
     * Obtener el porcentaje disponible para nuevos materiales
     */
    public function getPorcentajeDisponibleMaterialesAttribute(): float
    {
        return 100 - $this->porcentaje_materiales_asignado;
    }

    /**
     * Validar que los porcentajes de materiales no excedan 100%
     */
    public function validarPorcentajeMateriales(float $nuevoPorcentaje, ?int $materialIdExcluir = null): bool
    {
        $porcentajeActual = $this->materiales()
            ->when($materialIdExcluir, function($query) use ($materialIdExcluir) {
                return $query->where('id', '!=', $materialIdExcluir);
            })
            ->sum('porcentaje_curso');
        
        return ($porcentajeActual + $nuevoPorcentaje) <= 100;
    }

    /**
     * Calcular la nota final de un estudiante en el curso
     * La nota se calcula sumando las notas ponderadas de todos los materiales
     */
    public function calcularNotaFinalEstudiante($userId): float
    {
        $notaFinal = 0;
        
        foreach ($this->materiales as $material) {
            $notaMaterial = $material->calcularNotaEstudiante($userId);
            // Ponderar la nota del material según su porcentaje en el curso
            $notaFinal += ($notaMaterial * floatval($material->porcentaje_curso ?? 0) / 100);
        }
        
        // La nota final está en escala 0-5.0
        return min(round($notaFinal, 2), self::NOTA_MAXIMA);
    }

    /**
     * Verificar si un estudiante aprobó el curso
     */
    public function estudianteAprobo($userId): bool
    {
        $notaFinal = $this->calcularNotaFinalEstudiante($userId);
        return $notaFinal >= $this->nota_minima_aprobacion;
    }

    /**
     * Obtener resumen de calificaciones de un estudiante
     */
    public function getResumenCalificacionesEstudiante($userId): array
    {
        $resumen = [
            'nota_final' => $this->calcularNotaFinalEstudiante($userId),
            'nota_minima_aprobacion' => $this->nota_minima_aprobacion,
            'aprobado' => $this->estudianteAprobo($userId),
            'materiales' => [],
        ];

        foreach ($this->materiales as $material) {
            $notaMaterial = $material->calcularNotaEstudiante($userId);
            $resumen['materiales'][] = [
                'id' => $material->id,
                'titulo' => $material->titulo,
                'porcentaje_curso' => $material->porcentaje_curso,
                'nota_minima' => $material->nota_minima_aprobacion,
                'nota_obtenida' => $notaMaterial,
                'aprobado' => $notaMaterial >= $material->nota_minima_aprobacion,
                'actividades' => $material->getResumenActividadesEstudiante($userId),
            ];
        }

        return $resumen;
    }

    public function plantillaCertificado()
    {
        return $this->belongsTo(PlantillaCertificado::class, 'plantilla_certificado_id');
    }
}
