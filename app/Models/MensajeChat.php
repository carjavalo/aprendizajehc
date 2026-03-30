<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeChat extends Model
{
    use HasFactory;

    protected $table = 'mensajes_chat';

    protected $fillable = [
        'remitente_id',
        'destinatario_id',
        'mensaje',
        'tipo',
        'grupo_destinatario',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el remitente
     */
    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    /**
     * Relación con el destinatario
     */
    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    /**
     * Scope para mensajes no leídos
     */
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    /**
     * Scope para mensajes entre dos usuarios
     */
    public function scopeEntreUsuarios($query, $usuario1Id, $usuario2Id)
    {
        return $query->where(function($q) use ($usuario1Id, $usuario2Id) {
            $q->where('remitente_id', $usuario1Id)->where('destinatario_id', $usuario2Id);
        })->orWhere(function($q) use ($usuario1Id, $usuario2Id) {
            $q->where('remitente_id', $usuario2Id)->where('destinatario_id', $usuario1Id);
        });
    }
}
