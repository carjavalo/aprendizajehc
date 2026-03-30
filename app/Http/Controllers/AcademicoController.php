<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoMaterial;
use App\Models\CursoActividad;
use App\Models\CursoEstudiante;
use App\Models\CursoAsignacion;
use App\Services\OperationLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicoController extends Controller
{
    /**
     * Mostrar cursos disponibles para estudiantes
     */
    public function cursosDisponibles(): View
    {
        return view('academico.cursos-disponibles.index');
    }

    /**
     * Obtener datos de cursos disponibles para DataTable
     * - Super Admin, Admin, Operador: ven TODOS los cursos activos
     * - Estudiantes y Docentes: solo ven cursos asignados
     */
    public function getCursosDisponiblesData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        
        // Roles que pueden ver todos los cursos
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        
        // Query base de cursos activos
        $cursosQuery = Curso::with(['area.categoria', 'instructor', 'estudiantes'])
            ->where('estado', 'activo');
        
        // Si NO es un rol privilegiado, filtrar solo cursos asignados
        if (!in_array($userRole, $rolesVerTodos)) {
            // Obtener IDs de cursos asignados al usuario (asignaciones activas)
            $cursosAsignadosIds = CursoAsignacion::where('estudiante_id', $user->id)
                ->activas()
                ->pluck('curso_id')
                ->toArray();
            
            $cursosQuery->whereIn('id', $cursosAsignadosIds);
        }

        return DataTables::of($cursosQuery)
            ->addColumn('area_categoria', function ($curso) {
                return $curso->area->categoria->descripcion ?? 'Sin categoría';
            })
            ->addColumn('area_descripcion', function ($curso) {
                return $curso->area->descripcion ?? 'Sin área';
            })
            ->addColumn('instructor_nombre', function ($curso) use ($user, $userRole, $rolesVerTodos) {
                if (!in_array($userRole, $rolesVerTodos)) {
                    $asignacion = \App\Models\CursoAsignacion::with('docente')->where('curso_id', $curso->id)->where('estudiante_id', $user->id)->activas()->first();
                    if ($asignacion && $asignacion->docente) {
                        return trim($asignacion->docente->name . ' ' . $asignacion->docente->apellido1);
                    }
                }
                return $curso->instructor->full_name ?? 'Sin docente asignado';
            })
            ->addColumn('progreso', function ($curso) use ($user) {
                // Solo calcular progreso si está inscrito
                if ($curso->tieneEstudiante($user->id)) {
                    return $curso->getProgresoEstudiante($user->id);
                }
                return 0;
            })
            ->addColumn('estado_inscripcion', function ($curso) use ($user, $userRole, $rolesVerTodos) {
                // Roles privilegiados siempre tienen acceso directo
                if (in_array($userRole, $rolesVerTodos)) {
                    return 'acceso_directo';
                }
                
                // Verificar si está inscrito (tiene registro en curso_estudiantes)
                $estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
                if ($estudiante && $estudiante->pivot) {
                    // El pivot estado puede ser 'activo', 'completado', 'inactivo', etc.
                    // Normalizamos: 'activo' = inscrito, 'completado' = completado, otro = inscrito
                    $pivotEstado = $estudiante->pivot->estado ?? 'activo';
                    if ($pivotEstado === 'activo') {
                        return 'inscrito';
                    } elseif ($pivotEstado === 'completado') {
                        return 'completado';
                    }
                    return 'inscrito';
                }
                
                // Si no está inscrito pero tiene asignación activa
                $tieneAsignacion = \App\Models\CursoAsignacion::where('curso_id', $curso->id)
                    ->where('estudiante_id', $user->id)
                    ->activas()
                    ->exists();
                
                if ($tieneAsignacion) {
                    return 'no_inscrito'; // Asignado pero no inscrito
                }
                
                return 'sin_acceso';
            })
            ->addColumn('fecha_inscripcion', function ($curso) use ($user, $userRole, $rolesVerTodos) {
                // Roles privilegiados no necesitan fecha de inscripción
                if (in_array($userRole, $rolesVerTodos)) {
                    return '-';
                }
                
                // Verificar si está inscrito
                $estudiante = $curso->estudiantes()->where('users.id', $user->id)->first();
                if ($estudiante && $estudiante->pivot && $estudiante->pivot->created_at) {
                    return $estudiante->pivot->created_at->format('d/m/Y');
                }
                
                // Si tiene asignación, mostrar fecha de asignación
                $asignacion = \App\Models\CursoAsignacion::where('curso_id', $curso->id)
                    ->where('estudiante_id', $user->id)
                    ->activas()
                    ->first();
                
                if ($asignacion) {
                    return $asignacion->fecha_asignacion->format('d/m/Y');
                }
                
                return '-';
            })
            ->addColumn('acciones', function ($curso) use ($user, $userRole, $rolesVerTodos) {
                // Verificar si está inscrito (no solo asignado)
                $isEnrolled = $curso->estudiantes()->where('users.id', $user->id)->exists();
                
                // Super Admin, Admin, Operador - Acceso directo sin inscripción
                if (in_array($userRole, $rolesVerTodos)) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="verCurso(' . $curso->id . ')" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="aulaVirtual(' . $curso->id . ')" title="Aula Virtual">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="accederCurso(' . $curso->id . ')" title="Ejecutar curso">
                                <i class="fas fa-play"></i> Ejecutar
                            </button>
                        </div>
                    ';
                }
                
                // Docente - Botón "Iniciar" en lugar de "Inscribirse"
                if ($userRole === 'Docente') {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="verCurso(' . $curso->id . ')" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="aulaVirtual(' . $curso->id . ')" title="Aula Virtual">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="accederCurso(' . $curso->id . ')" title="Iniciar curso">
                                <i class="fas fa-play"></i> Iniciar
                            </button>
                        </div>
                    ';
                }
                
                // Estudiante inscrito - mostrar botones de acceso
                if ($isEnrolled) {
                    $html = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="verCurso(' . $curso->id . ')" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="aulaVirtual(' . $curso->id . ')" title="Aula Virtual">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="accederCurso(' . $curso->id . ')" title="Acceder al curso">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    ';
                    
                    // Solo para estudiantes normales (no administradores en vista de estudiante, o si queremos mostrarlo a todos, está bien)
                    // Mostrar badge del estado al lado de los botones de acciones
                    $notaFinal = $curso->calcularNotaFinalEstudiante($user->id);
                    $porcentaje = ($notaFinal / 5.0) * 100;
                    
                    if ($porcentaje >= 60) {
                        $estadoStr = 'Aprobado';
                        $estadoCls = 'status-passed';
                        $icono = 'fas fa-check-circle';
                    } elseif ($porcentaje >= 50) {
                        $estadoStr = 'En Riesgo';
                        $estadoCls = 'status-at_risk';
                        $icono = 'fas fa-exclamation-triangle';
                    } else {
                        $estadoStr = 'Reprobado';
                        $estadoCls = 'status-failed';
                        $icono = 'fas fa-times-circle';
                    }
                    
                    $studentName = htmlentities($user->name . ' ' . $user->apellido1 . ' ' . $user->apellido2);
                    $studentDoc = htmlentities($user->tipo_documento . ': ' . $user->numero_documento);
                    $courseName = htmlentities($curso->titulo);
                    $fechaInicio = $curso->fecha_inicio ? \Carbon\Carbon::parse($curso->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') : 'N/A';
                    $fechaFin = $curso->fecha_fin ? \Carbon\Carbon::parse($curso->fecha_fin)->translatedFormat('d \d\e F \d\e Y') : 'N/A';
                    
                    // Inline styles for badge directly attached
                    $badgeStyle = 'display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.875rem; border-radius:20px; font-weight:600; font-size:0.8125rem; white-space:nowrap;';
                    if ($estadoStr === 'Aprobado') {
                        $badgeStyle .= ' background:#d4edda; color:#155724;';
                    } elseif ($estadoStr === 'En Riesgo') {
                        $badgeStyle .= ' background:#fff3cd; color:#856404;';
                    } else {
                        $badgeStyle .= ' background:#f8d7da; color:#721c24;';
                    }

                    $badgeHtml = '<span class="ml-2 ' . $estadoCls . '" style="' . $badgeStyle . '">';
                    
                    if ($estadoStr === 'Aprobado' && $curso->plantilla_certificado_id) {
                        $badgeHtml .= '<a href="javascript:void(0)" class="btn-certificado-preview-student" 
                            data-curso-id="'.$curso->id.'" 
                            data-curso-nombre="'.$courseName.'"
                            data-estudiante-nombre="'.$studentName.'"
                            data-estudiante-documento="'.$studentDoc.'"
                            data-fecha-inicio="'.$fechaInicio.'"
                            data-fecha-fin="'.$fechaFin.'"
                            title="Click para ver certificado" style="text-decoration: none; color: #155724; cursor: pointer; display:inline-flex; align-items:center; gap: 2px; transition: all 0.2s ease;">
                            <i class="'.$icono.'"></i> '.$estadoStr.'
                            <i class="fas fa-certificate ml-1" style="font-size: 0.8rem; color: #ffc107;"></i>
                        </a>';
                    } else {
                        $badgeHtml .= '<i class="'.$icono.'"></i> ' . $estadoStr;
                    }
                    $badgeHtml .= '</span>';
                    
                    return '<div style="display:flex; align-items:center;">' . $html . $badgeHtml . '</div>';
                }
                
                // Estudiante NO inscrito - mostrar botón de inscripción
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="verCurso(' . $curso->id . ')" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="inscribirseCurso(' . $curso->id . ')" title="Inscribirse">
                            <i class="fas fa-user-plus"></i> Inscribirse
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    /**
     * Ver detalles de un curso específico
     */
    public function verCurso(Curso $curso): View
    {
        $user = Auth::user();
        $userRole = $user->role;
        
        // Roles que pueden ver todos los cursos
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        
        // Verificar acceso: roles privilegiados o estar inscrito
        if (!in_array($userRole, $rolesVerTodos) && !$curso->tieneEstudiante($user->id)) {
            abort(403, 'No tienes acceso a este curso');
        }

        $curso->load([
            'area.categoria',
            'instructor',
            'materiales' => function($query) {
                $query->where('es_publico', true)->orderBy('orden');
            },
            'actividades' => function($query) {
                $query->orderBy('fecha_apertura');
            }
        ]);

        $progreso = $curso->tieneEstudiante($user->id) ? $curso->getProgresoEstudiante($user->id) : 0;
        $materialesVistos = $this->getMaterialesVistos($curso->id, $user->id);
        $actividadesCompletadas = $this->getActividadesCompletadas($curso->id, $user->id);
        $resumen = $curso->tieneEstudiante($user->id) ? $curso->getResumenCalificacionesEstudiante($user->id) : ['aprobado' => false];

        return view('academico.curso.index', compact(
            'curso', 'progreso', 'materialesVistos', 'resumen', 'actividadesCompletadas'
        ));
    }

    /**
     * Aula Virtual Interactiva - Vista principal para estudiantes
     */
    public function aulaVirtual(Curso $curso): View
    {
        $user = Auth::user();
        $userRole = $user->role;
        
        // Roles que pueden ver todos los cursos
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        // Verificar acceso: roles privilegiados, instructor del curso o estar inscrito
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            abort(403, 'No tienes acceso a este curso');
        }

        $curso->load([
            'area.categoria',
            'instructor',
            'materiales' => function($query) {
                $query->where('es_publico', true)->orderBy('orden');
            },
            'actividades' => function($query) {
                $query->orderBy('fecha_apertura');
            }
        ]);

        $materiales = $curso->materiales()
            ->where('es_publico', true)
            ->orderBy('orden')
            ->get();

        $progreso = $curso->tieneEstudiante($user->id) ? $curso->getProgresoEstudiante($user->id) : 0;
        $materialesVistos = $this->getMaterialesVistos($curso->id, $user->id);
        $actividadesCompletadas = $this->getActividadesCompletadas($curso->id, $user->id);
        $resumen = $curso->tieneEstudiante($user->id) ? $curso->getResumenCalificacionesEstudiante($user->id) : ['aprobado' => false];

        return view('academico.curso.aula-virtual', compact(
            'curso', 'materiales', 'resumen', 'progreso', 'materialesVistos', 'actividadesCompletadas'
        ));
    }

    /**
     * Ver materiales del curso
     */
    public function verMateriales(Curso $curso): View|RedirectResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        
        // Roles que pueden ver todos los cursos
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            abort(403, 'No tienes acceso a este curso');
        }

        // Si no es una petición AJAX, redirigir a la vista principal con la pestaña activa
        if (!request()->ajax()) {
            return redirect()->route('academico.curso.ver', ['curso' => $curso->id, 'tab' => 'materiales']);
        }

        $materiales = $curso->materiales()
            ->where('es_publico', true)
            ->orderBy('orden')
            ->get();

        $materialesVistos = $this->getMaterialesVistos($curso->id, $user->id);
        
        $resumen = $curso->tieneEstudiante($user->id) ? $curso->getResumenCalificacionesEstudiante($user->id) : ['aprobado' => false];

        return view('academico.curso.materiales', compact('curso', 'materiales', 'resumen', 'materialesVistos'));
    }

    /**
     * Ver actividades del curso
     */
    public function verActividades(Curso $curso): View|RedirectResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        
        // Roles que pueden ver todos los cursos
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            abort(403, 'No tienes acceso a este curso');
        }

        // Si no es una petición AJAX, redirigir a la vista principal con la pestaña activa
        if (!request()->ajax()) {
            return redirect()->route('academico.curso.ver', ['curso' => $curso->id, 'tab' => 'actividades']);
        }

        $actividades = $curso->actividades()
            ->orderBy('fecha_apertura')
            ->get();

        $actividadesCompletadas = $this->getActividadesCompletadas($curso->id, $user->id);
        $resumen = $curso->tieneEstudiante($user->id) ? $curso->getResumenCalificacionesEstudiante($user->id) : ['aprobado' => false];

        return view('academico.curso.actividades', compact('curso', 'actividades', 'actividadesCompletadas'));
    }

    /**
     * Ver evaluaciones del curso
     */
    public function verEvaluaciones(Curso $curso): View|RedirectResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            abort(403, 'No tienes acceso a este curso');
        }

        // Si no es una petición AJAX, redirigir a la vista principal con la pestaña activa
        if (!request()->ajax()) {
            return redirect()->route('academico.curso.ver', ['curso' => $curso->id, 'tab' => 'evaluaciones']);
        }

        // Por ahora, las evaluaciones son actividades de tipo 'evaluacion'
        $evaluaciones = $curso->actividades()
            ->where('tipo', 'evaluacion')
            ->orderBy('fecha_apertura')
            ->get();

        $evaluacionesCompletadas = $this->getActividadesCompletadas($curso->id, $user->id);

        return view('academico.curso.evaluaciones', compact('curso', 'evaluaciones', 'evaluacionesCompletadas'));
    }

    /**
     * Marcar material como visto
     */
    public function marcarMaterialVisto(Request $request, Curso $curso, CursoMaterial $material): JsonResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a este curso'], 403);
        }

        // Verificar si el material tiene prerrequisito
        if ($material->prerequisite_id) {
            // Verificar si el prerrequisito ha sido completado
            $prerequisitoCompletado = DB::table('curso_material_visto')
                ->where('curso_id', $curso->id)
                ->where('material_id', $material->prerequisite_id)
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$prerequisitoCompletado) {
                $prerequisito = CursoMaterial::find($material->prerequisite_id);
                $nombrePrerequisito = $prerequisito ? $prerequisito->titulo : 'el material previo';
                
                return response()->json([
                    'success' => false, 
                    'message' => "Debes completar primero: {$nombrePrerequisito}"
                ], 400);
            }
        }

        // Registrar que el material fue visto
        DB::table('curso_material_visto')->updateOrInsert([
            'curso_id' => $curso->id,
            'material_id' => $material->id,
            'user_id' => $user->id,
        ], [
            'visto_at' => now(),
            'updated_at' => now(),
        ]);

        // Registrar operación de visualización de material
        OperationLogger::logMaterialView($material->id, $material->titulo, $curso->id);

        return response()->json(['success' => true, 'message' => 'Material marcado como visto']);
    }

    /**
     * Entregar actividad
     */
    public function entregarActividad(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;
        
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a este curso'], 403);
        }

        // Validar que la actividad esté abierta
        if ($actividad->fecha_cierre && now() > $actividad->fecha_cierre) {
            return response()->json(['success' => false, 'message' => 'La actividad ya está cerrada'], 400);
        }

        // Registrar entrega de actividad
        DB::table('curso_actividad_entrega')->updateOrInsert([
            'curso_id' => $curso->id,
            'actividad_id' => $actividad->id,
            'user_id' => $user->id,
        ], [
            'contenido' => $request->input('contenido', ''),
            'observaciones_estudiante' => $request->input('observaciones', ''),
            'archivo_path' => $request->hasFile('archivo') ? $request->file('archivo')->store('entregas', 'public') : null,
            'entregado_at' => now(),
            'updated_at' => now(),
        ]);

        // Registrar operación de entrega de actividad
        OperationLogger::logActivitySubmission($actividad->id, $actividad->titulo, $curso->id);

        return response()->json(['success' => true, 'message' => 'Actividad entregada correctamente']);
    }

    /**
     * Inscribir usuario a un curso
     */
    public function inscribirseCurso(Request $request, Curso $curso)
    {
        $user = Auth::user();

        // Verificar que el curso esté activo
        if ($curso->estado !== 'activo') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El curso no está disponible para inscripciones'
                ], 400);
            }
            return redirect()->back()->with('error', 'El curso no está disponible para inscripciones');
        }

        // Verificar si ya está inscrito (solo en curso_estudiantes, no en asignaciones)
        $yaInscrito = $curso->estudiantes()->wherePivot('estudiante_id', $user->id)->exists();
        
        if ($yaInscrito) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya estás inscrito en este curso'
                ], 400);
            }
            return redirect()->back()->with('info', 'Ya estás inscrito en este curso');
        }

        // Verificar límite de estudiantes
        if ($curso->max_estudiantes && $curso->estudiantes_count >= $curso->max_estudiantes) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El curso ha alcanzado el límite máximo de estudiantes'
                ], 400);
            }
            return redirect()->back()->with('error', 'El curso ha alcanzado el límite máximo de estudiantes');
        }

        try {
            $curso->estudiantes()->attach($user->id, [
                'estado' => 'activo',
                'progreso' => 0,
                'fecha_inscripcion' => now(),
                'ultima_actividad' => now(),
            ]);

            // Registrar operación de inscripción
            OperationLogger::logEnrollment($curso->id, $curso->titulo);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Te has inscrito exitosamente al curso'
                ]);
            }
            
            return redirect()->route('academico.cursos-disponibles')->with('success', '¡Te has inscrito exitosamente al curso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al inscribirse al curso: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al inscribirse al curso: ' . $e->getMessage());
        }
    }

    /**
     * Resolver quiz o evaluación (estudiante)
     */
    public function resolverQuiz(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $user = Auth::user();
        $userRole = $user->role;
        $rolesVerTodos = ['Super Admin', 'Admin', 'Administrador', 'Operador'];
        $esInstructor = $curso->instructor_id == $user->id;

        // Verificar que el usuario tenga acceso al curso (inscrito, rol privilegiado o instructor)
        if (!in_array($userRole, $rolesVerTodos) && !$esInstructor && !$curso->tieneEstudiante($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes estar inscrito en el curso para resolver esta actividad'
            ], 403);
        }

        // Verificar que sea un quiz o evaluación
        if (!in_array($actividad->tipo, ['quiz', 'evaluacion'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta actividad no es un quiz ni una evaluación'
            ], 400);
        }

        $tipoLabel = $actividad->tipo === 'quiz' ? 'quiz' : 'evaluación';

        // Verificar que el quiz/evaluación esté habilitado
        if (!$actividad->habilitado) {
            return response()->json([
                'success' => false,
                'message' => "Esta $tipoLabel no está habilitada"
            ], 400);
        }

        // Verificar que el quiz/evaluación esté abierto
        if ($actividad->estado !== 'abierta') {
            return response()->json([
                'success' => false,
                'message' => "Esta $tipoLabel no está disponible"
            ], 400);
        }

        $request->validate([
            'respuestas' => 'required|array',
            'tiempo_transcurrido' => 'nullable|integer'
        ]);

        try {
            $respuestas = $request->respuestas;
            $quizData = $actividad->contenido_json;
            $preguntas = $quizData['questions'] ?? [];
            
            // ========================================================
            // SISTEMA DE AUTO-CALIFICACIÓN BASADO EN PORCENTAJES
            // ========================================================
            // Cada pregunta tiene un porcentaje (%) que suma máx 100%
            // Nota máxima = 5.0
            // Respuesta correcta: (porcentaje/100) × 5
            // Respuesta incorrecta: (porcentaje/100) × 0 = 0
            // Si hay múltiples respuestas correctas:
            //   El porcentaje se distribuye entre la cantidad de respuestas correctas
            //   Cada respuesta correcta marcada: (porcentaje/N_correctas/100) × 5
            //   Cada respuesta incorrecta marcada o no marcada correcta: 0
            // ========================================================
            
            $notaObtenida = 0;
            $resultados = [];

            // Helper: obtener texto de una opción soportando ambos formatos
            // Formato nuevo: {"A": "texto", "B": "texto"} → $options["A"] = "texto"
            // Formato viejo: [{"id":"x","text":"texto","isCorrect":false},...] → buscar por índice
            $getOptionText = function($options, $key) {
                if (!is_array($options)) return '';
                if (isset($options[$key])) {
                    $val = $options[$key];
                    return is_string($val) ? $val : ($val['text'] ?? '');
                }
                // Formato array numérico: key "A" → índice 0, "B" → 1, etc.
                $index = ord(strtoupper($key)) - 65;
                if (isset($options[$index])) {
                    $val = $options[$index];
                    return is_array($val) ? ($val['text'] ?? '') : (string)$val;
                }
                return '';
            };

            foreach ($preguntas as $pregunta) {
                $preguntaId = $pregunta['id'];
                $respuestaEstudiante = $respuestas[$preguntaId] ?? null;
                $porcentajePregunta = floatval($pregunta['points'] ?? 0); // 'points' almacena el porcentaje
                
                // Soportar tanto el formato antiguo (correctAnswer) como el nuevo (correctAnswers)
                $respuestasCorrectas = $pregunta['correctAnswers'] ?? [$pregunta['correctAnswer'] ?? ''];
                $esMultiple = ($pregunta['isMultipleChoice'] ?? false) || count($respuestasCorrectas) > 1;
                
                $puntosEstaPregunta = 0;
                $respuestaEstudianteTexto = '';
                $respuestaCorrectaTexto = '';
                $esCorrecta = false;
                
                if ($esMultiple) {
                    // MÚLTIPLES RESPUESTAS CORRECTAS
                    // El porcentaje se distribuye entre las N respuestas correctas
                    $numCorrectasTotal = count($respuestasCorrectas);
                    $porcentajePorRespuesta = ($numCorrectasTotal > 0) ? $porcentajePregunta / $numCorrectasTotal : 0;
                    
                    // Si el frontend envió un solo string en vez de array, envolverlo
                    if (is_array($respuestaEstudiante)) {
                        $respuestasEstudianteArray = $respuestaEstudiante;
                    } elseif (!empty($respuestaEstudiante)) {
                        $respuestasEstudianteArray = [$respuestaEstudiante];
                    } else {
                        $respuestasEstudianteArray = [];
                    }
                    
                    // Por cada respuesta correcta que el estudiante marcó: sumar (porcentajePorRespuesta/100) × 5
                    $aciertos = 0;
                    foreach ($respuestasEstudianteArray as $resp) {
                        if (in_array($resp, $respuestasCorrectas)) {
                            $puntosEstaPregunta += ($porcentajePorRespuesta / 100) * 5;
                            $aciertos++;
                        }
                    }
                    
                    // Determinar si respondió todo correctamente
                    sort($respuestasEstudianteArray);
                    sort($respuestasCorrectas);
                    $esCorrecta = ($respuestasEstudianteArray === $respuestasCorrectas);
                    
                    // Formatear texto de respuestas
                    $respuestaEstudianteTexto = implode(', ', array_map(function($r) use ($pregunta, $getOptionText) {
                        return $r . ') ' . $getOptionText($pregunta['options'], $r);
                    }, $respuestasEstudianteArray));
                    
                    $respuestaCorrectaTexto = implode(', ', array_map(function($r) use ($pregunta, $getOptionText) {
                        return $r . ') ' . $getOptionText($pregunta['options'], $r);
                    }, $respuestasCorrectas));
                } else {
                    // RESPUESTA ÚNICA
                    $respuestaCorrecta = $respuestasCorrectas[0] ?? '';
                    $esCorrecta = ($respuestaEstudiante === $respuestaCorrecta);
                    
                    if ($esCorrecta) {
                        $puntosEstaPregunta = ($porcentajePregunta / 100) * 5;
                    }
                    
                    $respuestaEstudianteTexto = $respuestaEstudiante ? $respuestaEstudiante . ') ' . $getOptionText($pregunta['options'], $respuestaEstudiante) : 'Sin respuesta';
                    $respuestaCorrectaTexto = $respuestaCorrecta . ') ' . $getOptionText($pregunta['options'], $respuestaCorrecta);
                }
                
                $notaObtenida += $puntosEstaPregunta;

                $resultados[] = [
                    'pregunta_id' => $preguntaId,
                    'pregunta' => $pregunta['text'],
                    'respuesta_estudiante' => $respuestaEstudianteTexto,
                    'respuesta_correcta' => $respuestaCorrectaTexto,
                    'es_correcta' => $esCorrecta,
                    'puntos' => round($puntosEstaPregunta, 2),
                    'porcentaje_pregunta' => $porcentajePregunta,
                    'es_multiple' => $esMultiple
                ];
            }

            // La nota final está en escala 0-5.0
            $notaFinal = min(round($notaObtenida, 2), 5.0);
            $notaMinimaAprobacion = floatval($actividad->nota_minima_aprobacion ?? 3.0);
            $aprobado = $notaFinal >= $notaMinimaAprobacion;

            // Guardar resultado en la base de datos (calificación en escala 0-5.0)
            DB::table('curso_actividad_entrega')->updateOrInsert(
                [
                    'curso_id' => $curso->id,
                    'actividad_id' => $actividad->id,
                    'user_id' => $user->id
                ],
                [
                    'contenido' => json_encode([
                        'respuestas' => $respuestas,
                        'resultados' => $resultados,
                        'tiempo_transcurrido' => $request->tiempo_transcurrido
                    ]),
                    'puntos_obtenidos' => $notaFinal,
                    'calificacion' => $notaFinal,
                    'estado' => $aprobado ? 'aprobado' : 'revisado',
                    'entregado_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Actualizar progreso del estudiante
            $curso->actualizarProgresoEstudiante($user->id);

            // Registrar operación de resolución de quiz
            OperationLogger::logQuizSubmission($actividad->id, $actividad->titulo, $notaFinal, $curso->id);

            return response()->json([
                'success' => true,
                'message' => $aprobado ? '¡Quiz aprobado!' : 'Quiz completado',
                'resultados' => $resultados,
                'nota_obtenida' => $notaFinal,
                'nota_maxima' => 5.0,
                'nota_minima_aprobacion' => $notaMinimaAprobacion,
                'porcentaje' => round(($notaFinal / 5.0) * 100, 2),
                'aprobado' => $aprobado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener materiales vistos por el usuario
     */
    private function getMaterialesVistos(int $cursoId, int $userId): array
    {
        return DB::table('curso_material_visto')
            ->where('curso_id', $cursoId)
            ->where('user_id', $userId)
            ->pluck('material_id')
            ->toArray();
    }

    /**
     * Obtener actividades completadas por el usuario
     */
    private function getActividadesCompletadas(int $cursoId, int $userId): array
    {
        return DB::table('curso_actividad_entrega')
            ->where('curso_id', $cursoId)
            ->where('user_id', $userId)
            ->pluck('actividad_id')
            ->toArray();
    }

    /**
     * Generar Certificado para el estudiante
     */
    public function generarCertificado(Curso $curso)
    {
        $user = Auth::user();

        // Verificar inscripción
        $inscrito = DB::table('curso_estudiantes')
            ->where('curso_id', $curso->id)
            ->where('estudiante_id', $user->id)
            ->exists();

        if (!$inscrito) {
            abort(403, 'No estás inscrito en este curso.');
        }

        // Obtener resumen y nota final
        $resumen = $curso->getResumenCalificacionesEstudiante($user->id);
        $notaFinal = number_format($resumen['nota_final'], 1);

        if (!$resumen['aprobado']) {
            abort(403, 'Aún no has aprobado este curso.');
        }

        // Encontrar plantilla
        $plantilla = $curso->plantillaCertificado;
        if (!$plantilla) {
            // Placeholder o Fallback si no tiene plantilla asiganada
            abort(404, 'Este curso aún no tiene un diseño de certificado configurado. Contacte a soporte.');
        }

        // Obtener o crear registro de certificado emitido con código de verificación
        $certificadoEmitido = \App\Models\CertificadoEmitido::obtenerOCrear(
            $curso->id, $user->id, $resumen['nota_final'], $plantilla->id
        );

        // Detectar si se carga en iframe
        $enIframe = request()->query('iframe', false);

        $response = response()->view('academico.curso.certificado', compact('curso', 'user', 'resumen', 'plantilla', 'notaFinal', 'certificadoEmitido', 'enIframe'));
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        return $response;
    }
}



