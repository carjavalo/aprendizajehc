<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLogin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'status',
        'email_verified',
        'failure_reason',
        'attempted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the login attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por estado de login
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope para filtrar por intentos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para filtrar por usuarios no verificados
     */
    public function scopeUnverified($query)
    {
        return $query->where('email_verified', 'unverified');
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attempted_at', [$startDate, $endDate]);
    }

    /**
     * Accessor para obtener el estado formateado
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status === 'success'
            ? '<span class="badge badge-success">Exitoso</span>'
            : '<span class="badge badge-danger">Fallido</span>';
    }

    /**
     * Accessor para obtener el estado de verificación formateado
     */
    public function getEmailVerifiedBadgeAttribute()
    {
        if ($this->email_verified === 'verified') {
            return '<span class="badge badge-success"><i class="fas fa-check"></i> Verificado</span>';
        } elseif ($this->email_verified === 'unverified') {
            return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Pendiente</span>';
        }
        return '<span class="badge badge-secondary">N/A</span>';
    }
}
