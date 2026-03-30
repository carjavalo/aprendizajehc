<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descripcion',
        'cod_categoria',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cod_categoria' => 'integer',
    ];

    /**
     * Relación con el modelo Categoria.
     * Un área pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'cod_categoria');
    }

    /**
     * Accessor para obtener el nombre de la categoría.
     */
    public function getCategoriaDescripcionAttribute(): string
    {
        return $this->categoria ? $this->categoria->descripcion : 'Sin categoría';
    }

    /**
     * Scope para filtrar por categoría.
     */
    public function scopeByCategoria($query, $categoriaId)
    {
        return $query->where('cod_categoria', $categoriaId);
    }

    /**
     * Scope para buscar por descripción.
     */
    public function scopeByDescripcion($query, $descripcion)
    {
        return $query->where('descripcion', 'like', '%' . $descripcion . '%');
    }
}
