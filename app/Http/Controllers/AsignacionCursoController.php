<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use App\Models\CursoAsignacion;
use App\Services\OperationLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AsignacionCursoController extends Controller
{
    /**
     * Roles permitidos para acceder a la asignación de cursos
     */
    protected $rolesPermitidos = ['Super Admin', 'Administrador', 'Operador'];

    /**
     * Verificar permisos de acceso
     */
    private function verificarAcceso(): void
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, $this->rolesPermitidos)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }
    }

    /**
     * Mostrar la vista de asignación de cursos
     */
    public function index(): View
    {
        $this->verificarAcceso();
        
        // Obtener docentes para el select
        $docentes = User::where('role', 'Docente')
                        ->orderBy('name')
                        ->get();
        
        return view('admin.configuracion.asignacion-cursos.index', compact('docentes'));
    }

    /**
     * Obtener lista de estudiantes para búsqueda
     */
    public function buscarEstudiantes(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        $search = $request->get('q', '');
        
        $query = User::where('role', 'Estudiante')
            ->select('id', 'name', 'apellido1', 'apellido2', 'email', 'numero_documento', 'created_at')
            ->orderBy('name');
        
        // Si hay búsqueda, filtrar por los campos
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('apellido1', 'like', "%{$search}%")
                  ->orWhere('apellido2', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }
        
        $estudiantes = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'estudiantes' => $estudiantes->map(function ($est) {
                return [
                    'id' => $est->id,
                    'nombre_completo' => $est->name . ' ' . $est->apellido1 . ' ' . ($est->apellido2 ?? ''),
                    'email' => $est->email,
                    'documento' => $est->numero_documento,
                    'fecha_registro' => $est->created_at->format('d/m/Y'),
                ];
            })
        ]);
    }

    /**
     * Obtener información de un estudiante específico
     */
    public function getEstudiante(User $estudiante): JsonResponse
    {
        $this->verificarAcceso();
        if ($estudiante->role !== 'Estudiante') {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es un estudiante'
            ], 400);
        }

        // Obtener cursos asignados activos
        $cursosAsignados = CursoAsignacion::with(['curso', 'docente'])
            ->where('estudiante_id', $estudiante->id)
            ->activas()
            ->get();

        // Obtener cursos inscritos
        $cursosInscritos = $estudiante->cursosInscritos()
            ->withPivot('estado', 'progreso', 'created_at')
            ->wherePivot('estado', 'activo')
            ->get();

        return response()->json([
            'success' => true,
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre_completo' => $estudiante->name . ' ' . $estudiante->apellido1 . ' ' . ($estudiante->apellido2 ?? ''),
                'email' => $estudiante->email,
                'documento' => $estudiante->tipo_documento . ': ' . $estudiante->numero_documento,
                'fecha_registro' => $estudiante->created_at->format('d/m/Y'),
            ],
            'cursos_asignados' => $cursosAsignados->map(function ($asig) {
                return [
                    'id' => $asig->id,
                    'curso_id' => $asig->curso_id,
                    'titulo' => $asig->curso->titulo ?? 'Sin título',
                    'fecha_asignacion' => $asig->fecha_asignacion->format('d/m/Y'),
                    'docente' => $asig->docente ? ($asig->docente->name . ' ' . ($asig->docente->apellido1 ?? '')) : null,
                    'docente_id' => $asig->docente_id,
                ];
            }),
            'cursos_inscritos' => $cursosInscritos->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'titulo' => $curso->titulo,
                    'progreso' => $curso->pivot->progreso ?? 0,
                    'estado' => $curso->pivot->estado ?? 'activo',
                ];
            }),
            'total_asignados' => $cursosAsignados->count(),
            'total_inscritos' => $cursosInscritos->count(),
        ]);
    }

    /**
     * Obtener lista de cursos disponibles para asignar
     */
    public function getCursosDisponibles(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        $estudianteId = $request->get('estudiante_id');
        $search = $request->get('search', '');
        $categoria = $request->get('categoria', '');

        $query = Curso::with(['area.categoria', 'instructor'])
            ->where('estado', 'activo'); // Solo cursos activos son asignables (borrador no aparece en cursos-disponibles)

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($categoria) {
            $query->whereHas('area.categoria', function ($q) use ($categoria) {
                $q->where('id', $categoria);
            });
        }

        $cursos = $query->orderBy('titulo')->get();

        // Obtener IDs de cursos ya asignados (solo activos) al estudiante
        $cursosAsignadosIds = [];
        $cursosInscritosIds = [];
        
        if ($estudianteId) {
            // Solo considerar asignaciones activas como "ya asignado"
            $cursosAsignadosIds = CursoAsignacion::where('estudiante_id', $estudianteId)
                ->activas()
                ->pluck('curso_id')
                ->toArray();

            $cursosInscritosIds = DB::table('curso_estudiantes')
                ->where('estudiante_id', $estudianteId)
                ->where('estado', 'activo')
                ->pluck('curso_id')
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'cursos' => $cursos->map(function ($curso) use ($cursosAsignadosIds, $cursosInscritosIds) {
                $yaAsignado = in_array($curso->id, $cursosAsignadosIds);
                $yaInscrito = in_array($curso->id, $cursosInscritosIds);
                
                return [
                    'id' => $curso->id,
                    'titulo' => $curso->titulo,
                    'descripcion' => $curso->descripcion,
                    'categoria' => $curso->area && $curso->area->categoria ? $curso->area->categoria->descripcion : 'Sin categoría',
                    'area' => $curso->area ? $curso->area->descripcion : 'Sin área',
                    'instructor' => $curso->instructor ? 
                        $curso->instructor->name . ' ' . $curso->instructor->apellido1 : 'Sin instructor',
                    'duracion_horas' => $curso->duracion_horas,
                    'max_estudiantes' => $curso->max_estudiantes,
                    'estudiantes_count' => $curso->estudiantes()->count(),
                    'ya_asignado' => $yaAsignado,
                    'ya_inscrito' => $yaInscrito,
                ];
            })
        ]);
    }

    /**
     * Obtener categorías para el filtro
     */
    public function getCategorias(): JsonResponse
    {
        $this->verificarAcceso();
        $categorias = DB::table('categorias')
            ->select('id', 'descripcion')
            ->orderBy('descripcion')
            ->get();

        return response()->json([
            'success' => true,
            'categorias' => $categorias
        ]);
    }

    /**
     * Eliminar completamente el progreso y datos de un estudiante en un curso
     */
    private function eliminarDatosEstudianteCurso(int $cursoId, int $estudianteId): void
    {
        // Eliminar materiales vistos
        try {
            DB::table('curso_material_visto')
                ->where('curso_id', $cursoId)
                ->where('user_id', $estudianteId)
                ->delete();
        } catch (\Exception $e) {
            \Log::warning("No se pudo eliminar materiales vistos: " . $e->getMessage());
        }

        // Eliminar entregas de actividades
        try {
            DB::table('curso_actividad_entrega')
                ->where('curso_id', $cursoId)
                ->where('user_id', $estudianteId)
                ->delete();
        } catch (\Exception $e) {
            \Log::warning("No se pudo eliminar entregas: " . $e->getMessage());
        }

        // Eliminar inscripción del estudiante en el curso
        DB::table('curso_estudiantes')
            ->where('curso_id', $cursoId)
            ->where('estudiante_id', $estudianteId)
            ->delete();
    }

    /**
     * Reiniciar el progreso de un estudiante en un curso (sin eliminar inscripción)
     */
    private function reiniciarProgresoCurso(int $cursoId, int $estudianteId): void
    {
        // Eliminar inscripción previa para que el estudiante deba volver a inscribirse manualmente
        DB::table('curso_estudiantes')
            ->where('curso_id', $cursoId)
            ->where('estudiante_id', $estudianteId)
            ->delete();

        // Eliminar materiales vistos
        try {
            DB::table('curso_material_visto')
                ->where('curso_id', $cursoId)
                ->where('user_id', $estudianteId)
                ->delete();
        } catch (\Exception $e) {
            \Log::warning("No se pudo eliminar materiales vistos: " . $e->getMessage());
        }

        // Eliminar entregas de actividades
        try {
            DB::table('curso_actividad_entrega')
                ->where('curso_id', $cursoId)
                ->where('user_id', $estudianteId)
                ->delete();
        } catch (\Exception $e) {
            \Log::warning("No se pudo eliminar entregas: " . $e->getMessage());
        }
    }

    /**
     * Asignar cursos a un estudiante
     */
    public function asignar(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'cursos' => 'required|array|min:1',
            'cursos.*' => 'exists:cursos,id',
            'docente_id' => 'nullable|exists:users,id',
        ], [
            'estudiante_id.required' => 'Debe seleccionar un estudiante',
            'estudiante_id.exists' => 'El estudiante seleccionado no existe',
            'cursos.required' => 'Debe seleccionar al menos un curso',
            'cursos.min' => 'Debe seleccionar al menos un curso',
        ]);

        $docenteId = $request->docente_id;

        $estudiante = User::find($request->estudiante_id);
        
        if ($estudiante->role !== 'Estudiante') {
            return response()->json([
                'success' => false,
                'message' => 'El usuario seleccionado no tiene rol de Estudiante'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $asignados = 0;
            $reasignados = 0;
            $yaAsignados = 0;
            $emailsParaEnviar = [];

            foreach ($request->cursos as $cursoId) {
                // Verificar si ya tiene asignación (activa o inactiva)
                $asignacionExistente = CursoAsignacion::where('curso_id', $cursoId)
                    ->where('estudiante_id', $request->estudiante_id)
                    ->first();

                if ($asignacionExistente) {
                    if ($asignacionExistente->estado === 'activo') {
                        // Actualizar docente_id aunque ya esté activo (el admin puede querer cambiarlo)
                        if ($docenteId && $asignacionExistente->docente_id !== $docenteId) {
                            $asignacionExistente->update(['docente_id' => $docenteId]);
                        }
                        $yaAsignados++;
                        continue;
                    }
                    // Reactivar asignación inactiva y reiniciar progreso
                    $asignacionExistente->update([
                        'estado' => 'activo',
                        'asignado_por' => Auth::id(),
                        'fecha_asignacion' => now(),
                        'docente_id' => $docenteId ?? $asignacionExistente->docente_id, // Actualizar docente si se envía, o mantener el previo
                    ]);
                    
                    // Reiniciar progreso del estudiante en el curso
                    $this->reiniciarProgresoCurso($cursoId, $request->estudiante_id);
                    
                    // Registrar operación de reasignación
                    $curso = Curso::find($cursoId);
                    OperationLogger::logCourseAssignment(
                        $request->estudiante_id,
                        $cursoId,
                        $curso->titulo ?? 'Curso #' . $cursoId,
                        $estudiante->name . ' ' . $estudiante->apellido1
                    );

                    // Programar correos (se enviarán después de la respuesta HTTP)
                    $emailsParaEnviar[] = ['to' => $estudiante->email, 'mailable' => new \App\Mail\AsignacionCurso($estudiante, $curso, route('academico.curso.inscribirse', $cursoId)), 'error' => 'Error al enviar correo de reasignación'];
                    if ($curso && $curso->instructor) {
                        $emailsParaEnviar[] = ['to' => $curso->instructor->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($curso->instructor, $estudiante, $curso), 'error' => 'Error al enviar correo de reasignación al instructor'];
                    }
                    if ($docenteId) {
                        $docenteReasignado = User::find($docenteId);
                        if ($docenteReasignado && ($curso->instructor_id === null || $docenteReasignado->id !== $curso->instructor_id)) {
                            $emailsParaEnviar[] = ['to' => $docenteReasignado->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($docenteReasignado, $estudiante, $curso), 'error' => 'Error al enviar correo al docente asignado (reasignación)'];
                        }
                    }

                    $reasignados++;
                    continue;
                }

                // Crear nueva asignación
                $asignacionData = [
                    'curso_id' => $cursoId,
                    'estudiante_id' => $request->estudiante_id,
                    'asignado_por' => Auth::id(),
                    'estado' => 'activo',
                    'fecha_asignacion' => now(),
                ];
                if ($docenteId) {
                    $asignacionData['docente_id'] = $docenteId;
                }
                CursoAsignacion::create($asignacionData);

                // Registrar operación de asignación
                $curso = Curso::find($cursoId);
                OperationLogger::logCourseAssignment(
                    $request->estudiante_id,
                    $cursoId,
                    $curso->titulo ?? 'Curso #' . $cursoId,
                    $estudiante->name . ' ' . $estudiante->apellido1
                );

                // Programar correos (se enviarán después de la respuesta HTTP)
                $emailsParaEnviar[] = ['to' => $estudiante->email, 'mailable' => new \App\Mail\AsignacionCurso($estudiante, $curso, route('academico.curso.inscribirse', $cursoId)), 'error' => 'Error al enviar correo de asignación al estudiante'];
                if ($curso && $curso->instructor) {
                    $emailsParaEnviar[] = ['to' => $curso->instructor->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($curso->instructor, $estudiante, $curso), 'error' => 'Error al enviar correo al instructor'];
                }
                if ($docenteId) {
                    $docenteNuevo = User::find($docenteId);
                    if ($docenteNuevo && ($curso->instructor_id === null || $docenteNuevo->id !== $curso->instructor_id)) {
                        $emailsParaEnviar[] = ['to' => $docenteNuevo->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($docenteNuevo, $estudiante, $curso), 'error' => 'Error al enviar correo al docente asignado'];
                    }
                }

                $asignados++;
            }

            DB::commit();

            // Enviar correos después de la respuesta HTTP (no bloquea al usuario)
            if (!empty($emailsParaEnviar)) {
                $correosPendientes = $emailsParaEnviar;
                \Illuminate\Support\defer(function () use ($correosPendientes) {
                    foreach ($correosPendientes as $correo) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($correo['to'])->send($correo['mailable']);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error($correo['error'] . ': ' . $e->getMessage());
                        }
                    }
                });
            }

            $mensaje = "";
            if ($asignados > 0) {
                $mensaje .= "Se asignaron {$asignados} curso(s) nuevos. ";
            }
            if ($reasignados > 0) {
                $mensaje .= "Se reasignaron {$reasignados} curso(s) (progreso reiniciado). ";
            }
            if ($yaAsignados > 0) {
                $mensaje .= "{$yaAsignados} curso(s) ya estaban asignados.";
            }

            return response()->json([
                'success' => true,
                'message' => trim($mensaje),
                'asignados' => $asignados,
                'reasignados' => $reasignados,
                'ya_asignados' => $yaAsignados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar cursos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover asignación de curso
     */
    public function removerAsignacion(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        try {
            DB::beginTransaction();

            $cursoId = $request->curso_id;
            $estudianteId = $request->estudiante_id;

            // Buscar asignación activa
            $asignacion = CursoAsignacion::where('curso_id', $cursoId)
                ->where('estudiante_id', $estudianteId)
                ->activas()
                ->first();

            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la asignación activa'
                ], 404);
            }

            // Marcar asignación como inactiva
            $asignacion->update(['estado' => 'inactivo']);

            // Desactivar la inscripción para que no aparezca en listados de control pedagógico etc.
            // Mantenemos los datos y todo el progreso del estudiante en este curso
            DB::table('curso_estudiantes')
                ->where('curso_id', $cursoId)
                ->where('estudiante_id', $estudianteId)
                ->update(['estado' => 'inactivo']);

            // $this->eliminarDatosEstudianteCurso($cursoId, $estudianteId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asignación removida correctamente. Se mantuvo la inscripción y todo el progreso del estudiante en este curso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al remover asignación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al remover asignación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar un curso a todos los usuarios con rol Estudiante
     */
    public function asignarATodos(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'docente_id' => 'nullable|exists:users,id',
        ], [
            'curso_id.required' => 'Debe seleccionar un curso',
            'curso_id.exists' => 'El curso seleccionado no existe',
        ]);

        try {
            DB::beginTransaction();

            $curso = Curso::find($request->curso_id);
            $docenteIdMasivo = $request->docente_id;
            
            // Obtener todos los usuarios con rol Estudiante
            $estudiantes = User::where('role', 'Estudiante')->get();
            
            if ($estudiantes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay usuarios con rol Estudiante en el sistema'
                ], 400);
            }

            $asignados = 0;
            $reasignados = 0;
            $yaAsignados = 0;
            $yaInscritos = 0;
            $emailsParaEnviar = [];

            foreach ($estudiantes as $estudiante) {
                // Verificar si ya está inscrito en el curso
                $yaInscrito = DB::table('curso_estudiantes')
                    ->where('curso_id', $request->curso_id)
                    ->where('estudiante_id', $estudiante->id)
                    ->exists();

                if ($yaInscrito) {
                    $yaInscritos++;
                    continue;
                }

                // Verificar si ya tiene asignación (activa o inactiva)
                $asignacionExistente = CursoAsignacion::where('curso_id', $request->curso_id)
                    ->where('estudiante_id', $estudiante->id)
                    ->first();

                if ($asignacionExistente) {
                    if ($asignacionExistente->estado === 'activo') {
                        // Actualizar docente_id aunque ya esté activo
                        if ($docenteIdMasivo && $asignacionExistente->docente_id !== $docenteIdMasivo) {
                            $asignacionExistente->update(['docente_id' => $docenteIdMasivo]);
                        }
                        $yaAsignados++;
                        continue;
                    }
                    // Reactivar asignación inactiva y reiniciar progreso
                    $updateDataMasivo = [
                        'estado' => 'activo',
                        'asignado_por' => Auth::id(),
                        'fecha_asignacion' => now(),
                        'docente_id' => $docenteIdMasivo ?? $asignacionExistente->docente_id, // Actualizar docente si se envía, o mantener el previo
                    ];
                    $asignacionExistente->update($updateDataMasivo);
                    
                    // Reiniciar progreso del estudiante en el curso
                    $this->reiniciarProgresoCurso($request->curso_id, $estudiante->id);

                    // Programar correos (se enviarán después de la respuesta HTTP)
                    $emailsParaEnviar[] = ['to' => $estudiante->email, 'mailable' => new \App\Mail\AsignacionCurso($estudiante, $curso, route('academico.curso.inscribirse', $request->curso_id)), 'error' => 'Error al enviar correo de reasignación masiva a ' . $estudiante->email];
                    if ($curso && $curso->instructor) {
                        $emailsParaEnviar[] = ['to' => $curso->instructor->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($curso->instructor, $estudiante, $curso), 'error' => 'Error al enviar correo de reasignación masiva al instructor'];
                    }
                    if ($docenteIdMasivo) {
                        $docenteReasignadoMasivo = User::find($docenteIdMasivo);
                        if ($docenteReasignadoMasivo && ($curso->instructor_id === null || $docenteReasignadoMasivo->id !== $curso->instructor_id)) {
                            $emailsParaEnviar[] = ['to' => $docenteReasignadoMasivo->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($docenteReasignadoMasivo, $estudiante, $curso), 'error' => 'Error al enviar correo al docente asignado (reasignación masiva)'];
                        }
                    }

                    $reasignados++;
                    continue;
                }

                // Crear nueva asignación
                $asignacionDataMasivo = [
                    'curso_id' => $request->curso_id,
                    'estudiante_id' => $estudiante->id,
                    'asignado_por' => Auth::id(),
                    'estado' => 'activo',
                    'fecha_asignacion' => now(),
                ];
                if ($docenteIdMasivo) {
                    $asignacionDataMasivo['docente_id'] = $docenteIdMasivo;
                }
                CursoAsignacion::create($asignacionDataMasivo);

                // Programar correos (se enviarán después de la respuesta HTTP)
                $emailsParaEnviar[] = ['to' => $estudiante->email, 'mailable' => new \App\Mail\AsignacionCurso($estudiante, $curso, route('academico.curso.inscribirse', $request->curso_id)), 'error' => 'Error al enviar correo de asignación masiva a ' . $estudiante->email];
                if ($curso && $curso->instructor) {
                    $emailsParaEnviar[] = ['to' => $curso->instructor->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($curso->instructor, $estudiante, $curso), 'error' => 'Error al enviar correo al instructor (masivo)'];
                }
                if ($docenteIdMasivo) {
                    $docenteNuevoMasivo = User::find($docenteIdMasivo);
                    if ($docenteNuevoMasivo && ($curso->instructor_id === null || $docenteNuevoMasivo->id !== $curso->instructor_id)) {
                        $emailsParaEnviar[] = ['to' => $docenteNuevoMasivo->email, 'mailable' => new \App\Mail\NotificacionInstructorAsignacion($docenteNuevoMasivo, $estudiante, $curso), 'error' => 'Error al enviar correo al docente asignado (masivo)'];
                    }
                }

                $asignados++;
            }

            DB::commit();

            // Enviar correos después de la respuesta HTTP (no bloquea al usuario)
            if (!empty($emailsParaEnviar)) {
                $correosPendientes = $emailsParaEnviar;
                \Illuminate\Support\defer(function () use ($correosPendientes) {
                    foreach ($correosPendientes as $correo) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($correo['to'])->send($correo['mailable']);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error($correo['error'] . ': ' . $e->getMessage());
                        }
                    }
                });
            }

            // Registrar operación masiva
            OperationLogger::log(
                'asignacion_masiva',
                'Curso',
                $curso->id,
                "Asignación masiva del curso '{$curso->titulo}' a " . ($asignados + $reasignados) . " estudiantes",
                [
                    'curso_id' => $curso->id,
                    'curso_titulo' => $curso->titulo,
                    'total_estudiantes' => $estudiantes->count(),
                    'asignados' => $asignados,
                    'reasignados' => $reasignados,
                    'ya_asignados' => $yaAsignados,
                    'ya_inscritos' => $yaInscritos,
                ]
            );

            $mensaje = "";
            if ($asignados > 0) {
                $mensaje .= "<strong>{$asignados}</strong> estudiante(s) asignado(s) correctamente.";
            }
            if ($reasignados > 0) {
                $mensaje .= "<br><strong>{$reasignados}</strong> estudiante(s) reasignado(s) (progreso reiniciado).";
            }
            if ($yaAsignados > 0) {
                $mensaje .= "<br><small class='text-muted'>{$yaAsignados} ya tenían asignación activa.</small>";
            }
            if ($yaInscritos > 0) {
                $mensaje .= "<br><small class='text-muted'>{$yaInscritos} ya estaban inscritos en el curso.</small>";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'asignados' => $asignados,
                'reasignados' => $reasignados,
                'ya_asignados' => $yaAsignados,
                'ya_inscritos' => $yaInscritos,
                'total_estudiantes' => $estudiantes->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar curso a todos los estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de asignaciones
     */
    public function getHistorial(Request $request): JsonResponse
    {
        $this->verificarAcceso();
        $query = CursoAsignacion::with(['curso', 'estudiante', 'asignadoPor'])
            ->orderBy('created_at', 'desc');

        if ($request->has('estudiante_id')) {
            $query->where('estudiante_id', $request->estudiante_id);
        }

        return DataTables::of($query)
            ->addColumn('estudiante_nombre', function ($asig) {
                return $asig->estudiante->name . ' ' . $asig->estudiante->apellido1;
            })
            ->addColumn('curso_titulo', function ($asig) {
                return $asig->curso->titulo ?? 'Curso eliminado';
            })
            ->addColumn('asignado_por_nombre', function ($asig) {
                return $asig->asignadoPor->name ?? 'Usuario eliminado';
            })
            ->addColumn('fecha_formateada', function ($asig) {
                return $asig->fecha_asignacion->format('d/m/Y H:i');
            })
            ->make(true);
    }
}
