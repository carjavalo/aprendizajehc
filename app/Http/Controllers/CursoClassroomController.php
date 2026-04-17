<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoMaterial;
use App\Models\CursoForo;
use App\Models\CursoActividad;
use App\Services\OperationLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CursoClassroomController extends Controller
{
    /**
     * Mostrar la vista principal del classroom
     */
    public function index(Curso $curso)
    {
        // Verificar acceso al curso
        $this->verificarAccesoCurso($curso);

        $curso->loadCount('estudiantes');
        $curso->load([
            'area.categoria', 
            'instructor', 
            'estudiantes',
            'materiales' => function($query) {
                $query->where('es_publico', true)->orderBy('orden');
            },
            'foros' => function($query) {
                $query->with('usuario')->orderBy('es_fijado', 'desc')
                      ->orderBy('es_anuncio', 'desc')
                      ->orderBy('created_at', 'desc');
            },
            'actividades' => function($query) {
                $query->orderBy('fecha_apertura');
            }
        ]);

        $user = Auth::user();
        $esInstructor = $curso->esGestorODocente($user);
        $esEstudiante = $curso->tieneEstudiante($user->id);
        $progreso = $esEstudiante ? $curso->getProgresoEstudiante($user->id) : 0;

        return view('admin.capacitaciones.cursos.classroom.index', compact(
            'curso', 'esInstructor', 'esEstudiante', 'progreso'
        ));
    }

    /**
     * Mostrar la pestaña de materiales
     */
    public function materiales(Curso $curso)
    {
        try {
            $this->verificarAccesoCurso($curso);

            $materiales = $curso->materiales()
                ->where('es_publico', true)
                ->orderBy('orden')
                ->get();

            $user = Auth::user();
            $esInstructor = $curso->esGestorODocente($user);

            // Agregar headers para forzar no-cache
            return response()
                ->view('admin.capacitaciones.cursos.classroom.materiales', compact(
                    'curso', 'materiales', 'esInstructor'
                ))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            // Si es una petición AJAX, retornar JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar materiales: ' . $e->getMessage()
                ], 500);
            }
            
            // Si no, retornar la vista con error
            return response()->view('errors.500', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Subir nuevo material
     */
    public function subirMaterial(Request $request, Curso $curso): JsonResponse
    {
        // Debug: Log the request data
        \Log::info('subirMaterial called', [
            'curso_id' => $curso->id,
            'user_id' => Auth::id(),
            'instructor_id' => $curso->instructor_id,
            'request_data' => $request->all()
        ]);

        // Solo el instructor, admin u operador pueden subir materiales
        $user = Auth::user();
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para subir materiales'
            ], 403);
        }

        // Limpiar url_externa si está vacía para evitar problemas de validación
        $datosValidacion = $request->all();
        if (empty($datosValidacion['url_externa'])) {
            unset($datosValidacion['url_externa']);
        }

        $validator = Validator::make($datosValidacion, [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:archivo,video,imagen,documento',
            'archivo' => 'required_without:url_externa|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,avi,mov,txt,zip,rar',
            'url_externa' => 'nullable|url', // Cambiado a nullable en lugar de required_without
            'orden' => 'nullable|integer|min:0',
            'porcentaje_curso' => 'nullable|numeric|min:0|max:100',
            'nota_minima_aprobacion' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar que el porcentaje no exceda el disponible
        $porcentajeSolicitado = floatval($request->input('porcentaje_curso', 0));
        if ($porcentajeSolicitado > 0) {
            $porcentajeActual = $curso->materiales()->sum('porcentaje_curso');
            if (($porcentajeActual + $porcentajeSolicitado) > 100) {
                $disponible = 100 - $porcentajeActual;
                return response()->json([
                    'success' => false,
                    'message' => "El porcentaje total de materiales excede el 100%. Porcentaje disponible: {$disponible}%"
                ], 422);
            }
        }

        try {
            $data = $request->all();
            $data['curso_id'] = $curso->id;

            // Manejar archivo subido
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                
                // Validaciones adicionales de seguridad
                $allowedMimes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'ppt' => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/x-msvideo',
                    'mov' => 'video/quicktime',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed'
                ];
                
                $extension = strtolower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
                
                // Verificar que la extensión coincida con el MIME type
                if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de archivo no válido o sospechoso'
                    ], 422);
                }
                
                // Generar nombre único para evitar conflictos
                $fileName = time() . '_' . Str::random(10) . '.' . $extension;
                $path = $file->storeAs('cursos/' . $curso->id . '/materiales', $fileName, 'public');
                
                $data['archivo_path'] = $path;
                $data['archivo_nombre'] = $file->getClientOriginalName();
                $data['archivo_extension'] = $extension;
                $data['archivo_size'] = $file->getSize();
            }

            // Establecer orden automático si no se especifica
            if (!isset($data['orden'])) {
                $data['orden'] = CursoMaterial::where('curso_id', $curso->id)->max('orden') + 1;
            }

            // Establecer como público por defecto
            $data['es_publico'] = true;

            $material = CursoMaterial::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Material "' . $material->titulo . '" subido exitosamente',
                'material' => $material
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener material para edición
     */
    public function obtenerMaterial(Curso $curso, CursoMaterial $material): JsonResponse
    {
        // Solo el instructor, admin u operador pueden obtener materiales para edición
        $user = Auth::user();
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver este material'
            ], 403);
        }

        // Verificar que el material pertenece al curso
        if ($material->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'material' => [
                'id' => $material->id,
                'titulo' => $material->titulo,
                'descripcion' => $material->descripcion,
                'tipo' => $material->tipo,
                'url_externa' => $material->url_externa,
                'archivo_path' => $material->archivo_path,
                'archivo_nombre' => $material->archivo_nombre,
                'orden' => $material->orden,
                'porcentaje_curso' => $material->porcentaje_curso,
                'prerequisite_id' => $material->prerequisite_id,
            ]
        ]);
    }

    /**
     * Eliminar material
     */
    public function eliminarMaterial(Request $request, Curso $curso, CursoMaterial $material): JsonResponse
    {
        // Solo el instructor, admin u operador pueden eliminar materiales
        $user = Auth::user();
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar materiales'
            ], 403);
        }

        // Verificar que el material pertenece al curso
        if ($material->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material no encontrado'
            ], 404);
        }

        try {
            // Eliminar archivo físico si existe
            if ($material->archivo_path && file_exists(public_path('storage/' . $material->archivo_path))) {
                unlink(public_path('storage/' . $material->archivo_path));
            }

            // Eliminar registro de la base de datos
            $material->delete();

            return response()->json([
                'success' => true,
                'message' => 'Material eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar material existente
     */
    public function actualizarMaterial(Request $request, Curso $curso, CursoMaterial $material): JsonResponse
    {
        // Solo el instructor, admin u operador pueden actualizar materiales
        $user = Auth::user();
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar materiales'
            ], 403);
        }

        // Verificar que el material pertenece al curso
        if ($material->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:archivo,video,imagen,documento',
            'archivo' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,avi,mov,txt,zip,rar',
            'orden' => 'nullable|integer|min:0',
            'porcentaje_curso' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Actualizar datos básicos
            $material->titulo = $request->titulo;
            $material->descripcion = $request->descripcion;
            $material->tipo = $request->tipo;
            
            if ($request->has('orden')) {
                $material->orden = $request->orden;
            }
            
            // Actualizar porcentaje del curso
            if ($request->has('porcentaje_curso')) {
                $material->porcentaje_curso = $request->porcentaje_curso;
            }
            
            // Actualizar prerrequisito
            if ($request->has('prerequisite_id')) {
                $prerequisiteId = $request->prerequisite_id;
                // Verificar que no se vincule a sí mismo
                if ($prerequisiteId && $prerequisiteId != $material->id) {
                    $material->prerequisite_id = $prerequisiteId;
                } else {
                    $material->prerequisite_id = null;
                }
            }
            
            // Actualizar URL externa si se proporcionó
            if ($request->has('url_externa') && !empty($request->url_externa)) {
                $material->url_externa = $request->url_externa;
            }

            // Manejar nuevo archivo si se subió
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                
                // Validaciones adicionales de seguridad
                $allowedMimes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'ppt' => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/x-msvideo',
                    'mov' => 'video/quicktime',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed'
                ];
                
                $extension = strtolower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
                
                // Verificar que la extensión coincida con el MIME type
                if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de archivo no válido o sospechoso'
                    ], 422);
                }
                
                // Eliminar archivo anterior si existe
                if ($material->archivo_path && Storage::disk('public')->exists($material->archivo_path)) {
                    Storage::disk('public')->delete($material->archivo_path);
                }
                
                // Generar nombre único para evitar conflictos
                $fileName = time() . '_' . Str::random(10) . '.' . $extension;
                $path = $file->storeAs('cursos/' . $curso->id . '/materiales', $fileName, 'public');
                
                $material->archivo_path = $path;
                $material->archivo_nombre = $file->getClientOriginalName();
                $material->archivo_extension = $extension;
                $material->archivo_size = $file->getSize();
            }

            $material->save();

            return response()->json([
                'success' => true,
                'message' => 'Material "' . $material->titulo . '" actualizado exitosamente',
                'material' => $material
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el material: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la pestaña de foros
     */
    public function foros(Curso $curso)
    {
        $this->verificarAccesoCurso($curso);

        $foros = $curso->foros()
            ->with(['usuario', 'respuestas.usuario'])
            ->orderBy('es_fijado', 'desc')
            ->orderBy('es_anuncio', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $user = Auth::user();
        $esInstructor = $curso->esGestorODocente($user);

        // Agregar headers para forzar no-cache
        return response()
            ->view('admin.capacitaciones.cursos.classroom.foros', compact(
                'curso', 'foros', 'esInstructor'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Crear nuevo post en el foro
     */
    public function crearPost(Request $request, Curso $curso): JsonResponse
    {
        $this->verificarAccesoCurso($curso);

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string',
            'es_anuncio' => 'boolean',
            'es_fijado' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['curso_id'] = $curso->id;
            $data['usuario_id'] = Auth::id();

            // Solo el instructor, admin u operador pueden crear anuncios y fijar posts
            $user = Auth::user();
            $esInstructor = $curso->esGestorODocente($user);
            if (!$esInstructor) {
                $data['es_anuncio'] = false;
                $data['es_fijado'] = false;
            }

            $post = CursoForo::create($data);
            $post->load('usuario');

            // Registrar operación de publicación en foro
            OperationLogger::logForumPost($post->id, $curso->titulo, 'post');

            return response()->json([
                'success' => true,
                'message' => 'Post creado exitosamente',
                'post' => $post
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Responder a un post del foro
     */
    public function responderPost(Request $request, Curso $curso, CursoForo $post): JsonResponse
    {
        $this->verificarAccesoCurso($curso);

        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $respuesta = CursoForo::create([
                'curso_id' => $curso->id,
                'usuario_id' => Auth::id(),
                'titulo' => 'Re: ' . $post->titulo,
                'contenido' => $request->contenido,
                'parent_id' => $post->id,
            ]);

            $respuesta->load('usuario');

            // Registrar operación de respuesta en foro
            OperationLogger::logForumPost($respuesta->id, $curso->titulo, 'reply');

            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente',
                'respuesta' => $respuesta
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la respuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la pestaña de actividades
     */
    public function actividades(Curso $curso)
    {
        $this->verificarAccesoCurso($curso);

        $actividades = $curso->actividades()
            ->orderBy('fecha_apertura')
            ->get();

        $user = Auth::user();
        $esInstructor = $curso->esGestorODocente($user);

        // Agregar headers para forzar no-cache
        return response()
            ->view('admin.capacitaciones.cursos.classroom.actividades', compact(
                'curso', 'actividades', 'esInstructor'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Mostrar la pestaña de participantes
     */
    public function participantes(Curso $curso)
    {
        $this->verificarAccesoCurso($curso);

        $estudiantes = $curso->estudiantes()
            ->withPivot(['estado', 'progreso', 'fecha_inscripcion', 'ultima_actividad'])
            ->orderBy('name')
            ->get();

        $user = Auth::user();
        $esInstructor = $curso->esGestorODocente($user);

        // Agregar headers para forzar no-cache
        return response()
            ->view('admin.capacitaciones.cursos.classroom.participantes', compact(
                'curso', 'estudiantes', 'esInstructor'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Crear nueva actividad
     */
    public function crearActividad(Request $request, Curso $curso): JsonResponse
    {
        $this->verificarAccesoCurso($curso);
        $user = Auth::user();

        // Verificar que sea instructor, admin u operador
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear actividades en este curso'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:tarea,quiz,evaluacion,proyecto',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'instrucciones' => 'nullable|string',
            'fecha_apertura' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_apertura',
            'puntos_maximos' => 'nullable|integer|min:1|max:1000',
            'intentos_permitidos' => 'nullable|integer|min:1|max:10',
            'contenido_json' => 'nullable|array',
            'porcentaje_curso' => 'nullable|numeric|min:0|max:100',
            'material_id' => 'nullable|exists:curso_materiales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar porcentaje de la actividad
        $porcentajeSolicitado = floatval($request->input('porcentaje_curso', 0));
        $materialId = $request->input('material_id');
        
        if ($porcentajeSolicitado > 0) {
            if ($materialId) {
                // Validar contra el porcentaje del material
                $material = CursoMaterial::find($materialId);
                if ($material) {
                    $porcentajeActual = $material->actividades()->sum('porcentaje_curso');
                    if (($porcentajeActual + $porcentajeSolicitado) > $material->porcentaje_curso) {
                        $disponible = $material->porcentaje_curso - $porcentajeActual;
                        return response()->json([
                            'success' => false,
                            'message' => "El porcentaje de actividades excede el porcentaje del material ({$material->porcentaje_curso}%). Disponible: {$disponible}%"
                        ], 422);
                    }
                }
            } else {
                // Validar contra el porcentaje total del curso
                $porcentajeMateriales = $curso->materiales()->sum('porcentaje_curso');
                $porcentajeActividadesSinMaterial = $curso->actividades()->whereNull('material_id')->sum('porcentaje_curso');
                $porcentajeTotal = $porcentajeMateriales + $porcentajeActividadesSinMaterial + $porcentajeSolicitado;
                
                if ($porcentajeTotal > 100) {
                    $disponible = 100 - $porcentajeMateriales - $porcentajeActividadesSinMaterial;
                    return response()->json([
                        'success' => false,
                        'message' => "El porcentaje total del curso excede el 100%. Disponible: {$disponible}%"
                    ], 422);
                }
            }
        }

        // Validar porcentajes de preguntas para quiz/evaluacion
        if (in_array($request->tipo, ['quiz', 'evaluacion']) && $request->has('contenido_json')) {
            $contenido = $request->contenido_json;
            if (isset($contenido['questions']) && is_array($contenido['questions'])) {
                $totalPorcentaje = 0;
                foreach ($contenido['questions'] as $pregunta) {
                    $totalPorcentaje += floatval($pregunta['points'] ?? 0);
                }
                if ($totalPorcentaje > 100) {
                    return response()->json([
                        'success' => false,
                        'message' => "La suma de porcentajes de las preguntas ({$totalPorcentaje}%) excede el 100%"
                    ], 422);
                }
            }
        }

        try {
            // Procesar prerequisite_activity_ids
            $prereqActivityIds = $request->input('prerequisite_activity_ids');
            if (is_string($prereqActivityIds)) {
                $prereqActivityIds = json_decode($prereqActivityIds, true);
            }
            if (!is_array($prereqActivityIds)) {
                $prereqActivityIds = [];
            }

            $actividad = CursoActividad::create([
                'curso_id' => $curso->id,
                'material_id' => $materialId,
                'tipo' => $request->tipo,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'instrucciones' => $request->instrucciones,
                'fecha_apertura' => $request->fecha_apertura,
                'fecha_cierre' => $request->fecha_cierre,
                'puntos_maximos' => $request->puntos_maximos ?? 100,
                'intentos_permitidos' => $request->intentos_permitidos ?? 1,
                'contenido_json' => $request->contenido_json,
                'porcentaje_curso' => $porcentajeSolicitado,
                'nota_minima_aprobacion' => $request->input('nota_minima_aprobacion', 3.0),
                'habilitado' => in_array($request->tipo, ['quiz', 'evaluacion']) ? false : true,
                'orden' => $curso->actividades()->count() + 1,
                'prerequisite_activity_ids' => $prereqActivityIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Actividad creada exitosamente',
                'actividad' => $actividad
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear actividad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Entregar actividad
     */
    public function entregarActividad(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $this->verificarAccesoCurso($curso);

        $user = Auth::user();

        // Verificar que la actividad pertenece al curso
        if ($actividad->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'La actividad no pertenece a este curso'
            ], 400);
        }

        // Verificar que la actividad esté abierta
        if ($actividad->estado !== 'abierta') {
            return response()->json([
                'success' => false,
                'message' => 'La actividad no está disponible para entrega'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string',
            'archivo' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'curso_id' => $curso->id,
                'actividad_id' => $actividad->id,
                'user_id' => $user->id,
                'contenido' => $request->contenido,
                'entregado_at' => now(),
            ];

            // Manejar archivo adjunto
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $path = $file->store('cursos/' . $curso->id . '/entregas', 'public');
                $data['archivo_path'] = $path;
                $data['archivo_nombre'] = $file->getClientOriginalName();
            }

            // Verificar si ya existe una entrega previa
            $entregaExistente = DB::table('curso_actividad_entrega')
                ->where('curso_id', $curso->id)
                ->where('actividad_id', $actividad->id)
                ->where('user_id', $user->id)
                ->first();

            if ($entregaExistente) {
                // Actualizar entrega existente
                DB::table('curso_actividad_entrega')
                    ->where('id', $entregaExistente->id)
                    ->update($data);
                $message = 'Actividad actualizada exitosamente';
            } else {
                // Crear nueva entrega
                DB::table('curso_actividad_entrega')->insert($data);
                $message = 'Actividad entregada exitosamente';
            }

            // Actualizar progreso del estudiante
            $curso->actualizarProgresoEstudiante($user->id);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al entregar la actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inscribir estudiante al curso
     */
    public function inscribirEstudiante(Request $request, Curso $curso): JsonResponse
    {
        $user = Auth::user();

        // Verificar si el curso está activo
        if ($curso->estado !== 'activo') {
            return response()->json([
                'success' => false,
                'message' => 'El curso no está disponible para inscripciones'
            ], 400);
        }

        // Verificar límite de estudiantes
        if ($curso->max_estudiantes && $curso->estudiantes_count >= $curso->max_estudiantes) {
            return response()->json([
                'success' => false,
                'message' => 'El curso ha alcanzado el límite máximo de estudiantes'
            ], 400);
        }

        // Verificar si ya está inscrito
        if ($curso->tieneEstudiante($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya estás inscrito en este curso'
            ], 400);
        }

        try {
            $curso->estudiantes()->attach($user->id, [
                'estado' => 'activo',
                'progreso' => 0,
                'fecha_inscripcion' => now(),
                'ultima_actividad' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Te has inscrito exitosamente al curso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al inscribirse al curso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Habilitar/Deshabilitar actividad (solo instructor/admin)
     */
    public function toggleActividad(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $user = Auth::user();
        
        // Solo instructor, admin u operador pueden habilitar actividades
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para modificar actividades'
            ], 403);
        }

        // Verificar que la actividad pertenece al curso
        if ($actividad->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'La actividad no pertenece a este curso'
            ], 400);
        }

        try {
            $actividad->habilitado = !$actividad->habilitado;
            $actividad->save();
            $actividad->refresh(); // Refrescar el modelo para asegurar que tiene el valor actualizado

            return response()->json([
                'success' => true,
                'message' => $actividad->habilitado ? 'Actividad habilitada correctamente' : 'Actividad deshabilitada correctamente',
                'habilitado' => $actividad->habilitado
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar estado de actividad', [
                'actividad_id' => $actividad->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolver quiz o evaluación
     */
    public function resolverQuiz(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $this->verificarAccesoCurso($curso);
        $user = Auth::user();

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

        $validator = Validator::make($request->all(), [
            'respuestas' => 'required|array',
            'tiempo_transcurrido' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $respuestas = $request->respuestas;
            $quizData = $actividad->contenido_json;
            $preguntas = $quizData['questions'] ?? [];
            
            // ========================================================
            // BANCO DE PREGUNTAS Y ALEATORIZACIÓN: Filtrar y redistribuir
            // ========================================================
            $quizConfig = $quizData['quizConfig'] ?? [];
            $enableBank = $quizConfig['enableQuestionBank'] ?? false;
            $randomizeOrder = $quizConfig['randomizeOrder'] ?? false;
            $questionIds = $request->input('question_ids', []);
            
            // Si se enviaron question_ids (banco o aleatorización activos), filtrar
            if (!empty($questionIds) && ($enableBank || $randomizeOrder)) {
                $preguntasFiltradas = [];
                foreach ($preguntas as $pregunta) {
                    if (in_array($pregunta['id'], $questionIds)) {
                        $preguntasFiltradas[] = $pregunta;
                    }
                }
                if (!empty($preguntasFiltradas)) {
                    $preguntas = $preguntasFiltradas;
                }
            }
            
            // Redistribuir porcentajes para que las preguntas activas totalicen 100%
            $totalTarget = floatval($quizData['totalPoints'] ?? 100);
            $sumaSeleccionadas = 0;
            foreach ($preguntas as $p) {
                $sumaSeleccionadas += floatval($p['points'] ?? 0);
            }
            if ($sumaSeleccionadas > 0 && abs($sumaSeleccionadas - $totalTarget) > 0.01) {
                $factor = $totalTarget / $sumaSeleccionadas;
                foreach ($preguntas as &$p) {
                    $p['points'] = round(floatval($p['points']) * $factor, 2);
                }
                unset($p);
            }
            
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
                    // Por cada respuesta incorrecta que marcó: multiplicar por 0 (no resta)
                    $aciertos = 0;
                    foreach ($respuestasEstudianteArray as $resp) {
                        if (in_array($resp, $respuestasCorrectas)) {
                            $puntosEstaPregunta += ($porcentajePorRespuesta / 100) * 5;
                            $aciertos++;
                        }
                        // Las incorrectas se multiplican por 0 (no suman nada)
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
                        // Correcta: (porcentaje/100) × 5
                        $puntosEstaPregunta = ($porcentajePregunta / 100) * 5;
                    }
                    // Incorrecta: (porcentaje/100) × 0 = 0
                    
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
                        'tiempo_transcurrido' => $request->tiempo_transcurrido,
                        'preguntas_servidas' => ($enableBank && !empty($questionIds)) ? $questionIds : null,
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
     * Obtener datos de una actividad para edición (solo instructor)
     */
    public function obtenerActividad(Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $user = Auth::user();
        
        // Solo instructor, admin u operador pueden obtener datos de actividades
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver esta actividad'
            ], 403);
        }

        // Verificar que la actividad pertenece al curso
        if ($actividad->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'La actividad no pertenece a este curso'
            ], 400);
        }

        try {
            // Cargar relaciones necesarias
            $actividad->load('material');
            
            // Preparar datos seguros para JSON (sin accessors problemáticos)
            $actividadData = [
                'id' => $actividad->id,
                'curso_id' => $actividad->curso_id,
                'material_id' => $actividad->material_id,
                'titulo' => $actividad->titulo,
                'descripcion' => $actividad->descripcion,
                'tipo' => $actividad->tipo,
                'instrucciones' => $actividad->instrucciones,
                'contenido_json' => $actividad->contenido_json,
                'fecha_apertura' => $actividad->fecha_apertura ? $actividad->fecha_apertura->format('Y-m-d\TH:i') : null,
                'fecha_cierre' => $actividad->fecha_cierre ? $actividad->fecha_cierre->format('Y-m-d\TH:i') : null,
                'puntos_maximos' => $actividad->puntos_maximos,
                'duracion_minutos' => $actividad->duracion_minutos,
                'intentos_permitidos' => $actividad->intentos_permitidos,
                'habilitado' => $actividad->habilitado,
                'prerequisite_activity_ids' => $actividad->prerequisite_activity_ids,
                'porcentaje_curso' => $actividad->porcentaje_curso,
                'nota_minima_aprobacion' => $actividad->nota_minima_aprobacion,
                // Accessors seguros
                'tipo_icon' => $actividad->tipo_icon,
                'estado' => $actividad->estado,
                'estado_color' => $actividad->estado_color,
            ];
            
            return response()->json([
                'success' => true,
                'actividad' => $actividadData
            ]);
        } catch (\Exception $e) {
            \Log::error('ERROR al obtener actividad', [
                'message' => $e->getMessage(),
                'actividad_id' => $actividad->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la actividad'
            ], 500);
        }
    }

    /**
     * Obtener datos de quiz/evaluación para iniciar (sin accessors problemáticos)
     */
    public function obtenerDatosQuiz(Curso $curso, CursoActividad $actividad): JsonResponse
    {
        try {
            // Verificar que la actividad pertenece al curso
            if ($actividad->curso_id !== $curso->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'La actividad no pertenece a este curso'
                ], 400);
            }

            // Preparar datos del quiz con lógica anti-fraude (banco de preguntas)
            $contenidoJson = $actividad->contenido_json;
            $allQuestions = $contenidoJson['questions'] ?? [];
            $quizConfig = $contenidoJson['quizConfig'] ?? [];
            $enableBank = $quizConfig['enableQuestionBank'] ?? false;
            $questionsPerAttempt = intval($quizConfig['questionsPerAttempt'] ?? count($allQuestions));
            $randomizeOrder = $quizConfig['randomizeOrder'] ?? false;
            
            $selectedQuestions = $allQuestions;
            $totalTarget = floatval($contenidoJson['totalPoints'] ?? 100);
            
            // Aplicar lógica de banco de preguntas
            if ($enableBank && $questionsPerAttempt > 0 && $questionsPerAttempt < count($allQuestions)) {
                $keys = array_keys($allQuestions);
                shuffle($keys);
                $selectedKeys = array_slice($keys, 0, $questionsPerAttempt);
                
                $selectedQuestions = [];
                foreach ($selectedKeys as $key) {
                    $selectedQuestions[] = $allQuestions[$key];
                }
            }
            
            // Aleatorizar orden si está habilitado
            if ($randomizeOrder) {
                shuffle($selectedQuestions);
            }
            
            // Redistribuir porcentajes para que las preguntas activas totalicen 100%
            $selectedSum = 0;
            foreach ($selectedQuestions as $q) {
                $selectedSum += floatval($q['points'] ?? 0);
            }
            if ($selectedSum > 0 && abs($selectedSum - $totalTarget) > 0.01) {
                $factor = $totalTarget / $selectedSum;
                foreach ($selectedQuestions as &$q) {
                    $q['points'] = round(floatval($q['points']) * $factor, 2);
                }
                unset($q);
            }
            
            // Obtener IDs de preguntas seleccionadas
            $questionIds = array_map(function($q) { return $q['id']; }, $selectedQuestions);
            
            // Si hay configuración anti-fraude activa, limpiar respuestas correctas del envío
            if ($enableBank || $randomizeOrder) {
                foreach ($selectedQuestions as &$q) {
                    $q['isMultipleChoice'] = $q['isMultipleChoice'] ?? false;
                    unset($q['correctAnswers']);
                    unset($q['correctAnswer']);
                }
                unset($q);
            }
            
            // Preparar datos para el estudiante
            $contenidoParaEstudiante = [
                'duration' => $contenidoJson['duration'] ?? 30,
                'totalPoints' => $totalTarget,
                'questions' => $selectedQuestions,
                'questionIds' => $questionIds,
            ];

            return response()->json([
                'success' => true,
                'actividad' => [
                    'id' => $actividad->id,
                    'titulo' => $actividad->titulo,
                    'tipo' => $actividad->tipo,
                    'contenido_json' => $contenidoParaEstudiante,
                    'duracion_minutos' => $actividad->duracion_minutos,
                    'puntos_maximos' => $actividad->puntos_maximos,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del quiz'
            ], 500);
        }
    }

    /**
     * Obtener materiales y actividades disponibles (para modales de edición)
     */
    public function obtenerDatosDisponibles(Curso $curso): JsonResponse
    {
        try {
            $this->verificarAccesoCurso($curso);

            // Obtener materiales del curso
            $materiales = $curso->materiales->map(function($material) {
                return [
                    'id' => $material->id,
                    'titulo' => $material->titulo,
                    'porcentaje_curso' => $material->porcentaje_curso ?? 0,
                ];
            });

            // Obtener actividades del curso
            $actividades = $curso->actividades->map(function($actividad) {
                return [
                    'id' => $actividad->id,
                    'titulo' => $actividad->titulo,
                    'tipo' => $actividad->tipo,
                ];
            });

            return response()->json([
                'success' => true,
                'materiales' => $materiales,
                'actividades' => $actividades,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos disponibles'
            ], 500);
        }
    }

    /**
     * Actualizar actividad (solo instructor)
     */
    public function actualizarActividad(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        \Log::info('=== INICIO actualizarActividad ===', [
            'curso_id' => $curso->id,
            'actividad_id' => $actividad->id,
            'request_data' => $request->all()
        ]);

        $user = Auth::user();
        
        // Solo instructor, admin u operador pueden actualizar actividades
        if (!$curso->esGestorODocente($user)) {
            \Log::warning('Usuario sin permisos intentó actualizar actividad');
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para modificar actividades'
            ], 403);
        }

        // Verificar que la actividad pertenece al curso
        if ($actividad->curso_id !== $curso->id) {
            \Log::warning('Actividad no pertenece al curso');
            return response()->json([
                'success' => false,
                'message' => 'La actividad no pertenece a este curso'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'instrucciones' => 'nullable|string',
            'fecha_apertura' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after:fecha_apertura',
            'puntos_maximos' => 'nullable|integer|min:0',
            'duracion_minutos' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            \Log::warning('Validación falló', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \Log::info('Intentando actualizar actividad');
            
            // Preparar datos para actualizar
            $updateData = [
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'instrucciones' => $request->instrucciones,
            ];

            // Solo agregar fechas si fueron enviadas
            if ($request->has('fecha_apertura') && !empty($request->fecha_apertura)) {
                $updateData['fecha_apertura'] = $request->fecha_apertura;
            }
            
            if ($request->has('fecha_cierre') && !empty($request->fecha_cierre)) {
                $updateData['fecha_cierre'] = $request->fecha_cierre;
            }

            // Solo agregar puntos y duración si fueron enviados
            if ($request->has('puntos_maximos') && !empty($request->puntos_maximos)) {
                $updateData['puntos_maximos'] = (int)$request->puntos_maximos;
            }
            
            if ($request->has('duracion_minutos') && !empty($request->duracion_minutos)) {
                $updateData['duracion_minutos'] = (int)$request->duracion_minutos;
            }
            
            // Actualizar intentos permitidos si fue enviado
            if ($request->has('intentos_permitidos')) {
                $updateData['intentos_permitidos'] = (int)$request->intentos_permitidos;
            }
            
            // Actualizar contenido_json para quiz/evaluación
            if ($request->has('contenido_json') && $request->contenido_json) {
                $contenidoJson = $request->contenido_json;
                // Si viene como string JSON, decodificarlo
                if (is_string($contenidoJson)) {
                    $contenidoJson = json_decode($contenidoJson, true);
                }
                $updateData['contenido_json'] = $contenidoJson;
            }
            
            // Actualizar prerrequisitos de actividades
            if ($request->has('prerequisite_activity_ids')) {
                $prereqActivities = $request->prerequisite_activity_ids;
                // Si viene como string JSON, decodificarlo primero
                if (is_string($prereqActivities)) {
                    $prereqActivities = json_decode($prereqActivities, true);
                }
                // Guardar como array (el modelo lo convertirá a JSON automáticamente por el cast)
                if (is_array($prereqActivities)) {
                    $updateData['prerequisite_activity_ids'] = $prereqActivities;
                } else {
                    $updateData['prerequisite_activity_ids'] = [];
                }
            }
            
            // Actualizar material_id (al que pertenece la actividad)
            if ($request->has('material_id')) {
                $updateData['material_id'] = $request->material_id ? (int)$request->material_id : null;
            }
            
            // Actualizar porcentaje del material
            if ($request->has('porcentaje_curso')) {
                $updateData['porcentaje_curso'] = (float)$request->porcentaje_curso;
            }
            
            // Actualizar nota mínima de aprobación
            if ($request->has('nota_minima_aprobacion')) {
                $updateData['nota_minima_aprobacion'] = (float)$request->nota_minima_aprobacion;
            }

            \Log::info('Datos a actualizar', $updateData);

            $actividad->update($updateData);

            \Log::info('Actividad actualizada exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Actividad actualizada exitosamente',
                'actividad' => $actividad->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('ERROR al actualizar actividad', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar actividad (solo instructor)
     */
    public function eliminarActividad(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $user = Auth::user();
        
        // Solo instructor, admin u operador pueden eliminar actividades
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar actividades'
            ], 403);
        }

        // Verificar que la actividad pertenece al curso
        if ($actividad->curso_id !== $curso->id) {
            return response()->json([
                'success' => false,
                'message' => 'La actividad no pertenece a este curso'
            ], 400);
        }

        try {
            // Eliminar entregas asociadas
            DB::table('curso_actividad_entrega')
                ->where('actividad_id', $actividad->id)
                ->delete();

            // Eliminar la actividad
            $actividad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Actividad eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar entregas de una actividad
     */
    public function entregas(Curso $curso, CursoActividad $actividad)
    {
        $this->verificarAccesoCurso($curso);
        
        $user = Auth::user();
        $esInstructor = $curso->esGestorODocente($user);
        
        if (!$esInstructor) {
            abort(403, 'Solo los instructores pueden ver las entregas');
        }

        // Obtener todos los estudiantes del curso
        $estudiantes = $curso->estudiantes()->get();
        $totalEstudiantes = $estudiantes->count();

        // Obtener entregas de la actividad (tabla correcta: curso_actividad_entrega singular)
        $entregas = DB::table('curso_actividad_entrega')
            ->where('actividad_id', $actividad->id)
            ->get()
            ->keyBy('user_id');  // La columna es user_id, no estudiante_id

        // Crear lista completa de entregas (incluyendo pendientes)
        $entregasCompletas = $estudiantes->map(function($estudiante) use ($entregas, $actividad) {
            $entrega = $entregas->get($estudiante->id);
            
            if ($entrega) {
                // Determinar estado basado en la tabla actual
                $estado = 'pendiente';
                if ($entrega->entregado_at) {
                    $estado = $entrega->estado === 'revisado' ? 'entregado' : 'entregado';
                    
                    // Verificar si es tarde
                    if ($actividad->fecha_cierre) {
                        $fechaCierre = \Carbon\Carbon::parse($actividad->fecha_cierre);
                        $fechaEntrega = \Carbon\Carbon::parse($entrega->entregado_at);
                        if ($fechaEntrega->gt($fechaCierre)) {
                            $estado = 'tarde';
                        }
                    }
                }
                
                return (object)[
                    'id' => $entrega->id,
                    'estudiante' => $estudiante,
                    'fecha_entrega' => $entrega->entregado_at ? \Carbon\Carbon::parse($entrega->entregado_at) : null,
                    'estado' => $estado,
                    'calificacion' => $entrega->calificacion,
                    'archivo_path' => $entrega->archivo_path,
                    'comentarios' => $entrega->comentarios_instructor ?? $entrega->observaciones_estudiante
                ];
            } else {
                return (object)[
                    'id' => null,
                    'estudiante' => $estudiante,
                    'fecha_entrega' => null,
                    'estado' => 'pendiente',
                    'calificacion' => null,
                    'archivo_path' => null,
                    'comentarios' => null
                ];
            }
        });

        // Calcular estadísticas
        $entregasRealizadas = $entregasCompletas->where('estado', '!=', 'pendiente')->count();
        $entregasPendientes = $entregasCompletas->where('estado', 'pendiente')->count();
        $entregasATiempo = $entregasCompletas->where('estado', 'entregado')->count();
        $entregasTarde = $entregasCompletas->where('estado', 'tarde')->count();
        
        $calificaciones = $entregasCompletas->whereNotNull('calificacion')->pluck('calificacion');
        $promedioCalificacion = $calificaciones->count() > 0 ? $calificaciones->average() : 0;

        // Distribución de calificaciones
        $distribucionCalificaciones = [
            $calificaciones->filter(fn($c) => $c >= 0 && $c < 61)->count(),
            $calificaciones->filter(fn($c) => $c >= 61 && $c <= 70)->count(),
            $calificaciones->filter(fn($c) => $c >= 71 && $c <= 80)->count(),
            $calificaciones->filter(fn($c) => $c >= 81 && $c <= 90)->count(),
            $calificaciones->filter(fn($c) => $c >= 91 && $c <= 100)->count(),
        ];

        // Paginar entregas
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $entregasPaginadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $entregasCompletas->forPage($currentPage, $perPage),
            $entregasCompletas->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.capacitaciones.cursos.classroom.entregas', [
            'curso' => $curso,
            'actividad' => $actividad,
            'entregas' => $entregasPaginadas,
            'totalEstudiantes' => $totalEstudiantes,
            'entregasRealizadas' => $entregasRealizadas,
            'entregasPendientes' => $entregasPendientes,
            'entregasATiempo' => $entregasATiempo,
            'entregasTarde' => $entregasTarde,
            'promedioCalificacion' => $promedioCalificacion,
            'distribucionCalificaciones' => $distribucionCalificaciones
        ]);
    }

    /**
     * Verificar acceso al curso
     */
    private function verificarAccesoCurso(Curso $curso)
    {
        $user = Auth::user();
        
        // El instructor siempre tiene acceso
        if ($curso->esGestorODocente($user)) {
            return;
        }

        // Los administradores y operadores tienen acceso completo
        if ($user->tienePermisoGestion()) {
            return;
        }

        // Los estudiantes inscritos tienen acceso
        if ($curso->tieneEstudiante($user->id)) {
            return;
        }

        abort(403, 'No tienes acceso a este curso');
    }

    /**
     * Obtener resumen de calificaciones de un estudiante
     */
    public function getCalificacionesEstudiante(Curso $curso, $estudianteId = null): JsonResponse
    {
        $this->verificarAccesoCurso($curso);
        $user = Auth::user();
        
        // Si no se especifica estudiante, usar el usuario actual
        if (!$estudianteId) {
            $estudianteId = $user->id;
        }
        
        // Verificar permisos para ver calificaciones de otros
        if ($estudianteId != $user->id && !$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las calificaciones de este estudiante'
            ], 403);
        }
        
        try {
            $resumen = $curso->getResumenCalificacionesEstudiante($estudianteId);
            
            return response()->json([
                'success' => true,
                'data' => $resumen
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de porcentajes del curso
     */
    public function getResumenPorcentajes(Curso $curso): JsonResponse
    {
        $this->verificarAccesoCurso($curso);
        
        try {
            $porcentajeMateriales = $curso->materiales()->sum('porcentaje_curso');
            $porcentajeActividadesSinMaterial = $curso->actividades()
                ->whereNull('material_id')
                ->sum('porcentaje_curso');
            
            $porcentajeTotal = $porcentajeMateriales + $porcentajeActividadesSinMaterial;
            
            $materiales = $curso->materiales()->with('actividades')->get()->map(function($material) {
                $porcentajeActividades = $material->actividades()->sum('porcentaje_curso');
                return [
                    'id' => $material->id,
                    'titulo' => $material->titulo,
                    'porcentaje_curso' => $material->porcentaje_curso,
                    'nota_minima_aprobacion' => $material->nota_minima_aprobacion,
                    'porcentaje_actividades_asignado' => $porcentajeActividades,
                    'porcentaje_disponible' => $material->porcentaje_curso - $porcentajeActividades,
                    'actividades' => $material->actividades->map(function($actividad) {
                        return [
                            'id' => $actividad->id,
                            'titulo' => $actividad->titulo,
                            'tipo' => $actividad->tipo,
                            'porcentaje_curso' => $actividad->porcentaje_curso,
                        ];
                    }),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'porcentaje_materiales' => $porcentajeMateriales,
                    'porcentaje_actividades_sin_material' => $porcentajeActividadesSinMaterial,
                    'porcentaje_total_asignado' => $porcentajeTotal,
                    'porcentaje_disponible' => 100 - $porcentajeTotal,
                    'es_valido' => $porcentajeTotal <= 100,
                    'materiales' => $materiales,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de porcentajes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calificar una tarea manualmente
     */
    public function calificarTarea(Request $request, Curso $curso, CursoActividad $actividad): JsonResponse
    {
        $this->verificarAccesoCurso($curso);
        $user = Auth::user();
        
        // Solo instructor, admin u operador pueden calificar
        if (!$curso->esGestorODocente($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para calificar tareas'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:users,id',
            'calificacion' => 'required|numeric|min:0|max:5',
            'comentarios' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Buscar o crear la entrega
            $entrega = DB::table('curso_actividad_entrega')
                ->where('curso_id', $curso->id)
                ->where('actividad_id', $actividad->id)
                ->where('user_id', $request->estudiante_id)
                ->first();
            
            if ($entrega) {
                DB::table('curso_actividad_entrega')
                    ->where('id', $entrega->id)
                    ->update([
                        'calificacion' => $request->calificacion,
                        'comentarios_docente' => $request->comentarios,
                        'calificado_at' => now(),
                        'calificado_por' => $user->id,
                    ]);
            } else {
                DB::table('curso_actividad_entrega')->insert([
                    'curso_id' => $curso->id,
                    'actividad_id' => $actividad->id,
                    'user_id' => $request->estudiante_id,
                    'calificacion' => $request->calificacion,
                    'comentarios_docente' => $request->comentarios,
                    'calificado_at' => now(),
                    'calificado_por' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Actualizar progreso del estudiante
            $curso->actualizarProgresoEstudiante($request->estudiante_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Calificación guardada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar calificación: ' . $e->getMessage()
            ], 500);
        }
    }
}

