<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoAsignacion extends Model
{
    protected $table = 'curso_asignaciones';

    protected $fillable = [
        'curso_id',
        'estudiante_id',
        'asignado_por',
        'docente_id',
        'estado',
        'fecha_asignacion',
        'fecha_expiracion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación con el estudiante
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Relación con el usuario que asignó
     */
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    /**
     * Relación con el docente asignado
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    /**
     * Scope para asignaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo')
                     ->where(function ($q) {
                         $q->whereNull('fecha_expiracion')
                           ->orWhere('fecha_expiracion', '>', now());
                     });
    }

    /**
     * Verificar si la asignación está activa
     */
    public function estaActiva(): bool
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        if ($this->fecha_expiracion && $this->fecha_expiracion < now()) {
            return false;
        }

        return true;
    }
}
