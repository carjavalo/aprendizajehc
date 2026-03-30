<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Area;
use App\Models\User;
use App\Models\CursoMaterial;
use App\Models\CursoForo;
use App\Models\CursoActividad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('cursos.view');
        $areas = Area::with('categoria')->orderBy('descripcion')->get();
        return view('admin.capacitaciones.cursos.index', compact('areas'));
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $query = Curso::with(['area.categoria', 'instructor'])
                     ->select(['id', 'titulo', 'descripcion', 'id_area', 'instructor_id', 
                              'fecha_inicio', 'fecha_fin', 'estado', 'codigo_acceso', 
                              'max_estudiantes', 'created_at']);

        // Aplicar filtros
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('area')) {
            $query->where('id_area', $request->area);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->instructor);
        }

        return DataTables::of($query)
            ->addColumn('area_info', function ($curso) {
                $areaDesc = $curso->area->descripcion ?? 'Sin área';
                $catDesc = $curso->area && $curso->area->categoria ? $curso->area->categoria->descripcion : 'Sin categoría';
                return '<div class="text-sm">' .
                       '<strong>' . $areaDesc . '</strong><br>' .
                       '<small class="text-muted">' . $catDesc . '</small>' .
                       '</div>';
            })
            ->addColumn('instructor_info', function ($curso) {
                $instructorNombre = $curso->instructor ? ($curso->instructor->name . ' ' . $curso->instructor->apellido1) : 'Sin instructor';
                $instructorEmail = $curso->instructor ? $curso->instructor->email : '-';
                return '<div class="text-sm">' .
                       '<strong>' . $instructorNombre . '</strong><br>' .
                       '<small class="text-muted">' . $instructorEmail . '</small>' .
                       '</div>';
            })
            ->addColumn('estudiantes_info', function ($curso) {
                $count = $curso->estudiantes_count;
                $max = $curso->max_estudiantes ? $curso->max_estudiantes : '∞';
                
                return '<div class="text-center">' .
                       '<span class="badge badge-info">' . $count . ' / ' . $max . '</span>' .
                       '</div>';
            })
            ->addColumn('fechas_info', function ($curso) {
                $inicio = $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'Sin fecha';
                $fin = $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'Sin fecha';
                
                return '<div class="text-sm">' .
                       '<strong>Inicio:</strong> ' . $inicio . '<br>' .
                       '<strong>Fin:</strong> ' . $fin .
                       '</div>';
            })
            ->addColumn('estado_badge', function ($curso) {
                return $curso->estado_badge;
            })
            ->addColumn('actions', function ($curso) {
                $user = Auth::user();
                $html = '<div class="btn-group" role="group">';
                
                $html .= '<button type="button" class="btn btn-info btn-sm" onclick="viewCurso(' . $curso->id . ')" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>';
                
                $html .= '<button type="button" class="btn btn-warning btn-sm" onclick="viewCursoStats(' . $curso->id . ')" title="Estadísticas">
                            <i class="fas fa-chart-bar"></i>
                        </button>';
                
                if (Gate::allows('cursos.edit')) {
                    $html .= '<button type="button" class="btn btn-primary btn-sm" onclick="editCurso(' . $curso->id . ')" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>';
                }
                
                $html .= '<a href="' . route('capacitaciones.cursos.classroom', $curso->id) . '" class="btn btn-success btn-sm" title="Classroom">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </a>';
                
                if (Gate::allows('cursos.delete')) {
                    $html .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteCurso(' . $curso->id . ')" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['area_info', 'instructor_info', 'estudiantes_info', 'fechas_info', 'estado_badge', 'actions'])
            ->make(true);
    }

    /**
     * Get course statistics for modal
     */
    public function getStats(Curso $curso): JsonResponse
    {
        try {
            // Cargar relaciones necesarias
            $curso->load(['instructor', 'area.categoria', 'actividades']);
            
            // Obtener estudiantes con su progreso
            $estudiantesRaw = $curso->estudiantes()
                ->withPivot(['estado', 'progreso', 'fecha_inscripcion', 'ultima_actividad'])
                ->get();
            
            // Obtener actividades del curso
            $actividades = $curso->actividades ?? collect([]);
            $totalActividades = $actividades->count();
            
            // Resumen de actividades
            $resumenActividades = [
                'total' => $totalActividades,
                'tareas' => $actividades->where('tipo', 'tarea')->count(),
                'quizzes' => $actividades->where('tipo', 'quiz')->count(),
                'evaluaciones' => $actividades->where('tipo', 'evaluacion')->count(),
                'proyectos' => $actividades->where('tipo', 'proyecto')->count(),
            ];
            
            // Obtener entregas de actividades
            $entregas = collect([]);
            try {
                $entregas = DB::table('curso_actividad_entrega')
                    ->where('curso_id', $curso->id)
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('No se pudo obtener entregas: ' . $e->getMessage());
            }
            
            // Procesar estudiantes
            $estudiantes = [];
            foreach ($estudiantesRaw as $estudiante) {
                $entregasEstudiante = $entregas->where('user_id', $estudiante->id);
                $actividadesRealizadas = $entregasEstudiante->count();
                $actividadesFaltantes = max(0, $totalActividades - $actividadesRealizadas);
                
                // Contar aprobadas y no aprobadas
                $aprobadas = $entregasEstudiante->filter(function ($entrega) {
                    return isset($entrega->calificacion) && $entrega->calificacion !== null && $entrega->calificacion >= 60;
                })->count();
                
                $noAprobadas = $entregasEstudiante->filter(function ($entrega) {
                    return isset($entrega->calificacion) && $entrega->calificacion !== null && $entrega->calificacion < 60;
                })->count();
                
                $sinCalificar = $actividadesRealizadas - $aprobadas - $noAprobadas;
                
                $estudiantes[] = [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->name . ' ' . ($estudiante->apellido1 ?? ''),
                    'email' => $estudiante->email,
                    'estado' => $estudiante->pivot->estado ?? 'activo',
                    'progreso' => $estudiante->pivot->progreso ?? 0,
                    'fecha_inscripcion' => $estudiante->pivot->fecha_inscripcion 
                        ? \Carbon\Carbon::parse($estudiante->pivot->fecha_inscripcion)->format('d/m/Y') 
                        : 'N/A',
                    'ultima_actividad' => $estudiante->pivot->ultima_actividad 
                        ? \Carbon\Carbon::parse($estudiante->pivot->ultima_actividad)->diffForHumans() 
                        : 'Sin actividad',
                    'actividades' => [
                        'realizadas' => $actividadesRealizadas,
                        'faltantes' => $actividadesFaltantes,
                        'aprobadas' => $aprobadas,
                        'no_aprobadas' => $noAprobadas,
                        'sin_calificar' => max(0, $sinCalificar),
                    ],
                ];
            }
            
            // Calcular progreso promedio del curso
            $progresoPromedio = count($estudiantes) > 0 
                ? round(collect($estudiantes)->avg('progreso'), 1) 
                : 0;
            
            // Contar estudiantes activos
            $estudiantesActivos = collect($estudiantes)->where('estado', 'activo')->count();
            
            // Resumen general
            $resumenGeneral = [
                'total_estudiantes' => count($estudiantes),
                'estudiantes_activos' => $estudiantesActivos,
                'progreso_promedio' => $progresoPromedio,
                'total_entregas' => $entregas->count(),
            ];
            
            return response()->json([
                'success' => true,
                'curso' => [
                    'id' => $curso->id,
                    'titulo' => $curso->titulo,
                    'instructor' => $curso->instructor ? $curso->instructor->name : 'Sin instructor',
                    'estado' => $curso->estado,
                ],
                'estudiantes' => $estudiantes,
                'actividades' => $resumenActividades,
                'resumen' => $resumenGeneral,
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', '0');
            
        } catch (\Exception $e) {
            \Log::error('Error en getStats: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('cursos.create');
        $areas = Area::with('categoria')->orderBy('descripcion')->get();
        // Creadores del curso: usuarios con roles administrativos
        $creadores = User::whereIn('role', ['Super Admin', 'Administrador', 'Admin', 'Operador'])
                           ->orderBy('name')
                           ->get();
                           
        $plantillas = \App\Models\PlantillaCertificado::orderBy('nombre')->get();

        return view('admin.capacitaciones.cursos.create', compact('areas', 'creadores', 'plantillas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('cursos.create');
        // Aumentar límites de tiempo y memoria para cursos grandes
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:65535',
            'id_area' => 'required|exists:areas,id',
            'instructor_id' => 'required|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'nullable|in:borrador,activo,finalizado,archivado',
            'max_estudiantes' => 'nullable|integer|min:1',
            'plantilla_certificado_id' => 'nullable|exists:plantilla_certificados,id',
            'objetivos' => 'nullable|string|max:65535',
            'requisitos' => 'nullable|string|max:65535',
            'duracion_horas' => 'nullable|integer|min:1',
            'imagen_portada' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'materials_data' => 'nullable|string',
            'forum_posts_data' => 'nullable|string',
            'activities_data' => 'nullable|string',
            'material_files' => 'nullable|array',
            'material_files.*' => 'nullable|file|max:204800', // 200MB max
            'nota_minima_aprobacion' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->except(['materials_data', 'forum_posts_data', 'activities_data']);

            // Establecer estado por defecto
            if (!isset($data['estado'])) {
                $data['estado'] = 'borrador';
            }

            // Manejar la imagen de portada
            if ($request->hasFile('imagen_portada')) {
                $data['imagen_portada'] = $request->file('imagen_portada')
                    ->store('cursos/portadas', 'public');
            }

            // Crear el curso
            $curso = Curso::create($data);
            
            \Log::info('Curso creado con ID: ' . $curso->id);

            // Procesar materiales del wizard
            if ($request->has('materials_data') && !empty($request->input('materials_data'))) {
                // Validar que la suma de porcentajes no exceda 100%
                $materialsDataCheck = json_decode($request->input('materials_data'), true);
                if (is_array($materialsDataCheck)) {
                    $totalPorcentaje = array_reduce($materialsDataCheck, function($carry, $mat) {
                        return $carry + (floatval($mat['porcentajeCurso'] ?? 0));
                    }, 0);
                    
                    if ($totalPorcentaje > 100.0) {
                        throw new \Exception("La suma de los porcentajes de los materiales ({$totalPorcentaje}%) excede el 100% permitido. Ajusta los porcentajes antes de crear el curso.");
                    }
                    
                    // Validar que cada material tenga porcentaje > 0
                    foreach ($materialsDataCheck as $index => $matCheck) {
                        $porcentaje = floatval($matCheck['porcentajeCurso'] ?? 0);
                        if ($porcentaje <= 0) {
                            $titulo = $matCheck['title'] ?? 'Material ' . ($index + 1);
                            throw new \Exception("El material '{$titulo}' tiene porcentaje 0%. Cada material debe tener un porcentaje asignado.");
                        }
                    }
                }
                
                try {
                    $this->processMaterials($curso, $request);
                    \Log::info('Materiales procesados para curso: ' . $curso->id);
                } catch (\Exception $e) {
                    \Log::error('Error procesando materiales: ' . $e->getMessage());
                    throw new \Exception('Error al procesar materiales: ' . $e->getMessage());
                }
            }

            // Procesar posts del foro
            if ($request->has('forum_posts_data') && !empty($request->input('forum_posts_data'))) {
                try {
                    $this->processForumPosts($curso, $request);
                    \Log::info('Posts del foro procesados para curso: ' . $curso->id);
                } catch (\Exception $e) {
                    \Log::error('Error procesando posts del foro: ' . $e->getMessage());
                    throw new \Exception('Error al procesar posts del foro: ' . $e->getMessage());
                }
            }

            // Procesar actividades
            if ($request->has('activities_data') && !empty($request->input('activities_data'))) {
                // Validar que la suma de porcentajes de actividades por material no exceda 100%
                $activitiesDataCheck = json_decode($request->input('activities_data'), true);
                if (is_array($activitiesDataCheck) && is_array($materialsDataCheck ?? null)) {
                    // Agrupar actividades por materialId y sumar porcentajes
                    $porcentajesPorMaterial = [];
                    foreach ($activitiesDataCheck as $actData) {
                        $matId = $actData['materialId'] ?? null;
                        if ($matId) {
                            if (!isset($porcentajesPorMaterial[$matId])) {
                                $porcentajesPorMaterial[$matId] = 0;
                            }
                            $porcentajesPorMaterial[$matId] += floatval($actData['porcentajeMaterial'] ?? 0);
                        }
                    }
                    
                    // Verificar que ningún material exceda 100%
                    foreach ($porcentajesPorMaterial as $matId => $totalPorcentajeAct) {
                        if ($totalPorcentajeAct > 100.0) {
                            // Buscar el nombre del material
                            $nombreMaterial = 'Material ID ' . $matId;
                            foreach ($materialsDataCheck as $mat) {
                                if (($mat['id'] ?? null) == $matId) {
                                    $nombreMaterial = $mat['title'] ?? $nombreMaterial;
                                    break;
                                }
                            }
                            throw new \Exception("La suma de porcentajes de las actividades del material '{$nombreMaterial}' es {$totalPorcentajeAct}%, lo cual excede el 100% permitido.");
                        }
                    }
                    
                    // Validar que cada actividad tenga porcentaje > 0
                    foreach ($activitiesDataCheck as $index => $actData) {
                        $porcentaje = floatval($actData['porcentajeMaterial'] ?? 0);
                        if ($porcentaje <= 0) {
                            $tituloAct = $actData['title'] ?? 'Actividad ' . ($index + 1);
                            throw new \Exception("La actividad '{$tituloAct}' tiene porcentaje 0%. Cada actividad debe tener un porcentaje asignado mayor a 0%.");
                        }
                    }
                    
                    // Validar que cada material tenga al menos una actividad
                    foreach ($materialsDataCheck as $mat) {
                        $matId = $mat['id'] ?? null;
                        if ($matId) {
                            $tieneActividad = false;
                            foreach ($activitiesDataCheck as $actData) {
                                if (($actData['materialId'] ?? null) == $matId) {
                                    $tieneActividad = true;
                                    break;
                                }
                            }
                            if (!$tieneActividad) {
                                $nombreMaterial = $mat['title'] ?? 'Material ID ' . $matId;
                                throw new \Exception("El material '{$nombreMaterial}' no tiene actividades asignadas. Cada material debe tener al menos una actividad.");
                            }
                        }
                    }
                }
                
                try {
                    $this->processActivities($curso, $request);
                    \Log::info('Actividades procesadas para curso: ' . $curso->id);
                } catch (\Exception $e) {
                    \Log::error('Error procesando actividades: ' . $e->getMessage());
                    throw new \Exception('Error al procesar actividades: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Curso creado exitosamente con todo su contenido',
                'curso_id' => $curso->id,
                'curso' => $curso
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear curso: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el curso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Curso $curso): JsonResponse
    {
        $curso->load(['area.categoria', 'instructor', 'estudiantes']);
        
        return response()->json([
            'id' => $curso->id,
            'titulo' => $curso->titulo,
            'descripcion' => $curso->descripcion,
            'area' => $curso->area_descripcion,
            'categoria' => $curso->area->categoria->descripcion,
            'instructor' => $curso->instructor_nombre,
            'fecha_inicio' => $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'Sin fecha',
            'fecha_fin' => $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'Sin fecha',
            'estado' => ucfirst($curso->estado),
            'codigo_acceso' => $curso->codigo_acceso,
            'max_estudiantes' => $curso->max_estudiantes ?? 'Sin límite',
            'estudiantes_inscritos' => $curso->estudiantes_count,
            'objetivos' => $curso->objetivos,
            'requisitos' => $curso->requisitos,
            'duracion_horas' => $curso->duracion_horas,
            'imagen_portada_url' => $curso->imagen_portada_url,
            'created_at' => $curso->created_at->format('d/m/Y H:i'),
            'updated_at' => $curso->updated_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso): View
    {
        Gate::authorize('cursos.edit');
        // Cargar relaciones necesarias
        $curso->load(['area.categoria', 'instructor']);

        // Obtener datos para los selects
        $areas = Area::with('categoria')->orderBy('descripcion')->get();
        // Creadores del curso: usuarios con roles administrativos
        $creadores = User::whereIn('role', ['Super Admin', 'Administrador', 'Admin', 'Operador'])
                           ->orderBy('name')
                           ->get();
                           
        $plantillas = \App\Models\PlantillaCertificado::orderBy('nombre')->get();

        return view('admin.capacitaciones.cursos.edit', compact('curso', 'areas', 'creadores', 'plantillas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curso $curso): JsonResponse
    {
        Gate::authorize('cursos.edit');
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'id_area' => 'required|exists:areas,id',
            'instructor_id' => 'required|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:borrador,activo,finalizado,archivado',
            'max_estudiantes' => 'nullable|integer|min:1',
            'plantilla_certificado_id' => 'nullable|exists:plantilla_certificados,id',
            'objetivos' => 'nullable|string',
            'requisitos' => 'nullable|string',
            'duracion_horas' => 'nullable|integer|min:1',
            'imagen_portada' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nota_minima_aprobacion' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            // Manejar la imagen de portada
            if ($request->hasFile('imagen_portada')) {
                // Eliminar imagen anterior si existe
                if ($curso->imagen_portada) {
                    Storage::disk('public')->delete($curso->imagen_portada);
                }
                
                $data['imagen_portada'] = $request->file('imagen_portada')
                    ->store('cursos/portadas', 'public');
            }

            $curso->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Curso actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el curso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curso $curso): JsonResponse
    {
        Gate::authorize('cursos.delete');
        try {
            // Eliminar imagen de portada si existe
            if ($curso->imagen_portada) {
                Storage::disk('public')->delete($curso->imagen_portada);
            }

            $curso->delete();

            return response()->json([
                'success' => true,
                'message' => 'Curso eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el curso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar materiales del wizard
     */
    private function processMaterials(Curso $curso, Request $request)
    {
        $materialsData = json_decode($request->input('materials_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al decodificar datos de materiales: ' . json_last_error_msg());
        }

        if (!$materialsData || !is_array($materialsData)) return;

        // Mapeo de IDs temporales a IDs reales para prerrequisitos
        $idMapping = [];

        foreach ($materialsData as $index => $materialData) {
            $data = [
                'curso_id' => $curso->id,
                'titulo' => $materialData['title'] ?? 'Sin título',
                'descripcion' => $materialData['description'] ?? null,
                'tipo' => $materialData['type'] ?? 'archivo',
                'orden' => $materialData['order'] ?? $index + 1,
                'es_publico' => $materialData['isPublic'] ?? true,
                'porcentaje_curso' => $materialData['porcentajeCurso'] ?? 0,
                'nota_minima_aprobacion' => $materialData['notaMinimaAprobacion'] ?? 3.0,
            ];

            // Manejar archivo subido
            $materialFiles = $request->file('material_files');
            if (isset($materialData['file']) && $materialFiles && isset($materialFiles[$index])) {
                $file = $materialFiles[$index];
                $path = $file->store("cursos/{$curso->id}/materiales", 'public');

                $data['archivo_path'] = $path;
                $data['archivo_nombre'] = $file->getClientOriginalName();
                $data['archivo_extension'] = $file->getClientOriginalExtension();
                $data['archivo_size'] = $file->getSize();
            }

            // Manejar URL externa
            if (isset($materialData['url']) && !empty($materialData['url'])) {
                $data['url_externa'] = $materialData['url'];
            }

            // Manejar datos de clase en línea (Meet)
            if (isset($materialData['meetUrl']) && !empty($materialData['meetUrl'])) {
                $data['url_externa'] = $materialData['meetUrl'];
            }
            if (isset($materialData['meetStart']) && !empty($materialData['meetStart'])) {
                $data['fecha_inicio'] = $materialData['meetStart'];
            }
            if (isset($materialData['meetEnd']) && !empty($materialData['meetEnd'])) {
                $data['fecha_fin'] = $materialData['meetEnd'];
            }

            $material = CursoMaterial::create($data);
            
            // Guardar mapeo de ID temporal a ID real
            if (isset($materialData['id'])) {
                $idMapping[$materialData['id']] = $material->id;
            }
        }
        
        // Segunda pasada: actualizar prerrequisitos con IDs reales
        foreach ($materialsData as $index => $materialData) {
            if (isset($materialData['prerequisiteId']) && $materialData['prerequisiteId']) {
                $tempPrerequisiteId = $materialData['prerequisiteId'];
                $realPrerequisiteId = $idMapping[$tempPrerequisiteId] ?? null;
                
                if ($realPrerequisiteId && isset($materialData['id'])) {
                    $realMaterialId = $idMapping[$materialData['id']] ?? null;
                    if ($realMaterialId) {
                        CursoMaterial::where('id', $realMaterialId)
                            ->update(['prerequisite_id' => $realPrerequisiteId]);
                    }
                }
            }
        }
    }

    /**
     * Procesar posts del foro del wizard
     */
    private function processForumPosts(Curso $curso, Request $request)
    {
        $forumPostsData = json_decode($request->input('forum_posts_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al decodificar datos de posts del foro: ' . json_last_error_msg());
        }

        if (!$forumPostsData || !is_array($forumPostsData)) return;

        foreach ($forumPostsData as $postData) {
            CursoForo::create([
                'curso_id' => $curso->id,
                'usuario_id' => $curso->instructor_id,
                'titulo' => $postData['title'] ?? 'Sin título',
                'contenido' => $postData['content'] ?? '',
                'es_anuncio' => $postData['isAnnouncement'] ?? false,
                'es_fijado' => $postData['isPinned'] ?? false,
            ]);
        }
    }

    /**
     * Procesar actividades del wizard
     */
    private function processActivities(Curso $curso, Request $request)
    {
        $activitiesData = json_decode($request->input('activities_data'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al decodificar datos de actividades: ' . json_last_error_msg());
        }
        
        $materialsData = json_decode($request->input('materials_data'), true);

        if (!$activitiesData || !is_array($activitiesData)) return;

        // Crear mapeo de IDs temporales de materiales a IDs reales
        $materialIdMapping = [];
        if ($materialsData && is_array($materialsData)) {
            $cursoMateriales = $curso->materiales()->orderBy('id')->get();
            foreach ($materialsData as $index => $matData) {
                if (isset($matData['id']) && isset($cursoMateriales[$index])) {
                    $materialIdMapping[$matData['id']] = $cursoMateriales[$index]->id;
                }
            }
        }

        foreach ($activitiesData as $activityData) {
            $data = [
                'curso_id' => $curso->id,
                'titulo' => $activityData['title'] ?? 'Sin título',
                'descripcion' => $activityData['description'] ?? null,
                'instrucciones' => $activityData['instructions'] ?? null,
                'tipo' => $activityData['type'] ?? 'tarea',
                'puntos_maximos' => $activityData['points'] ?? 100,
                'intentos_permitidos' => $activityData['attempts'] ?? 1,
                'es_obligatoria' => $activityData['isRequired'] ?? true,
                'permite_entregas_tardias' => $activityData['allowLateSubmissions'] ?? false,
                'porcentaje_curso' => $activityData['porcentajeMaterial'] ?? 0,
                'nota_minima_aprobacion' => $activityData['notaMinimaAprobacion'] ?? 3.0,
            ];

            // Manejar material_id (convertir ID temporal a ID real)
            if (isset($activityData['materialId']) && $activityData['materialId']) {
                $tempMaterialId = $activityData['materialId'];
                if (isset($materialIdMapping[$tempMaterialId])) {
                    $data['material_id'] = $materialIdMapping[$tempMaterialId];
                }
            }

            // Manejar fechas
            if (!empty($activityData['openDate'])) {
                $data['fecha_apertura'] = $activityData['openDate'];
            }

            if (!empty($activityData['closeDate'])) {
                $data['fecha_cierre'] = $activityData['closeDate'];
            }

            // Manejar datos del quiz si existe
            if (isset($activityData['quizData']) && !empty($activityData['quizData'])) {
                $quizData = $activityData['quizData'];
                
                // Limpiar y validar las preguntas para evitar datos corruptos
                $questions = [];
                if (isset($quizData['questions']) && is_array($quizData['questions'])) {
                    foreach ($quizData['questions'] as $question) {
                        // Procesar opciones según el formato recibido
                        $cleanOptions = [];
                        if (isset($question['options']) && is_array($question['options'])) {
                            $firstKey = array_key_first($question['options']);
                            if (is_string($firstKey) && preg_match('/^[A-Z]$/', $firstKey)) {
                                // Formato nuevo del frontend: {"A": "texto", "B": "texto"}
                                foreach ($question['options'] as $letter => $text) {
                                    $cleanOptions[$letter] = is_string($text) ? mb_substr($text, 0, 1000) : (string)$text;
                                }
                            } else {
                                // Formato antiguo: array de objetos [{id, text, isCorrect}]
                                foreach ($question['options'] as $index => $option) {
                                    if (is_array($option)) {
                                        $letter = chr(65 + $index); // A, B, C...
                                        $cleanOptions[$letter] = mb_substr($option['text'] ?? '', 0, 1000);
                                    }
                                }
                            }
                        }

                        // Determinar respuestas correctas
                        $correctAnswers = $question['correctAnswers'] ?? [];
                        if (empty($correctAnswers) && isset($question['correctAnswer']) && $question['correctAnswer'] !== null) {
                            $correctAnswers = [$question['correctAnswer']];
                        }

                        $cleanQuestion = [
                            'id' => $question['id'] ?? uniqid(),
                            'type' => $question['type'] ?? 'multiple',
                            'text' => mb_substr($question['text'] ?? '', 0, 5000),
                            'points' => floatval($question['points'] ?? 1),
                            'options' => $cleanOptions,
                            'correctAnswers' => $correctAnswers,
                            'isMultipleChoice' => ($question['isMultipleChoice'] ?? false) || count($correctAnswers) > 1,
                        ];
                        
                        // Mantener correctAnswer para compatibilidad con quizzes de respuesta única
                        if (count($correctAnswers) === 1) {
                            $cleanQuestion['correctAnswer'] = $correctAnswers[0];
                        }
                        
                        $questions[] = $cleanQuestion;
                    }
                }
                
                // Almacenar las preguntas y respuestas como JSON
                $contenidoJson = [
                    'duration' => intval($quizData['duration'] ?? 30),
                    'totalPoints' => floatval($quizData['totalPoints'] ?? 0),
                    'questions' => $questions
                ];
                
                // Preservar configuración del banco de preguntas anti-fraude
                if (isset($quizData['quizConfig'])) {
                    $contenidoJson['quizConfig'] = [
                        'enableQuestionBank' => $quizData['quizConfig']['enableQuestionBank'] ?? false,
                        'questionsPerAttempt' => intval($quizData['quizConfig']['questionsPerAttempt'] ?? count($questions)),
                        'randomizeOrder' => $quizData['quizConfig']['randomizeOrder'] ?? false,
                    ];
                }
                
                $data['contenido_json'] = $contenidoJson;
                
                // Actualizar puntos máximos con el total del quiz (redondeado)
                if (isset($quizData['totalPoints'])) {
                    $data['puntos_maximos'] = round(floatval($quizData['totalPoints']), 1);
                }
            }

            // Manejar actividades prerrequisito
            if (isset($activityData['prerequisiteActivityIds']) && !empty($activityData['prerequisiteActivityIds'])) {
                // Los IDs de actividades prerrequisito son temporales, necesitamos mapearlos después
                // Por ahora guardamos los IDs temporales y los actualizaremos después de crear todas las actividades
                $data['prerequisite_activity_ids'] = $activityData['prerequisiteActivityIds'];
            }

            CursoActividad::create($data);
        }
    }
}

