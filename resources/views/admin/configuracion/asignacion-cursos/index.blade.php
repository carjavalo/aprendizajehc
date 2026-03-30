@extends('adminlte::page')

@section('title', 'Asignación de Cursos')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-graduate"></i> Asignación de Cursos</h1>
                <p class="text-muted">Gestione las matrículas de los estudiantes asignando cursos de la oferta académica.</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Configuración</a></li>
                    <li class="breadcrumb-item active">Asignación de Cursos</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Columna Izquierda: Selección de Estudiante -->
            <div class="col-lg-4">
                <!-- Card de Búsqueda -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search"></i> 1. Seleccionar Estudiante</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Busque por nombre, email o número de documento.</p>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="buscar-estudiante" 
                                       placeholder="Escriba para buscar..." autocomplete="off">
                            </div>
                        </div>
                        <!-- Resultados de búsqueda -->
                        <div id="resultados-busqueda" class="list-group" style="display: none; max-height: 250px; overflow-y: auto;">
                        </div>
                    </div>
                </div>

                <!-- Card de Perfil del Estudiante -->
                <div class="card card-info card-outline" id="card-perfil" style="display: none;">
                    <div class="card-header bg-gradient-info">
                        <h3 class="card-title"><i class="fas fa-id-card"></i> Perfil del Estudiante</h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <div class="img-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 80px; height: 80px; font-size: 2rem;" id="avatar-estudiante">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <h3 class="profile-username text-center mt-3" id="nombre-estudiante">-</h3>
                        <p class="text-muted text-center" id="documento-estudiante">-</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <i class="fas fa-envelope text-primary"></i> <b>Email</b>
                                <span class="float-right" id="email-estudiante">-</span>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-calendar text-success"></i> <b>Registro</b>
                                <span class="float-right" id="fecha-registro-estudiante">-</span>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-book text-info"></i> <b>Cursos Asignados</b>
                                <span class="float-right badge badge-info" id="total-asignados">0</span>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-graduation-cap text-success"></i> <b>Cursos Inscritos</b>
                                <span class="float-right badge badge-success" id="total-inscritos">0</span>
                            </li>
                        </ul>

                        <!-- Lista de cursos asignados -->
                        <div id="cursos-asignados-lista" class="mt-3" style="display: none;">
                            <h6 class="text-muted"><i class="fas fa-list"></i> Cursos Asignados Actualmente:</h6>
                            <div id="cursos-asignados-items" style="max-height: 150px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Selección de Cursos -->
            <div class="col-lg-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-book-open"></i> 2. Seleccionar Cursos</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary" id="periodo-actual">Período Activo</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Selección de Docente -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="docente-asignado"><i class="fas fa-chalkboard-teacher text-primary"></i> Docente Asignado <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="docente-asignado" name="docente_id" style="width: 100%; max-width: 500px;">
                                        <option value="">Seleccionar docente...</option>
                                        @foreach($docentes as $docente)
                                            <option value="{{ $docente->id }}">
                                                {{ $docente->name }} {{ $docente->apellido1 }} {{ $docente->apellido2 }} - {{ $docente->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Seleccione el docente que impartirá el curso al estudiante</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="filtrar-cursos" 
                                           placeholder="Filtrar cursos...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-control" id="filtrar-categoria">
                                    <option value="">Todas las categorías</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla de Cursos -->
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-sm" id="tabla-cursos">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="seleccionar-todos">
                                                <label class="custom-control-label" for="seleccionar-todos"></label>
                                            </div>
                                        </th>
                                        <th>Curso</th>
                                        <th style="width: 120px;">Código</th>
                                        <th style="width: 80px;" class="text-center">Horas</th>
                                        <th style="width: 130px;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-cursos">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin"></i> Cargando cursos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen de Selección -->
                        <div class="card card-body bg-light mt-3" id="resumen-seleccion" style="display: none;">
                            <h6 class="text-primary mb-2"><i class="fas fa-clipboard-list"></i> Resumen de Asignación</h6>
                            <div id="cursos-seleccionados-tags" class="mb-2">
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span><span id="count-seleccionados">0</span> curso(s) seleccionado(s)</span>
                                <span>Total horas: <strong id="total-horas">0</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary mr-2" id="btn-cancelar">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" id="btn-confirmar" disabled>
                                <i class="fas fa-save"></i> Confirmar Asignación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Input oculto para el estudiante seleccionado -->
    <input type="hidden" id="estudiante-seleccionado-id" value="">
@stop

@section('css')
<style>
    .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
    .curso-row.ya-asignado {
        background-color: #d4edda !important;
    }
    .curso-row.ya-inscrito {
        background-color: #e2e3e5 !important;
        opacity: 0.7;
    }
    .badge-tag {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
        margin: 0.2rem;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
    }
    #resultados-busqueda .list-group-item {
        cursor: pointer;
        transition: all 0.2s;
    }
    #resultados-busqueda .list-group-item:hover {
        background-color: #007bff;
        color: white;
    }
    #resultados-busqueda .list-group-item:hover .text-muted {
        color: rgba(255,255,255,0.8) !important;
    }
    .curso-categoria-badge {
        font-size: 0.7rem;
    }
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

    let estudianteSeleccionado = null;
    let cursosData = [];
    let cursosSeleccionados = [];
    let timeoutBusqueda = null;

    // Cargar categorías y cursos al inicio
    cargarCategorias();
    cargarCursos();
    
    // Cargar todos los estudiantes al inicio
    buscarEstudiantes('');

    // Búsqueda de estudiantes con debounce
    $('#buscar-estudiante').on('input', function() {
        const query = $(this).val().trim();
        
        clearTimeout(timeoutBusqueda);

        timeoutBusqueda = setTimeout(function() {
            buscarEstudiantes(query);
        }, 300);
    });
    
    // Mostrar lista al hacer focus en el campo de búsqueda
    $('#buscar-estudiante').on('focus', function() {
        if ($('#resultados-busqueda').children().length > 0) {
            $('#resultados-busqueda').show();
        }
    });

    // Función para buscar estudiantes
    function buscarEstudiantes(query) {
        $.ajax({
            url: '{{ route("configuracion.asignacion-cursos.buscar-estudiantes") }}',
            method: 'GET',
            data: { q: query },
            success: function(response) {
                if (response.success && response.estudiantes.length > 0) {
                    mostrarResultadosBusqueda(response.estudiantes);
                } else {
                    $('#resultados-busqueda').html(
                        '<div class="list-group-item text-muted text-center">No se encontraron estudiantes</div>'
                    ).show();
                }
            },
            error: function() {
                $('#resultados-busqueda').html(
                    '<div class="list-group-item text-danger text-center">Error al buscar</div>'
                ).show();
            }
        });
    }

    // Mostrar resultados de búsqueda
    function mostrarResultadosBusqueda(estudiantes) {
        let html = '';
        estudiantes.forEach(function(est) {
            html += `
                <a href="#" class="list-group-item list-group-item-action" data-id="${est.id}">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" 
                             style="width: 40px; height: 40px; font-size: 1rem;">
                            ${est.nombre_completo.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <strong>${est.nombre_completo}</strong>
                            <br><small class="text-muted">${est.email}</small>
                        </div>
                    </div>
                </a>
            `;
        });
        $('#resultados-busqueda').html(html).show();
    }

    // Seleccionar estudiante de los resultados
    $(document).on('click', '#resultados-busqueda .list-group-item', function(e) {
        e.preventDefault();
        const estudianteId = $(this).data('id');
        seleccionarEstudiante(estudianteId);
        $('#resultados-busqueda').hide();
        $('#buscar-estudiante').val('');
    });

    // Función para seleccionar estudiante
    function seleccionarEstudiante(estudianteId) {
        $.ajax({
            url: '{{ route("configuracion.asignacion-cursos.get-estudiante", ":id") }}'.replace(':id', estudianteId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    estudianteSeleccionado = response.estudiante;
                    estudianteSeleccionado.cursos_asignados = response.cursos_asignados;
                    
                    // Actualizar UI del perfil
                    $('#estudiante-seleccionado-id').val(estudianteId);
                    $('#avatar-estudiante').html(response.estudiante.nombre_completo.charAt(0).toUpperCase());
                    $('#nombre-estudiante').text(response.estudiante.nombre_completo);
                    $('#documento-estudiante').text(response.estudiante.documento);
                    $('#email-estudiante').text(response.estudiante.email);
                    $('#fecha-registro-estudiante').text(response.estudiante.fecha_registro);
                    $('#total-asignados').text(response.total_asignados);
                    $('#total-inscritos').text(response.total_inscritos);

                    // Mostrar cursos asignados actuales
                    if (response.cursos_asignados.length > 0) {
                        let htmlAsignados = '';
                        response.cursos_asignados.forEach(function(curso) {
                            let docenteInfo = curso.docente ? `<br><small class="text-info"><i class="fas fa-chalkboard-teacher"></i> ${curso.docente}</small>` : '';
                            htmlAsignados += `
                                <div class="d-flex justify-content-between align-items-center mb-1 p-2 bg-light rounded">
                                    <div class="text-truncate" style="max-width: 200px;">
                                        <small>${curso.titulo}</small>${docenteInfo}
                                    </div>
                                    <button type="button" class="btn btn-xs btn-danger btn-remover-asignacion" 
                                            data-curso-id="${curso.curso_id}" title="Remover">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                        });
                        $('#cursos-asignados-items').html(htmlAsignados);
                        $('#cursos-asignados-lista').show();
                    } else {
                        $('#cursos-asignados-lista').hide();
                    }

                    $('#card-perfil').show();

                    // Cargar cursos disponibles
                    cargarCursos();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo cargar la información del estudiante', 'error');
            }
        });
    }

    // Función para cargar categorías
    function cargarCategorias() {
        $.ajax({
            url: '{{ route("configuracion.asignacion-cursos.get-categorias") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Todas las categorías</option>';
                    response.categorias.forEach(function(cat) {
                        options += `<option value="${cat.id}">${cat.descripcion}</option>`;
                    });
                    $('#filtrar-categoria').html(options);
                }
            }
        });
    }

    // Función para cargar cursos
    function cargarCursos() {
        const estudianteId = $('#estudiante-seleccionado-id').val();
        const search = $('#filtrar-cursos').val();
        const categoria = $('#filtrar-categoria').val();

        $.ajax({
            url: '{{ route("configuracion.asignacion-cursos.get-cursos") }}',
            method: 'GET',
            data: {
                estudiante_id: estudianteId,
                search: search,
                categoria: categoria
            },
            success: function(response) {
                if (response.success) {
                    cursosData = response.cursos;
                    renderizarCursos();
                }
            },
            error: function() {
                $('#tbody-cursos').html(
                    '<tr><td colspan="4" class="text-center text-danger">Error al cargar cursos</td></tr>'
                );
            }
        });
    }

    // Renderizar tabla de cursos
    function renderizarCursos() {
        if (cursosData.length === 0) {
            $('#tbody-cursos').html(
                '<tr><td colspan="5" class="text-center text-muted py-4">No hay cursos disponibles</td></tr>'
            );
            return;
        }

        let html = '';
        cursosData.forEach(function(curso) {
            let rowClass = '';
            let checkboxDisabled = '';
            let statusBadge = '';

            if (curso.ya_inscrito) {
                rowClass = 'curso-row ya-inscrito';
                checkboxDisabled = 'disabled';
                statusBadge = '<span class="badge badge-success ml-2"><i class="fas fa-check"></i> Inscrito</span>';
            } else if (curso.ya_asignado) {
                rowClass = 'curso-row ya-asignado';
                checkboxDisabled = 'disabled';
                statusBadge = '<span class="badge badge-info ml-2"><i class="fas fa-check"></i> Asignado</span>';
            }

            const isChecked = cursosSeleccionados.includes(curso.id) ? 'checked' : '';

            html += `
                <tr class="${rowClass}" data-curso-id="${curso.id}">
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input curso-checkbox" 
                                   id="curso-${curso.id}" value="${curso.id}" 
                                   data-titulo="${curso.titulo}" data-horas="${curso.duracion_horas || 0}"
                                   ${isChecked} ${checkboxDisabled}>
                            <label class="custom-control-label" for="curso-${curso.id}"></label>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>${curso.titulo}</strong>${statusBadge}
                            <br>
                            <small class="text-muted">
                                <span class="badge badge-secondary curso-categoria-badge">${curso.categoria}</span>
                                <i class="fas fa-user ml-2"></i> ${curso.instructor}
                            </small>
                        </div>
                    </td>
                    <td><code>CUR-${String(curso.id).padStart(4, '0')}</code></td>
                    <td class="text-center">
                        ${curso.duracion_horas ? `<span class="badge badge-light">${curso.duracion_horas}h</span>` : '-'}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-info btn-asignar-todos" 
                                data-curso-id="${curso.id}" data-curso-titulo="${curso.titulo}"
                                title="Asignar a todos los estudiantes">
                            <i class="fas fa-users"></i> Asignar a todos
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tbody-cursos').html(html);
    }

    // Filtrar cursos (funciona con o sin estudiante seleccionado)
    $('#filtrar-cursos, #filtrar-categoria').on('input change', function() {
        cargarCursos();
    });

    // Seleccionar/deseleccionar todos
    $('#seleccionar-todos').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.curso-checkbox:not(:disabled)').prop('checked', isChecked).trigger('change');
    });

    // Manejar selección de cursos
    $(document).on('change', '.curso-checkbox', function() {
        const cursoId = parseInt($(this).val());
        const titulo = $(this).data('titulo');
        const horas = parseInt($(this).data('horas')) || 0;

        if ($(this).is(':checked')) {
            if (!cursosSeleccionados.includes(cursoId)) {
                cursosSeleccionados.push(cursoId);
            }
        } else {
            cursosSeleccionados = cursosSeleccionados.filter(id => id !== cursoId);
        }

        actualizarResumen();
    });

    // Actualizar resumen de selección
    function actualizarResumen() {
        if (cursosSeleccionados.length === 0) {
            $('#resumen-seleccion').hide();
            $('#btn-confirmar').prop('disabled', true);
            return;
        }

        let tagsHtml = '';
        let totalHoras = 0;

        cursosSeleccionados.forEach(function(cursoId) {
            const curso = cursosData.find(c => c.id === cursoId);
            if (curso) {
                totalHoras += parseInt(curso.duracion_horas) || 0;
                tagsHtml += `
                    <span class="badge badge-primary badge-tag">
                        ${curso.titulo}
                        <button type="button" class="btn btn-xs text-white ml-1 btn-remover-seleccion" data-id="${curso.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            }
        });

        $('#cursos-seleccionados-tags').html(tagsHtml);
        $('#count-seleccionados').text(cursosSeleccionados.length);
        $('#total-horas').text(totalHoras);
        $('#resumen-seleccion').show();
        $('#btn-confirmar').prop('disabled', false);
    }

    // Remover curso de la selección
    $(document).on('click', '.btn-remover-seleccion', function() {
        const cursoId = parseInt($(this).data('id'));
        $(`#curso-${cursoId}`).prop('checked', false).trigger('change');
    });

    // Remover asignación existente
    $(document).on('click', '.btn-remover-asignacion', function() {
        const cursoId = $(this).data('curso-id');
        const estudianteId = $('#estudiante-seleccionado-id').val();

        Swal.fire({
            title: '¿Remover asignación?',
            text: 'El estudiante ya no podrá ver este curso en su lista de cursos disponibles.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("configuracion.asignacion-cursos.remover") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        estudiante_id: estudianteId,
                        curso_id: cursoId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Removido', response.message, 'success');
                            seleccionarEstudiante(estudianteId);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudo remover la asignación', 'error');
                    }
                });
            }
        });
    });

    // Cancelar
    $('#btn-cancelar').on('click', function() {
        cursosSeleccionados = [];
        $('.curso-checkbox').prop('checked', false);
        $('#seleccionar-todos').prop('checked', false);
        actualizarResumen();
    });

    // Confirmar asignación
    $('#btn-confirmar').on('click', function() {
        const estudianteId = $('#estudiante-seleccionado-id').val();

        if (!estudianteId) {
            Swal.fire('Error', 'Debe seleccionar un estudiante', 'warning');
            return;
        }

        if (cursosSeleccionados.length === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos un curso', 'warning');
            return;
        }

        const docenteId = $('#docente-asignado').val();

        Swal.fire({
            title: '¿Confirmar asignación?',
            html: `Se asignarán <strong>${cursosSeleccionados.length}</strong> curso(s) al estudiante <strong>${estudianteSeleccionado.nombre_completo}</strong>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let postData = {
                    _token: '{{ csrf_token() }}',
                    estudiante_id: estudianteId,
                    cursos: cursosSeleccionados
                };
                if (docenteId) {
                    postData.docente_id = docenteId;
                }
                $.ajax({
                    url: '{{ route("configuracion.asignacion-cursos.asignar") }}',
                    method: 'POST',
                    data: postData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Asignación exitosa!',
                                text: response.message,
                                timer: 3000,
                                showConfirmButton: false
                            });

                            // Limpiar selección y recargar
                            cursosSeleccionados = [];
                            $('#seleccionar-todos').prop('checked', false);
                            actualizarResumen();
                            seleccionarEstudiante(estudianteId);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error al asignar cursos';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Asignar curso a todos los estudiantes
    $(document).on('click', '.btn-asignar-todos', function() {
        const cursoId = $(this).data('curso-id');
        const cursoTitulo = $(this).data('curso-titulo');

        Swal.fire({
            title: '¿Asignar a todos los estudiantes?',
            html: `<p>Se asignará el curso <strong>"${cursoTitulo}"</strong> a <strong>todos los usuarios con rol Estudiante</strong>.</p>
                   <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta acción puede tomar unos segundos dependiendo de la cantidad de estudiantes.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-users"></i> Sí, asignar a todos',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                let postDataMasivo = {
                    _token: '{{ csrf_token() }}',
                    curso_id: cursoId
                };
                const docenteIdMasivo = $('#docente-asignado').val();
                if (docenteIdMasivo) {
                    postDataMasivo.docente_id = docenteIdMasivo;
                }
                return $.ajax({
                    url: '{{ route("configuracion.asignacion-cursos.asignar-todos") }}',
                    method: 'POST',
                    data: postDataMasivo
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        error.responseJSON?.message || 'Error al asignar el curso'
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Asignación masiva exitosa!',
                    html: result.value.message,
                    timer: 4000,
                    showConfirmButton: true
                });
                
                // Recargar cursos si hay estudiante seleccionado
                const estudianteId = $('#estudiante-seleccionado-id').val();
                if (estudianteId) {
                    seleccionarEstudiante(estudianteId);
                }
            }
        });
    });

    // Cerrar resultados al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscar-estudiante, #resultados-busqueda').length) {
            $('#resultados-busqueda').hide();
        }
    });
});
</script>
@stop
