@extends('admin.layouts.master')

@section('title', 'Gestión de Áreas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-layer-group"></i> Gestión de Áreas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Configuración</li>
                <li class="breadcrumb-item">Capacitaciones</li>
                <li class="breadcrumb-item active">Áreas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
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
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label for="descripcion">Buscar por descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               placeholder="Escriba para buscar áreas en tiempo real...">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> La búsqueda se realiza automáticamente mientras escribe
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label for="categoria">Filtrar por categoría</label>
                        <select class="form-control" id="categoria" name="categoria">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Seleccione una categoría para filtrar
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Áreas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Lista de Áreas</h3>
            <div class="card-tools">
                @can('areas.create')
                <button type="button" class="btn btn-success" id="btn-nueva-area">
                    <i class="fas fa-plus"></i> Nueva Área
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="areas-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Área -->
    <div class="modal fade" id="areaModal" tabindex="-1" role="dialog" aria-labelledby="areaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="areaModalLabel">Nueva Área</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="areaForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="area_id" name="area_id">
                        <div class="form-group">
                            <label for="modal_descripcion">Descripción <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_descripcion" name="descripcion" 
                                   placeholder="Ingrese la descripción del área" maxlength="100" required>
                            <small class="form-text text-muted">Máximo 100 caracteres</small>
                            <div class="invalid-feedback" id="descripcion-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="modal_categoria">Categoría <span class="text-danger">*</span></label>
                            <select class="form-control" id="modal_categoria" name="cod_categoria" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->descripcion }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="cod_categoria-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Área -->
    <div class="modal fade" id="viewAreaModal" tabindex="-1" role="dialog" aria-labelledby="viewAreaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewAreaModalLabel">
                        <i class="fas fa-eye"></i> Detalles del Área
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-hashtag"></i> <strong>ID:</strong></label>
                                                <p class="form-control-static text-primary font-weight-bold" id="view_id">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-layer-group"></i> <strong>Descripción:</strong></label>
                                                <p class="form-control-static text-dark" id="view_descripcion">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-tags"></i> <strong>Categoría:</strong></label>
                                                <p class="form-control-static text-info" id="view_categoria">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-calendar-plus"></i> <strong>Fecha de Creación:</strong></label>
                                                <p class="form-control-static text-success" id="view_created_at">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-calendar-edit"></i> <strong>Última Actualización:</strong></label>
                                                <p class="form-control-static text-warning" id="view_updated_at">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
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
            // Configurar CSRF token para todas las peticiones AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Verificar que estamos en la página correcta antes de inicializar DataTable
            if ($('#areas-table').length === 0) {
                return; // Salir si no existe la tabla de áreas
            }

            // Variables para el timeout del debounce
            var searchTimeout;
            var categoryTimeout;

            // Inicializar DataTable
            var table = $('#areas-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("capacitaciones.areas.data") }}',
                    data: function(d) {
                        d.descripcion = $('#descripcion').val();
                        d.categoria = $('#categoria').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'descripcion', name: 'descripcion' },
                    { data: 'categoria_descripcion', name: 'categoria.descripcion' },
                    { data: 'fecha_creacion', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    url: '{{ asset('js/datatables-spanish.json') }}'
                }
            });

            // Búsqueda automática en tiempo real con debounce
            $('#descripcion').on('input keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    table.draw();
                }, 500);
            });

            // Filtro por categoría
            $('#categoria').on('change', function() {
                clearTimeout(categoryTimeout);
                categoryTimeout = setTimeout(function() {
                    table.draw();
                }, 300);
            });

            // Nueva área
            $('#btn-nueva-area').click(function() {
                $('#areaForm')[0].reset();
                $('#area_id').val('');
                $('#areaModalLabel').text('Nueva Área');
                $('#btn-guardar').html('<i class="fas fa-save"></i> Guardar');
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#areaModal').modal('show');
            });

            // Guardar área
            $('#areaForm').submit(function(e) {
                e.preventDefault();

                var areaId = $('#area_id').val();
                var url = areaId ?
                    '{{ route("capacitaciones.areas.update", ":id") }}'.replace(':id', areaId) :
                    '{{ route("capacitaciones.areas.store") }}';
                var method = areaId ? 'PUT' : 'POST';

                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Preparar datos del formulario
                var formData = $(this).serialize();

                // Para métodos PUT, agregar _method
                if (method === 'PUT') {
                    formData += '&_method=PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST', // Siempre usar POST para compatibilidad
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#areaModal').modal('hide');
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log('Error completo:', xhr);

                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                $('#modal_' + field).addClass('is-invalid');
                                $('#' + field + '-error').text(messages[0]);
                            });
                        } else {
                            var errorMessage = 'Ocurrió un error al guardar el área';
                            var debugInfo = '';

                            // Intentar obtener mensaje de error del servidor
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            // Agregar información de debug si está disponible
                            if (xhr.responseJSON && xhr.responseJSON.debug_info) {
                                debugInfo = '\n\nInformación técnica:\n' +
                                           'Archivo: ' + xhr.responseJSON.debug_info.file + '\n' +
                                           'Línea: ' + xhr.responseJSON.debug_info.line;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage + debugInfo,
                                footer: 'Status: ' + xhr.status + ' - ' + xhr.statusText
                            });
                        }
                    }
                });
            });

            // Funciones globales para botones de acciones
            window.viewArea = function(id) {
                $.get('{{ route("capacitaciones.areas.show", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#view_id').text(data.id);
                        $('#view_descripcion').text(data.descripcion);
                        $('#view_categoria').text(data.categoria);
                        $('#view_created_at').text(data.created_at);
                        $('#view_updated_at').text(data.updated_at);
                        $('#viewAreaModal').modal('show');
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos del área', 'error');
                    });
            };

            window.editArea = function(id) {
                $.get('{{ route("capacitaciones.areas.edit", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#area_id').val(data.id);
                        $('#modal_descripcion').val(data.descripcion);
                        $('#modal_categoria').val(data.cod_categoria);
                        $('#areaModalLabel').text('Editar Área');
                        $('#btn-guardar').html('<i class="fas fa-save"></i> Actualizar');
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').text('');
                        $('#areaModal').modal('show');
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos del área', 'error');
                    });
            };

            window.deleteArea = function(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("capacitaciones.areas.destroy", ":id") }}'.replace(':id', id),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.draw();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el área', 'error');
                            }
                        });
                    }
                });
            };
        });
    </script>
@stop
