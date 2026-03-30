@extends('admin.layouts.master')

@section('title', 'Asignar Permisos')

@section('content_header')
    <h1><i class="fas fa-key mr-2"></i>Asignar Permisos</h1>
@stop

@section('content')
    {{-- Tabs de navegación --}}
    <ul class="nav nav-tabs" id="permisosTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="permisos-tab" data-toggle="tab" href="#tab-permisos" role="tab">
                <i class="fas fa-shield-alt mr-1"></i> Permisos por Rol
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="asignables-tab" data-toggle="tab" href="#tab-asignables" role="tab">
                <i class="fas fa-user-cog mr-1"></i> Roles Asignables
            </a>
        </li>
    </ul>

    <div class="tab-content" id="permisosTabContent">
        {{-- ═══════════ TAB 1: PERMISOS POR ROL ═══════════ --}}
        <div class="tab-pane fade show active" id="tab-permisos" role="tabpanel">
            <div class="card card-outline card-primary mt-0" style="border-top: none; border-top-left-radius: 0; border-top-right-radius: 0;">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-table mr-2"></i>Matriz de Permisos por Rol</h3>
                    <button type="button" class="btn btn-light btn-sm" id="btnGuardarPermisos">
                        <i class="fas fa-save mr-1"></i> Guardar Permisos
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0" id="permissionsMatrix">
                            <thead>
                                <tr class="bg-primary text-white text-center">
                                    <th class="text-left" style="min-width: 250px; position: sticky; left: 0; z-index: 2; background: #2c4370;">
                                        Permiso
                                    </th>
                                    @foreach($roles as $role)
                                        <th style="min-width: 120px;">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="small">{{ $role }}</span>
                                                <label class="mb-0 mt-1" title="Marcar/desmarcar todos para {{ $role }}">
                                                    <input type="checkbox" class="toggle-all-role" data-role="{{ $role }}">
                                                    <small class="text-light">Todos</small>
                                                </label>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissionGroups as $group => $groupPerms)
                                    <tr class="bg-light">
                                        <td colspan="{{ count($roles) + 1 }}" class="font-weight-bold text-primary" style="background: #e8edf5;">
                                            <i class="fas fa-folder-open mr-1"></i> {{ $group }}
                                        </td>
                                    </tr>
                                    @foreach($groupPerms as $permission)
                                        <tr>
                                            <td class="pl-4" style="position: sticky; left: 0; z-index: 1; background: #fff;">
                                                <span title="{{ $permission->name }}">{{ $permission->display_name }}</span>
                                                <br><small class="text-muted">{{ $permission->name }}</small>
                                            </td>
                                            @foreach($roles as $role)
                                                @php
                                                    $isChecked = isset($rolePermissions[$role]) && in_array($permission->id, $rolePermissions[$role]);
                                                    $isSuperAdmin = $role === 'Super Admin';
                                                @endphp
                                                <td class="text-center align-middle">
                                                    <div class="custom-control custom-checkbox d-flex justify-content-center">
                                                        <input type="checkbox"
                                                            class="custom-control-input perm-checkbox"
                                                            id="perm_{{ $role }}_{{ $permission->id }}"
                                                            data-role="{{ $role }}"
                                                            data-permission="{{ $permission->id }}"
                                                            {{ $isChecked ? 'checked' : '' }}
                                                            {{ $isSuperAdmin ? 'checked disabled' : '' }}>
                                                        <label class="custom-control-label" for="perm_{{ $role }}_{{ $permission->id }}"></label>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary" id="btnGuardarPermisosBottom">
                        <i class="fas fa-save mr-1"></i> Guardar Permisos
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════ TAB 2: ROLES ASIGNABLES ═══════════ --}}
        <div class="tab-pane fade" id="tab-asignables" role="tabpanel">
            <div class="card card-outline card-primary mt-0" style="border-top: none; border-top-left-radius: 0; border-top-right-radius: 0;">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-users-cog mr-2"></i>¿Qué roles puede asignar cada rol?</h3>
                    <button type="button" class="btn btn-light btn-sm" id="btnGuardarAsignables">
                        <i class="fas fa-save mr-1"></i> Guardar Configuración
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Defina qué roles puede asignar cada tipo de usuario al editar otros usuarios del sistema.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="assignableMatrix">
                            <thead>
                                <tr class="bg-primary text-white text-center">
                                    <th class="text-left" style="min-width: 200px;">
                                        Rol del usuario
                                    </th>
                                    @foreach($roles as $targetRole)
                                        <th style="min-width: 120px;">
                                            <small>Puede asignar</small><br>
                                            <strong>{{ $targetRole }}</strong>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $sourceRole)
                                    <tr>
                                        <td class="font-weight-bold align-middle">
                                            <i class="fas fa-user-tag mr-1 text-primary"></i>
                                            {{ $sourceRole }}
                                        </td>
                                        @foreach($roles as $targetRole)
                                            @php
                                                $isAssignable = isset($roleAssignableRoles[$sourceRole]) && in_array($targetRole, $roleAssignableRoles[$sourceRole]);
                                                $isSuperAdmin = $sourceRole === 'Super Admin';
                                            @endphp
                                            <td class="text-center align-middle">
                                                <div class="custom-control custom-checkbox d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="custom-control-input assignable-checkbox"
                                                        id="assign_{{ Str::slug($sourceRole) }}_{{ Str::slug($targetRole) }}"
                                                        data-source="{{ $sourceRole }}"
                                                        data-target="{{ $targetRole }}"
                                                        {{ $isAssignable ? 'checked' : '' }}
                                                        {{ $isSuperAdmin ? 'checked disabled' : '' }}>
                                                    <label class="custom-control-label" for="assign_{{ Str::slug($sourceRole) }}_{{ Str::slug($targetRole) }}"></label>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary" id="btnGuardarAsignablesBottom">
                        <i class="fas fa-save mr-1"></i> Guardar Configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    /* ── Colores corporativos ── */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2c4370 0%, #1e2f4d 100%) !important;
    }
    .nav-tabs .nav-link.active {
        background-color: #2c4370 !important;
        color: #fff !important;
        border-color: #2c4370 #2c4370 transparent !important;
        font-weight: 600;
    }
    .nav-tabs .nav-link {
        color: #2c4370;
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        color: #1e2f4d;
        background: #e8edf5;
        border-color: #dee2e6 #dee2e6 transparent;
    }
    .card-outline.card-primary {
        border-top-color: #2c4370 !important;
    }
    .text-primary {
        color: #2c4370 !important;
    }
    .btn-primary {
        background-color: #2c4370 !important;
        border-color: #2c4370 !important;
    }
    .btn-primary:hover {
        background-color: #1e2f4d !important;
        border-color: #1e2f4d !important;
    }
    .bg-primary {
        background-color: #2c4370 !important;
    }

    /* ── Tabla matriz ── */
    #permissionsMatrix th,
    #assignableMatrix th {
        vertical-align: middle;
        font-size: 0.85rem;
    }
    #permissionsMatrix td,
    #assignableMatrix td {
        vertical-align: middle;
    }
    #permissionsMatrix tbody tr:hover,
    #assignableMatrix tbody tr:hover {
        background-color: #f0f4fa;
    }

    /* ── Checkbox personalizado ── */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #2c4370;
        border-color: #2c4370;
    }
    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(44, 67, 112, 0.25);
    }

    /* ── Fila de grupo ── */
    .table tbody tr.bg-light td {
        border-bottom: 2px solid #2c4370;
    }

    /* ── Sticky column ── */
    #permissionsMatrix td:first-child {
        border-right: 2px solid #dee2e6;
    }

    /* ── Card tab seamless ── */
    .tab-content > .tab-pane > .card {
        margin-top: -1px !important;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        #permissionsMatrix th, #permissionsMatrix td,
        #assignableMatrix th, #assignableMatrix td {
            font-size: 0.75rem;
            padding: 0.35rem;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ═══════ Toggle all checkboxes for a role column ═══════
    $('.toggle-all-role').on('change', function() {
        var role = $(this).data('role');
        var checked = $(this).is(':checked');
        $('.perm-checkbox[data-role="' + role + '"]:not(:disabled)').prop('checked', checked);
    });

    // ═══════ GUARDAR PERMISOS (Tab 1) ═══════
    function guardarPermisos() {
        var permissions = {};

        // Super Admin siempre tiene todos los permisos
        @foreach($roles as $role)
            permissions['{{ $role }}'] = [];
        @endforeach

        // Recoger checkboxes marcados
        $('.perm-checkbox:checked').each(function() {
            var role = $(this).data('role');
            var permId = $(this).data('permission');
            if (!permissions[role]) permissions[role] = [];
            permissions[role].push(permId);
        });

        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...').attr('disabled', true);

        $.ajax({
            url: '{{ route("permisos.update-permissions") }}',
            type: 'POST',
            data: { permissions: permissions },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Permisos guardados!',
                    text: response.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar permisos'
                });
            },
            complete: function() {
                $('#btnGuardarPermisos, #btnGuardarPermisosBottom')
                    .html('<i class="fas fa-save mr-1"></i> Guardar Permisos')
                    .removeAttr('disabled');
            }
        });
    }

    $('#btnGuardarPermisos, #btnGuardarPermisosBottom').on('click', guardarPermisos);

    // ═══════ GUARDAR ROLES ASIGNABLES (Tab 2) ═══════
    function guardarAsignables() {
        var assignable = {};

        @foreach($roles as $role)
            assignable['{{ $role }}'] = [];
        @endforeach

        $('.assignable-checkbox:checked').each(function() {
            var source = $(this).data('source');
            var target = $(this).data('target');
            if (!assignable[source]) assignable[source] = [];
            assignable[source].push(target);
        });

        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...').attr('disabled', true);

        $.ajax({
            url: '{{ route("permisos.update-assignable") }}',
            type: 'POST',
            data: { assignable: assignable },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Configuración guardada!',
                    text: response.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar configuración'
                });
            },
            complete: function() {
                $('#btnGuardarAsignables, #btnGuardarAsignablesBottom')
                    .html('<i class="fas fa-save mr-1"></i> Guardar Configuración')
                    .removeAttr('disabled');
            }
        });
    }

    $('#btnGuardarAsignables, #btnGuardarAsignablesBottom').on('click', guardarAsignables);

    // ═══════ Highlight de filas al pasar el mouse ═══════
    $('td').on('mouseenter', function() {
        $(this).closest('tr').addClass('table-active');
    }).on('mouseleave', function() {
        $(this).closest('tr').removeClass('table-active');
    });
});
</script>
@stop
