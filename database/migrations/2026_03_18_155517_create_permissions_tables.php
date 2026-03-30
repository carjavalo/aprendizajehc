<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de permisos disponibles en el sistema
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('group')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabla pivote: qué permisos tiene cada rol
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['role_name', 'permission_id']);
        });

        // Tabla: qué roles puede asignar cada rol
        Schema::create('role_assignable_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->string('assignable_role_name');
            $table->timestamps();

            $table->unique(['role_name', 'assignable_role_name']);
        });

        // Insertar permisos predefinidos basados en el menú del sistema
        $permissions = [
            ['name' => 'users.view',           'display_name' => 'Ver Lista de Usuarios',           'group' => 'Gestión de Usuarios'],
            ['name' => 'users.create',         'display_name' => 'Crear Usuarios',                  'group' => 'Gestión de Usuarios'],
            ['name' => 'users.edit',           'display_name' => 'Editar Usuarios',                 'group' => 'Gestión de Usuarios'],
            ['name' => 'users.delete',         'display_name' => 'Eliminar Usuarios',               'group' => 'Gestión de Usuarios'],
            ['name' => 'users.import',         'display_name' => 'Importar Usuarios',               'group' => 'Gestión de Usuarios'],
            ['name' => 'roles.manage',         'display_name' => 'Gestionar Roles',                 'group' => 'Gestión de Usuarios'],
            ['name' => 'permissions.manage',   'display_name' => 'Asignar Permisos',                'group' => 'Gestión de Usuarios'],
            ['name' => 'tracking.logins',      'display_name' => 'Ver Seguimiento de Ingresos',     'group' => 'Seguimiento'],
            ['name' => 'tracking.operations',  'display_name' => 'Ver Seguimiento de Operaciones',  'group' => 'Seguimiento'],
            ['name' => 'academic.courses',     'display_name' => 'Ver Cursos Disponibles',          'group' => 'Académico'],
            ['name' => 'academic.control',     'display_name' => 'Control Pedagógico',              'group' => 'Académico'],
            ['name' => 'config.categorias',    'display_name' => 'Gestionar Categorías',            'group' => 'Capacitaciones'],
            ['name' => 'config.areas',         'display_name' => 'Gestionar Áreas',                 'group' => 'Capacitaciones'],
            ['name' => 'config.cursos',        'display_name' => 'Gestionar Cursos',                'group' => 'Capacitaciones'],
            ['name' => 'config.servicios',     'display_name' => 'Gestionar Servicios/Áreas',       'group' => 'Componentes'],
            ['name' => 'config.vinculacion',   'display_name' => 'Gestionar Vinculación/Contrato',  'group' => 'Componentes'],
            ['name' => 'config.sedes',         'display_name' => 'Gestionar Sedes',                 'group' => 'Componentes'],
            ['name' => 'config.asignacion',    'display_name' => 'Asignación de Cursos',            'group' => 'Configuración'],
            ['name' => 'config.certificados',  'display_name' => 'Editor de Certificados',          'group' => 'Configuración'],
            ['name' => 'config.publicidad',    'display_name' => 'Publicidad y Productos',          'group' => 'Configuración'],
            ['name' => 'chat.access',          'display_name' => 'Acceso al Chat',                  'group' => 'Comunicación'],
        ];

        $now = now();
        foreach ($permissions as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        \DB::table('permissions')->insert($permissions);

        // Super Admin: todos los permisos
        $allPermissions = \DB::table('permissions')->pluck('id');
        $superAdminPerms = [];
        foreach ($allPermissions as $permId) {
            $superAdminPerms[] = ['role_name' => 'Super Admin', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now];
        }
        \DB::table('role_permissions')->insert($superAdminPerms);

        // Administrador
        $adminPermNames = ['users.view','users.create','users.edit','users.import','tracking.logins','tracking.operations','academic.courses','academic.control','config.categorias','config.areas','config.cursos','config.servicios','config.vinculacion','config.sedes','config.asignacion','config.certificados','config.publicidad','chat.access'];
        $adminPermIds = \DB::table('permissions')->whereIn('name', $adminPermNames)->pluck('id');
        foreach ($adminPermIds as $permId) {
            \DB::table('role_permissions')->insert(['role_name' => 'Administrador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Operador
        $operadorPermNames = ['users.view','tracking.logins','tracking.operations','academic.courses','academic.control','config.categorias','config.areas','config.cursos','config.asignacion','chat.access'];
        $operadorPermIds = \DB::table('permissions')->whereIn('name', $operadorPermNames)->pluck('id');
        foreach ($operadorPermIds as $permId) {
            \DB::table('role_permissions')->insert(['role_name' => 'Operador', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Docente
        $docentePermNames = ['academic.courses', 'academic.control', 'chat.access'];
        $docentePermIds = \DB::table('permissions')->whereIn('name', $docentePermNames)->pluck('id');
        foreach ($docentePermIds as $permId) {
            \DB::table('role_permissions')->insert(['role_name' => 'Docente', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Estudiante
        $estudiantePermNames = ['academic.courses', 'chat.access'];
        $estudiantePermIds = \DB::table('permissions')->whereIn('name', $estudiantePermNames)->pluck('id');
        foreach ($estudiantePermIds as $permId) {
            \DB::table('role_permissions')->insert(['role_name' => 'Estudiante', 'permission_id' => $permId, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Roles asignables por defecto
        $assignableDefaults = [
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Super Admin', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Administrador', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Operador', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Docente', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Estudiante', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Super Admin', 'assignable_role_name' => 'Registrado', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Administrador', 'assignable_role_name' => 'Operador', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Administrador', 'assignable_role_name' => 'Docente', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Administrador', 'assignable_role_name' => 'Estudiante', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'Administrador', 'assignable_role_name' => 'Registrado', 'created_at' => $now, 'updated_at' => $now],
        ];
        \DB::table('role_assignable_roles')->insert($assignableDefaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignable_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
