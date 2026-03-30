<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellido1',
        'apellido2',
        'email',
        'phone',
        'password',
        'role',
        'tipo_documento',
        'numero_documento',
        'servicio_area_id',
        'vinculacion_contrato_id',
        'sede_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the available roles for users
     */
    public static function getAvailableRoles(): array
    {
        // Obtener los roles directamente desde la tabla de roles
        $roles = \App\Models\Role::pluck('name')->toArray();
        
        // Si por alguna razón la tabla está vacía, devuelve el listado base para evitar errores
        if (empty($roles)) {
            return ['Super Admin', 'Administrador', 'Docente', 'Estudiante', 'Registrado', 'Operador', 'Consultor Agesoc', 'Consultor Asstracud'];
        }
        
        // Add specific custom roles if they are not in the DB yet, just to be safe
        if (!in_array('Consultor Agesoc', $roles)) {
            $roles[] = 'Consultor Agesoc';
        }
        if (!in_array('Consultor Asstracud', $roles)) {
            $roles[] = 'Consultor Asstracud';
        }

        return $roles;
    }

    /**
     * Get the available document types
     */
    public static function getAvailableDocumentTypes(): array
    {
        return ['DNI', 'Pasaporte', 'Carnet de Extranjería', 'Cédula'];
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->apellido1 . ' ' . $this->apellido2);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Check if user is admin (Super Admin or Administrador)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['Super Admin', 'Administrador']);
    }

    /**
     * Check if user is Operador
     */
    public function isOperador(): bool
    {
        return $this->role === 'Operador';
    }

    /**
     * Check if user has management permissions (Admin or Operador)
     * Operadores can manage courses, materials and activities like admins
     */
    public function tienePermisoGestion(): bool
    {
        return $this->isAdmin() || $this->isOperador();
    }

    /**
     * Get formatted document information
     */
    public function getFormattedDocumentAttribute(): string
    {
        if ($this->tipo_documento && $this->numero_documento) {
            return $this->tipo_documento . ': ' . $this->numero_documento;
        }
        return 'No especificado';
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Relación con Servicio/Área
     */
    public function servicioArea()
    {
        return $this->belongsTo(\App\Models\ServicioArea::class, 'servicio_area_id');
    }

    /**
     * Relación con Vinculación/Contrato
     */
    public function vinculacionContrato()
    {
        return $this->belongsTo(\App\Models\VinculacionContrato::class, 'vinculacion_contrato_id');
    }

    /**
     * Relación con Sede
     */
    public function sede()
    {
        return $this->belongsTo(\App\Models\Sede::class, 'sede_id');
    }

    /**
     * Relación con los cursos donde el usuario está inscrito como estudiante
     */
    public function cursosInscritos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_estudiantes', 'estudiante_id', 'curso_id')
                    ->withPivot(['estado', 'progreso', 'fecha_inscripcion', 'ultima_actividad'])
                    ->withTimestamps();
    }

    /**
     * Alias para cursosInscritos (para compatibilidad)
     */
    public function inscripciones(): BelongsToMany
    {
        return $this->cursosInscritos();
    }

    /**
     * Relación con las asignaciones de cursos
     */
    public function cursosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_asignaciones', 'estudiante_id', 'curso_id')
                    ->withPivot(['estado', 'fecha_asignacion', 'fecha_expiracion', 'asignado_por'])
                    ->withTimestamps();
    }

    /**
     * Send the password reset notification with custom email.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        try {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $this->email,
            ], false));
            
            \Illuminate\Support\Facades\Mail::to($this->email)->send(
                new \App\Mail\RecuperarPassword($this, $resetUrl)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar correo de recuperación de contraseña: ' . $e->getMessage());
            // Fallback to default notification if custom email fails
            $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
        }
    }
}
