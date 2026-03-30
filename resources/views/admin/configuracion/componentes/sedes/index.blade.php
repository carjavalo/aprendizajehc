@extends('admin.layouts.master')

@section('title', 'Gestión de Sedes')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-map-marker-alt"></i> Gestión de Sedes</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Configuración</li>
                <li class="breadcrumb-item">Gestión de Componentes</li>
                <li class="breadcrumb-item active">Sedes</li>
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
                        <label for="nombre">Buscar por nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               placeholder="Escriba para buscar en tiempo real...">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> La búsqueda se realiza automáticamente mientras escribe
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Lista de Sedes</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success" id="btn-nuevo">
                    <i class="fas fa-plus"></i> Nueva Sede
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="data-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="itemModalLabel">Nueva Sede</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="itemForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="item_id" name="item_id">
                        <div class="form-group">
                            <label for="modal_nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_nombre" name="nombre" 
                                   placeholder="Ingrese el nombre de la sede" maxlength="100" required>
                            <small class="form-text text-muted">Máximo 100 caracteres</small>
                            <div class="invalid-feedback" id="nombre-error"></div>
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

    <!-- Modal Ver -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewModalLabel">
                        <i class="fas fa-eye"></i> Detalles de la Sede
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                                        <label class="text-muted"><i class="fas fa-map-marker-alt"></i> <strong>Nombre:</strong></label>
                                        <p class="form-control-static text-dark" id="view_nombre">-</p>
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
                                        <p class="form-control-static text-warning" id="view_updated_at">-</p>
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
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            var searchTimeout;
            var table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("configuracion.componentes.sedes.data") }}',
                    data: function(d) {
                        d.nombre = $('#nombre').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'nombre', name: 'nombre' },
                    { data: 'fecha_creacion', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: { url: '{{ asset('js/datatables-spanish.json') }}' }
            });

            $('#nombre').on('input keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { table.draw(); }, 500);
            });

            $('#btn-nuevo').click(function() {
                $('#itemForm')[0].reset();
                $('#item_id').val('');
                $('#itemModalLabel').text('Nueva Sede');
                $('#btn-guardar').html('<i class="fas fa-save"></i> Guardar');
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#itemModal').modal('show');
            });

            $('#itemForm').submit(function(e) {
                e.preventDefault();
                var itemId = $('#item_id').val();
                var url = itemId ?
                    '{{ route("configuracion.componentes.sedes.update", ":id") }}'.replace(':id', itemId) :
                    '{{ route("configuracion.componentes.sedes.store") }}';
                var formData = $(this).serialize();
                if (itemId) formData += '&_method=PUT';

                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#itemModal').modal('hide');
                            table.draw();
                            Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 3000, showConfirmButton: false });
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
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al guardar' });
                        }
                    }
                });
            });

            window.viewItem = function(id) {
                $.get('{{ route("configuracion.componentes.sedes.show", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#view_id').text(data.id);
                        $('#view_nombre').text(data.nombre);
                        $('#view_created_at').text(data.created_at);
                        $('#view_updated_at').text(data.updated_at);
                        $('#viewModal').modal('show');
                    })
                    .fail(function() { Swal.fire('Error', 'No se pudieron cargar los datos', 'error'); });
            };

            window.editItem = function(id) {
                $.get('{{ route("configuracion.componentes.sedes.edit", ":id") }}'.replace(':id', id))
                    .done(function(data) {
                        $('#item_id').val(data.id);
                        $('#modal_nombre').val(data.nombre);
                        $('#itemModalLabel').text('Editar Sede');
                        $('#btn-guardar').html('<i class="fas fa-save"></i> Actualizar');
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').text('');
                        $('#itemModal').modal('show');
                    })
                    .fail(function() { Swal.fire('Error', 'No se pudieron cargar los datos', 'error'); });
            };

            window.deleteItem = function(id) {
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
                            url: '{{ route("configuracion.componentes.sedes.destroy", ":id") }}'.replace(':id', id),
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                if (response.success) {
                                    table.draw();
                                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: response.message, timer: 3000, showConfirmButton: false });
                                }
                            },
                            error: function() { Swal.fire('Error', 'No se pudo eliminar', 'error'); }
                        });
                    }
                });
            };
        });
    </script>
@stop
