<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Vista principal de asignación de permisos
     */
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get();
        $roles = Role::orderBy('name')->pluck('name')->toArray();

        // Si no hay roles en la tabla, usar los hardcoded
        if (empty($roles)) {
            $roles = ['Super Admin', 'Administrador', 'Docente', 'Estudiante', 'Registrado', 'Operador'];
        }

        // Obtener permisos asignados a cada rol
        $rolePermissions = DB::table('role_permissions')
            ->select('role_name', 'permission_id')
            ->get()
            ->groupBy('role_name')
            ->map(function ($items) {
                return $items->pluck('permission_id')->toArray();
            })
            ->toArray();

        // Obtener roles asignables por cada rol
        $roleAssignableRoles = DB::table('role_assignable_roles')
            ->select('role_name', 'assignable_role_name')
            ->get()
            ->groupBy('role_name')
            ->map(function ($items) {
                return $items->pluck('assignable_role_name')->toArray();
            })
            ->toArray();

        // Agrupar permisos por grupo con orden personalizado
        $permissionGroups = $permissions->groupBy('group');

        // Orden específico de grupos para la vista
        $groupOrder = [
            'Gestión de Usuarios',
            'Seguimiento',
            'Académico',
            'Capacitaciones',
            'Gestión de Cursos',
            'Gestión de Categorías',
            'Gestión de Áreas',
            'Publicidad y Productos',
            'Ayuda',
            'Reportes',
            'Componentes',
            'Configuración',
            'Comunicación',
        ];

        $sorted = collect();
        foreach ($groupOrder as $group) {
            if ($permissionGroups->has($group)) {
                $sorted[$group] = $permissionGroups[$group];
            }
        }
        // Agregar grupos que no estén en el orden definido (por si se crean nuevos)
        foreach ($permissionGroups as $group => $perms) {
            if (!$sorted->has($group)) {
                $sorted[$group] = $perms;
            }
        }
        $permissionGroups = $sorted;

        return view('permisos.index', compact(
            'permissions',
            'roles',
            'rolePermissions',
            'roleAssignableRoles',
            'permissionGroups'
        ));
    }

    /**
     * Guardar los permisos asignados a los roles
     */
    public function updatePermissions(Request $request)
    {
        $data = $request->input('permissions', []);

        DB::beginTransaction();
        try {
            // Limpiar permisos anteriores
            DB::table('role_permissions')->delete();

            $now = now();
            $inserts = [];
            foreach ($data as $roleName => $permissionIds) {
                foreach ($permissionIds as $permId) {
                    $inserts[] = [
                        'role_name' => $roleName,
                        'permission_id' => (int) $permId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($inserts)) {
                // Insertar en lotes de 500
                foreach (array_chunk($inserts, 500) as $chunk) {
                    DB::table('role_permissions')->insert($chunk);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permisos actualizados correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar permisos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Guardar los roles asignables por cada rol
     */
    public function updateAssignableRoles(Request $request)
    {
        $data = $request->input('assignable', []);

        DB::beginTransaction();
        try {
            DB::table('role_assignable_roles')->delete();

            $now = now();
            $inserts = [];
            foreach ($data as $roleName => $assignableRoles) {
                foreach ($assignableRoles as $assignableRole) {
                    $inserts[] = [
                        'role_name' => $roleName,
                        'assignable_role_name' => $assignableRole,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($inserts)) {
                DB::table('role_assignable_roles')->insert($inserts);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Roles asignables actualizados correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar roles asignables: ' . $e->getMessage()], 500);
        }
    }
}
