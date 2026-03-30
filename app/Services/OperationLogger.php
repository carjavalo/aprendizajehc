<?php

namespace App\Services;

use App\Models\UserOperation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class OperationLogger
{
    /**
     * Registrar una operación del usuario
     *
     * @param string $operationType Tipo: create, update, delete, view, login, logout, enroll, submit, grade, complete, access
     * @param string $entityType Entidad: Curso, Actividad, Entrega, Material, Quiz, Examen, Foro, Usuario, Perfil, Session
     * @param int|null $entityId ID de la entidad
     * @param string $description Descripción de la operación
     * @param array $details Detalles adicionales en formato array
     * @return UserOperation
     */
    public static function log(
        string $operationType,
        string $entityType,
        ?int $entityId = null,
        string $description = '',
        array $details = []
    ): UserOperation {
        $user = Auth::user();
        
        return UserOperation::create([
            'user_id' => $user ? $user->id : null,
            'operation_type' => $operationType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'details' => $details,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Registrar login de usuario
     */
    public static function logLogin(int $userId, string $email): UserOperation
    {
        return UserOperation::create([
            'user_id' => $userId,
            'operation_type' => 'login',
            'entity_type' => 'Session',
            'entity_id' => null,
            'description' => "Inicio de sesión: {$email}",
            'details' => [
                'email' => $email,
                'login_time' => now()->toDateTimeString(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Registrar logout de usuario
     */
    public static function logLogout(): UserOperation
    {
        $user = Auth::user();
        
        return UserOperation::create([
            'user_id' => $user ? $user->id : null,
            'operation_type' => 'logout',
            'entity_type' => 'Session',
            'entity_id' => null,
            'description' => 'Cierre de sesión',
            'details' => [
                'logout_time' => now()->toDateTimeString(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Registrar inscripción a curso
     */
    public static function logEnrollment(int $cursoId, string $cursoTitulo): UserOperation
    {
        return self::log(
            'enroll',
            'Curso',
            $cursoId,
            "Inscripción al curso: {$cursoTitulo}",
            ['curso_titulo' => $cursoTitulo, 'fecha_inscripcion' => now()->toDateTimeString()]
        );
    }

    /**
     * Registrar entrega de actividad
     */
    public static function logActivitySubmission(int $actividadId, string $actividadTitulo, int $cursoId): UserOperation
    {
        return self::log(
            'submit',
            'Actividad',
            $actividadId,
            "Entrega de actividad: {$actividadTitulo}",
            [
                'actividad_titulo' => $actividadTitulo,
                'curso_id' => $cursoId,
                'fecha_entrega' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar resolución de quiz/examen
     */
    public static function logQuizSubmission(int $actividadId, string $titulo, float $calificacion, int $cursoId): UserOperation
    {
        return self::log(
            'submit',
            'Quiz',
            $actividadId,
            "Resolución de quiz: {$titulo} - Calificación: {$calificacion}",
            [
                'quiz_titulo' => $titulo,
                'calificacion' => $calificacion,
                'curso_id' => $cursoId,
                'fecha_resolucion' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar visualización de material
     */
    public static function logMaterialView(int $materialId, string $materialTitulo, int $cursoId): UserOperation
    {
        return self::log(
            'view',
            'Material',
            $materialId,
            "Visualización de material: {$materialTitulo}",
            [
                'material_titulo' => $materialTitulo,
                'curso_id' => $cursoId,
                'fecha_visualizacion' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar acceso a curso
     */
    public static function logCourseAccess(int $cursoId, string $cursoTitulo): UserOperation
    {
        return self::log(
            'access',
            'Curso',
            $cursoId,
            "Acceso al curso: {$cursoTitulo}",
            ['curso_titulo' => $cursoTitulo, 'fecha_acceso' => now()->toDateTimeString()]
        );
    }

    /**
     * Registrar completar curso
     */
    public static function logCourseComplete(int $cursoId, string $cursoTitulo, float $progreso): UserOperation
    {
        return self::log(
            'complete',
            'Curso',
            $cursoId,
            "Curso completado: {$cursoTitulo} - Progreso: {$progreso}%",
            [
                'curso_titulo' => $cursoTitulo,
                'progreso' => $progreso,
                'fecha_completado' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar participación en foro
     */
    public static function logForumPost(int $foroId, string $cursoTitulo, string $tipo = 'post'): UserOperation
    {
        $descripcion = $tipo === 'reply' ? 'Respuesta en foro' : 'Publicación en foro';
        
        return self::log(
            'create',
            'Foro',
            $foroId,
            "{$descripcion} del curso: {$cursoTitulo}",
            [
                'curso_titulo' => $cursoTitulo,
                'tipo' => $tipo,
                'fecha_publicacion' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar asignación de curso a estudiante
     */
    public static function logCourseAssignment(int $estudianteId, int $cursoId, string $cursoTitulo, string $estudianteNombre): UserOperation
    {
        return self::log(
            'create',
            'Asignacion',
            $cursoId,
            "Asignación de curso '{$cursoTitulo}' al estudiante: {$estudianteNombre}",
            [
                'estudiante_id' => $estudianteId,
                'estudiante_nombre' => $estudianteNombre,
                'curso_id' => $cursoId,
                'curso_titulo' => $cursoTitulo,
                'fecha_asignacion' => now()->toDateTimeString()
            ]
        );
    }

    /**
     * Registrar calificación de actividad
     */
    public static function logGrading(int $entregaId, string $estudianteNombre, float $calificacion, string $actividadTitulo): UserOperation
    {
        return self::log(
            'grade',
            'Entrega',
            $entregaId,
            "Calificación de actividad '{$actividadTitulo}' para {$estudianteNombre}: {$calificacion}",
            [
                'estudiante_nombre' => $estudianteNombre,
                'calificacion' => $calificacion,
                'actividad_titulo' => $actividadTitulo,
                'fecha_calificacion' => now()->toDateTimeString()
            ]
        );
    }
}
