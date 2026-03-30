<?php

namespace App\Http\Controllers;

use App\Models\MensajeChat;
use App\Models\User;
use App\Models\Curso;
use App\Models\CursoAsignacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Buscar usuarios según permisos del rol
     */
    public function buscarUsuarios(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        $user = Auth::user();
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Ingrese al menos 2 caracteres'
            ]);
        }

        try {
            $usuarios = User::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhereRaw("CONCAT(name, ' ', apellido1, ' ', apellido2) LIKE ?", ["%{$query}%"]);
            })
            ->where('id', '!=', $user->id) // Excluir al usuario actual
            ->where(function($q) use ($user) {
                // Aplicar filtros según rol
                if ($user->role === 'Docente') {
                    // Docentes pueden ver: estudiantes de sus cursos + operadores
                    $cursosIds = Curso::where('instructor_id', $user->id)->pluck('id');
                    $estudiantesIds = DB::table('curso_estudiantes')
                        ->whereIn('curso_id', $cursosIds)
                        ->where('estado', 'activo')
                        ->pluck('estudiante_id');
                    
                    $q->whereIn('id', $estudiantesIds)
                      ->orWhere('role', 'Operador');
                      
                } elseif ($user->role === 'Estudiante') {
                    // Estudiantes pueden ver: operadores + docentes y estudiantes del mismo curso
                    $cursosIds = DB::table('curso_estudiantes')
                        ->where('estudiante_id', $user->id)
                        ->where('estado', 'activo')
                        ->pluck('curso_id');
                    
                    $estudiantesIds = DB::table('curso_estudiantes')
                        ->whereIn('curso_id', $cursosIds)
                        ->where('estado', 'activo')
                        ->pluck('estudiante_id');
                    
                    $docentesIds = Curso::whereIn('id', $cursosIds)->pluck('instructor_id');
                    
                    $q->where('role', 'Operador')
                      ->orWhereIn('id', $estudiantesIds)
                      ->orWhereIn('id', $docentesIds);

                    // Si el estudiante es de tipo Agesoc, puede ver a los Consultores Agesoc
                    $vinculacion = \App\Models\VinculacionContrato::find($user->vinculacion_contrato_id);
                    if ($vinculacion && $vinculacion->nombre === 'Agesoc') {
                        $q->orWhere('role', 'Consultor Agesoc');
                    } elseif ($vinculacion && $vinculacion->nombre === 'Asstracud') {
                        $q->orWhere('role', 'Consultor Asstracud');
                    }
                      
                } elseif ($user->role === 'Consultor Agesoc') {
                    // Consultor Agesoc: Puede ver Operadores + Estudiantes con vinculación Agesoc
                    $q->where('role', 'Operador')
                      ->orWhere(function($sub) {
                          $sub->where('role', 'Estudiante')
                              ->whereHas('vinculacionContrato', function($vc) {
                                  $vc->where('nombre', 'Agesoc');
                              });
                      });

                } elseif ($user->role === 'Consultor Asstracud') {
                    // Consultor Asstracud: Puede ver Operadores + Estudiantes con vinculación Asstracud
                    $q->where('role', 'Operador')
                      ->orWhere(function($sub) {
                          $sub->where('role', 'Estudiante')
                              ->whereHas('vinculacionContrato', function($vc) {
                                  $vc->where('nombre', 'Asstracud');
                              });
                      });

                } else {
                    // Admin, Super Admin, Operador pueden ver todos
                    $q->whereNotNull('id');
                }
            })
            ->select('id', 'name', 'apellido1', 'apellido2', 'email', 'role')
            ->limit(10)
            ->get()
            ->map(function($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->full_name,
                    'email' => $usuario->email,
                    'role' => $usuario->role
                ];
            });

            return response()->json([
                'success' => true,
                'usuarios' => $usuarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje
     */
    public function enviarMensaje(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:4000',
            'tipo' => 'required|in:individual,grupal',
            'destinatario_id' => 'required_if:tipo,individual|exists:users,id',
            'grupo_destinatario' => 'required_if:tipo,grupal|in:estudiantes,docentes,operadores,mis_cursos,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Verificar si el estudiante está en Quiz/Evaluación activa
        if ($user->role === 'Estudiante' && $this->estudianteEnEvaluacion($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes enviar mensajes mientras estás en una evaluación activa'
            ], 403);
        }

        try {
            $tipo = $request->input('tipo');
            $mensaje = $request->input('mensaje');
            $destinatariosCount = 0;

            if ($tipo === 'individual') {
                // Mensaje individual
                $destinatarioId = $request->input('destinatario_id');
                
                // Verificar permisos
                if (!$this->puedeEnviarA($user, $destinatarioId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para enviar mensajes a este usuario'
                    ], 403);
                }

                MensajeChat::create([
                    'remitente_id' => $user->id,
                    'destinatario_id' => $destinatarioId,
                    'mensaje' => $mensaje,
                    'tipo' => 'individual',
                ]);
                
                $destinatariosCount = 1;

            } else {
                // Mensaje grupal
                $grupo = $request->input('grupo_destinatario');
                $destinatarios = $this->obtenerDestinatariosGrupo($user, $grupo);

                if ($destinatarios->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay destinatarios disponibles para este grupo'
                    ], 400);
                }

                foreach ($destinatarios as $destinatario) {
                    MensajeChat::create([
                        'remitente_id' => $user->id,
                        'destinatario_id' => $destinatario->id,
                        'mensaje' => $mensaje,
                        'tipo' => 'grupal',
                        'grupo_destinatario' => $grupo,
                    ]);
                }
                
                $destinatariosCount = $destinatarios->count();
            }

            return response()->json([
                'success' => true,
                'message' => "Mensaje enviado exitosamente a {$destinatariosCount} destinatario(s)",
                'destinatarios_count' => $destinatariosCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mensajes del usuario
     */
    public function obtenerMensajes(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $mensajes = MensajeChat::where('destinatario_id', $user->id)
                ->orWhere('remitente_id', $user->id)
                ->with(['remitente', 'destinatario'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'mensajes' => $mensajes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mensajes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar bandeja de entrada
     */
    public function bandeja()
    {
        $user = Auth::user();
        
        // Obtener mensajes recibidos
        $mensajesRecibidos = MensajeChat::where('destinatario_id', $user->id)
            ->with('remitente')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Obtener mensajes enviados
        $mensajesEnviados = MensajeChat::where('remitente_id', $user->id)
            ->with('destinatario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Contar no leídos
        $noLeidos = MensajeChat::where('destinatario_id', $user->id)
            ->where('leido', false)
            ->count();
        
        return view('chat.bandeja', compact('mensajesRecibidos', 'mensajesEnviados', 'noLeidos'));
    }

    /**
     * Marcar mensaje como leído
     */
    public function marcarLeido(Request $request, $mensajeId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $mensaje = MensajeChat::where('id', $mensajeId)
                ->where('destinatario_id', $user->id)
                ->first();
            
            if (!$mensaje) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensaje no encontrado'
                ], 404);
            }
            
            $mensaje->leido = true;
            $mensaje->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Mensaje marcado como leído'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un estudiante está en evaluación activa
     */
    private function estudianteEnEvaluacion(int $estudianteId): bool
    {
        // Obtener cursos activos del estudiante
        $cursosIds = DB::table('curso_estudiantes')
            ->where('estudiante_id', $estudianteId)
            ->where('estado', 'activo')
            ->pluck('curso_id');

        if ($cursosIds->isEmpty()) {
            return false;
        }

        // Verificar si hay quiz/evaluaciones activas en esos cursos
        $now = now();
        $evaluacionActiva = DB::table('curso_actividades')
            ->whereIn('curso_id', $cursosIds)
            ->whereIn('tipo', ['quiz', 'evaluacion'])
            ->where('habilitado', true)
            ->where('fecha_apertura', '<=', $now)
            ->where(function($q) use ($now) {
                $q->where('fecha_cierre', '>=', $now)
                  ->orWhereNull('fecha_cierre');
            })
            ->exists();

        return $evaluacionActiva;
    }

    /**
     * Verificar si el usuario puede enviar mensaje a otro usuario
     */
    private function puedeEnviarA(User $remitente, int $destinatarioId): bool
    {
        $destinatario = User::find($destinatarioId);
        
        if (!$destinatario) {
            return false;
        }

        // Admin, Super Admin y Operador pueden enviar a todos
        if (in_array($remitente->role, ['Super Admin', 'Administrador', 'Operador'])) {
            return true;
        }

        // Docentes pueden enviar a estudiantes de sus cursos + operadores
        if ($remitente->role === 'Docente') {
            if ($destinatario->role === 'Operador') {
                return true;
            }
            
            $cursosIds = Curso::where('instructor_id', $remitente->id)->pluck('id');
            $esEstudianteDeCurso = DB::table('curso_estudiantes')
                ->whereIn('curso_id', $cursosIds)
                ->where('estudiante_id', $destinatarioId)
                ->where('estado', 'activo')
                ->exists();
                
            return $esEstudianteDeCurso;
        }

        // Estudiantes pueden enviar a operadores + docentes y estudiantes del mismo curso
        if ($remitente->role === 'Estudiante') {
            if ($destinatario->role === 'Operador') {
                return true;
            }
            if ($destinatario->role === 'Consultor Agesoc') {
                $vinculacion = \App\Models\VinculacionContrato::find($remitente->vinculacion_contrato_id);
                if ($vinculacion && $vinculacion->nombre === 'Agesoc') {
                    return true;
                }
            }
            if ($destinatario->role === 'Consultor Asstracud') {
                $vinculacion = \App\Models\VinculacionContrato::find($remitente->vinculacion_contrato_id);
                if ($vinculacion && $vinculacion->nombre === 'Asstracud') {
                    return true;
                }
            }
            
            $cursosIds = DB::table('curso_estudiantes')
                ->where('estudiante_id', $remitente->id)
                ->where('estado', 'activo')
                ->pluck('curso_id');
            
            // Verificar si es docente de alguno de sus cursos
            $esDocente = Curso::whereIn('id', $cursosIds)
                ->where('instructor_id', $destinatarioId)
                ->exists();
                
            if ($esDocente) {
                return true;
            }
            
            // Verificar si es estudiante del mismo curso
            $esCompañero = DB::table('curso_estudiantes')
                ->whereIn('curso_id', $cursosIds)
                ->where('estudiante_id', $destinatarioId)
                ->where('estado', 'activo')
                ->exists();
                
            return $esCompañero;
        }

        // Consultor Agesoc puede enviar a Operadores y Estudiantes con vinculación Agesoc
        if ($remitente->role === 'Consultor Agesoc') {
            if ($destinatario->role === 'Operador') {
                return true;
            }
            if ($destinatario->role === 'Estudiante') {
                $vinculacion = \App\Models\VinculacionContrato::find($destinatario->vinculacion_contrato_id);
                if ($vinculacion && $vinculacion->nombre === 'Agesoc') {
                    return true;
                }
            }
            return false;
        }

        // Consultor Asstracud puede enviar a Operadores y Estudiantes con vinculación Asstracud
        if ($remitente->role === 'Consultor Asstracud') {
            if ($destinatario->role === 'Operador') {
                return true;
            }
            if ($destinatario->role === 'Estudiante') {
                $vinculacion = \App\Models\VinculacionContrato::find($destinatario->vinculacion_contrato_id);
                if ($vinculacion && $vinculacion->nombre === 'Asstracud') {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Obtener destinatarios según el grupo seleccionado
     */
    private function obtenerDestinatariosGrupo(User $remitente, string $grupo)
    {
        $query = User::where('id', '!=', $remitente->id);

        switch ($grupo) {
            case 'all':
                // Admin/Operador pueden enviar a todos
                if (!in_array($remitente->role, ['Super Admin', 'Administrador', 'Operador'])) {
                    return collect([]);
                }
                break;

            case 'estudiantes':
                $query->where('role', 'Estudiante');
                // Si es docente, solo sus estudiantes
                if ($remitente->role === 'Docente') {
                    $cursosIds = Curso::where('instructor_id', $remitente->id)->pluck('id');
                    $estudiantesIds = DB::table('curso_estudiantes')
                        ->whereIn('curso_id', $cursosIds)
                        ->where('estado', 'activo')
                        ->pluck('estudiante_id');
                    $query->whereIn('id', $estudiantesIds);
                }
                break;

            case 'docentes':
                $query->where('role', 'Docente');
                // Si es estudiante, solo docentes de sus cursos
                if ($remitente->role === 'Estudiante') {
                    $cursosIds = DB::table('curso_estudiantes')
                        ->where('estudiante_id', $remitente->id)
                        ->where('estado', 'activo')
                        ->pluck('curso_id');
                    $docentesIds = Curso::whereIn('id', $cursosIds)->pluck('instructor_id');
                    $query->whereIn('id', $docentesIds);
                }
                break;

            case 'operadores':
                $query->where('role', 'Operador');
                break;

            case 'mis_cursos':
                // Estudiantes de los cursos del docente
                if ($remitente->role === 'Docente') {
                    $cursosIds = Curso::where('instructor_id', $remitente->id)->pluck('id');
                    $estudiantesIds = DB::table('curso_estudiantes')
                        ->whereIn('curso_id', $cursosIds)
                        ->where('estado', 'activo')
                        ->pluck('estudiante_id');
                    $query->whereIn('id', $estudiantesIds);
                } else {
                    return collect([]);
                }
                break;

            default:
                return collect([]);
        }

        return $query->get();
    }
}

