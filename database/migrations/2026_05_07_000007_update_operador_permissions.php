<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $roleName = 'Operador';

        // Todos los permisos relevantes
        $permNames = [
            'cargos.create', 'cargos.edit', 'cargos.delete',
            'actividades_conf.create', 'actividades_conf.edit', 'actividades_conf.delete'
        ];

        // Obtener los ids de los permisos
        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');

        // Asignarlos al rol Operador si no existen
        foreach ($permIds as $permId) {
            if (!DB::table('role_permissions')->where('role_name', $roleName)->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_name' => $roleName,
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }

    public function down(): void
    {
        $roleName = 'Operador';
        $permNames = [
            'cargos.create', 'cargos.edit', 'cargos.delete',
            'actividades_conf.create', 'actividades_conf.edit', 'actividades_conf.delete'
        ];

        $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
        DB::table('role_permissions')
            ->where('role_name', $roleName)
            ->whereIn('permission_id', $permIds)
            ->delete();
    }
};
