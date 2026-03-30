<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoForo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'curso_foros';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'curso_id',
        'usuario_id',
        'titulo',
        'contenido',
        'parent_id',
        'es_anuncio',
        'es_fijado',
        'likes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'es_anuncio' => 'boolean',
        'es_fijado' => 'boolean',
        'likes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Relación con el usuario que creó el post
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el post padre (para respuestas)
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(CursoForo::class, 'parent_id');
    }

    /**
     * Relación con las respuestas
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(CursoForo::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * Accessors
     */
    public function getUsuarioNombreAttribute(): string
    {
        return $this->usuario ? $this->usuario->name . ' ' . $this->usuario->apellido1 : 'Usuario desconocido';
    }

    public function getRespuestasCountAttribute(): int
    {
        return $this->respuestas()->count();
    }

    public function getTipoPostAttribute(): string
    {
        if ($this->es_anuncio) {
            return 'anuncio';
        } elseif ($this->parent_id) {
            return 'respuesta';
        } else {
            return 'discusion';
        }
    }

    public function getTipoBadgeAttribute(): string
    {
        if ($this->es_anuncio) {
            return '<span class="badge badge-warning"><i class="fas fa-bullhorn"></i> Anuncio</span>';
        } elseif ($this->es_fijado) {
            return '<span class="badge badge-info"><i class="fas fa-thumbtack"></i> Fijado</span>';
        } elseif ($this->parent_id) {
            return '<span class="badge badge-secondary"><i class="fas fa-reply"></i> Respuesta</span>';
        } else {
            return '<span class="badge badge-primary"><i class="fas fa-comments"></i> Discusión</span>';
        }
    }

    public function getFechaFormateadaAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scopes
     */
    public function scopeAnuncios($query)
    {
        return $query->where('es_anuncio', true);
    }

    public function scopeFijados($query)
    {
        return $query->where('es_fijado', true);
    }

    public function scopeDiscusiones($query)
    {
        return $query->where('es_anuncio', false)->whereNull('parent_id');
    }

    public function scopeRespuestas($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
