<?php

namespace App\Http\Controllers;

use App\Models\UserOperation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class UserOperationController extends Controller
{
    /**
     * Display a listing of user operations.
     */
    public function index()
    {
        return view('admin.tracking.operations.index');
    }

    /**
     * Get data for DataTables with role-based filtering.
     */
    public function getData(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $userRole = $currentUser->role ?? null;
        
        $query = UserOperation::with('user')
            ->select([
                'user_operations.*',
                'users.name',
                'users.apellido1',
                'users.apellido2'
            ])
            ->leftJoin('users', 'user_operations.user_id', '=', 'users.id')
            ->orderBy('user_operations.created_at', 'desc');
        
        // Filtrar por rol del usuario
        if ($userRole === 'Estudiante') {
            // Estudiantes solo ven sus propias operaciones
            $query->where('user_operations.user_id', $currentUser->id);
        } elseif ($userRole === 'Docente') {
            // Docentes ven operaciones de estudiantes en sus cursos
            $cursosDocente = \App\Models\CursoAsignacion::where('docente_id', $currentUser->id)->where('estado', 'activo')->pluck('curso_id');
                $estudiantesIds = DB::table('curso_estudiantes')->whereIn('curso_id', $cursosDocente)->where('estado', 'activo')->pluck('estudiante_id')->toArray();
            
            // Incluir también las propias operaciones del docente
            $estudiantesIds[] = $currentUser->id;
            $query->whereIn('user_operations.user_id', $estudiantesIds);        } elseif ($userRole === 'Consultor Agesoc') {
            // Consultor Agesoc solo ve operaciones de estudiantes con vinculación Agesoc
            $query->where('users.role', 'Estudiante')
                  ->whereIn('users.vinculacion_contrato_id', function($q) {
                      $q->select('id')->from('vinculacion_contrato')->where('nombre', 'Agesoc');
                  });
        } elseif ($userRole === 'Consultor Asstracud') {
            // Consultor Asstracud solo ve operaciones de estudiantes con vinculación Asstracud
            $query->where('users.role', 'Estudiante')
                  ->whereIn('users.vinculacion_contrato_id', function($q) {
                      $q->select('id')->from('vinculacion_contrato')->where('nombre', 'Asstracud');
                  });
        }
        // Admin y Super Admin ven todo (sin filtro adicional)

        // Aplicar filtros de búsqueda
        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->operation_type);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('user_operations.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('user_operations.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search_user')) {
            $query->where(function($q) use ($request) {
                $q->where('users.name', 'like', '%' . $request->search_user . '%')
                  ->orWhere('users.email', 'like', '%' . $request->search_user . '%');
            });
        }

        return DataTables::of($query)
            ->addColumn('user_name', function ($operation) {
                if ($operation->user) {
                    return $operation->user->name . ' ' . $operation->user->apellido1 . ' ' . ($operation->user->apellido2 ?? '');
                }
                return '<span class="text-muted">Usuario eliminado</span>';
            })
            ->addColumn('formatted_date', function ($operation) {
                return $operation->created_at->format('d/m/Y H:i:s');
            })
            ->addColumn('operation_badge', function ($operation) {
                $badges = [
                    'create' => '<span class="badge badge-success"><i class="fas fa-plus"></i> Crear</span>',
                    'update' => '<span class="badge badge-info"><i class="fas fa-edit"></i> Editar</span>',
                    'delete' => '<span class="badge badge-danger"><i class="fas fa-trash"></i> Eliminar</span>',
                    'view' => '<span class="badge badge-secondary"><i class="fas fa-eye"></i> Ver</span>',
                    'login' => '<span class="badge badge-primary"><i class="fas fa-sign-in-alt"></i> Login</span>',
                    'logout' => '<span class="badge badge-warning"><i class="fas fa-sign-out-alt"></i> Logout</span>',
                    'enroll' => '<span class="badge badge-success"><i class="fas fa-user-plus"></i> Inscripción</span>',
                    'submit' => '<span class="badge badge-info"><i class="fas fa-upload"></i> Entrega</span>',
                    'grade' => '<span class="badge badge-primary"><i class="fas fa-star"></i> Calificar</span>',
                    'access' => '<span class="badge badge-info"><i class="fas fa-door-open"></i> Acceso</span>',
                    'complete' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Completado</span>',
                ];
                
                return $badges[$operation->operation_type] ?? '<span class="badge badge-secondary">' . ucfirst($operation->operation_type) . '</span>';
            })
            ->addColumn('entity_badge', function ($operation) {
                $icon = match($operation->entity_type) {
                    'Curso' => 'fa-book',
                    'Actividad' => 'fa-tasks',
                    'Entrega' => 'fa-file-upload',
                    'Perfil' => 'fa-user',
                    'Usuario' => 'fa-users',
                    'Session' => 'fa-sign-in-alt',
                    'Material' => 'fa-file-alt',
                    'Quiz' => 'fa-question-circle',
                    'Foro' => 'fa-comments',
                    'Asignacion' => 'fa-user-graduate',
                    default => 'fa-cube'
                };
                
                return '<span class="badge badge-light"><i class="fas ' . $icon . '"></i> ' . $operation->entity_type . '</span>';
            })
            ->addColumn('actions', function ($operation) {
                return '<button type="button" class="btn btn-sm btn-info" onclick="showDetails(' . $operation->id . ')" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>';
            })
            ->rawColumns(['user_name', 'operation_badge', 'entity_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show details of a specific operation.
     */
    public function show(UserOperation $operation): JsonResponse
    {
        $operation->load('user');
        
        return response()->json([
            'id' => $operation->id,
            'user' => $operation->user ? [
                'name' => $operation->user->name . ' ' . $operation->user->apellido1 . ' ' . ($operation->user->apellido2 ?? ''),
                'email' => $operation->user->email,
                'role' => $operation->user->role,
            ] : null,
            'operation_type' => $operation->operation_type,
            'entity_type' => $operation->entity_type,
            'entity_id' => $operation->entity_id,
            'description' => $operation->description,
            'details' => $operation->details,
            'ip_address' => $operation->ip_address,
            'user_agent' => $operation->user_agent,
            'created_at' => $operation->created_at->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Get statistics for dashboard with role-based filtering.
     */
    public function getStats(): JsonResponse
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        $currentUser = auth()->user();
        $userRole = $currentUser->role ?? null;
        
        // Crear query base según rol
        $baseQuery = UserOperation::query();
        
        if ($userRole === 'Estudiante') {
            // Estudiantes solo ven sus propias estadísticas
            $baseQuery->where('user_id', $currentUser->id);
        } elseif ($userRole === 'Docente') {
            // Docentes ven estadísticas de estudiantes en sus cursos
            $cursosDocente = \App\Models\CursoAsignacion::where('docente_id', $currentUser->id)->where('estado', 'activo')->pluck('curso_id');
                $estudiantesIds = DB::table('curso_estudiantes')->whereIn('curso_id', $cursosDocente)->where('estado', 'activo')->pluck('estudiante_id')->toArray();
            
            $estudiantesIds[] = $currentUser->id;
            $baseQuery->whereIn('user_id', $estudiantesIds);
        }
        // Admin y Super Admin ven todas las estadísticas

        $stats = [
            'total_operations' => (clone $baseQuery)->count(),
            'today_operations' => (clone $baseQuery)->whereDate('created_at', $today)->count(),
            'week_operations' => (clone $baseQuery)->where('created_at', '>=', $thisWeek)->count(),
            'month_operations' => (clone $baseQuery)->where('created_at', '>=', $thisMonth)->count(),
            'operation_types' => (clone $baseQuery)
                ->select('operation_type', DB::raw('count(*) as count'))
                ->groupBy('operation_type')
                ->pluck('count', 'operation_type'),
        ];

        return response()->json($stats);
    }
}

