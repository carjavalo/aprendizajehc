<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        $roles = [
            ['name' => 'Super Admin',    'description' => 'Administrador principal del sistema',                        'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Administrador',  'description' => 'Administrador del sistema',                                  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Estudiante',     'description' => 'Usuario Basico del Sistema',                                 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Operador',       'description' => 'Administra la operatividad de la plataforma',                'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Docente',        'description' => 'Dirige los cursos del Sistema',                              'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Registrado',     'description' => 'Solo se le admite ver la publicidad de la plataforma',       'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')->where('name', $role['name'])->exists();
            if (!$exists) {
                DB::table('roles')->insert($role);
            }
        }
    }

    public function down(): void
    {
        // No eliminamos roles en rollback para no perder datos
    }
};
