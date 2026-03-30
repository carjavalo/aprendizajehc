<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descripcion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el modelo Area.
     * Una categoría puede tener muchas áreas.
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'cod_categoria');
    }

    /**
     * Accessor para obtener el conteo de áreas.
     */
    public function getAreasCountAttribute(): int
    {
        return $this->areas()->count();
    }
}
