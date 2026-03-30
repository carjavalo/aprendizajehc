<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Nuevos permisos CRUD para Gestión de Categorías
        $permissions = [
            ['name' => 'categorias.view',   'display_name' => 'Ver Lista de Categorías',  'group' => 'Gestión de Categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'categorias.create', 'display_name' => 'Crear Categorías',          'group' => 'Gestión de Categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'categorias.edit',   'display_name' => 'Editar Categorías',          'group' => 'Gestión de Categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'categorias.delete', 'display_name' => 'Eliminar Categorías',        'group' => 'Gestión de Categorías', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm['name'])->exists()) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Asignar todos al Super Admin
        $newPermIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        foreach ($newPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Super Admin')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Super Admin', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Administrador: todos los permisos de categorías
        $adminPerms = ['categorias.view', 'categorias.create', 'categorias.edit', 'categorias.delete'];
        $adminPermIds = DB::table('permissions')->whereIn('name', $adminPerms)->pluck('id');
        foreach ($adminPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Administrador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Administrador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Operador: solo ver
        $opPerms = ['categorias.view'];
        $opPermIds = DB::table('permissions')->whereIn('name', $opPerms)->pluck('id');
        foreach ($opPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Operador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Operador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $permNames = ['categorias.view', 'categorias.create', 'categorias.edit', 'categorias.delete'];
        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
