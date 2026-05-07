<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'actividades_conf.view',   'display_name' => 'Ver Lista de Actividades',    'group' => 'Gestión de Actividades', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'actividades_conf.create', 'display_name' => 'Crear Actividades',           'group' => 'Gestión de Actividades', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'actividades_conf.edit',   'display_name' => 'Editar Actividades',           'group' => 'Gestión de Actividades', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'actividades_conf.delete', 'display_name' => 'Eliminar Actividades',         'group' => 'Gestión de Actividades', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm['name'])->exists()) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Super Admin: todos
        $permIds = DB::table('permissions')->whereIn('name', array_column($permissions, 'name'))->pluck('id');
        foreach ($permIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Super Admin')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Super Admin', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Administrador: todos
        $adminPermIds = DB::table('permissions')->whereIn('name', ['actividades_conf.view', 'actividades_conf.create', 'actividades_conf.edit', 'actividades_conf.delete'])->pluck('id');
        foreach ($adminPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Administrador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Administrador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Operador: solo ver
        $opPermIds = DB::table('permissions')->whereIn('name', ['actividades_conf.view'])->pluck('id');
        foreach ($opPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Operador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Operador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $permNames = ['actividades_conf.view', 'actividades_conf.create', 'actividades_conf.edit', 'actividades_conf.delete'];
        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
