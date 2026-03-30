@extends('admin.layouts.master')

@section('title', 'Cursos Disponibles')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-graduation-cap"></i> Cursos Disponibles</h1>
                <p class="text-muted mb-0">Cursos asignados para tu formación académica</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cursos Disponibles</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Lista de Cursos Asignados
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="cursosTable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Área</th>
                                        <th>Categoría</th>
                                        <th>Docente</th>
                                        <th>Progreso</th>
                                        <th>Estado</th>
                                        <th>Fecha Inscripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del curso -->
    <div class="modal fade" id="viewCursoModal" tabindex="-1" role="dialog" aria-labelledby="viewCursoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="viewCursoModalLabel">
                        <i class="fas fa-eye"></i> Detalles del Curso
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Título:</strong>
                            <p id="view_titulo"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Área:</strong>
                            <p id="view_area"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Docente:</strong>
                            <p id="view_instructor"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <p id="view_estado"></p>
                        </div>
                        <div class="col-md-12">
                            <strong>Descripción:</strong>
                            <p id="view_descripcion"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha Inicio:</strong>
                            <p id="view_fecha_inicio"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha Fin:</strong>
                            <p id="view_fecha_fin"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Duración:</strong>
                            <p id="view_duracion"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Tu Progreso:</strong>
                            <div class="progress">
                                <div id="view_progreso_bar" class="progress-bar bg-success" role="progressbar" style="width: 0%">
                                    <span id="view_progreso_text">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="accederCursoBtn">
                        <i class="fas fa-play"></i> Acceder al Curso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Vista Previa de Certificado -->
    <div class="modal fade" id="certificadoPreviewModal" tabindex="-1" role="dialog" aria-labelledby="certificadoPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1100px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c4370 100%); color: white; border: none;">
                    <h5 class="modal-title" id="certificadoPreviewModalLabel">
                        <i class="fas fa-certificate"></i> Vista Previa del Certificado
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color: white; opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="background: #525659; overflow: hidden;">
                    <div class="cert-info-bar" style="background: #f8f9fa; padding: 12px 20px; border-bottom: 2px solid #e9ecef;">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Estudiante</small>
                                <strong id="certInfoNombre" class="text-dark"></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Identificación</small>
                                <strong id="certInfoDocumento" class="text-dark"></strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Fecha Inicio</small>
                                <strong id="certInfoFechaInicio" class="text-dark"></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Fecha Fin</small>
                                <strong id="certInfoFechaFin" class="text-dark"></strong>
                            </div>
                        </div>
                    </div>
                    <div id="certIframeWrapper2" style="display: flex; justify-content: center; align-items: flex-start; padding: 15px; overflow: hidden; position: relative;">
                        <div id="certLoadingIndicator2" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 10;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #fff;"></i>
                            <p style="color: #ccc; margin-top: 10px; font-family: 'Inter',sans-serif;">Cargando certificado...</p>
                        </div>
                        <div id="certIframeScaler2" style="width: 960px; height: 680px; transform-origin: top center; flex-shrink: 0;">
                            <iframe id="certificadoIframe" onload="var li=document.getElementById('certLoadingIndicator2');if(li)li.style.display='none'; if(typeof scaleCertificateIframe2==='function')scaleCertificateIframe2();" style="width: 960px; height: 680px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: white; display: block;" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; background: #f8f9fa;">
                    <a id="certOpenNewTab" href="#" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Abrir en nueva pestaña
                    </a>
                    <button type="button" id="certPrintBtn" class="btn btn-primary" style="background: #1e3a5f; border-color: #1e3a5f;">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .progress {
            height: 25px;
        }
        .progress-bar {
            line-height: 25px;
        }
        .badge-estado {
            font-size: 0.9em;
        }
    </style>
@stop

@section('extra_js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#cursosTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route("academico.cursos.disponibles.data") }}',
                columns: [
                    { data: 'titulo', name: 'titulo' },
                    { data: 'area_descripcion', name: 'area.descripcion' },
                    { data: 'area_categoria', name: 'area.categoria.descripcion' },
                    { data: 'instructor_nombre', name: 'instructor.name' },
                    { 
                        data: 'progreso', 
                        name: 'progreso',
                        render: function(data, type, row) {
                            return '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: ' + data + '%">' + data + '%</div></div>';
                        }
                    },
                    { 
                        data: 'estado_inscripcion', 
                        name: 'estado_inscripcion',
                        render: function(data, type, row) {
                            if (data === 'inscrito' || data === 'activo') {
                                return '<span class="badge badge-success badge-estado">Inscrito</span>';
                            } else if (data === 'completado') {
                                return '<span class="badge badge-primary badge-estado">Completado</span>';
                            } else if (data === 'no_inscrito') {
                                return '<span class="badge badge-warning badge-estado">Pendiente</span>';
                            } else if (data === 'acceso_directo') {
                                return '<span class="badge badge-info badge-estado">Acceso Directo</span>';
                            } else {
                                return '<span class="badge badge-secondary badge-estado">Sin Acceso</span>';
                            }
                        }
                    },
                    { data: 'fecha_inscripcion', name: 'fecha_inscripcion' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                language: {
                    url: '{{ asset('js/datatables-spanish.json') }}'
                },
                order: [[0, 'asc']]
            });
        });

        // Función para ver detalles del curso
        function verCurso(id) {
            // Aquí harías una petición AJAX para obtener los detalles del curso
            // Por simplicidad, usaremos datos del DataTable
            $('#viewCursoModal').modal('show');
        }

        // Función para acceder al aula virtual (validar inscripción primero)
        function aulaVirtual(id) {
            window.location.href = '{{ route("academico.curso.aula-virtual", ":id") }}'.replace(':id', id);
        }

        // Función para acceder al curso
        function accederCurso(id) {
            window.location.href = '{{ route("academico.curso.ver", ":id") }}'.replace(':id', id);
        }

        // Función para inscribirse a un curso
        function inscribirseCurso(id) {
            Swal.fire({
                title: '¿Inscribirse al curso?',
                text: "Podrás acceder a todo el material del curso",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, inscribirme',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("academico.curso.inscribirse", ":id") }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Inscrito!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(() => {
                                    // Recargar la tabla
                                    $('#cursosTable').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'Error al inscribirse al curso';
                            Swal.fire('Error', message, 'error');
                        }
                    });
                }
            });
        }

        // Preview de certificado para el estudiante
        $(document).on('click', '.btn-certificado-preview-student', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const estudianteNombre = $(this).data('estudiante-nombre');
            const estudianteDocumento = $(this).data('estudiante-documento');
            const cursoId = $(this).data('curso-id');
            const cursoNombre = $(this).data('curso-nombre');
            const fechaInicio = $(this).data('fecha-inicio');
            const fechaFin = $(this).data('fecha-fin');

            $('#certInfoNombre').text(estudianteNombre);
            $('#certInfoDocumento').text(estudianteDocumento || 'No registrado');
            $('#certInfoFechaInicio').text(fechaInicio);
            $('#certInfoFechaFin').text(fechaFin);

            const certUrl = `{{ url('academico/curso') }}/${cursoId}/certificado?iframe=1`;
            const certUrlNewTab = `{{ url('academico/curso') }}/${cursoId}/certificado`;
            
            // Mostrar loading
            var li2 = document.getElementById('certLoadingIndicator2');
            if (li2) li2.style.display = 'block';
            
            // Cargar en iframe directamente (X-Frame-Options: SAMEORIGIN permite esto)
            $('#certificadoIframe').attr('src', certUrl);
            $('#certOpenNewTab').attr('href', certUrlNewTab);

            $('#certPrintBtn').off('click').on('click', function() {
                const iframeEl = document.getElementById('certificadoIframe');
                if (iframeEl && iframeEl.contentWindow) {
                    iframeEl.contentWindow.print();
                }
            });

            $('#certificadoPreviewModal').modal('show');
        });

        $('#certificadoPreviewModal').on('hidden.bs.modal', function() {
            $('#certificadoIframe').attr('src', '');
            var li2 = document.getElementById('certLoadingIndicator2');
            if (li2) li2.style.display = 'block';
        });

        // Escalar iframe para que quepa en el modal manteniendo proporciones
        function scaleCertificateIframe2() {
            var wrapper = document.getElementById('certIframeWrapper2');
            var scaler = document.getElementById('certIframeScaler2');
            if (!wrapper || !scaler) return;
            var availableWidth = wrapper.clientWidth - 30;
            var nativeW = 960, nativeH = 680;
            var scale = Math.min(1, availableWidth / nativeW);
            scaler.style.transform = 'scale(' + scale + ')';
            wrapper.style.height = (nativeH * scale + 30) + 'px';
        }
        $('#certificadoPreviewModal').on('shown.bs.modal', function() {
            scaleCertificateIframe2();
        });
        $(window).on('resize', function() {
            if ($('#certificadoPreviewModal').is(':visible')) {
                scaleCertificateIframe2();
            }
        });
    </script>
@stop
