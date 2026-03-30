@extends('admin.layouts.master')

@section('title', 'Seguimiento de Ingresos')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-sign-in-alt"></i> Seguimiento de Ingresos</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Seguimiento</li>
                <li class="breadcrumb-item active">Ingresos</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-logins">-</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="successful-logins">-</h3>
                    <p>Ingresos Exitosos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="failed-logins">-</h3>
                    <p>Ingresos Fallidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="unverified-users">-</h3>
                    <p>Usuarios Sin Verificar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Estado del Ingreso</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="success">Exitoso</option>
                                <option value="failed">Fallido</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email_verified">Estado de Verificación</label>
                            <select class="form-control" id="email_verified" name="email_verified">
                                <option value="">Todos</option>
                                <option value="verified">Verificado</option>
                                <option value="unverified">Pendiente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Fecha Desde</label>
                            <input type="date" class="form-control" id="date_from" name="date_from">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Fecha Hasta</label>
                            <input type="date" class="form-control" id="date_to" name="date_to">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Buscar por email">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" id="apply-filters">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <button type="button" class="btn btn-secondary" id="clear-filters">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Ingresos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Registro de Ingresos</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="logins-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>IP</th>
                            <th>Estado</th>
                            <th>Verificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('extra_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Verificar que estamos en la página correcta antes de inicializar DataTable
            if ($('#logins-table').length === 0) {
                return; // Salir si no existe la tabla de logins
            }

            // Cargar estadísticas
            loadStats();

            // Inicializar DataTable
            var table = $('#logins-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("tracking.logins.data") }}',
                    data: function(d) {
                        d.status = $('#status').val();
                        d.email_verified = $('#email_verified').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.email = $('#email').val();
                    }
                },
                columns: [
                    { data: 'formatted_date', name: 'attempted_at' },
                    { data: 'user_name', name: 'user_name', orderable: false },
                    { data: 'email', name: 'user_logins.email' },
                    { data: 'ip_address', name: 'ip_address' },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'email_verified_badge', name: 'email_verified', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    url: '{{ asset('js/datatables-spanish.json') }}'
                }
            });

            // Aplicar filtros
            $('#apply-filters').click(function() {
                table.draw();
            });

            // Limpiar filtros
            $('#clear-filters').click(function() {
                $('#filter-form')[0].reset();
                table.draw();
            });

            // Funciones globales para botones de acciones
            window.showDetails = function(id) {
                $.get('{{ route("tracking.logins.show", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        showDetailsModal(data);
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                    });
            };

            window.resendVerification = function(userId) {
                Swal.fire({
                    title: '¿Reenviar verificación?',
                    text: 'Se enviará un nuevo email de verificación al usuario',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reenviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ route("tracking.logins.resend-verification", ":id") }}'.replace(':id', userId))
                            .done(function(response) {
                                if (response.success) {
                                    Swal.fire('Éxito', response.message, 'success');
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            })
                            .fail(function() {
                                Swal.fire('Error', 'Error al reenviar la verificación', 'error');
                            });
                    }
                });
            };
        });

        function loadStats() {
            $.get('{{ route("tracking.stats") }}')
                .done(function(data) {
                    $('#total-logins').text(data.total_logins);
                    $('#successful-logins').text(data.successful_logins);
                    $('#failed-logins').text(data.failed_logins);
                    $('#unverified-users').text(data.unverified_users);
                })
                .fail(function() {
                    console.error('Error al cargar estadísticas');
                });
        }

        function showDetailsModal(data) {
            var modalContent = `
                <div class="modal fade" id="detailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Detalles del Intento de Ingreso</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Email:</strong> ${data.email}<br>
                                        <strong>IP:</strong> ${data.ip_address}<br>
                                        <strong>Estado:</strong> ${data.status}<br>
                                        <strong>Verificación:</strong> ${data.email_verified}<br>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Fecha:</strong> ${data.attempted_at}<br>
                                        <strong>Registrado:</strong> ${data.created_at}<br>
                                        ${data.failure_reason ? '<strong>Razón del fallo:</strong> ' + data.failure_reason : ''}
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <strong>User Agent:</strong><br>
                                        <small class="text-muted">${data.user_agent || 'N/A'}</small>
                                    </div>
                                </div>
                                ${data.user ? `
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Información del Usuario</h5>
                                        <strong>Nombre:</strong> ${data.user.name}<br>
                                        <strong>Email:</strong> ${data.user.email}<br>
                                        <strong>Rol:</strong> ${data.user.role}
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal anterior si existe
            $('#detailsModal').remove();
            
            // Agregar y mostrar nuevo modal
            $('body').append(modalContent);
            $('#detailsModal').modal('show');
        }
    </script>
@stop
