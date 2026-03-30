@extends('admin.layouts.master')

@section('title', 'Seguimiento de Operaciones')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-tasks"></i> Seguimiento de Operaciones</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Seguimiento</li>
                <li class="breadcrumb-item active">Operación</li>
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
                    <h3 id="total-operations">-</h3>
                    <p>Total Operaciones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="today-operations">-</h3>
                    <p>Operaciones Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="week-operations">-</h3>
                    <p>Operaciones Esta Semana</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="month-operations">-</h3>
                    <p>Operaciones Este Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
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
                            <label for="operation_type">Tipo de Operación</label>
                            <select class="form-control" id="operation_type" name="operation_type">
                                <option value="">Todos</option>
                                <option value="create">Crear</option>
                                <option value="update">Editar</option>
                                <option value="delete">Eliminar</option>
                                <option value="view">Ver</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="enroll">Inscribir</option>
                                <option value="submit">Entregar</option>
                                <option value="grade">Calificar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_type">Tipo de Entidad</label>
                            <select class="form-control" id="entity_type" name="entity_type">
                                <option value="">Todos</option>
                                <option value="Curso">Curso</option>
                                <option value="Actividad">Actividad</option>
                                <option value="Entrega">Entrega</option>
                                <option value="Perfil">Perfil</option>
                                <option value="Usuario">Usuario</option>
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
                            <label for="search_user">Usuario</label>
                            <input type="text" class="form-control" id="search_user" name="search_user" placeholder="Buscar usuario">
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

    <!-- Tabla de Operaciones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Registro de Operaciones</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="operations-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Operación</th>
                            <th>Entidad</th>
                            <th>Descripción</th>
                            <th>IP</th>
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
            // Verificar que estamos en la página correcta
            if ($('#operations-table').length === 0) {
                return; // Salir si no existe la tabla
            }

            // Cargar estadísticas
            loadStats();

            // Inicializar DataTable
            var table = $('#operations-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("tracking.operations.data") }}',
                    data: function(d) {
                        d.operation_type = $('#operation_type').val();
                        d.entity_type = $('#entity_type').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.search_user = $('#search_user').val();
                    }
                },
                columns: [
                    { data: 'formatted_date', name: 'user_operations.created_at' },
                    { data: 'user_name', name: 'user_name', orderable: false },
                    { data: 'operation_badge', name: 'operation_type', orderable: false },
                    { data: 'entity_badge', name: 'entity_type', orderable: false },
                    { data: 'description', name: 'description' },
                    { data: 'ip_address', name: 'ip_address' },
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

            // Función global para ver detalles
            window.showDetails = function(id) {
                $.get('{{ route("tracking.operations.show", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        showDetailsModal(data);
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                    });
            };
        });

        function loadStats() {
            $.get('{{ route("tracking.operations.stats") }}')
                .done(function(data) {
                    $('#total-operations').text(data.total_operations);
                    $('#today-operations').text(data.today_operations);
                    $('#week-operations').text(data.week_operations);
                    $('#month-operations').text(data.month_operations);
                })
                .fail(function() {
                    console.error('Error al cargar estadísticas');
                });
        }

        function showDetailsModal(data) {
            var detailsHTML = '';
            if (data.details && Object.keys(data.details).length > 0) {
                detailsHTML = '<div class="row mt-3"><div class="col-12"><h5>Detalles Adicionales</h5><pre>' + 
                    JSON.stringify(data.details, null, 2) + '</pre></div></div>';
            }

            var modalContent = `
                <div class="modal fade" id="detailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Detalles de la Operación</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Tipo de Operación:</strong> ${data.operation_type}<br>
                                        <strong>Entidad:</strong> ${data.entity_type}<br>
                                        <strong>ID Entidad:</strong> ${data.entity_id || 'N/A'}<br>
                                        <strong>IP:</strong> ${data.ip_address || 'N/A'}<br>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Fecha:</strong> ${data.created_at}<br>
                                        ${data.user ? '<strong>Usuario:</strong> ' + data.user.name + '<br>' : ''}
                                        ${data.user ? '<strong>Email:</strong> ' + data.user.email + '<br>' : ''}
                                        ${data.user ? '<strong>Rol:</strong> ' + data.user.role : ''}
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <strong>Descripción:</strong><br>
                                        <p>${data.description}</p>
                                    </div>
                                </div>
                                ${detailsHTML}
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <strong>User Agent:</strong><br>
                                        <small class="text-muted">${data.user_agent || 'N/A'}</small>
                                    </div>
                                </div>
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
