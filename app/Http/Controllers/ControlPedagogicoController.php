<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ControlPedagogicoController extends Controller
{
    /**
     * Display the pedagogical control gradebook
     */
    public function index(Request $request)
    {
        // Manejar peticiones AJAX
        if ($request->has('action')) {
            return $this->handleAjaxRequest($request);
        }
        
        $user = Auth::user();
        
        // Obtener cursos según el rol
        $cursos = $this->getCursosSegunRol($user);
        
        // Curso seleccionado (por defecto el primero)
        $cursoId = $request->get('curso_id', $cursos->first()->id ?? null);
        $cursoActual = Curso::with(['materiales' => function($query) {
            $query->select('*'); // Asegurar que se seleccionen todos los campos
        }, 'actividades', 'plantillaCertificado'])->find($cursoId);
        
        if (!$cursoActual) {
            return redirect()->back()->with('error', 'No hay cursos disponibles');
        }
        
        // Obtener estudiantes inscritos con sus calificaciones
        // El Docente solo ve los estudiantes que tiene asignados en este curso
        $estudiantesFiltro = null;
        if ($user->role === 'Docente') {
            $estudiantesFiltro = \App\Models\CursoAsignacion::where('curso_id', $cursoId)
                ->where('docente_id', $user->id)
                ->where('estado', 'activo')
                ->pluck('estudiante_id')
                ->toArray();
        }
        
        $estudiantes = $this->getEstudiantesConCalificaciones($cursoActual, $estudiantesFiltro);
        
        // Obtener estructura de evaluación
        $estructuraEvaluacion = $this->getEstructuraEvaluacion($cursoActual);
        
        return view('academico.control-pedagogico.index', compact(
            'cursos',
            'cursoActual',
            'estudiantes',
            'estructuraEvaluacion'
        ));
    }
    
    /**
     * Manejar peticiones AJAX
     */
    private function handleAjaxRequest(Request $request)
    {
        $action = $request->get('action');
        
        switch ($action) {
            case 'get_entrega':
                return $this->getEntrega($request);
            case 'guardar_calificacion':
                return $this->guardarCalificacion($request);
            default:
                return response()->json(['error' => 'Acción no válida'], 400);
        }
    }
    
    /**
     * Obtener detalles de la entrega de un estudiante
     */
    private function getEntrega(Request $request)
    {
        $cursoId = $request->get('curso_id');
        $estudianteId = $request->get('estudiante_id');
        $actividadId = $request->get('actividad_id');
        
        // Buscar la entrega
        $entrega = DB::table('curso_actividad_entrega')
            ->where('curso_id', $cursoId)
            ->where('actividad_id', $actividadId)
            ->where('user_id', $estudianteId)
            ->first();
        
        if (!$entrega) {
            return response()->json([
                'entrega' => null,
                'mensaje' => 'El estudiante aún no ha entregado esta actividad'
            ]);
        }
        
        // Obtener la actividad para determinar el tipo
        $actividad = \App\Models\CursoActividad::find($actividadId);
        
        // Preparar respuesta base
        $response = [
            'archivo_path' => $entrega->archivo_path,
            'fecha_entrega' => $entrega->entregado_at ? date('d/m/Y H:i', strtotime($entrega->entregado_at)) : null,
            'calificacion' => $entrega->calificacion,
            'retroalimentacion' => $entrega->comentarios_instructor,
            'estado' => $entrega->estado
        ];
        
        // Si es quiz o evaluación, incluir respuestas y calcular nota
        if ($actividad && in_array($actividad->tipo, ['quiz', 'evaluacion'])) {
            // Calcular la nota automáticamente (en escala 0-5)
            $nota = $actividad->calcularNotaEstudiante($estudianteId);
            $response['calificacion'] = $nota;
            
            // Intentar obtener respuestas del contenido JSON
            if ($entrega->contenido) {
                try {
                    $contenido = json_decode($entrega->contenido, true);
                    if (isset($contenido['resultados'])) {
                        // Formatear resultados para mostrar en el modal
                        $respuestasFormateadas = [];
                        foreach ($contenido['resultados'] as $index => $resultado) {
                            $respuestasFormateadas[] = [
                                'pregunta' => $resultado['pregunta'] ?? "Pregunta " . ($index + 1),
                                'respuesta' => $resultado['respuesta_estudiante'] ?? 'N/A',
                                'correcta' => $resultado['es_correcta'] ?? false,
                                'puntos' => $resultado['puntos'] ?? 0
                            ];
                        }
                        $response['respuestas'] = $respuestasFormateadas;
                    }
                } catch (\Exception $e) {
                    // Si no se puede decodificar, ignorar
                }
            }
        } else {
            // Para tareas, el contenido es el comentario del estudiante
            $response['comentario'] = $entrega->contenido;
        }
        
        return response()->json($response);
    }
    
    /**
     * Guardar calificación de una actividad (método público para ruta POST)
     */
    public function guardarCalificacionPublic(Request $request)
    {
        return $this->guardarCalificacion($request);
    }

    /**
     * Activar/desactivar una actividad (quiz o evaluación)
     */
    public function toggleActividad(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['Super Admin', 'Administrador', 'Operador', 'Docente'])) {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        $actividadId = $request->get('actividad_id');
        $habilitado = $request->boolean('habilitado');

        $actividad = \App\Models\CursoActividad::find($actividadId);
        if (!$actividad) {
            return response()->json(['error' => 'Actividad no encontrada'], 404);
        }

        // Si es docente, verificar que tenga asignación en el curso
        if ($user->role === 'Docente') {
            $tieneAsignacion = \App\Models\CursoAsignacion::where('curso_id', $actividad->curso_id)
                ->where('docente_id', $user->id)
                ->where('estado', 'activo')
                ->exists();
            if (!$tieneAsignacion) {
                return response()->json(['error' => 'No tienes asignación en este curso'], 403);
            }
        }

        // Solo permitir toggle en quiz y evaluacion
        if (!in_array($actividad->tipo, ['quiz', 'evaluacion'])) {
            return response()->json(['error' => 'Solo se pueden activar/desactivar Quiz o Evaluaciones'], 400);
        }

        $actividad->habilitado = $habilitado;
        $actividad->save();

        $tipoLabel = $actividad->tipo === 'quiz' ? 'Quiz' : 'Evaluación';
        $estadoLabel = $habilitado ? 'habilitado' : 'deshabilitado';

        return response()->json([
            'success' => true,
            'habilitado' => $habilitado,
            'mensaje' => "{$tipoLabel} '{$actividad->titulo}' {$estadoLabel} correctamente."
        ]);
    }

    /**
     * Habilitar reintento de una actividad (eliminar entrega)
     */
    public function resetActividad(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['Super Admin', 'Administrador', 'Operador', 'Docente'])) {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        $cursoId = $request->get('curso_id');
        $estudianteId = $request->get('estudiante_id');
        $actividadId = $request->get('actividad_id');

        // Si es docente, verificar asignación en el curso
        if ($user->role === 'Docente') {
            $tieneAsignacion = \App\Models\CursoAsignacion::where('curso_id', $cursoId)
                ->where('docente_id', $user->id)
                ->where('estado', 'activo')
                ->exists();
            if (!$tieneAsignacion) {
                return response()->json(['error' => 'No tienes asignación en este curso'], 403);
            }
        }

        $entrega = DB::table('curso_actividad_entrega')
            ->where('curso_id', $cursoId)
            ->where('actividad_id', $actividadId)
            ->where('user_id', $estudianteId)
            ->first();

        if (!$entrega) {
            return response()->json(['error' => 'No se encontró la entrega'], 404);
        }

        DB::table('curso_actividad_entrega')
            ->where('id', $entrega->id)
            ->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'La actividad ha sido reseteada, el estudiante puede volver a enviarla.'
        ]);
    }

    /**
     * Habilitar reintento grupal de una actividad (quiz/evaluación) para estudiantes reprobados
     */
    public function resetActividadGrupo(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['Super Admin', 'Administrador', 'Operador', 'Docente'])) {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
        }

        $cursoId = $request->get('curso_id');
        $actividadId = $request->get('actividad_id');
        $estudianteIds = $request->get('estudiante_ids', []);

        if (empty($estudianteIds)) {
            return response()->json(['error' => 'No se especificaron estudiantes'], 400);
        }

        // Verificar que la actividad sea quiz o evaluación
        $actividad = \App\Models\CursoActividad::find($actividadId);
        if (!$actividad || !in_array($actividad->tipo, ['quiz', 'evaluacion'])) {
            return response()->json(['error' => 'Solo se pueden reintentar Quiz o Evaluaciones'], 400);
        }

        // Si es docente, verificar asignación
        if ($user->role === 'Docente') {
            $tieneAsignacion = \App\Models\CursoAsignacion::where('curso_id', $cursoId)
                ->where('docente_id', $user->id)
                ->where('estado', 'activo')
                ->exists();
            if (!$tieneAsignacion) {
                return response()->json(['error' => 'No tienes asignación en este curso'], 403);
            }
        }

        $deleted = DB::table('curso_actividad_entrega')
            ->where('curso_id', $cursoId)
            ->where('actividad_id', $actividadId)
            ->whereIn('user_id', $estudianteIds)
            ->delete();

        $tipoLabel = $actividad->tipo === 'quiz' ? 'Quiz' : 'Evaluación';

        return response()->json([
            'success' => true,
            'mensaje' => "Se habilitó el reintento de {$tipoLabel} '{$actividad->titulo}' para {$deleted} estudiante(s).",
            'eliminados' => $deleted
        ]);
    }
    
    /**
     * Guardar calificación de una actividad
     */
    private function guardarCalificacion(Request $request)
    {
        $cursoId = $request->get('curso_id');
        $estudianteId = $request->get('estudiante_id');
        $actividadId = $request->get('actividad_id');
        $calificacion = $request->get('calificacion');
        $retroalimentacion = $request->get('retroalimentacion');
        
        // Validar calificación
        if ($calificacion < 0 || $calificacion > 5) {
            return response()->json(['error' => 'La calificación debe estar entre 0 y 5'], 400);
        }
        
        // Buscar la entrega
        $entrega = DB::table('curso_actividad_entrega')
            ->where('curso_id', $cursoId)
            ->where('actividad_id', $actividadId)
            ->where('user_id', $estudianteId)
            ->first();
        
        if (!$entrega) {
            return response()->json(['error' => 'No se encontró la entrega'], 404);
        }
        
        // Actualizar calificación
        DB::table('curso_actividad_entrega')
            ->where('id', $entrega->id)
            ->update([
                'calificacion' => $calificacion,
                'comentarios_instructor' => $retroalimentacion,
                'estado' => 'revisado',
                'revisado_at' => now(),
                'updated_at' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'mensaje' => 'Calificación guardada correctamente'
        ]);
    }
    
    /**
     * Obtener cursos según el rol del usuario
     */
    private function getCursosSegunRol($user)
    {
        if (in_array($user->role, ['Super Admin', 'Administrador', 'Operador'])) {
            // Admin ve todos los cursos
            return Curso::where('estado', 'activo')->get();
        } elseif ($user->role === 'Docente') {
            // Docente solo ve cursos donde fue asignado vía curso_asignaciones.docente_id
            $cursosAsignado = \App\Models\CursoAsignacion::where('docente_id', $user->id)
                ->where('estado', 'activo')
                ->pluck('curso_id')
                ->unique();

            return Curso::whereIn('id', $cursosAsignado)
                ->where('estado', 'activo')
                ->get();
        }
        
        return collect();
    }
    
    /**
     * Obtener estudiantes con sus calificaciones
     */
    private function getEstudiantesConCalificaciones($curso, $estudiantesFiltro = null)
    {
        $query = User::whereHas('inscripciones', function($q) use ($curso) {
            $q->where('curso_id', $curso->id)
              ->where('curso_estudiantes.estado', 'activo');
        })
        ->with(['inscripciones' => function($q) use ($curso) {
            $q->where('curso_id', $curso->id);
        }]);

        // Si hay un filtro de estudiantes (para docentes asignados), aplicarlo
        if ($estudiantesFiltro !== null) {
            $query->whereIn('id', $estudiantesFiltro);
        }

        return $query->get()
        ->map(function($estudiante) use ($curso) {
            $inscripcion = $estudiante->inscripciones->first();
            
            // Calcular calificaciones por material/actividad
            $calificaciones = $this->calcularCalificaciones($estudiante, $curso);
            
            // Calcular progreso general (nota final ponderada del curso)
            $progreso = $this->calcularProgreso($calificaciones, $curso);
            
            return [
                'id' => $estudiante->id,
                'nombre' => $estudiante->name,
                'apellido1' => $estudiante->apellido1 ?? '',
                'apellido2' => $estudiante->apellido2 ?? '',
                'email' => $estudiante->email,
                'numero_documento' => $estudiante->numero_documento ?? '',
                'tipo_documento' => $estudiante->tipo_documento ?? '',
                'avatar' => $estudiante->avatar ?? null,
                'calificaciones' => $calificaciones,
                'progreso' => $progreso,
                'estado' => $this->determinarEstado($progreso),
            ];
        });
    }
    
    /**
     * Calcular calificaciones del estudiante
     */
    private function calcularCalificaciones($estudiante, $curso)
    {
        $calificaciones = [];
        
        // Calificaciones por materiales y sus actividades
        foreach ($curso->materiales as $material) {
            $materialCalif = [];
            
            // Obtener actividades del material
            foreach ($material->actividades as $actividad) {
                $nota = $actividad->calcularNotaEstudiante($estudiante->id);
                // Guardar la nota en escala 0-5 (sin conversión)
                $materialCalif[$actividad->tipo . '_' . $actividad->id] = $nota;
            }
            
            // Si el material no tiene actividades, asignar 0
            if (empty($materialCalif)) {
                $materialCalif['sin_actividades'] = 0;
            }
            
            $calificaciones['material_' . $material->id] = $materialCalif;
        }
        
        // Calificaciones por actividades independientes (sin material)
        foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
            $nota = $actividad->calcularNotaEstudiante($estudiante->id);
            // Guardar la nota en escala 0-5 (sin conversión)
            $calificaciones['actividad_' . $actividad->id] = $nota;
        }
        
        return $calificaciones;
    }
    
    /**
     * Calcular progreso general (nota final ponderada del curso)
     * Retorna un valor en escala 0-5.0
     * 
     * Proceso:
     * 1. Por cada actividad: nota × porcentaje_actividad
     * 2. Sumar actividades del mismo material
     * 3. Multiplicar suma por porcentaje_material
     * 4. Sumar todos los materiales
     */
    private function calcularProgreso($calificaciones, $curso)
    {
        if (empty($calificaciones)) {
            \Log::info('calcularProgreso: calificaciones vacías');
            return 0;
        }
        
        $notaFinal = 0;
        \Log::info('=== INICIO CÁLCULO PROGRESO ===', ['curso_id' => $curso->id]);
        
        // Calcular nota ponderada por materiales
        foreach ($curso->materiales as $material) {
            $materialKey = 'material_' . $material->id;
            if (!isset($calificaciones[$materialKey])) {
                \Log::info('Material sin calificaciones', ['material_id' => $material->id]);
                continue;
            }
            
            $calificacionesMaterial = $calificaciones[$materialKey];
            $sumaActividadesMaterial = 0;
            
            \Log::info('Procesando material', [
                'material_id' => $material->id,
                'material_nombre' => $material->titulo,
                'porcentaje_material' => $material->porcentaje_curso
            ]);
            
            // Paso 1 y 2: Multiplicar cada nota por su porcentaje de actividad y sumar
            foreach ($material->actividades as $actividad) {
                $actividadKey = $actividad->tipo . '_' . $actividad->id;
                if (isset($calificacionesMaterial[$actividadKey]) && $calificacionesMaterial[$actividadKey] > 0) {
                    $nota = $calificacionesMaterial[$actividadKey]; // Nota en escala 0-5
                    $porcentajeActividad = floatval($actividad->porcentaje_curso ?? 0) / 100; // Convertir a decimal
                    
                    // Multiplicar nota por porcentaje de actividad
                    $notaPonderadaActividad = $nota * $porcentajeActividad;
                    $sumaActividadesMaterial += $notaPonderadaActividad;
                    
                    \Log::info('Actividad procesada', [
                        'actividad_id' => $actividad->id,
                        'actividad_nombre' => $actividad->titulo,
                        'nota' => $nota,
                        'porcentaje_actividad' => $actividad->porcentaje_curso,
                        'nota_ponderada' => $notaPonderadaActividad
                    ]);
                }
            }
            
            // Paso 3: Multiplicar la suma de actividades por el porcentaje del material
            $porcentajeMaterial = floatval($material->porcentaje_curso ?? 0) / 100; // Convertir a decimal
            $notaPonderadaMaterial = $sumaActividadesMaterial * $porcentajeMaterial;
            
            \Log::info('Material calculado', [
                'suma_actividades' => $sumaActividadesMaterial,
                'porcentaje_material' => $material->porcentaje_curso,
                'nota_ponderada_material' => $notaPonderadaMaterial
            ]);
            
            // Paso 4: Agregar al total
            $notaFinal += $notaPonderadaMaterial;
        }
        
        // Calcular nota ponderada por actividades independientes (sin material)
        foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
            $actividadKey = 'actividad_' . $actividad->id;
            if (isset($calificaciones[$actividadKey]) && $calificaciones[$actividadKey] > 0) {
                $nota = $calificaciones[$actividadKey]; // Nota en escala 0-5
                $porcentaje = floatval($actividad->porcentaje_curso ?? 0) / 100; // Convertir a decimal
                
                // Multiplicar nota por porcentaje
                $notaPonderada = $nota * $porcentaje;
                $notaFinal += $notaPonderada;
                
                \Log::info('Actividad independiente procesada', [
                    'actividad_id' => $actividad->id,
                    'nota' => $nota,
                    'porcentaje' => $actividad->porcentaje_curso,
                    'nota_ponderada' => $notaPonderada
                ]);
            }
        }
        
        \Log::info('=== FIN CÁLCULO PROGRESO ===', ['nota_final' => $notaFinal]);
        
        return round($notaFinal, 2);
    }
    
    /**
     * Determinar estado según progreso (escala 0-5)
     */
    private function determinarEstado($progreso)
    {
        // Convertir de escala 0-5 a porcentaje para determinar estado
        $porcentaje = ($progreso / 5.0) * 100;
        
        if ($porcentaje >= 60) {
            return 'passed';
        } elseif ($porcentaje >= 50) {
            return 'at_risk';
        } else {
            return 'failed';
        }
    }
    
    /**
     * Obtener estructura de evaluación del curso
     */
    private function getEstructuraEvaluacion($curso)
    {
        $estructura = [];
        
        \Log::info('=== INICIO getEstructuraEvaluacion ===', ['curso_id' => $curso->id]);
        
        // Agrupar por materiales con sus actividades
        foreach ($curso->materiales as $material) {
            $componentes = [];
            
            \Log::info('Material encontrado', [
                'material_id' => $material->id,
                'material_nombre' => $material->titulo,
                'porcentaje_curso' => $material->porcentaje_curso,
                'porcentaje_curso_raw' => $material->getAttributes()['porcentaje_curso'] ?? 'NO EXISTE'
            ]);
            
            // Obtener actividades del material
            foreach ($material->actividades as $actividad) {
                $componentes[] = [
                    'id' => $actividad->id,
                    'nombre' => $actividad->titulo,
                    'tipo' => $actividad->tipo,
                    'peso' => floatval($actividad->porcentaje_curso ?? 0),
                    'habilitado' => (bool) $actividad->habilitado,
                ];
            }
            
            // CAMBIO: Agregar TODOS los materiales, incluso sin actividades
            $estructura[] = [
                'tipo' => 'material',
                'id' => $material->id,
                'nombre' => $material->titulo,
                'peso' => floatval($material->porcentaje_curso ?? 0),
                'componentes' => $componentes,
                'sin_actividades' => empty($componentes) // Flag para identificar materiales sin actividades
            ];
        }
        
        \Log::info('=== FIN getEstructuraEvaluacion ===', ['total_materiales' => count($estructura)]);
        
        // Agregar actividades independientes (sin material)
        foreach ($curso->actividades()->whereNull('material_id')->get() as $actividad) {
            $estructura[] = [
                'tipo' => 'actividad',
                'id' => $actividad->id,
                'nombre' => $actividad->titulo,
                'tipo_actividad' => $actividad->tipo,
                'peso' => floatval($actividad->porcentaje_curso ?? 0),
                'componentes' => []
            ];
        }
        
        return $estructura;
    }

    /**
     * Vista previa del certificado para un estudiante desde Control Pedagógico
     */
    public function previewCertificado(Request $request, $cursoId, $estudianteId)
    {
        try {
            $authUser = Auth::user();

            // Solo admins, operadores y docentes pueden ver la vista previa
            if (!in_array($authUser->role, ['Super Admin', 'Administrador', 'Operador', 'Docente'])) {
                return $this->certificadoError('No tienes permisos para ver este certificado.');
            }

            // Buscar curso y estudiante manualmente para manejar errores amigablemente
            $curso = Curso::with(['materiales.actividades', 'plantillaCertificado'])->find($cursoId);
            if (!$curso) {
                return $this->certificadoError('El curso no fue encontrado.');
            }

            $estudiante = User::find($estudianteId);
            if (!$estudiante) {
                return $this->certificadoError('El estudiante no fue encontrado.');
            }

            // Verificar que el estudiante está inscrito en el curso
            $inscrito = DB::table('curso_estudiantes')
                ->where('curso_id', $curso->id)
                ->where('estudiante_id', $estudiante->id)
                ->exists();

            if (!$inscrito) {
                return $this->certificadoError('El estudiante no está inscrito en este curso.');
            }

            // Verificar que el curso tiene plantilla de certificado
            $plantilla = $curso->plantillaCertificado;
            if (!$plantilla) {
                return $this->certificadoError('Este curso no tiene una plantilla de certificado configurada.');
            }

            // Obtener resumen y nota final de forma segura
            $resumen = $curso->getResumenCalificacionesEstudiante($estudiante->id);
            $notaFinal = number_format(floatval($resumen['nota_final'] ?? 0), 1);

            // Obtener o crear registro de certificado emitido con código de verificación
            $certificadoEmitido = null;
            try {
                $certificadoEmitido = \App\Models\CertificadoEmitido::obtenerOCrear(
                    $curso->id, $estudiante->id, floatval($resumen['nota_final'] ?? 0), $plantilla->id
                );
            } catch (\Throwable $e) {
                \Log::warning('No se pudo crear/obtener certificado emitido: ' . $e->getMessage());
            }

            // Detectar si se carga en iframe (preview modal)
            $enIframe = $request->query('iframe', false);

            $response = response()->view('academico.curso.certificado', [
                'curso' => $curso,
                'user' => $estudiante,
                'resumen' => $resumen,
                'plantilla' => $plantilla,
                'notaFinal' => $notaFinal,
                'certificadoEmitido' => $certificadoEmitido,
                'enIframe' => $enIframe,
            ]);

            // Permitir carga en iframe del mismo origen
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            return $response;
        } catch (\Throwable $e) {
            \Log::error('Error en previewCertificado: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return $this->certificadoError('Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Devolver una página HTML de error amigable para el iframe del certificado
     */
    private function certificadoError(string $mensaje): \Illuminate\Http\Response
    {
        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
        <style>
            body { margin:0; font-family: "Inter","Segoe UI",sans-serif; background:#525659;
                   display:flex; justify-content:center; align-items:center; min-height:100vh; }
            .error-box { background:#fff; border-radius:12px; padding:40px 50px; text-align:center;
                         box-shadow:0 10px 30px rgba(0,0,0,.3); max-width:500px; }
            .error-box i { font-size:48px; color:#e74c3c; margin-bottom:16px; display:block; }
            .error-box h3 { color:#1e3a5f; margin:0 0 12px; font-size:18px; }
            .error-box p  { color:#64748b; margin:0; font-size:14px; line-height:1.5; }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        </head><body>
        <div class="error-box">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>No se puede mostrar el certificado</h3>
            <p>' . e($mensaje) . '</p>
        </div></body></html>';
        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
