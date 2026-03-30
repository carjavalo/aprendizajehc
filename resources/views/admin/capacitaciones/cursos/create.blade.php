@extends('adminlte::page')

@section('title', 'Constructor de Cursos')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-graduation-cap"></i> Constructor de Cursos</h1>
                <p class="text-muted">Crea tu curso completo paso a paso</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Capacitaciones</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('capacitaciones.cursos.index') }}">Cursos</a></li>
                    <li class="breadcrumb-item active">Constructor</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Progress Bar -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                             id="wizard-progress" role="progressbar" style="width: 20%"></div>
                    </div>

                    <!-- Step Indicators -->
                    <div class="d-flex justify-content-between">
                        <div class="step-indicator active" data-step="1">
                            <div class="step-circle">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <small>Información Básica</small>
                        </div>
                        <div class="step-indicator" data-step="2">
                            <div class="step-circle">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <small>Materiales</small>
                        </div>
                        <div class="step-indicator" data-step="3">
                            <div class="step-circle">
                                <i class="fas fa-comments"></i>
                            </div>
                            <small>Foros</small>
                        </div>
                        <div class="step-indicator" data-step="4">
                            <div class="step-circle">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <small>Actividades</small>
                        </div>
                        <div class="step-indicator" data-step="5">
                            <div class="step-circle">
                                <i class="fas fa-check"></i>
                            </div>
                            <small>Revisar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="cursoWizardForm" enctype="multipart/form-data">
        @csrf

        <!-- Step 1: Información Básica -->
        <div class="wizard-step active" id="step-1">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Paso 1: Información Básica del Curso
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="titulo">Título del Curso <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="200">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Un título claro y descriptivo para tu curso</small>
                            </div>

                            <div class="form-group">
                                <label for="descripcion">Descripción del Curso</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                         placeholder="Describe el contenido, objetivos y beneficios del curso..."></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Esta descripción será visible para los estudiantes</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_area">Área <span class="text-danger">*</span></label>
                                        <select class="form-control" id="id_area" name="id_area" required>
                                            <option value="">Seleccionar área...</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->descripcion }} ({{ $area->categoria->descripcion }})</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="instructor_id">Creado por <span class="text-danger">*</span></label>
                                        <select class="form-control" id="instructor_id" name="instructor_id" required>
                                            <option value="">Seleccionar creador...</option>
                                            @foreach($creadores as $creador)
                                                <option value="{{ $creador->id }}" {{ Auth::id() == $creador->id ? 'selected' : '' }}>
                                                    {{ $creador->name }} {{ $creador->apellido1 }} ({{ $creador->role }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="imagen_portada">Imagen de Portada</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="imagen_portada" name="imagen_portada" accept="image/*">
                                    <label class="custom-file-label" for="imagen_portada">Seleccionar imagen...</label>
                                </div>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">JPG, PNG, GIF. Máximo 2MB. Recomendado: 1200x600px</small>

                                <!-- Vista previa de la imagen -->
                                <div class="mt-3" id="imagen-preview" style="display: none;">
                                    <img id="preview-img" src="" alt="Vista previa" class="img-fluid rounded shadow" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha y Hora de Inicio</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Selecciona la fecha y hora de inicio del curso</small>
                            </div>

                            <div class="form-group">
                                <label for="max_estudiantes">Máximo de Estudiantes</label>
                                <input type="number" class="form-control" id="max_estudiantes" name="max_estudiantes"
                                       min="1" max="1000" placeholder="Sin límite">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Deja vacío para sin límite</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_fin">Fecha y Hora de Fin</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Selecciona la fecha y hora de finalización del curso</small>
                            </div>

                            <div class="form-group">
                                <label for="duracion_horas">Duración Estimada (horas)</label>
                                <input type="number" class="form-control" id="duracion_horas" name="duracion_horas"
                                       min="1" max="1000" placeholder="Ej: 40">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="objetivos">Objetivos del Curso</label>
                                <textarea class="form-control" id="objetivos" name="objetivos" rows="4"
                                         placeholder="¿Qué aprenderán los estudiantes al completar este curso?"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requisitos">Requisitos Previos</label>
                                <textarea class="form-control" id="requisitos" name="requisitos" rows="4"
                                         placeholder="¿Qué conocimientos o habilidades previas se necesitan?"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Sistema de Calificaciones -->
                    <hr>
                    <h5 class="text-primary mb-3"><i class="fas fa-star"></i> Configuración de Calificaciones</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nota_maxima">Nota Máxima del Curso</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="nota_maxima" name="nota_maxima" 
                                           value="5.0" step="0.1" min="5" max="5" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Nota máxima fija: 5.0 (100%)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nota_minima_aprobacion">Nota Mínima de Aprobación <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="nota_minima_aprobacion" name="nota_minima_aprobacion" 
                                           value="3.0" step="0.1" min="0" max="5" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Nota para aprobar (0.0 - 5.0)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distribución de Porcentajes del Curso</label>
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="font-weight-bold">Porcentaje Total Asignado:</span>
                                            <span class="badge badge-lg" id="porcentaje-total-badge" style="font-size: 1.1rem;">
                                                <span id="porcentaje-asignado-text">0</span>% / 100%
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar progress-bar-striped" role="progressbar" 
                                                 id="porcentaje-asignado-bar" 
                                                 style="width: 0%;" 
                                                 aria-valuenow="0" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            <i class="fas fa-info-circle"></i> 
                                            Los materiales y actividades deben sumar 100% del curso
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Sistema de Calificaciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>La nota máxima del curso es <strong>5.0</strong> (equivalente al 100%)</li>
                            <li>Cada material tiene un porcentaje sobre el curso y una nota mínima de aprobación</li>
                            <li>Las actividades de un material deben sumar el porcentaje del material</li>
                            <li>En quizzes/evaluaciones, la suma de puntos de las preguntas no puede exceder 5.0</li>
                            <li>Las tareas son calificadas manualmente por el docente (máximo 5.0)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Materiales del Curso -->
        <div class="wizard-step" id="step-2" style="display: none;">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-folder-open"></i> Paso 2: Materiales del Curso
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Lista de Materiales -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Materiales Agregados</h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-success btn-sm" id="btn-add-material">
                                            <i class="fas fa-plus"></i> Agregar Material
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="materials-list">
                                        <div class="text-center text-muted py-4" id="no-materials">
                                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                                            <p>No hay materiales agregados aún</p>
                                            <p>Haz clic en "Agregar Material" para comenzar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Estadísticas de Materiales -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Resumen</h5>
                                </div>
                                <div class="card-body">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Documentos</span>
                                            <span class="info-box-number" id="count-documentos">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-video"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Videos</span>
                                            <span class="info-box-number" id="count-videos">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-image"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Imágenes</span>
                                            <span class="info-box-number" id="count-imagenes">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-file"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Otros</span>
                                            <span class="info-box-number" id="count-otros">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Consejos -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">💡 Consejos</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Organiza los materiales por orden de importancia</li>
                                        <li><i class="fas fa-check text-success"></i> Usa títulos descriptivos</li>
                                        <li><i class="fas fa-check text-success"></i> Incluye una descripción breve</li>
                                        <li><i class="fas fa-check text-success"></i> Combina diferentes tipos de contenido</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Foros y Discusiones -->
        <div class="wizard-step" id="step-3" style="display: none;">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-comments"></i> Paso 3: Foros y Discusiones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Lista de Posts -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Posts y Anuncios</h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-post">
                                            <i class="fas fa-plus"></i> Agregar Post
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" id="btn-add-announcement">
                                            <i class="fas fa-bullhorn"></i> Agregar Anuncio
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="forum-posts-list">
                                        <div class="text-center text-muted py-4" id="no-posts">
                                            <i class="fas fa-comments fa-3x mb-3"></i>
                                            <p>No hay posts creados aún</p>
                                            <p>Crea un post de bienvenida o anuncio inicial</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Configuración del Foro -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Configuración</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="allow-student-posts" checked>
                                            <label class="custom-control-label" for="allow-student-posts">
                                                Permitir posts de estudiantes
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="moderate-posts">
                                            <label class="custom-control-label" for="moderate-posts">
                                                Moderar posts antes de publicar
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="email-notifications" checked>
                                            <label class="custom-control-label" for="email-notifications">
                                                Notificaciones por email
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Plantillas -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">📝 Plantillas</h5>
                                </div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-block mb-2" data-template="welcome">
                                        Post de Bienvenida
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-block mb-2" data-template="rules">
                                        Reglas del Curso
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-block mb-2" data-template="schedule">
                                        Cronograma
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm btn-block" data-template="faq">
                                        Preguntas Frecuentes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Actividades y Evaluaciones -->
        <div class="wizard-step" id="step-4" style="display: none;">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i> Paso 4: Actividades y Evaluaciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Lista de Actividades -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Actividades Creadas</h5>
                                    <div class="card-tools">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <i class="fas fa-plus"></i> Nueva Actividad
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#" data-activity-type="tarea">
                                                    <i class="fas fa-file-alt text-primary"></i> Tarea
                                                </a>
                                                <a class="dropdown-item" href="#" data-activity-type="quiz">
                                                    <i class="fas fa-question-circle text-info"></i> Quiz
                                                </a>
                                                <a class="dropdown-item" href="#" data-activity-type="evaluacion">
                                                    <i class="fas fa-clipboard-check text-warning"></i> Evaluación
                                                </a>
                                                <a class="dropdown-item" href="#" data-activity-type="proyecto">
                                                    <i class="fas fa-project-diagram text-success"></i> Proyecto
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="activities-list">
                                        <div class="text-center text-muted py-4" id="no-activities">
                                            <i class="fas fa-tasks fa-3x mb-3"></i>
                                            <p>No hay actividades creadas aún</p>
                                            <p>Crea tareas, quizzes o evaluaciones para tus estudiantes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Estadísticas de Actividades -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Resumen</h5>
                                </div>
                                <div class="card-body">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tareas</span>
                                            <span class="info-box-number" id="count-tareas">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-question-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Quizzes</span>
                                            <span class="info-box-number" id="count-quizzes">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-clipboard-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Evaluaciones</span>
                                            <span class="info-box-number" id="count-evaluaciones">0</span>
                                        </div>
                                    </div>

                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-project-diagram"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Proyectos</span>
                                            <span class="info-box-number" id="count-proyectos">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de Evaluación -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">⚙️ Configuración</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="grading-scale">Escala de Calificación</label>
                                        <select class="form-control" id="grading-scale">
                                            <option value="100">0-100 puntos</option>
                                            <option value="20">0-20 puntos</option>
                                            <option value="10">0-10 puntos</option>
                                            <option value="letter">A, B, C, D, F</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="auto-grade" checked>
                                            <label class="custom-control-label" for="auto-grade">
                                                Calificación automática
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="late-submissions">
                                            <label class="custom-control-label" for="late-submissions">
                                                Permitir entregas tardías
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Revisar y Publicar -->
        <div class="wizard-step" id="step-5" style="display: none;">
            <div class="card">
                <div class="card-header bg-dark">
                    <h3 class="card-title">
                        <i class="fas fa-check"></i> Paso 5: Revisar y Publicar Curso
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Resumen del Curso -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">📋 Resumen del Curso</h5>
                                </div>
                                <div class="card-body" id="course-summary">
                                    <!-- El contenido se llenará dinámicamente -->
                                </div>
                            </div>

                            <!-- Estado de Publicación -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">🚀 Estado de Publicación</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="estado">Estado del Curso</label>
                                        <select class="form-control" id="estado" name="estado">
                                            <option value="borrador">Borrador (Solo visible para ti)</option>
                                            <option value="activo">Activo (Visible para estudiantes)</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            Puedes cambiar el estado después de crear el curso
                                        </small>
                                    </div>

                                                                          <div class="form-group mt-3">
                                          <label for="plantilla_certificado_id"><i class="fas fa-certificate text-warning"></i> Plantilla de Certificado</label>
                                          <select class="form-control" id="plantilla_certificado_id" name="plantilla_certificado_id">
                                              <option value="">Por defecto (Plantilla básica)</option>
                                              @if(isset($plantillas))
                                                  @foreach($plantillas as $plantilla)
                                                      <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                                  @endforeach
                                              @endif
                                          </select>
                                          <small class="form-text text-muted">Elige la plantilla que se usará para generar el certificado de este curso.</small>
                                      </div>

                                      <div class="alert alert-info">
                                          <i "class="fas fa-info-circle"></i>
                                          <strong>Nota:</strong> Una vez creado el curso, podrás acceder al aula virtual
                                        para gestionar inscripciones, interactuar con estudiantes y realizar seguimiento del progreso.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Checklist de Completitud -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">✅ Lista de Verificación</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled" id="completion-checklist">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success" id="check-basic"></i>
                                            <span class="ml-2">Información básica completa</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-circle text-muted" id="check-materials"></i>
                                            <span class="ml-2">Al menos un material agregado</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-circle text-muted" id="check-forum"></i>
                                            <span class="ml-2">Post de bienvenida creado</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-circle text-muted" id="check-activities"></i>
                                            <span class="ml-2">Al menos una actividad creada</span>
                                        </li>
                                    </ul>

                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-success" id="completion-progress" style="width: 25%"></div>
                                    </div>

                                    <p class="text-center">
                                        <span id="completion-percentage">25</span>% Completado
                                    </p>
                                </div>
                            </div>

                            <!-- Próximos Pasos -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">🎯 Próximos Pasos</h5>
                                </div>
                                <div class="card-body">
                                    <ol class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-users text-primary"></i>
                                            <span class="ml-2">Invitar estudiantes</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar text-info"></i>
                                            <span class="ml-2">Configurar fechas de actividades</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-chart-line text-success"></i>
                                            <span class="ml-2">Monitorear progreso</span>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-comments text-warning"></i>
                                            <span class="ml-2">Interactuar en foros</span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-secondary" id="btn-previous" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                    </div>
                    <div class="col-6 text-right">
                        <button type="button" class="btn btn-primary" id="btn-next">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-success" id="btn-create-course" style="display: none;">
                            <i class="fas fa-graduation-cap"></i> Crear Curso
                        </button>
                        <a href="{{ route('capacitaciones.cursos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modales Adicionales -->

    <!-- Modal para Drag & Drop de Archivos -->
    <div class="modal fade" id="dropzoneModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Subir Archivos</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="material-dropzone" class="dropzone-area">
                        <div class="dz-message">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5>Arrastra archivos aquí o haz clic para seleccionar</h5>
                            <p class="text-muted">Soporta múltiples archivos. Máximo 10MB por archivo.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-process-uploads">
                        <i class="fas fa-check"></i> Procesar Archivos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Vista Previa del Curso -->
    <div class="modal fade" id="coursePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Vista Previa del Curso</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="course-preview-content">
                        <!-- Contenido generado dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="createCourse()">
                        <i class="fas fa-graduation-cap"></i> Crear Curso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Progreso de Creación -->
    <div class="modal fade" id="creationProgressModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5>Creando tu curso...</h5>
                    <p class="text-muted">Por favor espera mientras configuramos todo</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             id="creation-progress" style="width: 0%"></div>
                    </div>
                    <small class="text-muted mt-2" id="creation-status">Iniciando...</small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">

    <style>
        /* Wizard Styles */
        .step-indicator {
            text-align: center;
            position: relative;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
        }

        .step-indicator.active .step-circle {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .step-indicator.completed .step-circle {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }

        .step-indicator small {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .step-indicator.active small {
            color: #007bff;
            font-weight: 600;
        }

        .step-indicator.completed small {
            color: #28a745;
            font-weight: 600;
        }

        /* Material Item Styles */
        .material-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .material-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        }

        .material-item .drag-handle {
            cursor: move;
            color: #6c757d;
        }

        .material-item .drag-handle:hover {
            color: #007bff;
        }

        /* Forum Post Styles */
        .forum-post-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s ease;
        }

        .forum-post-item.announcement {
            border-left: 4px solid #ffc107;
            background: #fff8e1;
        }

        .forum-post-item.pinned {
            border-left: 4px solid #17a2b8;
            background: #e1f5fe;
        }

        /* Activity Item Styles */
        .activity-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40,167,69,0.15);
        }

        /* Dropzone Styles */
        .dropzone-area {
            border: 2px dashed #007bff;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone-area:hover {
            border-color: #0056b3;
            background: #e3f2fd;
        }

        .dropzone-area.dz-drag-hover {
            border-color: #28a745;
            background: #e8f5e8;
        }

        /* Animation for wizard transitions */
        .wizard-step {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Custom switches */
        .custom-control-label::before {
            border-radius: 1rem;
        }

        .custom-control-label::after {
            border-radius: 1rem;
        }

        /* Progress bar animation */
        .progress-bar {
            transition: width 0.6s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .step-indicator {
                margin-bottom: 20px;
            }

            .step-indicator small {
                display: none;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Desactivar autoDiscover de Dropzone para evitar error "No URL provided"
        Dropzone.autoDiscover = false;

        // Configurar jQuery para incluir CSRF token en todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Variables globales del wizard
            let currentStep = 1;
            const totalSteps = 5;

            // Inicializar componentes
            // bsCustomFileInput.init(); // No necesario
            initializeWizard();
            initializeStep1();
            initializeStep2();
            initializeStep3();
            initializeStep4();
            initializeStep5();

            // ==========================================
            // FUNCIONES DEL WIZARD
            // ==========================================

            function initializeWizard() {
                // Navegación del wizard
                $('#btn-next').click(function() {
                    if (validateCurrentStep()) {
                        nextStep();
                    }
                });

                $('#btn-previous').click(function() {
                    previousStep();
                });

                $('#btn-create-course').click(function() {
                    createCourse();
                });

                // Click en indicadores de paso
                $('.step-indicator').click(function() {
                    const targetStep = parseInt($(this).data('step'));
                    if (targetStep < currentStep || validateCurrentStep()) {
                        goToStep(targetStep);
                    }
                });
            }

            function nextStep() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateWizardDisplay();
                }
            }

            function previousStep() {
                if (currentStep > 1) {
                    currentStep--;
                    updateWizardDisplay();
                }
            }

            function goToStep(step) {
                currentStep = step;
                updateWizardDisplay();
            }

            function updateWizardDisplay() {
                // Ocultar todos los pasos
                $('.wizard-step').hide();

                // Mostrar paso actual
                $(`#step-${currentStep}`).show();

                // Actualizar indicadores
                $('.step-indicator').removeClass('active completed');
                for (let i = 1; i <= totalSteps; i++) {
                    if (i < currentStep) {
                        $(`.step-indicator[data-step="${i}"]`).addClass('completed');
                    } else if (i === currentStep) {
                        $(`.step-indicator[data-step="${i}"]`).addClass('active');
                    }
                }

                // Actualizar barra de progreso
                const progress = (currentStep / totalSteps) * 100;
                $('#wizard-progress').css('width', progress + '%');

                // Actualizar botones de navegación
                if (currentStep === 1) {
                    $('#btn-previous').hide();
                } else {
                    $('#btn-previous').show();
                }

                if (currentStep === totalSteps) {
                    $('#btn-next').hide();
                    $('#btn-create-course').show();
                } else {
                    $('#btn-next').show();
                    $('#btn-create-course').hide();
                }

                // Ejecutar funciones específicas del paso
                if (currentStep === 5) {
                    updateCourseSummary();
                    updateCompletionChecklist();
                }
            }

            function validateCurrentStep() {
                switch (currentStep) {
                    case 1:
                        return validateStep1();
                    case 2:
                        return true; // Materiales son opcionales
                    case 3:
                        return true; // Foros son opcionales
                    case 4:
                        return validateStep4Activities();
                    case 5:
                        return true; // Solo revisión
                    default:
                        return true;
                }
            }
            
            function validateStep4Activities() {
                // Si hay materiales, cada uno debe tener al menos una actividad
                if (typeof courseData !== 'undefined' && courseData.materials && courseData.materials.length > 0) {
                    const materialesSinActividad = courseData.materials.filter(mat => {
                        const actividadesDelMaterial = (courseData.activities || []).filter(a => a.materialId === mat.id);
                        return actividadesDelMaterial.length === 0;
                    });
                    
                    if (materialesSinActividad.length > 0) {
                        const lista = materialesSinActividad.map(m => `• ${m.title}`).join('\n');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Materiales sin actividades',
                            html: `Cada material debe tener <strong>al menos una actividad</strong>.<br><br>Los siguientes materiales no tienen actividades asignadas:<br><br>${materialesSinActividad.map(m => `• <strong>${m.title}</strong>`).join('<br>')}<br><br>Crea al menos una actividad (Tarea, Quiz o Evaluación) para cada material.`,
                            confirmButtonText: 'Entendido'
                        });
                        return false;
                    }
                    
                    // Validar que ningún material exceda 100% en la suma de sus actividades
                    const materialesExcedidos = [];
                    courseData.materials.forEach(mat => {
                        const actividadesDelMaterial = (courseData.activities || []).filter(a => a.materialId === mat.id);
                        const sumaPorcentajes = actividadesDelMaterial.reduce((sum, a) => sum + (parseFloat(a.porcentajeMaterial) || 0), 0);
                        if (sumaPorcentajes > 100) {
                            materialesExcedidos.push({ title: mat.title, suma: sumaPorcentajes });
                        }
                    });
                    
                    if (materialesExcedidos.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Porcentaje de actividades excedido',
                            html: `La suma de porcentajes de actividades no puede superar el <strong>100%</strong> por material.<br><br>${materialesExcedidos.map(m => `• <strong>${m.title}</strong>: ${m.suma.toFixed(1)}%`).join('<br>')}`,
                            confirmButtonText: 'Entendido'
                        });
                        return false;
                    }
                }
                return true;
            }

            // ==========================================
            // PASO 1: INFORMACIÓN BÁSICA
            // ==========================================

            function initializeStep1() {
                // Vista previa de imagen
                $('#imagen_portada').change(function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#preview-img').attr('src', e.target.result);
                            $('#imagen-preview').show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $('#imagen-preview').hide();
                    }
                });

                // Validación de fechas
                $('#fecha_inicio, #fecha_fin').change(function() {
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();

                    if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                        $('#fecha_fin').addClass('is-invalid');
                        $('#fecha_fin').siblings('.invalid-feedback').text('La fecha de fin debe ser posterior a la fecha de inicio');
                    } else {
                        $('#fecha_fin').removeClass('is-invalid');
                        $('#fecha_fin').siblings('.invalid-feedback').text('');
                    }
                });
            }

            function validateStep1() {
                let isValid = true;

                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Validar campos requeridos
                const requiredFields = ['titulo', 'id_area', 'instructor_id'];
                requiredFields.forEach(function(field) {
                    const value = $(`#${field}`).val();
                    if (!value || value.trim() === '') {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).siblings('.invalid-feedback').text('Este campo es requerido');
                        isValid = false;
                    }
                });

                // Validar fechas
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                    $('#fecha_fin').addClass('is-invalid');
                    $('#fecha_fin').siblings('.invalid-feedback').text('La fecha de fin debe ser posterior a la fecha de inicio');
                    isValid = false;
                }

                if (!isValid) {
                    Swal.fire('Error de Validación', 'Por favor, completa todos los campos requeridos correctamente', 'error');
                }

                return isValid;
            }
        });
    </script>

    <!-- Incluir funciones adicionales del wizard -->
    <script src="{{ asset('js/course-wizard.js') }}?v={{ time() }}"></script>
@stop

