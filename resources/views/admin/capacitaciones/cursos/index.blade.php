@extends('adminlte::page')

@section('title', 'Gestión de Cursos')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-book-open"></i> Gestión de Cursos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Capacitaciones</a></li>
                    <li class="breadcrumb-item active">Cursos</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros de búsqueda -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="titulo">Título del Curso</label>
                        <input type="text" class="form-control" id="titulo" placeholder="Buscar por título...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="area">Área</label>
                        <select class="form-control" id="area">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->descripcion }} ({{ $area->categoria->descripcion }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select class="form-control" id="estado">
                            <option value="">Todos los estados</option>
                            <option value="borrador">Borrador</option>
                            <option value="activo">Activo</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="archivado">Archivado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="instructor">Instructor</label>
                        <input type="text" class="form-control" id="instructor" placeholder="Buscar por instructor...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cursos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Lista de Cursos</h3>
            <div class="card-tools">
                @can('cursos.create')
                <button type="button" class="btn btn-success" id="btn-nuevo-curso">
                    <i class="fas fa-plus"></i> Nuevo Curso
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="cursos-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Área</th>
                            <th>Instructor</th>
                            <th>Estudiantes</th>
                            <th>Fechas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Curso -->
    <div class="modal fade" id="viewCursoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title"><i class="fas fa-eye"></i> Detalles del Curso</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr><td><strong>ID:</strong></td><td id="view_id"></td></tr>
                                <tr><td><strong>Título:</strong></td><td id="view_titulo"></td></tr>
                                <tr><td><strong>Descripción:</strong></td><td id="view_descripcion"></td></tr>
                                <tr><td><strong>Área:</strong></td><td id="view_area"></td></tr>
                                <tr><td><strong>Categoría:</strong></td><td id="view_categoria"></td></tr>
                                <tr><td><strong>Instructor:</strong></td><td id="view_instructor"></td></tr>
                                <tr><td><strong>Fecha Inicio:</strong></td><td id="view_fecha_inicio"></td></tr>
                                <tr><td><strong>Fecha Fin:</strong></td><td id="view_fecha_fin"></td></tr>
                                <tr><td><strong>Estado:</strong></td><td id="view_estado"></td></tr>
                                <tr><td><strong>Código Acceso:</strong></td><td id="view_codigo_acceso"></td></tr>
                                <tr><td><strong>Max. Estudiantes:</strong></td><td id="view_max_estudiantes"></td></tr>
                                <tr><td><strong>Estudiantes Inscritos:</strong></td><td id="view_estudiantes_inscritos"></td></tr>
                                <tr><td><strong>Duración (horas):</strong></td><td id="view_duracion_horas"></td></tr>
                                <tr><td><strong>Creado:</strong></td><td id="view_created_at"></td></tr>
                                <tr><td><strong>Actualizado:</strong></td><td id="view_updated_at"></td></tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <img id="view_imagen_portada" src="" alt="Portada del curso" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" id="objetivos_section" style="display: none;">
                        <div class="col-12">
                            <h5><i class="fas fa-bullseye"></i> Objetivos:</h5>
                            <p id="view_objetivos" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="row mt-3" id="requisitos_section" style="display: none;">
                        <div class="col-12">
                            <h5><i class="fas fa-list-check"></i> Requisitos:</h5>
                            <p id="view_requisitos" class="text-muted"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Estadísticas del Curso -->
    <div class="modal fade" id="statsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title"><i class="fas fa-chart-bar"></i> Estadísticas del Curso: <span id="stats_curso_titulo"></span></h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Resumen General -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Estudiantes</span>
                                    <span class="info-box-number" id="stats_total_estudiantes">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Estudiantes Activos</span>
                                    <span class="info-box-number" id="stats_estudiantes_activos">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Progreso Promedio</span>
                                    <span class="info-box-number" id="stats_progreso_promedio">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Entregas</span>
                                    <span class="info-box-number" id="stats_total_entregas">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficas -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-chart-pie"></i> Tipos de Actividades</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartActividadesTipo" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-chart-doughnut"></i> Estado de Entregas</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartEntregasEstado" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-chart-bar"></i> Progreso por Estudiante</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartProgresoEstudiantes" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Actividades -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-tasks"></i> Resumen de Actividades</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-primary"><i class="fas fa-list"></i></span>
                                                <h5 class="description-header" id="stats_total_actividades">0</h5>
                                                <span class="description-text">TOTAL</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-info"><i class="fas fa-file-alt"></i></span>
                                                <h5 class="description-header" id="stats_tareas">0</h5>
                                                <span class="description-text">TAREAS</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-warning"><i class="fas fa-question-circle"></i></span>
                                                <h5 class="description-header" id="stats_quizzes">0</h5>
                                                <span class="description-text">QUIZZES</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-danger"><i class="fas fa-clipboard-check"></i></span>
                                                <h5 class="description-header" id="stats_evaluaciones">0</h5>
                                                <span class="description-text">EVALUACIONES</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="description-block">
                                                <span class="description-percentage text-success"><i class="fas fa-project-diagram"></i></span>
                                                <h5 class="description-header" id="stats_proyectos">0</h5>
                                                <span class="description-text">PROYECTOS</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Estudiantes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-user-graduate"></i> Estudiantes Inscritos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm" id="stats-estudiantes-table">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Estudiante</th>
                                                    <th>Email</th>
                                                    <th>Estado</th>
                                                    <th>Progreso</th>
                                                    <th>Inscripción</th>
                                                    <th>Última Actividad</th>
                                                    <th class="text-center">Realizadas</th>
                                                    <th class="text-center">Faltantes</th>
                                                    <th class="text-center">Aprobadas</th>
                                                    <th class="text-center">No Aprobadas</th>
                                                    <th class="text-center">Sin Calificar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="stats_estudiantes_body">
                                                <!-- Se llena dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="stats_no_estudiantes" class="text-center text-muted py-4" style="display: none;">
                                        <i class="fas fa-users-slash fa-3x mb-3"></i>
                                        <p>No hay estudiantes inscritos en este curso</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btn-export-excel">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        // Variables globales para gráficas y datos
        var chartActividadesTipo = null;
        var chartEntregasEstado = null;
        var chartProgresoEstudiantes = null;
        var currentStatsData = null;

        $(document).ready(function() {
            var searchTimeout;

            var table = $('#cursos-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("capacitaciones.cursos.data") }}',
                    data: function(d) {
                        d.titulo = $('#titulo').val();
                        d.area = $('#area').val();
                        d.estado = $('#estado').val();
                        d.instructor = $('#instructor').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'titulo', name: 'titulo' },
                    { data: 'area_info', name: 'area.descripcion' },
                    { data: 'instructor_info', name: 'instructor.name' },
                    { data: 'estudiantes_info', name: 'estudiantes_info', orderable: false, searchable: false },
                    { data: 'fechas_info', name: 'fecha_inicio', orderable: false, searchable: false },
                    { data: 'estado_badge', name: 'estado' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    url: '{{ asset('js/datatables-spanish.json') }}'
                }
            });

            $('#titulo, #instructor').on('input keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { table.draw(); }, 500);
            });

            $('#area, #estado').on('change', function() { table.draw(); });

            $('#btn-nuevo-curso').click(function() {
                window.location.href = '{{ route("capacitaciones.cursos.create") }}';
            });

            $('#btn-export-excel').click(function() { exportToExcel(); });
        });

        window.viewCurso = function(id) {
            $.get('{{ route("capacitaciones.cursos.show", ":id") }}'.replace(':id', id))
                .done(function(data) {
                    $('#view_id').text(data.id);
                    $('#view_titulo').text(data.titulo);
                    $('#view_descripcion').text(data.descripcion || 'Sin descripción');
                    $('#view_area').text(data.area);
                    $('#view_categoria').text(data.categoria);
                    $('#view_instructor').text(data.instructor);
                    $('#view_fecha_inicio').text(data.fecha_inicio);
                    $('#view_fecha_fin').text(data.fecha_fin);
                    $('#view_estado').text(data.estado);
                    $('#view_codigo_acceso').text(data.codigo_acceso);
                    $('#view_max_estudiantes').text(data.max_estudiantes);
                    $('#view_estudiantes_inscritos').text(data.estudiantes_inscritos);
                    $('#view_duracion_horas').text(data.duracion_horas || 'No especificada');
                    $('#view_created_at').text(data.created_at);
                    $('#view_updated_at').text(data.updated_at);
                    $('#view_imagen_portada').attr('src', data.imagen_portada_url);
                    if (data.objetivos) { $('#view_objetivos').text(data.objetivos); $('#objetivos_section').show(); } else { $('#objetivos_section').hide(); }
                    if (data.requisitos) { $('#view_requisitos').text(data.requisitos); $('#requisitos_section').show(); } else { $('#requisitos_section').hide(); }
                    $('#viewCursoModal').modal('show');
                })
                .fail(function() { Swal.fire('Error', 'No se pudieron cargar los datos del curso', 'error'); });
        };

        window.editCurso = function(id) {
            window.location.href = '{{ route("capacitaciones.cursos.edit", ":id") }}'.replace(':id', id);
        };

        window.deleteCurso = function(id) {
            Swal.fire({
                title: '¿Estás seguro?', text: "Esta acción no se puede deshacer", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("capacitaciones.cursos.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE', data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) { Swal.fire('Eliminado', response.message, 'success'); $('#cursos-table').DataTable().ajax.reload(); }
                            else { Swal.fire('Error', response.message, 'error'); }
                        },
                        error: function() { Swal.fire('Error', 'Ocurrió un error al eliminar el curso', 'error'); }
                    });
                }
            });
        };

        window.viewCursoStats = function(id) {
            Swal.fire({ title: 'Cargando estadísticas...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            // Agregar timestamp para evitar caché
            var timestamp = new Date().getTime();
            
            $.ajax({
                url: '{{ url("capacitaciones/cursos") }}/' + id + '/stats?_t=' + timestamp,
                type: 'GET',
                cache: false,
                headers: { 
                    'Cache-Control': 'no-cache, no-store, must-revalidate', 
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            }).done(function(data) {
                    console.log('Datos recibidos del servidor:', data); // Debug
                    Swal.close();
                    if (data.success) {
                        currentStatsData = data;
                        $('#stats_curso_titulo').text(data.curso.titulo);
                        $('#stats_total_estudiantes').text(data.resumen.total_estudiantes);
                        $('#stats_estudiantes_activos').text(data.resumen.estudiantes_activos);
                        $('#stats_progreso_promedio').text(data.resumen.progreso_promedio + '%');
                        $('#stats_total_entregas').text(data.resumen.total_entregas);
                        $('#stats_total_actividades').text(data.actividades.total);
                        $('#stats_tareas').text(data.actividades.tareas);
                        $('#stats_quizzes').text(data.actividades.quizzes);
                        $('#stats_evaluaciones').text(data.actividades.evaluaciones);
                        $('#stats_proyectos').text(data.actividades.proyectos);

                        var tbody = $('#stats_estudiantes_body');
                        tbody.empty();
                        
                        console.log('Estudiantes en respuesta:', data.estudiantes ? data.estudiantes.length : 0); // Debug
                        
                        if (data.estudiantes && data.estudiantes.length > 0) {
                            $('#stats_no_estudiantes').hide();
                            $('#stats-estudiantes-table').show();
                            data.estudiantes.forEach(function(est) {
                                var estadoBadge = est.estado === 'activo' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">' + est.estado + '</span>';
                                var progresoColor = est.progreso >= 80 ? 'success' : (est.progreso >= 50 ? 'warning' : 'danger');
                                var progresoBar = '<div class="progress progress-sm"><div class="progress-bar bg-' + progresoColor + '" style="width: ' + est.progreso + '%"></div></div><small>' + est.progreso + '%</small>';
                                var act = est.actividades || {};
                                var row = '<tr><td><strong>' + est.nombre + '</strong></td><td><small>' + est.email + '</small></td><td class="text-center">' + estadoBadge + '</td><td style="min-width: 100px;">' + progresoBar + '</td><td><small>' + est.fecha_inscripcion + '</small></td><td><small>' + est.ultima_actividad + '</small></td><td class="text-center"><span class="badge badge-info">' + (act.realizadas || 0) + '</span></td><td class="text-center"><span class="badge badge-warning">' + (act.faltantes || 0) + '</span></td><td class="text-center"><span class="badge badge-success">' + (act.aprobadas || 0) + '</span></td><td class="text-center"><span class="badge badge-danger">' + (act.no_aprobadas || 0) + '</span></td><td class="text-center"><span class="badge badge-secondary">' + (act.sin_calificar || 0) + '</span></td></tr>';
                                tbody.append(row);
                            });
                        } else {
                            $('#stats-estudiantes-table').hide();
                            $('#stats_no_estudiantes').show();
                        }
                        renderCharts(data);
                        $('#statsModal').modal('show');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudieron cargar las estadísticas', 'error');
                    }
                })
                .fail(function(xhr) { 
                    console.error('Error en la petición:', xhr); // Debug
                    Swal.close(); 
                    Swal.fire('Error', 'No se pudieron cargar las estadísticas del curso', 'error'); 
                });
        };

        function renderCharts(data) {
            if (chartActividadesTipo) chartActividadesTipo.destroy();
            if (chartEntregasEstado) chartEntregasEstado.destroy();
            if (chartProgresoEstudiantes) chartProgresoEstudiantes.destroy();

            // Gráfica Tipos de Actividades
            var ctxTipo = document.getElementById('chartActividadesTipo').getContext('2d');
            chartActividadesTipo = new Chart(ctxTipo, {
                type: 'pie',
                data: {
                    labels: ['Tareas', 'Quizzes', 'Evaluaciones', 'Proyectos'],
                    datasets: [{ data: [data.actividades.tareas, data.actividades.quizzes, data.actividades.evaluaciones, data.actividades.proyectos], backgroundColor: ['#17a2b8', '#ffc107', '#dc3545', '#28a745'], borderWidth: 2, borderColor: '#fff' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
            });

            // Calcular totales
            var totalAprobadas = 0, totalNoAprobadas = 0, totalSinCalificar = 0, totalFaltantes = 0;
            data.estudiantes.forEach(function(est) {
                var act = est.actividades || {};
                totalAprobadas += act.aprobadas || 0;
                totalNoAprobadas += act.no_aprobadas || 0;
                totalSinCalificar += act.sin_calificar || 0;
                totalFaltantes += act.faltantes || 0;
            });

            // Gráfica Estado de Entregas
            var ctxEstado = document.getElementById('chartEntregasEstado').getContext('2d');
            chartEntregasEstado = new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: ['Aprobadas', 'No Aprobadas', 'Sin Calificar', 'Faltantes'],
                    datasets: [{ data: [totalAprobadas, totalNoAprobadas, totalSinCalificar, totalFaltantes], backgroundColor: ['#28a745', '#dc3545', '#6c757d', '#ffc107'], borderWidth: 2, borderColor: '#fff' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
            });

            // Gráfica Progreso por Estudiante
            var nombres = [], progresos = [], colores = [];
            data.estudiantes.slice(0, 10).forEach(function(est) {
                nombres.push(est.nombre.length > 15 ? est.nombre.substring(0, 15) + '...' : est.nombre);
                progresos.push(est.progreso);
                colores.push(est.progreso >= 80 ? '#28a745' : (est.progreso >= 50 ? '#ffc107' : '#dc3545'));
            });

            var ctxProgreso = document.getElementById('chartProgresoEstudiantes').getContext('2d');
            chartProgresoEstudiantes = new Chart(ctxProgreso, {
                type: 'bar',
                data: { labels: nombres, datasets: [{ label: 'Progreso %', data: progresos, backgroundColor: colores, borderWidth: 1, borderColor: '#333' }] },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } } } }, plugins: { legend: { display: false } } }
            });
        }

        function exportToExcel() {
            if (!currentStatsData) { Swal.fire('Error', 'No hay datos para exportar', 'error'); return; }
            var data = currentStatsData;
            var wb = XLSX.utils.book_new();

            // Hoja Resumen
            var resumenData = [
                ['ESTADÍSTICAS DEL CURSO'], [''],
                ['Curso:', data.curso.titulo], ['Instructor:', data.curso.instructor], ['Estado:', data.curso.estado], [''],
                ['RESUMEN GENERAL'],
                ['Total Estudiantes:', data.resumen.total_estudiantes], ['Estudiantes Activos:', data.resumen.estudiantes_activos],
                ['Progreso Promedio:', data.resumen.progreso_promedio + '%'], ['Total Entregas:', data.resumen.total_entregas], [''],
                ['ACTIVIDADES'], ['Total:', data.actividades.total], ['Tareas:', data.actividades.tareas],
                ['Quizzes:', data.actividades.quizzes], ['Evaluaciones:', data.actividades.evaluaciones], ['Proyectos:', data.actividades.proyectos],
                [''], ['LISTA DE ESTUDIANTES INSCRITOS']
            ];
            
            // Agregar lista de nombres de estudiantes en el resumen
            if (data.estudiantes && data.estudiantes.length > 0) {
                data.estudiantes.forEach(function(est, index) {
                    resumenData.push([(index + 1) + '. ' + est.nombre, est.email]);
                });
            } else {
                resumenData.push(['No hay estudiantes inscritos', '']);
            }
            
            var wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
            wsResumen['!cols'] = [{ wch: 35 }, { wch: 40 }];
            XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');

            // Hoja Estudiantes Detallada
            var estudiantesData = [['#', 'Nombre Completo', 'Email', 'Estado', 'Progreso %', 'Fecha Inscripción', 'Última Actividad', 'Act. Realizadas', 'Act. Faltantes', 'Aprobadas', 'No Aprobadas', 'Sin Calificar']];
            
            if (data.estudiantes && data.estudiantes.length > 0) {
                data.estudiantes.forEach(function(est, index) {
                    var act = est.actividades || {};
                    estudiantesData.push([
                        index + 1,
                        est.nombre || 'Sin nombre',
                        est.email || 'Sin email',
                        est.estado || 'N/A',
                        est.progreso || 0,
                        est.fecha_inscripcion || 'N/A',
                        est.ultima_actividad || 'Sin actividad',
                        act.realizadas || 0,
                        act.faltantes || 0,
                        act.aprobadas || 0,
                        act.no_aprobadas || 0,
                        act.sin_calificar || 0
                    ]);
                });
            } else {
                estudiantesData.push(['', 'No hay estudiantes inscritos en este curso', '', '', '', '', '', '', '', '', '', '']);
            }
            
            var wsEstudiantes = XLSX.utils.aoa_to_sheet(estudiantesData);
            wsEstudiantes['!cols'] = [{ wch: 5 }, { wch: 30 }, { wch: 30 }, { wch: 10 }, { wch: 12 }, { wch: 15 }, { wch: 18 }, { wch: 14 }, { wch: 14 }, { wch: 12 }, { wch: 14 }, { wch: 14 }];
            XLSX.utils.book_append_sheet(wb, wsEstudiantes, 'Estudiantes');

            var fileName = 'Estadisticas_' + data.curso.titulo.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.xlsx';
            XLSX.writeFile(wb, fileName);
            Swal.fire({ icon: 'success', title: 'Exportación exitosa', text: 'El archivo se ha descargado correctamente', timer: 2000, showConfirmButton: false });
        }
    </script>
@stop
