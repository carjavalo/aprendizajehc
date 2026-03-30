<?php

namespace App\Helpers;

use App\Models\UserOperation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Helper para registrar operaciones de usuario (auditoría)
 * 
 * Ejemplo de uso:
 * OperationLogger::log('create', 'Curso', $curso->id, "Creó el curso: {$curso->titulo}");
 */
class OperationLogger
{
    /**
     * Registra una operación de usuario
     *
     * @param string $operationType Tipo de operación: 'create', 'update', 'delete', 'view', 'login', etc.
     * @param string $entityType Tipo de entidad: 'Curso', 'Actividad', 'Entrega', 'Perfil', etc.
     * @param int|null $entityId ID del registro afectado (opcional)
     * @param string $description Descripción legible de la operación
     * @param array|null $details Detalles adicionales en formato array (se guardará como JSON)
     * @return UserOperation|null
     */
    public static function log(
        string $operationType,
        string $entityType,
        ?int $entityId,
        string $description,
        ?array $details = null
    ): ?UserOperation {
        try {
            return UserOperation::create([
                'user_id' => Auth::id(),
                'operation_type' => $operationType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'details' => $details,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging operation: ' . $e->getMessage(), [
                'operation_type' => $operationType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
            return null;
        }
    }

    /**
     * Atajo para registrar creación de entidad
     */
    public static function created(string $entityType, int $entityId, string $description, ?array $details = null): ?UserOperation
    {
        return self::log('create', $entityType, $entityId, $description, $details);
    }

    /**
     * Atajo para registrar actualización de entidad
     */
    public static function updated(string $entityType, int $entityId, string $description, ?array $details = null): ?UserOperation
    {
        return self::log('update', $entityType, $entityId, $description, $details);
    }

    /**
     * Atajo para registrar eliminación de entidad
     */
    public static function deleted(string $entityType, int $entityId, string $description, ?array $details = null): ?UserOperation
    {
        return self::log('delete', $entityType, $entityId, $description, $details);
    }

    /**
     * Atajo para registrar vista de entidad
     */
    public static function viewed(string $entityType, int $entityId, string $description, ?array $details = null): ?UserOperation
    {
        return self::log('view', $entityType, $entityId, $description, $details);
    }
}
