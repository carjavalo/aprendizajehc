<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::query();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn("action", function($row){
                    $btn = "<button data-id=\"".$row->id."\" class=\"edit btn btn-primary btn-sm mx-1 editRole\"><i class=\"fas fa-edit\"></i></button>";
                    $btn .= "<button data-id=\"".$row->id."\" class=\"delete btn btn-danger btn-sm mx-1 deleteRole\"><i class=\"fas fa-trash-alt\"></i></button>";
                    return $btn;
                })
                ->rawColumns(["action"])
                ->make(true);
        }

        return view("roles.index");
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:roles,name," . $request->id,
        ]);

        $role = Role::updateOrCreate(
            ["id" => $request->id],
            [
                "name" => $request->name, 
                "description" => $request->description
            ]
        );

        return response()->json(["success" => "Rol guardado exitosamente.", "data" => $role]);
    }

    public function edit($id)
    {
        $role = Role::find($id);
        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if ($role) {
            $roleName = $role->name;

            // Limpiar permisos asociados al rol eliminado
            DB::table('role_permissions')->where('role_name', $roleName)->delete();
            DB::table('role_assignable_roles')->where('role_name', $roleName)->delete();
            DB::table('role_assignable_roles')->where('assignable_role_name', $roleName)->delete();

            $role->delete();
        }
        return response()->json(["success" => "Rol eliminado exitosamente."]);
    }
}



