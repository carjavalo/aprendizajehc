<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class CursoActividad extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'curso_actividades';

    /**
     * Nota máxima para actividades (constante)
     */
    const NOTA_MAXIMA = 5.0;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'curso_id',
        'material_id',
        'titulo',
        'descripcion',
        'tipo',
        'instrucciones',
        'contenido_json',
        'fecha_apertura',
        'fecha_cierre',
        'puntos_maximos',
        'puntos_max',
        'duracion_minutos',
        'permite_entregas_tardias',
        'intentos_permitidos',
        'es_obligatoria',
        'habilitado',
        'linked_material_ids',
        'prerequisite_activity_ids',
        'porcentaje_curso',
        'nota_minima_aprobacion',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'contenido_json' => 'array',
        'linked_material_ids' => 'array',
        'prerequisite_activity_ids' => 'array',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'puntos_maximos' => 'integer',
        'puntos_max' => 'integer',
        'duracion_minutos' => 'integer',
        'permite_entregas_tardias' => 'boolean',
        'intentos_permitidos' => 'integer',
        'es_obligatoria' => 'boolean',
        'habilitado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'porcentaje_curso' => 'decimal:2',
        'nota_minima_aprobacion' => 'decimal:2',
        'material_id' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     * Solo incluir los que son seguros para JSON
     */
    protected $appends = [
        'tipo_icon',
        'estado',
        'estado_color',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Excluir accessors que generan HTML o pueden tener caracteres problemáticos
     */
    protected $hidden = [
        'tipo_badge',
        'estado_badge',
        'prerequisite_activities',
        'linked_materials',
        'total_puntos_preguntas',
    ];

    /**
     * Relación con el curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Relación con el material
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(CursoMaterial::class, 'material_id');
    }

    /**
     * Obtener las actividades prerrequisito de esta actividad
     */
    public function getPrerequisiteActivitiesAttribute()
    {
        if (empty($this->prerequisite_activity_ids)) {
            return collect([]);
        }
        
        return CursoActividad::whereIn('id', $this->prerequisite_activity_ids)->get();
    }

    /**
     * Verificar si el usuario ha completado todos los prerrequisitos
     */
    public function userHasCompletedPrerequisites(int $userId): bool
    {
        if (empty($this->prerequisite_activity_ids)) {
            return true; // Sin prerrequisitos, siempre puede acceder
        }
        
        foreach ($this->prerequisite_activity_ids as $prereqId) {
            $prereqActivity = CursoActividad::find($prereqId);
            if (!$prereqActivity) {
                continue; // Si la actividad prerrequisito no existe, ignorar
            }
            
            // Verificar si el usuario ha completado esta actividad prerrequisito
            $entrega = \DB::table('curso_actividad_entrega')
                ->where('actividad_id', $prereqId)
                ->where('user_id', $userId)
                ->whereNotNull('fecha_entrega')
                ->first();
            
            if (!$entrega) {
                return false; // No ha completado este prerrequisito
            }
        }
        
        return true;
    }

    /**
     * Obtener los materiales vinculados a esta actividad (legacy)
     */
    public function getLinkedMaterialsAttribute()
    {
        if (empty($this->linked_material_ids)) {
            return collect([]);
        }
        
        return CursoMaterial::whereIn('id', $this->linked_material_ids)->get();
    }

    /**
     * Accessors
     */
    public function getTipoIconAttribute(): string
    {
        $icons = [
            'tarea' => 'fas fa-tasks',
            'evaluacion' => 'fas fa-clipboard-check',
            'quiz' => 'fas fa-question-circle',
            'proyecto' => 'fas fa-project-diagram',
        ];

        return $icons[$this->tipo] ?? 'fas fa-file-alt';
    }

    public function getTipoBadgeAttribute(): string
    {
        $badges = [
            'tarea' => '<span class="badge badge-primary">Tarea</span>',
            'evaluacion' => '<span class="badge badge-danger">Evaluación</span>',
            'quiz' => '<span class="badge badge-info">Quiz</span>',
            'proyecto' => '<span class="badge badge-success">Proyecto</span>',
        ];

        return $badges[$this->tipo] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }

    public function getEstadoAttribute(): string
    {
        $now = Carbon::now();
        
        if ($this->fecha_apertura && $now->lt($this->fecha_apertura)) {
            return 'pendiente';
        } elseif ($this->fecha_cierre && $now->gt($this->fecha_cierre)) {
            return 'cerrada';
        } else {
            return 'abierta';
        }
    }

    public function getEstadoBadgeAttribute(): string
    {
        $estado = $this->estado;

        $badges = [
            'pendiente' => '<span class="badge badge-warning">Pendiente</span>',
            'abierta' => '<span class="badge badge-success">Abierta</span>',
            'cerrada' => '<span class="badge badge-danger">Cerrada</span>',
        ];

        return $badges[$estado] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }

    public function getEstadoColorAttribute(): string
    {
        $estado = $this->estado;

        $colors = [
            'pendiente' => 'warning',
            'abierta' => 'success',
            'cerrada' => 'danger',
        ];

        return $colors[$estado] ?? 'secondary';
    }

    public function getFechaAperturaFormateadaAttribute(): string
    {
        return $this->fecha_apertura ? $this->fecha_apertura->format('d/m/Y H:i') : 'Sin fecha';
    }

    public function getFechaCierreFormateadaAttribute(): string
    {
        return $this->fecha_cierre ? $this->fecha_cierre->format('d/m/Y H:i') : 'Sin fecha límite';
    }

    public function getTiempoRestanteAttribute(): string
    {
        if (!$this->fecha_cierre) {
            return 'Sin límite de tiempo';
        }

        $now = Carbon::now();
        
        if ($now->gt($this->fecha_cierre)) {
            return 'Tiempo agotado';
        }

        return $now->diffForHumans($this->fecha_cierre, true) . ' restantes';
    }

    /**
     * Scopes
     */
    public function scopeAbiertas($query)
    {
        $now = Carbon::now();
        return $query->where(function($q) use ($now) {
            $q->where('fecha_apertura', '<=', $now)
              ->where(function($q2) use ($now) {
                  $q2->whereNull('fecha_cierre')
                     ->orWhere('fecha_cierre', '>', $now);
              });
        });
    }

    public function scopeCerradas($query)
    {
        $now = Carbon::now();
        return $query->where('fecha_cierre', '<', $now);
    }

    public function scopePendientes($query)
    {
        $now = Carbon::now();
        return $query->where('fecha_apertura', '>', $now);
    }

    public function scopeObligatorias($query)
    {
        return $query->where('es_obligatoria', true);
    }

    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Validar que la suma de puntos de preguntas no exceda 5.0 (para quiz/evaluacion)
     */
    public static function validarPuntosPreguntas(array $preguntas): bool
    {
        $totalPuntos = 0;
        foreach ($preguntas as $pregunta) {
            $totalPuntos += floatval($pregunta['points'] ?? 0);
        }
        return $totalPuntos <= self::NOTA_MAXIMA;
    }

    /**
     * Obtener la suma total de puntos de las preguntas
     */
    public function getTotalPuntosPreguntasAttribute(): float
    {
        if (!$this->contenido_json || !isset($this->contenido_json['questions'])) {
            return 0;
        }
        
        $total = 0;
        foreach ($this->contenido_json['questions'] as $pregunta) {
            $total += floatval($pregunta['points'] ?? 0);
        }
        return $total;
    }

    /**
     * Calcular la nota de un estudiante en esta actividad
     * Retorna un valor entre 0.0 y 5.0
     */
    public function calcularNotaEstudiante($userId): float
    {
        // Buscar la entrega del estudiante
        $entrega = \DB::table('curso_actividad_entrega')
            ->where('curso_id', $this->curso_id)
            ->where('actividad_id', $this->id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$entrega) {
            return 0;
        }

        // Si ya tiene calificación asignada (tarea calificada manualmente)
        if (isset($entrega->calificacion) && $entrega->calificacion !== null) {
            // La calificación siempre se guarda en escala 0-5.0
            $calificacion = floatval($entrega->calificacion);
            return min(round($calificacion, 2), self::NOTA_MAXIMA);
        }

        // Para quiz/evaluacion, calcular basado en respuestas
        if (in_array($this->tipo, ['quiz', 'evaluacion']) && isset($entrega->contenido)) {
            $contenido = json_decode($entrega->contenido, true);
            if (isset($contenido['respuestas'])) {
                return $this->calcularNotaQuiz($contenido['respuestas']);
            }
        }

        return 0;
    }

    /**
     * Calcular la nota de un quiz/evaluación basado en las respuestas
     * Usa el sistema de porcentajes: cada pregunta tiene un porcentaje (en 'points')
     * Nota = Σ((porcentaje_pregunta / 100) × 5 × factor_correctitud)
     * Para múltiples respuestas correctas, el porcentaje se distribuye entre ellas
     */
    public function calcularNotaQuiz(array $respuestas): float
    {
        if (!$this->contenido_json || !isset($this->contenido_json['questions'])) {
            return 0;
        }

        $notaObtenida = 0;
        $preguntas = $this->contenido_json['questions'];

        foreach ($preguntas as $pregunta) {
            $preguntaId = $pregunta['id'] ?? null;
            if (!$preguntaId || !isset($respuestas[$preguntaId])) {
                continue;
            }

            $respuestaUsuario = $respuestas[$preguntaId];
            $porcentajePregunta = floatval($pregunta['points'] ?? 0); // 'points' almacena el porcentaje

            // Soportar tanto formato antiguo (correctAnswer) como nuevo (correctAnswers)
            $respuestasCorrectas = $pregunta['correctAnswers'] ?? (isset($pregunta['correctAnswer']) ? [$pregunta['correctAnswer']] : []);
            $esMultiple = ($pregunta['isMultipleChoice'] ?? false) || count($respuestasCorrectas) > 1;

            if ($esMultiple) {
                // MÚLTIPLES RESPUESTAS CORRECTAS
                $numCorrectasTotal = count($respuestasCorrectas);
                $porcentajePorRespuesta = ($numCorrectasTotal > 0) ? $porcentajePregunta / $numCorrectasTotal : 0;
                
                $respuestasUsuarioArray = is_array($respuestaUsuario) ? $respuestaUsuario : [$respuestaUsuario];
                
                foreach ($respuestasUsuarioArray as $resp) {
                    if (in_array($resp, $respuestasCorrectas)) {
                        $notaObtenida += ($porcentajePorRespuesta / 100) * 5;
                    }
                }
            } else {
                // RESPUESTA ÚNICA
                $respuestaCorrecta = $respuestasCorrectas[0] ?? '';
                if ($respuestaUsuario === $respuestaCorrecta) {
                    $notaObtenida += ($porcentajePregunta / 100) * 5;
                }
            }
        }

        return min(round($notaObtenida, 2), self::NOTA_MAXIMA);
    }
}
