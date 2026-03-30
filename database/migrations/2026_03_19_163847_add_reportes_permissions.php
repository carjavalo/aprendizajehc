<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'reportes.view',     'display_name' => 'Ver Reportes',              'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.edit',     'display_name' => 'Editar Reportes',            'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.delete',   'display_name' => 'Eliminar Reportes',          'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.print',    'display_name' => 'Imprimir Reportes',          'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reportes.export',   'display_name' => 'Exportar Reportes a Excel',  'group' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm['name'])->exists()) {
                DB::table('permissions')->insert($perm);
            }
        }

        // Asignar todos los permisos de reportes al Super Admin
        $permIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
        foreach ($permIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Super Admin')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_name'     => 'Super Admin',
                    'permission_id' => $permId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        // Asignar permisos de reportes al Administrador
        foreach ($permIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', 'Administrador')->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_name'     => 'Administrador',
                    'permission_id' => $permId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permIds = DB::table('permissions')->where('group', 'Reportes')->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->where('group', 'Reportes')->delete();
    }
};
