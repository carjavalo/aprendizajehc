@extends('adminlte::page')

@section('title', $curso->titulo . ' - Classroom')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1><i class="fas fa-chalkboard-teacher"></i> {{ $curso->titulo }}</h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-layer-group"></i> {{ $curso->area_descripcion }} • 
                    <i class="fas fa-user-tie"></i> {{ $curso->instructor_nombre }} • 
                    <i class="fas fa-users"></i> {{ $curso->estudiantes_count }} estudiantes
                </p>
            </div>
            <div class="col-sm-4">
                <div class="float-sm-right">
                    <a href="{{ route('capacitaciones.cursos.index') }}" class="btn btn-secondary mb-2">
                        <i class="fas fa-arrow-left"></i> Atrás
                    </a>
                </div>
                <ol class="breadcrumb float-sm-right" style="clear: both;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('capacitaciones.cursos.index') }}">Cursos</a></li>
                    <li class="breadcrumb-item active">{{ $curso->titulo }}</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Header del Curso con Imagen de Portada -->
    <div class="card card-widget widget-user">
        <div class="widget-user-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 200px; position: relative;">
            @if($curso->imagen_portada)
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('{{ $curso->imagen_portada_url }}'); background-size: cover; background-position: center; opacity: 0.3;"></div>
            @endif
            <div style="position: relative; z-index: 1;">
                <h3 class="widget-user-username">{{ $curso->titulo }}</h3>
                <h5 class="widget-user-desc">{{ $curso->area_descripcion }}</h5>
                <p class="mt-3">{{ $curso->descripcion }}</p>
                
                <div class="row mt-4">
                    <div class="col-md-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-white"><i class="fas fa-users"></i></span>
                            <h5 class="description-header text-white">{{ $curso->estudiantes_count }}</h5>
                            <span class="description-text text-white">ESTUDIANTES</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-white"><i class="fas fa-file-alt"></i></span>
                            <h5 class="description-header text-white">{{ $curso->materiales->count() }}</h5>
                            <span class="description-text text-white">MATERIALES</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-white"><i class="fas fa-tasks"></i></span>
                            <h5 class="description-header text-white">{{ $curso->actividades->count() }}</h5>
                            <span class="description-text text-white">ACTIVIDADES</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="description-block">
                            <span class="description-percentage text-white"><i class="fas fa-comments"></i></span>
                            <h5 class="description-header text-white">{{ $curso->foros->count() }}</h5>
                            <span class="description-text text-white">DISCUSIONES</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-sm-6">
                    <div class="description-block">
                        <h5 class="description-header">{{ $curso->codigo_acceso }}</h5>
                        <span class="description-text">CÓDIGO DE ACCESO</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    @if(!$esEstudiante && !$esInstructor)
                        <button class="btn btn-primary btn-lg float-right" id="btn-inscribirse">
                            <i class="fas fa-user-plus"></i> Inscribirse al Curso
                        </button>
                    @elseif($esEstudiante)
                        <div class="progress float-right" style="width: 200px; margin-top: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progreso }}%" aria-valuenow="{{ $progreso }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $progreso }}% Completado
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por Pestañas -->
    <div class="card">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="inicio-tab" data-toggle="pill" href="#inicio" role="tab" aria-controls="inicio" aria-selected="true">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="materiales-tab" data-toggle="pill" href="#materiales" role="tab" aria-controls="materiales" aria-selected="false">
                        <i class="fas fa-folder-open"></i> Materiales <span class="badge badge-info">{{ $curso->materiales->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="foros-tab" data-toggle="pill" href="#foros" role="tab" aria-controls="foros" aria-selected="false">
                        <i class="fas fa-comments"></i> Foros <span class="badge badge-primary">{{ $curso->foros->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="actividades-tab" data-toggle="pill" href="#actividades" role="tab" aria-controls="actividades" aria-selected="false">
                        <i class="fas fa-tasks"></i> Actividades <span class="badge badge-warning">{{ $curso->actividades->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="participantes-tab" data-toggle="pill" href="#participantes" role="tab" aria-controls="participantes" aria-selected="false">
                        <i class="fas fa-users"></i> Participantes <span class="badge badge-success">{{ $curso->estudiantes_count + 1 }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-one-tabContent">
                <!-- Pestaña Inicio -->
                <div class="tab-pane fade show active" id="inicio" role="tabpanel" aria-labelledby="inicio-tab">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Anuncios Recientes -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-bullhorn"></i> Anuncios Recientes</h3>
                                </div>
                                <div class="card-body">
                                    @forelse($curso->foros->where('es_anuncio', true)->take(3) as $anuncio)
                                        <div class="post">
                                            <div class="user-block">
                                                <i class="fas fa-user-circle fa-2x text-secondary" style="margin-right: 10px;"></i>
                                                <span class="username">
                                                    <a href="#">{{ $anuncio->usuario_nombre }}</a>
                                                    <span class="badge badge-warning">Anuncio</span>
                                                </span>
                                                <span class="description">{{ $anuncio->fecha_formateada }}</span>
                                            </div>
                                            <h5>{{ $anuncio->titulo }}</h5>
                                            <p>{{ Str::limit($anuncio->contenido, 200) }}</p>
                                        </div>
                                        @if(!$loop->last)<hr>@endif
                                    @empty
                                        <p class="text-muted text-center">
                                            <i class="fas fa-info-circle"></i> No hay anuncios recientes
                                        </p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Actividades Próximas -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Próximas Actividades</h3>
                                </div>
                                <div class="card-body">
                                    @forelse($curso->actividades->where('estado', 'abierta')->take(3) as $actividad)
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="{{ $actividad->tipo_icon }}"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ $actividad->titulo }}</span>
                                                <span class="info-box-number">{{ $actividad->tiempo_restante }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" style="width: 70%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    Fecha límite: {{ $actividad->fecha_cierre_formateada }}
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center">
                                            <i class="fas fa-tasks"></i> No hay actividades próximas
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Información del Curso -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Curso</h3>
                                </div>
                                <div class="card-body">
                                    <strong><i class="fas fa-calendar-alt mr-1"></i> Duración</strong>
                                    <p class="text-muted">
                                        @if($curso->duracion_horas)
                                            {{ $curso->duracion_horas }} horas académicas
                                        @else
                                            No especificada
                                        @endif
                                    </p>
                                    <hr>

                                    <strong><i class="fas fa-calendar-check mr-1"></i> Fechas</strong>
                                    <p class="text-muted">
                                        <strong>Inicio:</strong> {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'No definida' }}<br>
                                        <strong>Fin:</strong> {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'No definida' }}
                                    </p>
                                    <hr>

                                    @if($curso->objetivos)
                                        <strong><i class="fas fa-bullseye mr-1"></i> Objetivos</strong>
                                        <p class="text-muted">{{ $curso->objetivos }}</p>
                                        <hr>
                                    @endif

                                    @if($curso->requisitos)
                                        <strong><i class="fas fa-list-check mr-1"></i> Requisitos</strong>
                                        <p class="text-muted">{{ $curso->requisitos }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Progreso del Estudiante -->
                            @if($esEstudiante)
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-chart-line"></i> Tu Progreso</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" style="width: {{ $progreso }}%" aria-valuenow="{{ $progreso }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $progreso }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Has completado {{ $progreso }}% del curso
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Otras pestañas se cargarán dinámicamente -->
                <div class="tab-pane fade" id="materiales" role="tabpanel" aria-labelledby="materiales-tab">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando materiales...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="foros" role="tabpanel" aria-labelledby="foros-tab">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando foros...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="actividades" role="tabpanel" aria-labelledby="actividades-tab">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando actividades...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="participantes" role="tabpanel" aria-labelledby="participantes-tab">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando participantes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .widget-user-header {
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
        }
        
        .post {
            border-bottom: 1px solid #f4f4f4;
            margin-bottom: 15px;
            padding-bottom: 15px;
        }
        
        .post:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .user-block {
            margin-bottom: 15px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
        }
        
        .nav-tabs .nav-link.active {
            border-bottom-color: #007bff;
            background-color: transparent;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ============================================
        // VARIABLES GLOBALES PARA EDICIÓN DE ACTIVIDADES
        // ============================================
        window.editQuestions = [];
        window.editQuestionCounter = 0;
        window.editOptionCounters = {};
        window.optionLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        window.materialesDisponibles = [];
        window.actividadesDisponibles = [];
        window.cursoId = {{ $curso->id }};
        window.csrfToken = '{{ csrf_token() }}';

        // ============================================
        // FUNCIÓN PARA CARGAR DATOS DISPONIBLES
        // ============================================
        window.cargarDatosDisponibles = function() {
            if (window.materialesDisponibles.length === 0 || window.actividadesDisponibles.length === 0) {
                $.ajax({
                    url: '/capacitaciones/cursos/' + window.cursoId + '/classroom/datos-disponibles',
                    type: 'GET',
                    async: false,
                    success: function(response) {
                        if (response.success) {
                            window.materialesDisponibles = response.materiales || [];
                            window.actividadesDisponibles = response.actividades || [];
                        }
                    }
                });
            }
        };

        // ============================================
        // FUNCIÓN PRINCIPAL PARA EDITAR ACTIVIDAD
        // ============================================
        window.editarActividadCompleta = function(actividadId, actividad) {
            console.log('=== FUNCIÓN editarActividadCompleta ===');
            
            if (!actividad || typeof actividad !== 'object' || !actividad.tipo) {
                Swal.fire('Error', 'No se pudieron cargar los datos de la actividad', 'error');
                return;
            }
            
            // Asegurar que contenido_json sea un objeto
                        let contenidoJson = actividad.contenido_json || {};
            if (typeof contenidoJson === 'string') {
                try {
                    contenidoJson = JSON.parse(contenidoJson);
                    if (typeof contenidoJson === 'string') {
                        contenidoJson = JSON.parse(contenidoJson);
                    }
                } catch(e) {
                    console.error('No se pudo parsear contenido_json', e);
                    contenidoJson = {};
                }
            }

            const tipo = actividad.tipo;
            const requierePreguntas = tipo === 'quiz' || tipo === 'evaluacion';
            const typeLabels = { tarea: 'Tarea', quiz: 'Quiz', evaluacion: 'Evaluación', proyecto: 'Proyecto' };
            const tipoLabel = typeLabels[tipo] || 'Actividad';
            
            window.cargarDatosDisponibles();
            
            // Generar opciones de materiales
            let materialesOptions = '<option value="">-- Seleccionar material --</option>';
            window.materialesDisponibles.forEach(mat => {
                const selected = actividad.material_id === mat.id ? 'selected' : '';
                materialesOptions += `<option value="${mat.id}" ${selected}>${mat.titulo}</option>`;
            });
            
            // Formatear fechas
            let fechaApertura = actividad.fecha_apertura ? actividad.fecha_apertura.substring(0, 16) : '';
            let fechaCierre = actividad.fecha_cierre ? actividad.fecha_cierre.substring(0, 16) : '';
            
            const tituloEscapado = String(actividad.titulo || '').replace(/"/g, '&quot;');
            const descripcionEscapada = String(actividad.descripcion || '').replace(/"/g, '&quot;');
            const instruccionesEscapadas = String(actividad.instrucciones || '').replace(/"/g, '&quot;');
            
            // Campos para Quiz
            const quizFields = requierePreguntas ? `
                <hr class="my-3">
                <h5 class="text-primary"><i class="fas fa-list-ol"></i> Preguntas de la ${tipoLabel}</h5>
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle"></i> <strong>Nota máxima: 5.0</strong> — Cada pregunta tiene un <strong>porcentaje (%)</strong>. La suma de los porcentajes no puede exceder <strong>100%</strong>.<br>
                    <small>La nota se calcula así: si una respuesta es correcta, su porcentaje se multiplica por 5. Si es incorrecta, por 0.</small>
                </div>
                <div class="form-group">
                    <label>Porcentaje total asignado:</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" id="edit-quiz-points-progress" style="width: 0%">0% / 100%</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-actividad-duration">Duración (minutos)</label>
                    <input type="number" class="form-control" id="edit-actividad-duration" min="5" max="180" value="${contenidoJson.duration || 30}">
                    <small class="form-text text-muted">Tiempo máximo para completar</small>
                </div>
                <div class="card border-success mb-3">
                    <div class="card-header bg-success text-white py-2">
                        <strong><i class="fas fa-shield-alt"></i> Configuración Anti-fraude (Banco de Preguntas)</strong>
                    </div>
                    <div class="card-body py-2">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="edit-randomize-order" ${(contenidoJson.quizConfig?.randomizeOrder) ? 'checked' : ''}>
                            <label class="custom-control-label" for="edit-randomize-order">
                                <i class="fas fa-random text-info"></i> Aleatorizar orden de preguntas
                            </label>
                        </div>
                        <hr class="my-2">
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="edit-enable-bank" onchange="toggleEditBankConfig()" ${(contenidoJson.quizConfig?.enableQuestionBank) ? 'checked' : ''}>
                            <label class="custom-control-label" for="edit-enable-bank">
                                <i class="fas fa-database text-warning"></i> Habilitar Banco de Preguntas
                            </label>
                        </div>
                        <div id="edit-bank-details" style="display: ${(contenidoJson.quizConfig?.enableQuestionBank) ? 'block' : 'none'};">
                            <div class="form-group mt-3">
                                <label><i class="fas fa-tasks"></i> Preguntas por intento *</label>
                                <input type="number" class="form-control" id="edit-questions-per-attempt" min="1" value="${contenidoJson.quizConfig?.questionsPerAttempt || 5}" oninput="updateEditBankInfo()">
                                <small class="form-text text-muted" id="edit-bank-info-text"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="edit-actividad-questions-container"></div>
                <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="window.addEditQuestion()" id="add-edit-question-btn">
                    <i class="fas fa-plus"></i> Agregar Pregunta</button>
                <small class="form-text text-muted text-center d-block mt-2">
                    <i class="fas fa-info-circle"></i> Cada pregunta puede tener de 2 a 10 opciones. Marca las correctas.
                </small>` : '';
            
            Swal.fire({
                title: `<i class="fas fa-edit"></i> Modificar Actividad`,
                html: `<div class="text-left">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" class="form-control" id="edit-actividad-titulo" value="${tituloEscapado}">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" id="edit-actividad-descripcion" rows="2">${descripcionEscapada}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Instrucciones</label>
                        <textarea class="form-control" id="edit-actividad-instrucciones" rows="2">${instruccionesEscapadas}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Apertura</label>
                                <input type="datetime-local" class="form-control" id="edit-actividad-fecha-apertura" value="${fechaApertura}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Cierre</label>
                                <input type="datetime-local" class="form-control" id="edit-actividad-fecha-cierre" value="${fechaCierre}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Material</label>
                                <select class="form-control" id="edit-actividad-material">${materialesOptions}</select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Porcentaje (%)</label>
                                <input type="number" class="form-control" id="edit-actividad-porcentaje" min="0" max="100" step="0.1" value="${actividad.porcentaje_curso || 0}">
                            </div>
                        </div>
                    </div>
                    ${quizFields}
                </div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> Guardar Cambios',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                confirmButtonColor: '#28a745',
                width: '900px',
                didOpen: () => {
                    console.log('Modal opened. contenidoJson:', contenidoJson);
                    window.editQuestions = [];
                    window.editQuestionCounter = 0;
                    window.editOptionCounters = {};

                    // Funciones de porcentaje y antifraude para el modal
                    window.calcularPorcentajeTotalQuizEdit = function() {
                        let total = 0;
                        document.querySelectorAll('.edit-question-points-input').forEach(input => {
                            total += parseFloat(input.value) || 0;
                        });
                        return total;
                    };
                    
                    window.actualizarPorcentajeDisponibleQuizEdit = function() {
                        const porcentajeUsado = calcularPorcentajeTotalQuizEdit();
                        const progressBar = document.getElementById('edit-quiz-points-progress');
                        if (progressBar) {
                            const width = Math.min(100, porcentajeUsado);
                            progressBar.style.width = width + '%';
                            progressBar.textContent = porcentajeUsado.toFixed(1) + '% / 100%';
                            progressBar.className = 'progress-bar ' + (porcentajeUsado > 100 ? 'bg-danger' : 'bg-success');
                        }
                        const porcentajeDisponible = Math.max(0, 100 - porcentajeUsado).toFixed(1);
                        document.querySelectorAll('.edit-puntos-disponibles').forEach(span => {
                            span.textContent = porcentajeDisponible;
                        });
                        const addBtn = document.getElementById('add-edit-question-btn');
                        if (addBtn) addBtn.disabled = porcentajeUsado >= 100;
                    };
                    
                    window.toggleEditBankConfig = function() {
                        const enabled = document.getElementById('edit-enable-bank').checked;
                        document.getElementById('edit-bank-details').style.display = enabled ? 'block' : 'none';
                        if (enabled) {
                            document.getElementById('edit-randomize-order').checked = true;
                            updateEditBankInfo();
                        }
                    };
                    
                    window.updateEditBankInfo = function() {
                        const totalQuestions = window.editQuestions.length;
                        const perAttempt = parseInt(document.getElementById('edit-questions-per-attempt')?.value) || 1;
                        const infoText = document.getElementById('edit-bank-info-text');
                        if (infoText) {
                            if (totalQuestions > 0) {
                                if (perAttempt >= totalQuestions) {
                                    infoText.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Debe ser menor que el total (' + totalQuestions + ').</span>';
                                } else {
                                    infoText.innerHTML = 'Se seleccionarán <strong>' + perAttempt + '</strong> de <strong>' + totalQuestions + '</strong> preguntas.';
                                }
                            }
                        }
                    };

                    if (requierePreguntas && contenidoJson && contenidoJson.questions) {
                        let questionsArray = contenidoJson.questions;
                        
                        // Asegurar que sea un arreglo iterativo
                        if (!Array.isArray(questionsArray)) {
                           console.log('questions is not array, trying to parse o convert...');
                           if (typeof questionsArray === 'string') {
                               try { questionsArray = JSON.parse(questionsArray); } catch(e) {}
                           } else if (typeof questionsArray === 'object' && questionsArray !== null) {
                               questionsArray = Object.values(questionsArray);
                           }
                        }
                        
                        if (!Array.isArray(questionsArray)) {
                            questionsArray = [];
                        }

                        console.log('Loading questions:', questionsArray);
                        questionsArray.forEach(q => {
                            try {
                                window.loadEditQuestion(q);
                            } catch(e) {
                                console.error('Error loading question', q, e);
                            }
                        });
                        setTimeout(() => actualizarPorcentajeDisponibleQuizEdit(), 100);
                    } else {
                        console.log('No questions to load or requierePreguntas is false');
                    }
                },
                preConfirm: () => {
                    const titulo = document.getElementById('edit-actividad-titulo').value;
                    if (!titulo.trim()) { Swal.showValidationMessage('El título es requerido'); return false; }
                    
                    let quizData = null;
                    if (requierePreguntas && window.editQuestions.length > 0) {
                        const duration = document.getElementById('edit-actividad-duration').value;
                        const questions = [];
                        let totalQuestionPoints = 0;
                        
                        for (const qId of window.editQuestions) {
                            const qText = document.getElementById(`edit-question-text-${qId}`).value;
                            const qPoints = parseFloat(document.getElementById(`edit-question-points-${qId}`).value) || 0;
                            if (!qText.trim()) { Swal.showValidationMessage('Todas las preguntas deben tener texto'); return false; }
                            if (qPoints <= 0) { Swal.showValidationMessage('Cada pregunta debe tener un porcentaje mayor a 0%'); return false; }
                            if (qPoints > 100) { Swal.showValidationMessage('El porcentaje de cada pregunta no puede exceder 100%'); return false; }
                            
                            const optContainer = document.getElementById(`edit-options-container-${qId}`);
                            const optRows = optContainer.querySelectorAll('.option-row');
                            const options = {};
                            const correctAnswers = [];
                            let hasEmptyOption = false;
                            optRows.forEach((row, idx) => {
                                const letter = window.optionLetters[idx];
                                const textInput = row.querySelector('input[type="text"]');
                                const checkbox = row.querySelector('input[type="checkbox"]');
                                if (!textInput.value.trim()) hasEmptyOption = true;
                                options[letter] = textInput.value;
                                if (checkbox && checkbox.checked) correctAnswers.push(letter);
                            });
                            if (hasEmptyOption) { Swal.showValidationMessage('Todas las opciones deben tener texto'); return false; }
                            if (correctAnswers.length === 0) { Swal.showValidationMessage('Cada pregunta debe tener al menos una respuesta correcta'); return false; }
                            
                            totalQuestionPoints += qPoints;
                            questions.push({ id: qId, text: qText, points: qPoints, options, correctAnswers, isMultipleChoice: correctAnswers.length > 1 });
                        }
                        
                        if (totalQuestionPoints > 100) {
                            Swal.showValidationMessage('La suma de porcentajes no puede exceder 100% (actual: ' + totalQuestionPoints.toFixed(1) + '%)');
                            return false;
                        }
                        
                        const editEnableBank = document.getElementById('edit-enable-bank')?.checked || false;
                        const editRandomizeOrder = document.getElementById('edit-randomize-order')?.checked || false;
                        const editQuestionsPerAttempt = parseInt(document.getElementById('edit-questions-per-attempt')?.value) || questions.length;
                        
                        if (editEnableBank && editQuestionsPerAttempt >= questions.length) {
                            Swal.showValidationMessage('Las preguntas por intento (' + editQuestionsPerAttempt + ') deben ser menos que el total del banco (' + questions.length + ').');
                            return false;
                        }
                        
                        quizData = { 
                            duration: parseInt(duration), 
                            questions: questions,
                            totalPoints: totalQuestionPoints,
                            quizConfig: {
                                enableQuestionBank: editEnableBank,
                                questionsPerAttempt: editEnableBank ? editQuestionsPerAttempt : questions.length,
                                randomizeOrder: editRandomizeOrder
                            }
                        };
                    }
                    
                    return {
                        titulo,
                        descripcion: document.getElementById('edit-actividad-descripcion').value,
                        instrucciones: document.getElementById('edit-actividad-instrucciones').value,
                        fecha_apertura: document.getElementById('edit-actividad-fecha-apertura').value,
                        fecha_cierre: document.getElementById('edit-actividad-fecha-cierre').value,
                        material_id: document.getElementById('edit-actividad-material').value || null,
                        porcentaje_curso: parseFloat(document.getElementById('edit-actividad-porcentaje').value) || 0,
                        contenido_json: quizData,
                        nota_minima_aprobacion: parseFloat(document.getElementById('edit-actividad-nota-minima')?.value) || 3.0
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) window.actualizarActividadCompleta(actividadId, result.value);
            });
        };

        // ============================================
        // FUNCIÓN PARA ENVIAR ACTUALIZACIÓN
        // ============================================
        window.actualizarActividadCompleta = function(actividadId, data) {
            Swal.fire({ title: 'Guardando...', html: '<i class="fas fa-spinner fa-spin fa-2x"></i>', showConfirmButton: false, allowOutsideClick: false });
            
            $.ajax({
                url: `/capacitaciones/cursos/${window.cursoId}/classroom/actividades/${actividadId}/actualizar`,
                type: 'PUT',
                data: { _token: window.csrfToken, ...data },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Guardado!', timer: 1500, showConfirmButton: false }).then(() => {
                            if (typeof window.loadTabContent === 'function') window.loadTabContent('actividades', '#actividades');
                            else location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Error al guardar', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            });
        };

        // ============================================
        // FUNCIÓN PRINCIPAL PARA EDITAR MATERIAL
        // ============================================
        window.editarMaterialCompleto = function(materialId, material) {
            console.log('=== FUNCIÓN editarMaterialCompleto ===', material);
            
            if (!material || typeof material !== 'object') {
                Swal.fire('Error', 'No se pudieron cargar los datos del material', 'error');
                return;
            }
            
            const tipoOptions = [
                { value: 'documento', label: 'Documento', icon: 'fa-file-alt' },
                { value: 'video', label: 'Video', icon: 'fa-video' },
                { value: 'imagen', label: 'Imagen', icon: 'fa-image' },
                { value: 'archivo', label: 'Archivo', icon: 'fa-file' }
            ];
            
            let tipoSelect = '';
            tipoOptions.forEach(opt => {
                const selected = material.tipo === opt.value ? 'selected' : '';
                tipoSelect += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
            });
            
            // Generar opciones de prerequisitos (otros materiales)
            let prerequisitoOptions = '<option value="">-- Sin prerrequisito --</option>';
            window.materialesDisponibles.forEach(mat => {
                if (mat.id !== material.id) {
                    const selected = material.prerequisite_id === mat.id ? 'selected' : '';
                    prerequisitoOptions += `<option value="${mat.id}" ${selected}>${mat.titulo}</option>`;
                }
            });
            
            const tituloEscapado = (material.titulo || '').replace(/"/g, '&quot;');
            const descripcionEscapada = (material.descripcion || '').replace(/"/g, '&quot;');
            const urlExternaEscapada = (material.url_externa || '').replace(/"/g, '&quot;');
            const archivoActual = material.archivo_nombre ? `<small class="text-muted d-block mb-2"><i class="fas fa-file"></i> Archivo actual: ${material.archivo_nombre}</small>` : '';
            
            Swal.fire({
                title: `<i class="fas fa-edit"></i> Modificar Material`,
                html: `<div class="text-left">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" class="form-control" id="edit-material-titulo" value="${tituloEscapado}">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" id="edit-material-descripcion" rows="2">${descripcionEscapada}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo *</label>
                                <select class="form-control" id="edit-material-tipo">${tipoSelect}</select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Porcentaje del curso (%)</label>
                                <input type="number" class="form-control" id="edit-material-porcentaje" min="0" max="100" step="0.1" value="${material.porcentaje_curso || 0}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>URL Externa (opcional)</label>
                        <input type="url" class="form-control" id="edit-material-url-externa" value="${urlExternaEscapada}" placeholder="https://...">
                        <small class="text-muted">Para videos de YouTube, enlaces externos, etc.</small>
                    </div>
                    <div class="form-group">
                        <label>Nuevo Archivo (opcional)</label>
                        ${archivoActual}
                        <input type="file" class="form-control-file" id="edit-material-archivo">
                        <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" class="form-control" id="edit-material-orden" min="0" value="${material.orden || 0}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prerrequisito</label>
                                <select class="form-control" id="edit-material-prerequisito">${prerequisitoOptions}</select>
                            </div>
                        </div>
                    </div>
                </div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> Guardar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                confirmButtonColor: '#28a745',
                width: '700px',
                preConfirm: () => {
                    const titulo = document.getElementById('edit-material-titulo').value;
                    if (!titulo.trim()) { Swal.showValidationMessage('El título es requerido'); return false; }
                    
                    const tipo = document.getElementById('edit-material-tipo').value;
                    if (!tipo) { Swal.showValidationMessage('El tipo es requerido'); return false; }
                    
                    return {
                        titulo,
                        descripcion: document.getElementById('edit-material-descripcion').value,
                        tipo,
                        porcentaje_curso: parseFloat(document.getElementById('edit-material-porcentaje').value) || 0,
                        url_externa: document.getElementById('edit-material-url-externa').value,
                        orden: parseInt(document.getElementById('edit-material-orden').value) || 0,
                        prerequisite_id: document.getElementById('edit-material-prerequisito').value || null,
                        archivo: document.getElementById('edit-material-archivo').files[0] || null
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) window.actualizarMaterialCompleto(materialId, result.value);
            });
        };

        // ============================================
        // FUNCIÓN PARA ENVIAR ACTUALIZACIÓN DE MATERIAL
        // ============================================
        window.actualizarMaterialCompleto = function(materialId, data) {
            Swal.fire({ title: 'Guardando...', html: '<i class="fas fa-spinner fa-spin fa-2x"></i>', showConfirmButton: false, allowOutsideClick: false });
            
            // Usar FormData para soportar archivos
            const formData = new FormData();
            formData.append('_token', window.csrfToken);
            formData.append('_method', 'PUT');
            formData.append('titulo', data.titulo);
            formData.append('descripcion', data.descripcion || '');
            formData.append('tipo', data.tipo);
            formData.append('porcentaje_curso', data.porcentaje_curso || 0);
            formData.append('url_externa', data.url_externa || '');
            formData.append('orden', data.orden || 0);
            if (data.prerequisite_id) {
                formData.append('prerequisite_id', data.prerequisite_id);
            }
            if (data.archivo) {
                formData.append('archivo', data.archivo);
            }
            
            $.ajax({
                url: `/capacitaciones/cursos/${window.cursoId}/classroom/materiales/${materialId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Guardado!', timer: 1500, showConfirmButton: false }).then(() => {
                            if (typeof window.loadTabContent === 'function') window.loadTabContent('materiales', '#materiales');
                            else location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Error al guardar', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    let errorMsg = 'Error al guardar';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                        if (xhr.responseJSON.errors) {
                            const errores = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errores.join('<br>');
                        }
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        };

        // ============================================
        // FUNCIONES PARA PREGUNTAS
        // ============================================
        window.loadEditQuestion = function(question) {
            const qId = ++window.editQuestionCounter;
            const container = document.getElementById('edit-actividad-questions-container');
            window.editOptionCounters[qId] = 0;
            
            const qHtml = `<div class="card mb-2" id="edit-question-${qId}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Pregunta ${window.editQuestions.length + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="window.removeEditQuestion(${qId})"><i class="fas fa-trash"></i></button>
                    </div>
                    <input type="text" class="form-control mb-2" id="edit-question-text-${qId}" value="${String(question.text || '').replace(/"/g, '&quot;')}" placeholder="Texto de la pregunta">
                    <input type="number" class="form-control mb-2" id="edit-question-points-${qId}" min="0" max="5" step="0.1" value="${question.points || 1}" placeholder="Puntos">
                    <div id="edit-options-container-${qId}"></div>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="window.addEditQuestionOption(${qId})"><i class="fas fa-plus"></i> Opción</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', qHtml);
            window.editQuestions.push(qId);
            
            if (question.options) {
                const correctAnswers = question.correctAnswers || [];
                Object.keys(question.options).forEach(letter => {
                    window.addEditQuestionOptionWithData(qId, letter, question.options[letter], correctAnswers.includes(letter));
                });
            }
        };

        window.addEditQuestion = function() {
            const qId = ++window.editQuestionCounter;
            const container = document.getElementById('edit-actividad-questions-container');
            window.editOptionCounters[qId] = 0;
            
            const qHtml = `<div class="card mb-2" id="edit-question-${qId}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Pregunta ${window.editQuestions.length + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="window.removeEditQuestion(${qId})"><i class="fas fa-trash"></i></button>
                    </div>
                    <input type="text" class="form-control mb-2" id="edit-question-text-${qId}" placeholder="Texto de la pregunta">
                    <input type="number" class="form-control mb-2" id="edit-question-points-${qId}" min="0" max="5" step="0.1" value="1" placeholder="Puntos">
                    <div id="edit-options-container-${qId}"></div>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="window.addEditQuestionOption(${qId})"><i class="fas fa-plus"></i> Opción</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', qHtml);
            window.editQuestions.push(qId);
            window.addEditQuestionOption(qId);
            window.addEditQuestionOption(qId);
        };

        window.removeEditQuestion = function(qId) {
            document.getElementById(`edit-question-${qId}`).remove();
            window.editQuestions = window.editQuestions.filter(id => id !== qId);
        };

        window.addEditQuestionOptionWithData = function(qId, letter, text, isCorrect) {
            const container = document.getElementById(`edit-options-container-${qId}`);
            const optHtml = `<div class="input-group mb-1 option-row">
                <div class="input-group-prepend">
                    <div class="input-group-text"><input type="checkbox" ${isCorrect ? 'checked' : ''}></div>
                    <span class="input-group-text"><strong>${letter}</strong></span>
                </div>
                <input type="text" class="form-control" value="${String(text || '').replace(/"/g, '&quot;')}">
            </div>`;
            container.insertAdjacentHTML('beforeend', optHtml);
        };

        window.addEditQuestionOption = function(qId) {
            const container = document.getElementById(`edit-options-container-${qId}`);
            const count = container.querySelectorAll('.option-row').length;
            if (count >= 10) return;
            const letter = window.optionLetters[count];
            const optHtml = `<div class="input-group mb-1 option-row">
                <div class="input-group-prepend">
                    <div class="input-group-text"><input type="checkbox"></div>
                    <span class="input-group-text"><strong>${letter}</strong></span>
                </div>
                <input type="text" class="form-control" placeholder="Opción ${letter}">
            </div>`;
            container.insertAdjacentHTML('beforeend', optHtml);
        };

        $(document).ready(function() {
            // ============================================
            // EVENTO CLICK PARA BOTÓN EDITAR ACTIVIDAD
            // ============================================
            $(document).on('click', '.btn-editar-actividad', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var actividadId = $(this).data('actividad-id');
                console.log('=== CLICK EDITAR ACTIVIDAD ===', actividadId);
                
                if (!actividadId) {
                    Swal.fire('Error', 'No se pudo identificar la actividad', 'error');
                    return;
                }
                
                Swal.fire({ title: 'Cargando...', html: '<i class="fas fa-spinner fa-spin fa-2x"></i>', showConfirmButton: false, allowOutsideClick: false });
                
                $.ajax({
                    url: '/capacitaciones/cursos/' + window.cursoId + '/classroom/actividades/' + actividadId + '/obtener',
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.success && response.actividad) {
                            window.editarActividadCompleta(actividadId, response.actividad);
                        } else {
                            Swal.fire('Error', 'No se pudo cargar la actividad', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar', 'error');
                    }
                });
            });

            // ============================================
            // EVENTO CHANGE PARA TOGGLE DE ACTIVIDADES
            // ============================================
            $(document).on('change', '.toggle-actividad', function() {
                const checkbox = $(this);
                const actividadId = checkbox.data('actividad-id');
                const habilitado = checkbox.is(':checked');
                
                $.ajax({
                    url: `/capacitaciones/cursos/${window.cursoId}/classroom/actividades/${actividadId}/toggle`,
                    type: 'POST',
                    data: { _token: window.csrfToken },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        } else {
                            checkbox.prop('checked', !habilitado);
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        checkbox.prop('checked', !habilitado);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
                    }
                });
            });

            // ============================================
            // EVENTO CLICK PARA BOTÓN EDITAR MATERIAL
            // ============================================
            $(document).on('click', '.btn-editar-material', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var materialId = $(this).data('material-id');
                console.log('=== CLICK EDITAR MATERIAL ===', materialId);
                
                if (!materialId) {
                    Swal.fire('Error', 'No se pudo identificar el material', 'error');
                    return;
                }
                
                Swal.fire({ title: 'Cargando...', html: '<i class="fas fa-spinner fa-spin fa-2x"></i>', showConfirmButton: false, allowOutsideClick: false });
                
                $.ajax({
                    url: '/capacitaciones/cursos/' + window.cursoId + '/classroom/materiales/' + materialId + '/obtener',
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        if (response.success && response.material) {
                            window.editarMaterialCompleto(materialId, response.material);
                        } else {
                            Swal.fire('Error', 'No se pudo cargar el material', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar', 'error');
                    }
                });
            });

            // Inscribirse al curso
            $('#btn-inscribirse').click(function() {
                Swal.fire({
                    title: '¿Inscribirse al curso?',
                    text: "Te inscribirás en: {{ $curso->titulo }}",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, inscribirme',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("capacitaciones.cursos.classroom.inscribir", $curso->id) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('¡Inscrito!', response.message, 'success').then(() => {
                                        location.reload();
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
            });

            // Cargar contenido de pestañas dinámicamente
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr("href");
                const tabName = target.replace('#', '');
                
                if (tabName !== 'inicio' && $(target).find('.fa-spinner').length > 0) {
                    loadTabContent(tabName, target);
                }
            });

        });

        // Función global para cargar contenido de pestañas
        window.loadTabContent = function(tabName, target) {
            // Agregar timestamp para forzar bypass de caché
            const timestamp = Date.now();
            const urls = {
                'materiales': '{{ route("capacitaciones.cursos.classroom.materiales", $curso->id) }}?v=' + timestamp,
                'foros': '{{ route("capacitaciones.cursos.classroom.foros", $curso->id) }}?v=' + timestamp,
                'actividades': '{{ route("capacitaciones.cursos.classroom.actividades", $curso->id) }}?v=' + timestamp,
                'participantes': '{{ route("capacitaciones.cursos.classroom.participantes", $curso->id) }}?v=' + timestamp
            };

            if (urls[tabName]) {
                // Usar XMLHttpRequest nativo en lugar de jQuery para evitar parsing automático
                const xhr = new XMLHttpRequest();
                xhr.open('GET', urls[tabName], true);
                xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
                xhr.setRequestHeader('Pragma', 'no-cache');
                xhr.setRequestHeader('Expires', '0');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Obtener el elemento target usando DOM nativo
                        const targetElement = document.querySelector(target);
                        if (targetElement) {
                            // Limpiar contenido existente
                            targetElement.innerHTML = '';
                            
                            // Crear un parser temporal para extraer solo el HTML sin scripts problemáticos
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = xhr.responseText;
                            
                            // Remover todos los scripts antes de insertar
                            const scripts = tempDiv.querySelectorAll('script');
                            scripts.forEach(function(script) {
                                script.remove();
                            });
                            
                            // Insertar el HTML limpio
                            targetElement.innerHTML = tempDiv.innerHTML;
                            
                            // Ahora ejecutar solo los scripts externos (src) y scripts seguros
                            const originalScripts = xhr.responseText.match(/<script\b[^>]*>([\s\S]*?)<\/script>/gi) || [];
                            originalScripts.forEach(function(scriptTag) {
                                // Extraer el src si existe
                                const srcMatch = scriptTag.match(/src=["']([^"']+)["']/);
                                if (srcMatch) {
                                    // Script externo - seguro de cargar
                                    const newScript = document.createElement('script');
                                    newScript.src = srcMatch[1];
                                    targetElement.appendChild(newScript);
                                } else {
                                    // Script inline - ejecutar solo si no contiene JSON problemático
                                    const scriptContent = scriptTag.replace(/<script\b[^>]*>|<\/script>/gi, '');
                                    // Verificar si el script contiene definiciones de variables problemáticas
                                    if (!scriptContent.includes('var quizData') && 
                                        !scriptContent.includes('var materialesDisponibles') &&
                                        !scriptContent.includes('var actividadesDisponibles')) {
                                        try {
                                            // Ejecutar el script de forma segura
                                            const newScript = document.createElement('script');
                                            newScript.textContent = scriptContent;
                                            targetElement.appendChild(newScript);
                                        } catch(e) {
                                            console.warn('Error al ejecutar script:', e);
                                        }
                                    }
                                }
                            });
                        }
                    } else {
                        const targetElement = document.querySelector(target);
                        if (targetElement) {
                            targetElement.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar el contenido</div>';
                        }
                    }
                };
                
                xhr.onerror = function() {
                    const targetElement = document.querySelector(target);
                    if (targetElement) {
                        targetElement.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar el contenido</div>';
                    }
                };
                
                xhr.send();
            }
        };
    </script>
@stop

