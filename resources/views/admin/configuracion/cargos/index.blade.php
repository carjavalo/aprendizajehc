@extends('admin.layouts.master')

@section('title', 'Gestión Cargo/Especialidad')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="corp-title">
                <i class="fas fa-briefcase-medical corp-icon-header"></i>
                Gestión Cargo/Especialidad
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Configuración</li>
                <li class="breadcrumb-item active">Cargo/Especialidad</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

    <!-- Tarjeta de estadísticas -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box shadow-sm cargo-stat-box">
                <span class="info-box-icon corp-icon-bg">
                    <i class="fas fa-briefcase-medical"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Registros</span>
                    <span class="info-box-number" id="stat-total">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card cargo-card shadow-sm">
        <div class="card-header cargo-card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-search-plus mr-2"></i> Filtros de Búsqueda
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <div class="form-group mb-0">
                        <label for="filtro_nombre" class="corp-label">
                            <i class="fas fa-font mr-1"></i> Buscar por Nombre
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text corp-input-icon">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="filtro_nombre"
                                   placeholder="Escriba para buscar en tiempo real...">
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> La búsqueda se realiza automáticamente mientras escribe
                        </small>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <button type="button" class="btn corp-btn-outline" id="btn-limpiar">
                        <i class="fas fa-eraser mr-1"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de datos -->
    <div class="card cargo-card shadow-sm">
        <div class="card-header cargo-card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-table mr-2"></i> Lista de Cargo/Especialidad
            </h3>
            <div class="card-tools">
                @can('cargos.create')
                <button type="button" class="btn corp-btn-new" id="btn-nuevo">
                    <i class="fas fa-plus-circle mr-1"></i> Nuevo Cargo/Especialidad
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="cargo-table" class="table table-hover mb-0">
                    <thead class="cargo-thead">
                        <tr>
                            <th class="text-center" style="width:70px;">#</th>
                            <th><i class="fas fa-id-badge mr-1"></i> Nombre</th>
                            <th class="text-center"><i class="fas fa-calendar-alt mr-1"></i> Fecha Creación</th>
                            <th class="text-center" style="width:130px;">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL CREAR / EDITAR ===================== -->
    <div class="modal fade" id="cargoModal" tabindex="-1" role="dialog" aria-labelledby="cargoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content cargo-modal-content">
                <div class="modal-header cargo-modal-header">
                    <h4 class="modal-title text-white" id="cargoModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Nuevo Cargo/Especialidad
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cargoForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="cargo_id" name="cargo_id">
                        <div class="form-group">
                            <label for="modal_nombre" class="corp-label">
                                <i class="fas fa-briefcase-medical mr-1"></i>
                                Nombre del Cargo/Especialidad <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control cargo-input" id="modal_nombre" name="nombre"
                                   placeholder="Ej: Médico General, Enfermería, Administración..." maxlength="100" required
                                   autocomplete="off">
                            <div class="d-flex justify-content-between mt-1">
                                <small class="form-text text-muted">Máximo 100 caracteres</small>
                                <small class="text-muted char-counter"><span id="char-count">0</span>/100</small>
                            </div>
                            <div class="invalid-feedback" id="nombre-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer cargo-modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn corp-btn-save" id="btn-guardar">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL VER DETALLES ===================== -->
    <div class="modal fade" id="viewCargoModal" tabindex="-1" role="dialog" aria-labelledby="viewCargoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content cargo-modal-content">
                <div class="modal-header cargo-modal-header">
                    <h4 class="modal-title text-white" id="viewCargoModalLabel">
                        <i class="fas fa-eye mr-2"></i> Detalles del Cargo/Especialidad
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="cargo-view-card">
                        <div class="cargo-view-row">
                            <div class="cargo-view-label"><i class="fas fa-hashtag mr-1"></i> ID</div>
                            <div class="cargo-view-value text-primary font-weight-bold" id="view_id">--</div>
                        </div>
                        <div class="cargo-view-row">
                            <div class="cargo-view-label"><i class="fas fa-briefcase-medical mr-1"></i> Nombre</div>
                            <div class="cargo-view-value font-weight-bold" id="view_nombre">--</div>
                        </div>
                        <div class="cargo-view-row">
                            <div class="cargo-view-label"><i class="fas fa-calendar-plus mr-1"></i> Fecha Creación</div>
                            <div class="cargo-view-value text-success" id="view_created_at">--</div>
                        </div>
                        <div class="cargo-view-row">
                            <div class="cargo-view-label"><i class="fas fa-calendar-check mr-1"></i> Última Actualización</div>
                            <div class="cargo-view-value text-warning" id="view_updated_at">--</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer cargo-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                    @can('cargos.edit')
                    <button type="button" class="btn btn-warning" id="btn-view-to-edit">
                        <i class="fas fa-pencil-alt mr-1"></i> Editar
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

@stop

@section('extra_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.css">
    <style>
        /* ── Colores corporativos ── */
        :root {
            --corp-primary:       #2c4370;
            --corp-primary-dark:  #1e2f4d;
            --corp-primary-light: #3d5a8a;
            --corp-accent:        #e8edf5;
        }

        /* Header de página */
        .corp-title { color: var(--corp-primary); font-weight: 700; }
        .corp-icon-header { color: var(--corp-primary); }

        /* Stat box */
        .cargo-stat-box { border-radius: 10px; overflow: hidden; }
        .corp-icon-bg {
            background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%);
            color: #fff;
        }

        /* Cards */
        .cargo-card { border: none; border-radius: 10px; overflow: hidden; }
        .cargo-card-header {
            background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%);
            color: #fff;
            border-bottom: none;
        }
        .cargo-card-header .card-title { color: #fff; font-weight: 600; }
        .cargo-card-header .btn-tool { color: rgba(255,255,255,0.8); }
        .cargo-card-header .btn-tool:hover { color: #fff; }

        /* Labels */
        .corp-label { color: var(--corp-primary); font-weight: 600; font-size: 0.9rem; }

        /* Input prepend icon */
        .corp-input-icon {
            background: var(--corp-primary);
            color: #fff;
            border-color: var(--corp-primary);
        }
        .cargo-input:focus {
            border-color: var(--corp-primary-light);
            box-shadow: 0 0 0 0.2rem rgba(44, 67, 112, 0.25);
        }

        /* Botones */
        .corp-btn-new {
            background: linear-gradient(135deg, #fff 0%, var(--corp-accent) 100%);
            color: var(--corp-primary);
            border: 2px solid rgba(255,255,255,0.7);
            font-weight: 600;
            border-radius: 20px;
            padding: 6px 18px;
            transition: all .25s;
        }
        .corp-btn-new:hover {
            background: #fff;
            color: var(--corp-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .corp-btn-outline {
            background: transparent;
            color: var(--corp-primary);
            border: 2px solid var(--corp-primary);
            border-radius: 20px;
            font-weight: 600;
            padding: 6px 18px;
            transition: all .25s;
        }
        .corp-btn-outline:hover {
            background: var(--corp-primary);
            color: #fff;
        }
        .corp-btn-save {
            background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all .25s;
        }
        .corp-btn-save:hover {
            background: linear-gradient(135deg, var(--corp-primary-light) 0%, var(--corp-primary) 100%);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Tabla */
        .cargo-thead th {
            background: var(--corp-accent) !important;
            color: var(--corp-primary) !important;
            font-weight: 700;
            border-top: 3px solid var(--corp-primary) !important;
            border-bottom: 2px solid var(--corp-primary-light) !important;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }
        #cargo-table tbody tr {
            transition: background .15s;
        }
        #cargo-table tbody tr:hover {
            background: var(--corp-accent) !important;
        }
        #cargo-table tbody tr td {
            vertical-align: middle;
        }

        /* Modales */
        .cargo-modal-content { border: none; border-radius: 12px; overflow: hidden; }
        .cargo-modal-header {
            background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%);
            border-bottom: none;
        }
        .cargo-modal-footer {
            background: var(--corp-accent);
            border-top: 1px solid #dee2e6;
        }

        /* Vista detalle */
        .cargo-view-card { border-radius: 8px; overflow: hidden; border: 1px solid #e0e7f0; }
        .cargo-view-row {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f4f9;
        }
        .cargo-view-row:last-child { border-bottom: none; }
        .cargo-view-row:nth-child(even) { background: var(--corp-accent); }
        .cargo-view-label {
            width: 45%;
            color: var(--corp-primary);
            font-weight: 600;
            font-size: 0.88rem;
        }
        .cargo-view-value { width: 55%; font-size: 0.92rem; }

        /* Contador de caracteres */
        .char-counter { font-size: 0.78rem; }

        /* Badges de estado en tabla */
        .badge-id {
            background: var(--corp-primary);
            color: #fff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* DataTables override */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%) !important;
            color: #fff !important;
            border: none !important;
            border-radius: 6px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--corp-accent) !important;
            color: var(--corp-primary) !important;
            border: 1px solid var(--corp-primary-light) !important;
            border-radius: 6px;
        }
        .dataTables_wrapper .dataTables_info { color: var(--corp-primary); font-weight: 600; }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--corp-primary-light);
            border-radius: 6px;
        }
        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            box-shadow: 0 0 0 0.15rem rgba(44,67,112,.2);
            border-color: var(--corp-primary);
        }

        /* Loading overlay */
        .cargo-loading {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .cargo-loading.active { display: flex; }
        .cargo-spinner {
            width: 60px; height: 60px;
            border: 6px solid var(--corp-accent);
            border-top-color: var(--corp-primary);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

    <!-- Loading overlay -->
    <div class="cargo-loading" id="loadingOverlay">
        <div class="cargo-spinner"></div>
    </div>

    <script>
    $(document).ready(function () {

        /* ── CSRF ── */
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        /* ── DataTable ── */
        var searchTimeout;
        var currentViewId = null;

        var table = $('#cargo-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("configuracion.cargos.data") }}',
                data: function (d) {
                    d.nombre = $('#filtro_nombre').val();
                },
                complete: function (json) {
                    // Actualizar contador
                    if (json.responseJSON && json.responseJSON.recordsTotal !== undefined) {
                        $('#stat-total').text(json.responseJSON.recordsTotal);
                    }
                }
            },
            columns: [
                {
                    data: 'id', name: 'id', className: 'text-center',
                    render: function (data) {
                        return '<span class="badge-id">' + data + '</span>';
                    }
                },
                { data: 'nombre', name: 'nombre' },
                { data: 'fecha_creacion', name: 'created_at', className: 'text-center' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '{{ asset('js/datatables-spanish.json') }}' },
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        /* ── Filtro en tiempo real ── */
        $('#filtro_nombre').on('input keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () { table.draw(); }, 450);
        });

        /* ── Limpiar filtros ── */
        $('#btn-limpiar').on('click', function () {
            $('#filtro_nombre').val('');
            table.draw();
        });

        /* ── Contador de caracteres ── */
        $('#modal_nombre').on('input', function () {
            $('#char-count').text($(this).val().length);
        });

        /* ── Abrir modal nuevo ── */
        $('#btn-nuevo').on('click', function () {
            resetForm();
            $('#cargoModalLabel').html('<i class="fas fa-plus-circle mr-2"></i> Nuevo Cargo/Especialidad');
            $('#btn-guardar').html('<i class="fas fa-save mr-1"></i> Guardar');
            $('#cargoModal').modal('show');
        });

        /* ── Guardar / Actualizar ── */
        $('#cargoForm').on('submit', function (e) {
            e.preventDefault();
            var id  = $('#cargo_id').val();
            var url = id
                ? '{{ route("configuracion.cargos.update", ":id") }}'.replace(':id', id)
                : '{{ route("configuracion.cargos.store") }}';
            var data = $(this).serialize();
            if (id) data += '&_method=PUT';

            clearErrors();
            showLoading(true);

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function (res) {
                    showLoading(false);
                    if (res.success) {
                        $('#cargoModal').modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: res.message,
                            timer: 3000,
                            showConfirmButton: false,
                            background: '#f8faff',
                            iconColor: '#2c4370'
                        });
                    }
                },
                error: function (xhr) {
                    showLoading(false);
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, msgs) {
                            $('#modal_' + field).addClass('is-invalid');
                            $('#' + field + '-error').text(msgs[0]);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al guardar.' });
                    }
                }
            });
        });

        /* ── Ver detalle ── */
        window.viewItem = function (id) {
            currentViewId = id;
            showLoading(true);
            $.get('{{ route("configuracion.cargos.show", ":id") }}'.replace(':id', id))
                .done(function (data) {
                    showLoading(false);
                    $('#view_id').text(data.id);
                    $('#view_nombre').text(data.nombre);
                    $('#view_created_at').text(data.created_at);
                    $('#view_updated_at').text(data.updated_at);
                    $('#viewCargoModal').modal('show');
                })
                .fail(function () {
                    showLoading(false);
                    Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
                });
        };

        /* ── Editar desde modal ver ── */
        $('#btn-view-to-edit').on('click', function () {
            $('#viewCargoModal').modal('hide');
            setTimeout(function () { editItem(currentViewId); }, 350);
        });

        /* ── Editar ── */
        window.editItem = function (id) {
            showLoading(true);
            $.get('{{ route("configuracion.cargos.edit", ":id") }}'.replace(':id', id))
                .done(function (data) {
                    showLoading(false);
                    resetForm();
                    $('#cargo_id').val(data.id);
                    $('#modal_nombre').val(data.nombre);
                    $('#char-count').text(data.nombre.length);
                    $('#cargoModalLabel').html('<i class="fas fa-pencil-alt mr-2"></i> Editar Cargo/Especialidad');
                    $('#btn-guardar').html('<i class="fas fa-save mr-1"></i> Actualizar');
                    $('#cargoModal').modal('show');
                })
                .fail(function () {
                    showLoading(false);
                    Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
                });
        };

        /* ── Eliminar ── */
        window.deleteItem = function (id) {
            Swal.fire({
                title: '¿Eliminar Cargo/Especialidad?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2c4370',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Cancelar',
                background: '#f8faff'
            }).then(function (result) {
                if (result.isConfirmed) {
                    showLoading(true);
                    $.ajax({
                        url: '{{ route("configuracion.cargos.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            showLoading(false);
                            if (res.success) {
                                table.draw();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: res.message,
                                    timer: 3000,
                                    showConfirmButton: false,
                                    background: '#f8faff',
                                    iconColor: '#2c4370'
                                });
                            }
                        },
                        error: function () {
                            showLoading(false);
                            Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                        }
                    });
                }
            });
        };

        /* ── Helpers ── */
        function resetForm() {
            $('#cargoForm')[0].reset();
            $('#cargo_id').val('');
            $('#char-count').text('0');
            clearErrors();
        }

        function clearErrors() {
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function showLoading(show) {
            if (show) {
                $('#loadingOverlay').addClass('active');
            } else {
                $('#loadingOverlay').removeClass('active');
            }
        }
    });
    </script>
@stop
