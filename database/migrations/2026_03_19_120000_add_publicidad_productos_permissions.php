<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'publicidad.view',    'display_name' => 'Ver Publicidad y Productos',            'group' => 'Publicidad y Productos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'publicidad.create',  'display_name' => 'Crear Publicidad',                      'group' => 'Publicidad y Productos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'publicidad.edit',    'display_name' => 'Editar Publicidad',                      'group' => 'Publicidad y Productos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'publicidad.delete',  'display_name' => 'Eliminar Publicidad',                    'group' => 'Publicidad y Productos', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'publicidad.banner',  'display_name' => 'Configuración del Banner Principal',     'group' => 'Publicidad y Productos', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm['name'])->exists()) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Super Admin: todos
        $newPermIds = DB::table('permissions')->whereIn('name', array_column($permissions, 'name'))->pluck('id');
        foreach ($newPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Super Admin')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Super Admin', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Administrador: todos
        foreach ($newPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Administrador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Administrador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Operador: solo ver
        $opPermIds = DB::table('permissions')->whereIn('name', ['publicidad.view'])->pluck('id');
        foreach ($opPermIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Operador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert(['role_name' => 'Operador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $permNames = ['publicidad.view', 'publicidad.create', 'publicidad.edit', 'publicidad.delete', 'publicidad.banner'];
        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
