<?php

namespace App\Http\Controllers;

use App\Models\UserLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class UserLoginController extends Controller
{
    /**
     * Display a listing of user logins.
     */
    public function index()
    {
        return view('admin.tracking.logins.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $userRole = $currentUser->role ?? null;
        
        $query = UserLogin::with('user')
            ->select([
                'user_logins.*',
                'users.name',
                'users.apellido1',
                'users.apellido2'
            ])
            ->leftJoin('users', 'user_logins.user_id', '=', 'users.id')
            ->orderBy('attempted_at', 'desc');
        
        // Filtrar por rol del usuario
        if ($userRole === 'Estudiante') {
            // Estudiantes solo ven sus propios ingresos
            $query->where('user_logins.user_id', $currentUser->id);
        } elseif ($userRole === 'Docente') {
            // Docentes ven ingresos de estudiantes en sus cursos
            $cursosDocente = \App\Models\CursoAsignacion::where('docente_id', $currentUser->id)->where('estado', 'activo')->pluck('curso_id');
                $estudiantesIds = \DB::table('curso_estudiantes')->whereIn('curso_id', $cursosDocente)->where('estado', 'activo')->pluck('estudiante_id')->toArray();
            
            // Incluir también los propios ingresos del docente
            $estudiantesIds[] = $currentUser->id;
            $query->whereIn('user_logins.user_id', $estudiantesIds);
        } elseif ($userRole === 'Consultor Agesoc') {
            // Consultor Agesoc solo ve estudiantes de tipo de vinculación Agesoc
            $query->where('users.role', 'Estudiante')
                  ->whereIn('users.vinculacion_contrato_id', function($q) {
                      $q->select('id')->from('vinculacion_contrato')->where('nombre', 'Agesoc');
                  });
        } elseif ($userRole === 'Consultor Asstracud') {
            // Consultor Asstracud solo ve estudiantes de tipo de vinculación Asstracud
            $query->where('users.role', 'Estudiante')
                  ->whereIn('users.vinculacion_contrato_id', function($q) {
                      $q->select('id')->from('vinculacion_contrato')->where('nombre', 'Asstracud');
                  });
        }
        
        // Aplicar filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('attempted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attempted_at', '<=', $request->date_to);
        }

        if ($request->filled('email')) {
            $query->where('user_logins.email', 'like', '%' . $request->email . '%');
        }

        return DataTables::of($query)
            ->addColumn('user_name', function ($login) {
                if ($login->user) {
                    return $login->user->name . ' ' . $login->user->apellido1 . ' ' . ($login->user->apellido2 ?? '');
                }
                return '<span class="text-muted">Usuario no encontrado</span>';
            })
            ->addColumn('formatted_date', function ($login) {
                return $login->attempted_at->format('d/m/Y H:i:s');
            })
            ->addColumn('status_badge', function ($login) {
                return $login->status === 'success' 
                    ? '<span class="badge badge-success"><i class="fas fa-check"></i> Exitoso</span>'
                    : '<span class="badge badge-danger"><i class="fas fa-times"></i> Fallido</span>';
            })
            ->addColumn('email_verified_badge', function ($login) {
                if ($login->email_verified === 'verified') {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Verificado</span>';
                } elseif ($login->email_verified === 'unverified') {
                    return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Pendiente</span>';
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->addColumn('actions', function ($login) {
                $actions = '<div class="btn-group" role="group">';
                
                // Botón de detalles
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="showDetails(' . $login->id . ')" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>';
                
                // Botón de reenviar verificación si es necesario
                if ($login->email_verified === 'unverified' && $login->user) {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning ml-1" onclick="resendVerification(' . $login->user->id . ')" title="Reenviar verificación">
                        <i class="fas fa-envelope"></i>
                    </button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['user_name', 'status_badge', 'email_verified_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show details of a specific login attempt.
     */
    public function show(UserLogin $userLogin): JsonResponse
    {
        $userLogin->load('user');
        
        return response()->json([
            'id' => $userLogin->id,
            'user' => $userLogin->user ? [
                'name' => $userLogin->user->name . ' ' . $userLogin->user->apellido1 . ' ' . ($userLogin->user->apellido2 ?? ''),
                'email' => $userLogin->user->email,
                'role' => $userLogin->user->role,
            ] : null,
            'email' => $userLogin->email,
            'ip_address' => $userLogin->ip_address,
            'user_agent' => $userLogin->user_agent,
            'status' => $userLogin->status,
            'email_verified' => $userLogin->email_verified,
            'failure_reason' => $userLogin->failure_reason,
            'attempted_at' => $userLogin->attempted_at->format('d/m/Y H:i:s'),
            'created_at' => $userLogin->created_at->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Resend email verification for a user.
     */
    public function resendVerification(User $user): JsonResponse
    {
        try {
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya tiene su email verificado.'
                ]);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación reenviado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar el email de verificación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get statistics for dashboard.
     */
    public function getStats(): JsonResponse
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        $currentUser = auth()->user();
        $userRole = $currentUser->role ?? null;
        
        // Crear query base según rol
        $baseQuery = UserLogin::query();
        
        if ($userRole === 'Estudiante') {
            // Estudiantes solo ven sus propias estadísticas
            $baseQuery->where('user_id', $currentUser->id);
        } elseif ($userRole === 'Docente') {
            // Docentes ven estadísticas de estudiantes en sus cursos
            $cursosDocente = \App\Models\CursoAsignacion::where('docente_id', $currentUser->id)->where('estado', 'activo')->pluck('curso_id');
                $estudiantesIds = \DB::table('curso_estudiantes')->whereIn('curso_id', $cursosDocente)->where('estado', 'activo')->pluck('estudiante_id')->toArray();
            
            $estudiantesIds[] = $currentUser->id;
            $baseQuery->whereIn('user_id', $estudiantesIds);
        }
        // Admin y Super Admin ven todas las estadísticas

        $stats = [
            'total_logins' => (clone $baseQuery)->count(),
            'successful_logins' => (clone $baseQuery)->where('status', 'success')->count(),
            'failed_logins' => (clone $baseQuery)->where('status', 'failed')->count(),
            'unverified_users' => (clone $baseQuery)->where('email_verified', 'unverified')->count(),
            'today_logins' => (clone $baseQuery)->whereDate('attempted_at', $today)->count(),
            'week_logins' => (clone $baseQuery)->where('attempted_at', '>=', $thisWeek)->count(),
            'month_logins' => (clone $baseQuery)->where('attempted_at', '>=', $thisMonth)->count(),
        ];

        return response()->json($stats);
    }
}

