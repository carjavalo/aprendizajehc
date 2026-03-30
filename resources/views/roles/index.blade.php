@extends('admin.layouts.master')

@section('title', 'Gestión de Roles')

@section('content_header')
    <h1>Roles</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">Listado de roles del sistema</h3>
                <button type="button" class="btn btn-primary" id="createNewRole">
                    <i class="fas fa-plus"></i> Nuevo Rol
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-striped table-hover table-bordered responsive nowrap" width="100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>N°</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th width="150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar -->
    <div class="modal fade" id="ajaxModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="modelHeading"></h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="roleForm" name="roleForm" class="form-horizontal">
                        <input type="hidden" name="id" id="role_id">
                        <div class="form-group mb-3">
                            <label for="name" class="col-sm-12 control-label">Nombre del Rol</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Ej. Moderador" value="" maxlength="50" required="">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="col-sm-12 control-label">Descripción</label>
                            <div class="col-sm-12">
                                <textarea id="description" name="description" required placeholder="Descripción del rol" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-10 mt-4">
                            <button type="submit" class="btn btn-primary" id="saveBtn" value="create">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .dt-buttons { white-space: nowrap; }
        .dt-buttons .btn { margin-right: 6px; }
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

        var table = $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('roles.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'description', name: 'description'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                url: '{{ asset('js/datatables-spanish.json') }}'
            },
            responsive: true,
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-3"p><"col-sm-12 col-md-3"f>>rt<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'btn btn-secondary' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info' }
            ]
        });

        $('#createNewRole').click(function () {
            $('#saveBtn').val("create-role");
            $('#role_id').val('');
            $('#roleForm').trigger("reset");
            $('#modelHeading').html("Crear Nuevo Rol");
            $('#ajaxModel').modal('show');
        });

        $('body').on('click', '.editRole', function () {
            var role_id = $(this).data('id');
            $.get("{{ route('roles.index') }}" + '/' + role_id + '/edit', function (data) {
                $('#modelHeading').html("Editar Rol");
                $('#saveBtn').val("edit-user");
                $('#ajaxModel').modal('show');
                $('#role_id').val(data.id);
                $('#name').val(data.name);
                $('#description').val(data.description);
            })
        });

        $('#saveBtn').click(function (e) {
            e.preventDefault();
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            $(this).attr('disabled', true);

            $.ajax({
                data: $('#roleForm').serialize(),
                url: "{{ route('roles.store') }}",
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $('#roleForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    table.draw();
                    Swal.fire({
                        icon: 'success',
                        title: '\u00a1\u00c9xito!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#saveBtn').html('<i class="fas fa-save"></i> Guardar Cambios');
                    $('#saveBtn').removeAttr('disabled');
                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#saveBtn').html('<i class="fas fa-save"></i> Guardar Cambios');
                    $('#saveBtn').removeAttr('disabled');
                    
                    var errors = data.responseJSON;
                    if(errors && errors.errors) {
                        var errorString = '';
                        $.each(errors.errors, function(key, value) {
                            errorString += value + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validaci\u00f3n',
                            html: errorString
                        });
                    }
                }
            });
        });

        $('body').on('click', '.deleteRole', function () {
            var role_id = $(this).data("id");
            
            Swal.fire({
                title: '\u00bfEst\u00e1s seguro?',
                text: '\u00a1No podr\u00e1s revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'S\u00ed, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('roles.store') }}" + '/' + role_id,
                        success: function (data) {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: '\u00a1Eliminado!',
                                text: data.success,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurri\u00f3 un error al eliminar el rol'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@stop
