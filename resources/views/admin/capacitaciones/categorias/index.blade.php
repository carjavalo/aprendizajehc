@extends('admin.layouts.master')

@section('title', 'Gestión de Categorías')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-tags"></i> Gestión de Categorías</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Configuración</li>
                <li class="breadcrumb-item">Capacitaciones</li>
                <li class="breadcrumb-item active">Categorías</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Búsqueda -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search"></i> Búsqueda</h3>
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
                               placeholder="Escriba para buscar categorías en tiempo real...">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> La búsqueda se realiza automáticamente mientras escribe
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Categorías -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Lista de Categorías</h3>
            <div class="card-tools">
                @can('categorias.create')
                <button type="button" class="btn btn-success" id="btn-nueva-categoria">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="categorias-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Categoría -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" role="dialog" aria-labelledby="categoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="categoriaModalLabel">Nueva Categoría</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="categoriaForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="categoria_id" name="categoria_id">
                        <div class="form-group">
                            <label for="modal_descripcion">Descripción <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_descripcion" name="descripcion" 
                                   placeholder="Ingrese la descripción de la categoría" maxlength="100" required>
                            <small class="form-text text-muted">Máximo 100 caracteres</small>
                            <div class="invalid-feedback" id="descripcion-error"></div>
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

    <!-- Modal para Ver Categoría -->
    <div class="modal fade" id="viewCategoriaModal" tabindex="-1" role="dialog" aria-labelledby="viewCategoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewCategoriaModalLabel">
                        <i class="fas fa-eye"></i> Detalles de la Categoría
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
                                                <label class="text-muted"><i class="fas fa-tag"></i> <strong>Descripción:</strong></label>
                                                <p class="form-control-static text-dark" id="view_descripcion">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-calendar-plus"></i> <strong>Fecha de Creación:</strong></label>
                                                <p class="form-control-static text-success" id="view_created_at">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted"><i class="fas fa-calendar-edit"></i> <strong>Última Actualización:</strong></label>
                                                <p class="form-control-static text-info" id="view_updated_at">-</p>
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
            // Verificar que estamos en la página correcta antes de inicializar DataTable
            if ($('#categorias-table').length === 0) {
                return; // Salir si no existe la tabla de categorías
            }

            // Variable para el timeout del debounce
            var searchTimeout;

            // Inicializar DataTable
            var table = $('#categorias-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("capacitaciones.categorias.data") }}',
                    data: function(d) {
                        d.descripcion = $('#descripcion').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'descripcion', name: 'descripcion' },
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
                var searchValue = $(this).val();

                // Limpiar timeout anterior
                clearTimeout(searchTimeout);

                // Establecer nuevo timeout para evitar demasiadas peticiones
                searchTimeout = setTimeout(function() {
                    table.draw();
                }, 500); // 500ms de delay
            });

            // Nueva categoría
            $('#btn-nueva-categoria').click(function() {
                $('#categoriaForm')[0].reset();
                $('#categoria_id').val('');
                $('#categoriaModalLabel').text('Nueva Categoría');
                $('#btn-guardar').html('<i class="fas fa-save"></i> Guardar');
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#categoriaModal').modal('show');
            });

            // Guardar categoría
            $('#categoriaForm').submit(function(e) {
                e.preventDefault();
                
                var categoriaId = $('#categoria_id').val();
                var url = categoriaId ? 
                    '{{ route("capacitaciones.categorias.update", ":id") }}'.replace(':id', categoriaId) : 
                    '{{ route("capacitaciones.categorias.store") }}';
                var method = categoriaId ? 'PUT' : 'POST';
                
                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#categoriaModal').modal('hide');
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
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                $('#modal_' + field).addClass('is-invalid');
                                $('#' + field + '-error').text(messages[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al guardar la categoría'
                            });
                        }
                    }
                });
            });

            // Funciones globales para botones de acciones
            window.viewCategoria = function(id) {
                $.get('{{ route("capacitaciones.categorias.show", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#view_id').text(data.id);
                        $('#view_descripcion').text(data.descripcion);
                        $('#view_created_at').text(data.created_at);
                        $('#view_updated_at').text(data.updated_at);
                        $('#viewCategoriaModal').modal('show');
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos de la categoría', 'error');
                    });
            };

            window.editCategoria = function(id) {
                $.get('{{ route("capacitaciones.categorias.edit", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#categoria_id').val(data.id);
                        $('#modal_descripcion').val(data.descripcion);
                        $('#categoriaModalLabel').text('Editar Categoría');
                        $('#btn-guardar').html('<i class="fas fa-save"></i> Actualizar');
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').text('');
                        $('#categoriaModal').modal('show');
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos de la categoría', 'error');
                    });
            };

            window.deleteCategoria = function(id) {
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
                            url: '{{ route("capacitaciones.categorias.destroy", ":id") }}'.replace(':id', id),
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
                                Swal.fire('Error', 'No se pudo eliminar la categoría', 'error');
                            }
                        });
                    }
                });
            };
        });
    </script>
@stop
