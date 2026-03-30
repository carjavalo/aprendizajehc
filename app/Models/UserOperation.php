<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOperation extends Model
{
    protected $fillable = [
        'user_id',
        'operation_type',
        'entity_type',
        'entity_id',
        'description',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó la operación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope para filtrar por tipo de operación
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('operation_type', $type);
    }
    
    /**
     * Scope para filtrar por tipo de entidad
     */
    public function scopeOfEntity($query, string $entity)
    {
        return $query->where('entity_type', $entity);
    }
    
    /**
     * Scope para operaciones recientes
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
