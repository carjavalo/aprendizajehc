@extends('admin.layouts.master')

@section('title', 'Reporte de Estudiantes')

@section('content_header')
    <h1>Reporte de Estudiantes por Cursos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header border-0">
            <h3 class="card-title">Listado general de estudiantes en cursos</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportes-table" class="table table-striped table-hover table-bordered responsive nowrap" width="100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Nombre</th>
                            <th>Identificación</th>
                            <th>Tipo Vinculación</th>
                            <th>Área</th>
                            <th>Contacto</th>
                            <th>Correo</th>
                            <th>Curso</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th width="100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Ver/Editar -->
    <div class="modal fade" id="reporteModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="modalHeading">Detalles de Inscripción</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reporteForm" name="reporteForm" class="form-horizontal">
                        <input type="hidden" name="id" id="record_id">
                        
                        <div class="alert alert-info" id="studentInfo"></div>

                        <!-- Nota final (solo visible al VER) -->
                        <div class="form-group mb-3" id="notaFinalContainer" style="display: none;">
                            <label class="col-sm-12 control-label">Nota Final Calculada</label>
                            <div class="col-sm-12">
                                <div class="d-flex align-items-center">
                                    <span id="notaFinalDisplay" class="badge badge-lg p-2" style="font-size: 1.1em;"></span>
                                    <span id="notaEstadoDisplay" class="ml-2 badge badge-lg p-2" style="font-size: 1.1em;"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="col-sm-12 control-label">Estado de la Inscripción</label>
                            <div class="col-sm-12">
                                <select id="estado" name="estado" class="form-control form-control-sm" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="completado">Completado</option>
                                    <option value="abandonado">Abandonado</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="col-sm-12 control-label">Progreso (%)</label>
                            <div class="col-sm-12">
                                <input type="number" class="form-control form-control-sm" id="progreso" name="progreso" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-sm-12 mt-4" id="saveBtnContainer" style="display: none;">
                            <button type="submit" class="btn btn-primary" id="saveBtn">
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
        .dt-buttons { white-space: nowrap; margin-bottom: 10px; }
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

        var table = $('#reportes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('consultas.reportes.data') }}",
            columns: [
                {data: 'nombre_completo', name: 'nombre_completo'},
                {data: 'identificacion', name: 'identificacion'},
                {data: 'vinculacion', name: 'vinculacion', defaultContent: ''},
                {data: 'area', name: 'area', defaultContent: ''},
                {data: 'contacto', name: 'contacto', defaultContent: ''},
                {data: 'correo', name: 'correo', defaultContent: ''},
                {data: 'curso', name: 'curso'},
                {
                    data: 'fecha_inicio', 
                    name: 'fecha_inicio',
                    render: function(data) {
                        return data ? moment(data).format('YYYY-MM-DD HH:mm') : 'N/A';
                    }
                },
                {
                    data: 'fecha_fin', 
                    name: 'fecha_fin',
                    render: function(data) {
                        return data ? moment(data).format('YYYY-MM-DD HH:mm') : 'N/A';
                    }
                },
                {data: 'estado_badge', name: 'estado_badge', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                url: '{{ asset('js/datatables-spanish.json') }}'
            },
            responsive: true,
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i><"col-sm-12 mt-2"p>>',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'btn btn-secondary btn-sm' },
                @can('reportes.export')
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                @endcan
                @can('reportes.print')
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
                @endcan
            ]
        });

        // Ver Registro
        $('body').on('click', '.viewRecord', function () {
            var record_id = $(this).data('id');
            $.get("{{ route('consultas.reportes.index') }}" + '/' + record_id, function (data) {
                $('#modalHeading').html("Ver Detalles de Inscripci\u00f3n");
                $('#saveBtnContainer').hide();
                $('#estado').prop('disabled', true);
                $('#progreso').prop('disabled', true);
                
                $('#record_id').val(data.id);
                $('#estado').val(data.estado);
                $('#progreso').val(data.progreso);
                $('#studentInfo').html('<strong>Estudiante:</strong> ' + data.user_name + '<br><strong>Curso:</strong> ' + data.curso_titulo);
                
                // Mostrar nota final real
                $('#notaFinalContainer').show();
                $('#notaFinalDisplay').text(data.nota_final + ' / 5.0 (M\u00ednima: ' + data.nota_minima + ')');
                if (data.aprobado) {
                    $('#notaFinalDisplay').attr('class', 'badge badge-lg p-2 badge-primary').css('font-size', '1.1em');
                    $('#notaEstadoDisplay').attr('class', 'ml-2 badge badge-lg p-2 badge-success').css('font-size', '1.1em').text('Aprob\u00f3');
                } else {
                    $('#notaFinalDisplay').attr('class', 'badge badge-lg p-2 badge-warning').css('font-size', '1.1em');
                    $('#notaEstadoDisplay').attr('class', 'ml-2 badge badge-lg p-2 badge-danger').css('font-size', '1.1em').text('Reprob\u00f3');
                }

                $('#reporteModal').modal('show');
            }).fail(function() {
                Swal.fire('Error', 'No se pudo cargar la informaci\u00f3n.', 'error');
            });
        });

        // Editar Registro
        $('body').on('click', '.editRecord', function () {
            var record_id = $(this).data('id');
            $.get("{{ route('consultas.reportes.index') }}" + '/' + record_id + '/edit', function (data) {
                $('#modalHeading').html("Editar Inscripci\u00f3n");
                $('#saveBtnContainer').show();
                $('#notaFinalContainer').hide();
                $('#estado').prop('disabled', false);
                $('#progreso').prop('disabled', false);
                
                $('#record_id').val(data.id);
                $('#estado').val(data.estado);
                $('#progreso').val(data.progreso);
                $('#studentInfo').html('<strong>Estudiante:</strong> ' + data.user_name + '<br><strong>Curso:</strong> ' + data.curso_titulo);
                
                $('#reporteModal').modal('show');
            }).fail(function() {
                Swal.fire('Error', 'No se pudo cargar la informaci\u00f3n.', 'error');
            });
        });

        // Guardar Cambios (Update)
        $('#reporteForm').submit(function (e) {
            e.preventDefault();
            var record_id = $('#record_id').val();
            $('#saveBtn').html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

            $.ajax({
                data: $(this).serialize(),
                url: "{{ route('consultas.reportes.index') }}" + '/' + record_id,
                type: "PUT",
                dataType: 'json',
                success: function (data) {
                    $('#reporteForm').trigger("reset");
                    $('#reporteModal').modal('hide');
                    table.draw();
                    Swal.fire({
                        icon: 'success',
                        title: '\u00a1\u00c9xito!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#saveBtn').html('<i class="fas fa-save"></i> Guardar Cambios').prop('disabled', false);
                },
                error: function (data) {
                    $('#saveBtn').html('<i class="fas fa-save"></i> Guardar Cambios').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validaci\u00f3n',
                        text: 'Verifique los datos ingresados.'
                    });
                }
            });
        });

        // Eliminar Registro
        $('body').on('click', '.deleteRecord', function () {
            var record_id = $(this).data("id");
            Swal.fire({
                title: '\u00bfEst\u00e1s seguro?',
                text: "\u00a1No podr\u00e1s revertir esto!",
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
                        url: "{{ route('consultas.reportes.index') }}" + '/' + record_id,
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurri\u00f3 un error al eliminar el registro.'
                            });
                        }
                    });
                }
            });
        });
    });
</script>

<!-- Moment.js for date formatting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@stop
