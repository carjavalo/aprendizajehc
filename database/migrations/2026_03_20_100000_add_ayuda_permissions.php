<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'ayuda.view',   'display_name' => 'Ver Banners',     'group' => 'Ayuda', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ayuda.create', 'display_name' => 'Crear Banner',    'group' => 'Ayuda', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ayuda.edit',   'display_name' => 'Editar Banner',   'group' => 'Ayuda', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ayuda.delete', 'display_name' => 'Eliminar Banner', 'group' => 'Ayuda', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm['name'])->exists()) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Super Admin: todos los permisos de Ayuda
        $allPermIds = DB::table('permissions')->whereIn('name', array_column($permissions, 'name'))->pluck('id');
        foreach ($allPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Super Admin')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Super Admin', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Administrador: todos los permisos de Ayuda
        foreach ($allPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Administrador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Administrador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Operador: solo ver
        $opPermIds = DB::table('permissions')->whereIn('name', ['ayuda.view'])->pluck('id');
        foreach ($opPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Operador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Operador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $permNames = ['ayuda.view', 'ayuda.create', 'ayuda.edit', 'ayuda.delete'];
        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
