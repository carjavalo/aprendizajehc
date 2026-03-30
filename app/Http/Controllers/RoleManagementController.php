<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== "Super Admin") {
                abort(403, "Acceso no autorizado. Se requiere rol de Super Admin.");
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with(["servicioArea", "vinculacionContrato", "sede"])->get();
        // Roles disponibles
        $availableRoles = User::getAvailableRoles();
        
        return view("admin.roles.index", compact("users", "availableRoles"));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            "role" => "required|in:Super Admin,Administrador,Docente,Estudiante,Registrado,Operador"
        ]);

        $user->role = $request->role;
        $user->save();

        if ($request->ajax()) {
            return response()->json(["success" => true, "message" => "Rol actualizado correctamente"]);
        }

        return redirect()->route("roles.index")->with("success", "Rol actualizado correctamente.");
    }
}
