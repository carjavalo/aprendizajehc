<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Nuevos permisos CRUD para Gestión de Cursos
        $permissions = [
            ['name' => 'cursos.view',        'display_name' => 'Ver Lista de Cursos',      'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.create',      'display_name' => 'Crear Cursos',              'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.edit',        'display_name' => 'Editar Cursos',             'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.delete',      'display_name' => 'Eliminar Cursos',           'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.inscribir',   'display_name' => 'Inscribir Estudiantes',     'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.materiales',  'display_name' => 'Gestionar Materiales',      'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.actividades', 'display_name' => 'Gestionar Actividades',     'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'cursos.calificar',   'display_name' => 'Calificar Actividades',     'group' => 'Gestión de Cursos', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insertar solo los permisos que no existan ya
        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('name', $perm['name'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Asignar todos los nuevos permisos al Super Admin
        $newPermIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        foreach ($newPermIds as $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_name', 'Super Admin')
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_name' => 'Super Admin',
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Asignar permisos de gestión de cursos al Administrador
        $adminPerms = ['cursos.view', 'cursos.create', 'cursos.edit', 'cursos.delete', 'cursos.inscribir', 'cursos.materiales', 'cursos.actividades', 'cursos.calificar'];
        $adminPermIds = DB::table('permissions')->whereIn('name', $adminPerms)->pluck('id');
        foreach ($adminPermIds as $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_name', 'Administrador')
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_name' => 'Administrador',
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Asignar permisos limitados al Operador (ver, inscribir)
        $operadorPerms = ['cursos.view', 'cursos.inscribir', 'cursos.materiales', 'cursos.actividades'];
        $operadorPermIds = DB::table('permissions')->whereIn('name', $operadorPerms)->pluck('id');
        foreach ($operadorPermIds as $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_name', 'Operador')
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_name' => 'Operador',
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Asignar permisos al Docente (ver, materiales, actividades, calificar)
        $docentePerms = ['cursos.view', 'cursos.materiales', 'cursos.actividades', 'cursos.calificar'];
        $docentePermIds = DB::table('permissions')->whereIn('name', $docentePerms)->pluck('id');
        foreach ($docentePermIds as $permId) {
            $exists = DB::table('role_permissions')
                ->where('role_name', 'Docente')
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_name' => 'Docente',
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permNames = ['cursos.view', 'cursos.create', 'cursos.edit', 'cursos.delete', 'cursos.inscribir', 'cursos.materiales', 'cursos.actividades', 'cursos.calificar'];

        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
