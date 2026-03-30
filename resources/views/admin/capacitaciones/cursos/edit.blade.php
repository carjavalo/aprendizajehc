@extends('adminlte::page')

@section('title', 'Editar Curso')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-edit"></i> Editar Curso</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Capacitaciones</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('capacitaciones.cursos.index') }}">Cursos</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <form id="cursoEditForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Información Básica -->
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Básica</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="titulo">Título del Curso <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                   value="{{ old('titulo', $curso->titulo) }}" required maxlength="200">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                     placeholder="Describe el contenido y objetivos del curso...">{{ old('descripcion', $curso->descripcion) }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="imagen_portada">Imagen de Portada</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="imagen_portada" name="imagen_portada" accept="image/*">
                                <label class="custom-file-label" for="imagen_portada">Cambiar imagen...</label>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Máximo 2MB.</small>
                            
                            <!-- Vista previa de la imagen actual -->
                            @if($curso->imagen_portada)
                                <div class="mt-3" id="imagen-current">
                                    <p class="text-muted small">Imagen actual:</p>
                                    <img src="{{ $curso->imagen_portada_url }}" alt="Imagen actual" 
                                         class="img-fluid rounded" style="max-height: 150px;">
                                </div>
                            @endif
                            
                            <!-- Vista previa de nueva imagen -->
                            <div class="mt-3" id="imagen-preview" style="display: none;">
                                <p class="text-muted small">Nueva imagen:</p>
                                <img id="preview-img" src="" alt="Vista previa" class="img-fluid rounded" style="max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración del Curso -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Configuración del Curso</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_area">Área <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_area" name="id_area" required>
                                <option value="">Seleccionar área...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" 
                                            {{ old('id_area', $curso->id_area) == $area->id ? 'selected' : '' }}>
                                        {{ $area->descripcion }} ({{ $area->categoria->descripcion }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="instructor_id">Creado por <span class="text-danger">*</span></label>
                            <select class="form-control" id="instructor_id" name="instructor_id" required>
                                <option value="">Seleccionar creador...</option>
                                @foreach($creadores as $creador)
                                    <option value="{{ $creador->id }}" 
                                            {{ old('instructor_id', $curso->instructor_id) == $creador->id ? 'selected' : '' }}>
                                        {{ $creador->name }} {{ $creador->apellido1 }} ({{ $creador->role }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="borrador" {{ old('estado', $curso->estado) == 'borrador' ? 'selected' : '' }}>Borrador</option>
                                <option value="activo" {{ old('estado', $curso->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="finalizado" {{ old('estado', $curso->estado) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                <option value="archivado" {{ old('estado', $curso->estado) == 'archivado' ? 'selected' : '' }}>Archivado</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_acceso">Código de Acceso</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigo_acceso" name="codigo_acceso" 
                                       value="{{ old('codigo_acceso', $curso->codigo_acceso) }}" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-regenerar-codigo">
                                        <i class="fas fa-sync-alt"></i> Regenerar
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Los estudiantes usan este código para inscribirse</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha y Hora de Inicio</label>
                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d\TH:i') : '') }}">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Selecciona la fecha y hora de inicio del curso</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin">Fecha y Hora de Fin</label>
                            <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                   value="{{ old('fecha_fin', $curso->fecha_fin ? $curso->fecha_fin->format('Y-m-d\TH:i') : '') }}">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Selecciona la fecha y hora de finalización del curso</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_estudiantes">Máx. Estudiantes</label>
                                    <input type="number" class="form-control" id="max_estudiantes" name="max_estudiantes" 
                                           min="1" placeholder="Sin límite" 
                                           value="{{ old('max_estudiantes', $curso->max_estudiantes) }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duracion_horas">Duración (horas)</label>
                                    <input type="number" class="form-control" id="duracion_horas" name="duracion_horas" 
                                           min="1" value="{{ old('duracion_horas', $curso->duracion_horas) }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles Adicionales -->
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-list-alt"></i> Detalles Adicionales</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="objetivos">Objetivos del Curso</label>
                            <textarea class="form-control" id="objetivos" name="objetivos" rows="5" 
                                     placeholder="¿Qué aprenderán los estudiantes?">{{ old('objetivos', $curso->objetivos) }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="requisitos">Requisitos Previos</label>
                            <textarea class="form-control" id="requisitos" name="requisitos" rows="5" 
                                     placeholder="¿Qué conocimientos previos se necesitan?">{{ old('requisitos', $curso->requisitos) }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sistema de Calificaciones -->
        <div class="card">
            <div class="card-header bg-purple">
                <h3 class="card-title"><i class="fas fa-star"></i> Sistema de Calificaciones</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nota_maxima">Nota Máxima del Curso</label>
                            <input type="number" class="form-control" id="nota_maxima" name="nota_maxima" 
                                   value="5.0" step="0.1" min="5" max="5" readonly>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">La nota máxima siempre es 5.0 (equivalente al 100%)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nota_minima_aprobacion">Nota Mínima de Aprobación <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="nota_minima_aprobacion" name="nota_minima_aprobacion" 
                                   value="{{ old('nota_minima_aprobacion', $curso->nota_minima_aprobacion ?? 3.0) }}" 
                                   step="0.1" min="0" max="5" required>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Nota mínima para aprobar el curso (0.0 - 5.0)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Porcentaje Asignado</label>
                            @php
                                $porcentajeMateriales = $curso->materiales()->sum('porcentaje_curso') ?? 0;
                                $porcentajeActividades = $curso->actividades()->whereNull('material_id')->sum('porcentaje_curso') ?? 0;
                                $porcentajeTotal = $porcentajeMateriales + $porcentajeActividades;
                            @endphp
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar {{ $porcentajeTotal > 100 ? 'bg-danger' : 'bg-success' }}" 
                                     role="progressbar" id="porcentaje-asignado-bar" 
                                     style="width: {{ min($porcentajeTotal, 100) }}%;" 
                                     aria-valuenow="{{ $porcentajeTotal }}" aria-valuemin="0" aria-valuemax="100">
                                    <span id="porcentaje-asignado-text">{{ number_format($porcentajeTotal, 1) }}%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Materiales: {{ number_format($porcentajeMateriales, 1) }}% | 
                                Actividades: {{ number_format($porcentajeActividades, 1) }}%
                            </small>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Curso -->
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title"><i class="fas fa-info"></i> Información del Curso</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Creado:</strong> {{ $curso->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Última actualización:</strong> {{ $curso->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estudiantes inscritos:</strong> {{ $curso->estudiantes_count ?? 0 }}</p>
                        <p><strong>Código de acceso:</strong> <code>{{ $curso->codigo_acceso }}</code></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materiales del Curso -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-folder-open"></i> Materiales del Curso</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" id="btn-subir-material">
                        <i class="fas fa-upload"></i> Agregar Material
                    </button>
                </div>
            </div>
            <div class="card-body" id="materiales-container">
                @php
                    $materiales = $curso->materiales()->orderBy('orden')->get();
                    $esInstructor = true; // En edición, siempre es instructor
                @endphp
                @forelse($materiales as $material)
                    <div class="material-item mb-3 p-3 border rounded" data-material-id="{{ $material->id }}">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="{{ $material->tipo_icon }} fa-2x text-primary"></i>
                            </div>
                            <div class="col-md-7">
                                <h5 class="mb-1">{{ $material->titulo }}</h5>
                                <p class="text-muted mb-1">{{ $material->descripcion }}</p>
                                <small class="text-muted">
                                    {!! $material->tipo_badge !!} • 
                                    @if($material->archivo_size)
                                        {{ $material->archivo_size_formatted }} • 
                                    @endif
                                    Subido {{ $material->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                @php
                                    $extension = strtolower($material->archivo_extension ?? '');
                                    $archivoUrl = $material->archivo_url ?? ($material->archivo_path ? url('media/' . $material->archivo_path) : '');
                                    if (empty($archivoUrl) && !empty($material->url_externa)) {
                                        $archivoUrl = $material->url_externa;
                                    }
                                @endphp
                                
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="verDocumento({{ json_encode($archivoUrl) }}, {{ json_encode($material->titulo) }}, {{ json_encode($material->tipo) }}, {{ json_encode($extension) }})">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                <button type="button" class="btn btn-warning btn-sm btn-editar-material" 
                                        data-material-id="{{ $material->id }}">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMaterial({{ $material->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay materiales disponibles</h5>
                        <p class="text-muted">Agrega materiales para enriquecer el curso.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Actividades del Curso -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Actividades del Curso</h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-plus"></i> Agregar Actividad
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" onclick="mostrarModalActividad('tarea')">
                                <i class="fas fa-file-alt text-primary"></i> Tarea
                            </a>
                            <a class="dropdown-item" href="#" onclick="mostrarModalActividad('quiz')">
                                <i class="fas fa-question-circle text-info"></i> Quiz
                            </a>
                            <a class="dropdown-item" href="#" onclick="mostrarModalActividad('evaluacion')">
                                <i class="fas fa-clipboard-check text-warning"></i> Evaluación
                            </a>
                            <a class="dropdown-item" href="#" onclick="mostrarModalActividad('proyecto')">
                                <i class="fas fa-project-diagram text-success"></i> Proyecto
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" id="actividades-container">
                @php
                    $actividades = $curso->actividades()->orderBy('fecha_apertura')->get();
                @endphp
                @forelse($actividades as $actividad)
                    <div class="actividad-item mb-3 p-3 border rounded" data-actividad-id="{{ $actividad->id }}">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                @php
                                    $iconos = [
                                        'tarea' => 'fas fa-file-alt text-primary',
                                        'quiz' => 'fas fa-question-circle text-info',
                                        'evaluacion' => 'fas fa-clipboard-check text-warning',
                                        'proyecto' => 'fas fa-project-diagram text-success'
                                    ];
                                @endphp
                                <i class="{{ $iconos[$actividad->tipo] ?? 'fas fa-tasks text-secondary' }} fa-2x"></i>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">{{ $actividad->titulo }}</h5>
                                <p class="text-muted mb-1">{{ Str::limit($actividad->descripcion, 100) }}</p>
                                <small class="text-muted">
                                    {!! $actividad->tipo_badge !!} • 
                                    {{ $actividad->puntos_maximos }} puntos •
                                    @if($actividad->fecha_apertura)
                                        Apertura: {{ $actividad->fecha_apertura->format('d/m/Y H:i') }}
                                    @endif
                                    @if($actividad->fecha_cierre)
                                        • Cierre: {{ $actividad->fecha_cierre->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                @if(in_array($actividad->tipo, ['quiz', 'evaluacion']))
                                    <span class="badge badge-{{ $actividad->habilitado ? 'success' : 'secondary' }}">
                                        {{ $actividad->habilitado ? 'Habilitado' : 'Deshabilitado' }}
                                    </span>
                                    @if($actividad->contenido_json && isset($actividad->contenido_json['questions']))
                                        <br><small class="text-muted">{{ count($actividad->contenido_json['questions']) }} preguntas</small>
                                    @endif
                                @else
                                    <span class="badge badge-{{ $actividad->estado_color }}">{{ ucfirst($actividad->estado) }}</span>
                                @endif
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="button" class="btn btn-primary btn-sm" onclick="editarActividad({{ $actividad->id }})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarActividad({{ $actividad->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5" id="no-actividades">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay actividades disponibles</h5>
                        <p class="text-muted">Agrega tareas, quizzes o evaluaciones para tus estudiantes.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="btn-group float-left">
                            <a href="{{ route('capacitaciones.cursos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                            <a href="{{ route('capacitaciones.cursos.classroom', $curso->id) }}" class="btn btn-info">
                                <i class="fas fa-chalkboard-teacher"></i> Ir al Aula Virtual
                            </a>
                        </div>
                        <div class="btn-group float-right">
                            <button type="button" class="btn btn-warning" id="btn-reset">
                                <i class="fas fa-undo"></i> Restablecer
                            </button>
                            <button type="submit" class="btn btn-success" id="btn-actualizar">
                                <i class="fas fa-save"></i> Actualizar Curso
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal para Subir Material -->
    <div class="modal fade" id="subirMaterialModal" tabindex="-1" role="dialog" aria-labelledby="subirMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title" id="subirMaterialModalLabel"><i class="fas fa-upload"></i> Agregar Nuevo Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="subirMaterialForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="titulo">Título del Material <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="200">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe el contenido del material..."></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo">Tipo de Material <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="documento">Documento</option>
                                        <option value="video">Video</option>
                                        <option value="imagen">Imagen</option>
                                        <option value="archivo">Archivo General</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="0" placeholder="Automático">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Subida -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Método de Subida</label>
                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#archivo-tab">
                                                    <i class="fas fa-upload"></i> Subir Archivo
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#url-tab">
                                                    <i class="fas fa-link"></i> URL Externa
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="archivo-tab">
                                                <div class="form-group mt-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="archivo" name="archivo">
                                                        <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Máximo 10MB. Formatos: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, GIF, MP4, AVI, MOV</small>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="url-tab">
                                                <div class="form-group mt-3">
                                                    <label for="url_externa">URL Externa</label>
                                                    <input type="url" class="form-control" id="url_externa" name="url_externa" placeholder="https://ejemplo.com/video">
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Para videos de YouTube, Vimeo, Google Drive, etc.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btn-guardar-material">
                            <i class="fas fa-upload"></i> Agregar Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Visualizar Documentos -->
    <div class="modal fade" id="verDocumentoModal" tabindex="-1" role="dialog" aria-labelledby="verDocumentoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="verDocumentoModalLabel">
                        <i class="fas fa-file-alt"></i> <span id="documento-titulo">Documento</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div id="documento-viewer" class="w-100 h-100">
                        <!-- El contenido se cargará dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="btn-descargar-documento" href="#" download class="btn btn-info">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                    <a id="btn-abrir-nueva-ventana" href="#" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Material -->
    <div class="modal fade" id="editarMaterialModal" tabindex="-1" role="dialog" aria-labelledby="editarMaterialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title" id="editarMaterialModalLabel"><i class="fas fa-edit"></i> Editar Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editarMaterialForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_material_id" name="material_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="edit_titulo">Título del Material <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_titulo" name="titulo" required maxlength="200">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_descripcion">Descripción</label>
                                    <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" placeholder="Describe el contenido del material..."></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_tipo">Tipo de Material <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_tipo" name="tipo" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="documento">Documento</option>
                                        <option value="video">Video</option>
                                        <option value="imagen">Imagen</option>
                                        <option value="archivo">Archivo General</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_orden">Orden</label>
                                    <input type="number" class="form-control" id="edit_orden" name="orden" min="0">
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_porcentaje_curso">Porcentaje del Curso (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_porcentaje_curso" name="porcentaje_curso" 
                                           min="0" max="100" step="0.1" value="0" required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Porcentaje que representa este material en el curso (0-100%)
                                    </small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Vincular a Material Prerrequisito -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label><i class="fas fa-link text-info"></i> Vincular como Prerrequisito</label>
                                    <select class="form-control" id="edit_prerequisite_id" name="prerequisite_id">
                                        <option value="">Sin prerrequisito (material independiente)</option>
                                        @foreach($materiales as $mat)
                                            <option value="{{ $mat->id }}" data-material-id="{{ $mat->id }}">
                                                {{ $mat->titulo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Si seleccionas un material, los estudiantes deberán verlo primero antes de acceder a este.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Subida -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Cambiar Archivo o URL (opcional)</label>
                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#edit-archivo-tab">
                                                    <i class="fas fa-upload"></i> Subir Archivo
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#edit-url-tab">
                                                    <i class="fas fa-link"></i> URL Externa
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="edit-archivo-tab">
                                                <div class="form-group mt-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="edit_archivo" name="archivo">
                                                        <label class="custom-file-label" for="edit_archivo">Seleccionar nuevo archivo...</label>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Deja vacío para mantener el archivo actual. Máximo 10MB.</small>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="edit-url-tab">
                                                <div class="form-group mt-3">
                                                    <label for="edit_url_externa">URL Externa</label>
                                                    <input type="url" class="form-control" id="edit_url_externa" name="url_externa" placeholder="https://ejemplo.com/video">
                                                    <div class="invalid-feedback"></div>
                                                    <small class="form-text text-muted">Para videos de YouTube, Vimeo, Google Drive, etc.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del archivo actual -->
                        <div class="row" id="edit-archivo-actual-info">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i> <strong>Archivo actual:</strong> <span id="edit-archivo-actual-nombre">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" id="btn-actualizar-material">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-header {
            font-weight: 600;
        }

        .form-group label {
            font-weight: 500;
            color: #495057;
        }

        .text-danger {
            font-weight: 600;
        }

        /* Estilos para actividades */
        .actividad-item {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0 !important;
        }
        
        .actividad-item:hover {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transform: translateY(-2px);
        }

        .quiz-question-card {
            border-left: 3px solid #007bff;
        }

        .option-row {
            transition: all 0.2s ease;
        }

        .option-row:hover {
            background-color: #f8f9fa;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        #imagen-current img, #imagen-preview img {
            border: 2px solid #dee2e6;
            transition: border-color 0.3s ease;
        }

        #imagen-current img:hover, #imagen-preview img:hover {
            border-color: #007bff;
        }

        .input-group .btn {
            border-left: 0;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
        }

        /* Estilos para materiales */
        .material-item {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0 !important;
        }
        
        .material-item:hover {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transform: translateY(-2px);
        }

        .image-controls .btn {
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .image-controls .btn:hover {
            opacity: 1;
        }
        
        .pdf-controls .btn {
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }
        
        .pdf-controls .btn:hover {
            opacity: 1;
        }
        
        #document-image {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .office-viewer-container .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        
        .office-viewer-container .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }
        
        .office-viewer-container .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        
        .video-container {
            position: relative;
            background: #000;
        }
        
        .video-container video {
            outline: none;
        }
        
        .viewer-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar custom file input (no necesario)
            // bsCustomFileInput.init();

            // Vista previa de imagen
            $('#imagen_portada').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-img').attr('src', e.target.result);
                        $('#imagen-preview').show();
                        $('#imagen-current').hide();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagen-preview').hide();
                    $('#imagen-current').show();
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

            // Regenerar código de acceso
            $('#btn-regenerar-codigo').click(function() {
                Swal.fire({
                    title: '¿Regenerar código?',
                    text: 'Se generará un nuevo código de acceso. Los estudiantes necesitarán el nuevo código para inscribirse.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, regenerar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Generar nuevo código
                        const newCode = Math.random().toString(36).substr(2, 6).toUpperCase();
                        $('#codigo_acceso').val(newCode);

                        Swal.fire({
                            icon: 'success',
                            title: 'Código regenerado',
                            text: `Nuevo código: ${newCode}`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                });
            });

            // Botón restablecer
            $('#btn-reset').click(function() {
                Swal.fire({
                    title: '¿Restablecer formulario?',
                    text: 'Se perderán todos los cambios no guardados',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, restablecer',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });

            // Envío del formulario
            $('#cursoEditForm').submit(function(e) {
                e.preventDefault();

                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Deshabilitar botón de envío
                $('#btn-actualizar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

                // Crear FormData para manejar archivos
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("capacitaciones.cursos.update", $curso->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showCancelButton: true,
                                confirmButtonText: 'Ir al Aula Virtual',
                                cancelButtonText: 'Quedarse Aquí'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '{{ route("capacitaciones.cursos.classroom", $curso->id) }}';
                                } else {
                                    // Recargar la página para mostrar los datos actualizados
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Errores de validación
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                const input = $('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(messages[0]);
                            });

                            Swal.fire('Error de Validación', 'Por favor, corrige los errores en el formulario', 'error');
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al actualizar el curso', 'error');
                        }
                    },
                    complete: function() {
                        // Rehabilitar botón de envío
                        $('#btn-actualizar').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Curso');
                    }
                });
            });

            // Abrir modal para subir material
            $('#btn-subir-material').click(function() {
                $('#subirMaterialForm')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#subirMaterialModal').modal('show');
            });

            // Manejar eventos del modal para accesibilidad
            $('#subirMaterialModal').on('show.bs.modal', function () {
                // Solución específica para AdminLTE: remover aria-hidden de elementos problemáticos
                $('body').removeClass('modal-open');
                $('.wrapper, .content-wrapper, .main-sidebar, .main-header').removeAttr('aria-hidden');
                
                // Asegurar que el modal esté en el nivel superior
                $(this).appendTo('body');
            });
            
            $('#subirMaterialModal').on('shown.bs.modal', function () {
                // Enfocar el primer campo
                setTimeout(() => {
                    $('#titulo').focus();
                }, 100);
            });

            $('#subirMaterialModal').on('hidden.bs.modal', function () {
                // Limpiar formulario cuando se cierre el modal
                $('#subirMaterialForm')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                // Restaurar clases de Bootstrap
                $('body').addClass('modal-open');
            });

            // Envío del formulario de subir material
            $('#subirMaterialForm').submit(function(e) {
                e.preventDefault();
                
                // Limpiar errores previos
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                // Deshabilitar botón de envío
                $('#btn-guardar-material').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
                
                // Crear FormData para manejar archivos
                const formData = new FormData(this);
                
                // Verificar que tenemos los datos mínimos requeridos
                if (!formData.get('titulo') || !formData.get('tipo')) {
                    Swal.fire('Error', 'Por favor completa los campos requeridos (Título y Tipo)', 'error');
                    $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Agregar Material');
                    return;
                }
                
                // Verificar que tenemos archivo o URL externa
                const archivo = formData.get('archivo');
                const urlExterna = formData.get('url_externa');
                
                if ((!archivo || archivo.size === 0) && (!urlExterna || urlExterna.trim() === '')) {
                    Swal.fire('Error', 'Debes seleccionar un archivo o proporcionar una URL externa', 'error');
                    $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Agregar Material');
                    return;
                }
                
                // Si url_externa está vacía, eliminarla del FormData
                if (!urlExterna || urlExterna.trim() === '') {
                    formData.delete('url_externa');
                }
                
                $.ajax({
                    url: '{{ route("capacitaciones.cursos.classroom.materiales.store", $curso->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $('#subirMaterialModal').modal('hide');
                                // Recargar la lista de materiales
                                actualizarListaMateriales();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Errores de validación
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const input = $('[name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(messages[0]);
                                });
                                
                                Swal.fire('Error de Validación', 'Por favor, corrige los errores en el formulario', 'error');
                            } else {
                                Swal.fire('Error de Validación', 'Error 422: ' + (xhr.responseJSON?.message || xhr.responseText), 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al subir el material (Status: ' + xhr.status + ')', 'error');
                        }
                    },
                    complete: function() {
                        // Rehabilitar botón de envío
                        $('#btn-guardar-material').prop('disabled', false).html('<i class="fas fa-upload"></i> Agregar Material');
                    }
                });
            });

            // Función para actualizar la lista de materiales
            function actualizarListaMateriales() {
                // Recargar la página para asegurar que los materiales se muestren correctamente
                window.location.reload();
            }

            // Función para eliminar material
            window.eliminarMaterial = function(materialId) {
                Swal.fire({
                    title: '¿Eliminar material?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/materiales/${materialId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('¡Eliminado!', response.message, 'success');
                                    // Remover el material de la lista
                                    $(`[data-material-id="${materialId}"]`).fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el material', 'error');
                            }
                        });
                    }
                });
            };

            // Función para editar material (cargar datos via AJAX)
            $(document).on('click', '.btn-editar-material', function() {
                const materialId = $(this).data('material-id');
                
                if (!materialId) {
                    Swal.fire('Error', 'No se pudo identificar el material', 'error');
                    return;
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Cargando...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                // Cargar datos del material via AJAX
                $.ajax({
                    url: '/capacitaciones/cursos/{{ $curso->id }}/classroom/materiales/' + materialId + '/obtener',
                    type: 'GET',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success && response.material) {
                            const materialData = response.material;
                            
                            // Llenar el formulario con los datos actuales
                            $('#edit_material_id').val(materialId);
                            $('#edit_titulo').val(materialData.titulo || '');
                            $('#edit_descripcion').val(materialData.descripcion || '');
                            $('#edit_tipo').val(materialData.tipo || '');
                            $('#edit_orden').val(materialData.orden || 0);
                            $('#edit_porcentaje_curso').val(materialData.porcentaje_curso || 0);
                            $('#edit_url_externa').val(materialData.url_externa || '');
                            
                            // Configurar prerrequisito
                            $('#edit_prerequisite_id').val(materialData.prerequisite_id || '');
                            
                            // Ocultar la opción del material actual en el select de prerrequisitos
                            $('#edit_prerequisite_id option').show();
                            $('#edit_prerequisite_id option[data-material-id="' + materialId + '"]').hide();
                            
                            // Mostrar información del archivo actual
                            let archivoActual = '-';
                            if (materialData.archivo_path) {
                                archivoActual = materialData.archivo_path.split('/').pop();
                            } else if (materialData.url_externa) {
                                archivoActual = 'URL Externa: ' + materialData.url_externa;
                            }
                            $('#edit-archivo-actual-nombre').text(archivoActual);
                            
                            // Limpiar el campo de archivo
                            $('#edit_archivo').val('');
                            $('.custom-file-label[for="edit_archivo"]').text('Seleccionar nuevo archivo...');
                            
                            // Limpiar errores previos
                            $('#editarMaterialForm .form-control').removeClass('is-invalid');
                            $('#editarMaterialForm .invalid-feedback').text('');
                            
                            // Mostrar modal
                            $('#editarMaterialModal').modal('show');
                        } else {
                            Swal.fire('Error', 'No se pudieron cargar los datos del material', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar el material', 'error');
                    }
                });
            });

            // Manejar envío del formulario de editar material
            $('#editarMaterialForm').submit(function(e) {
                e.preventDefault();
                
                const materialId = $('#edit_material_id').val();
                
                // Limpiar errores previos
                $('#editarMaterialForm .form-control').removeClass('is-invalid');
                $('#editarMaterialForm .invalid-feedback').text('');
                
                // Deshabilitar botón de envío
                $('#btn-actualizar-material').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                
                // Crear FormData para manejar archivos
                const formData = new FormData(this);
                formData.append('_method', 'PUT');
                
                $.ajax({
                    url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/materiales/${materialId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $('#editarMaterialModal').modal('hide');
                                // Recargar la página para mostrar los cambios
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const input = $('#editarMaterialForm [name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.siblings('.invalid-feedback').text(messages[0]);
                                });
                                Swal.fire('Error de Validación', 'Por favor, corrige los errores en el formulario', 'error');
                            } else {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error de validación', 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al actualizar el material', 'error');
                        }
                    },
                    complete: function() {
                        $('#btn-actualizar-material').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
                    }
                });
            });

            // Manejar cambio de archivo en edición
            $('#edit_archivo').change(function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').text(fileName || 'Seleccionar nuevo archivo...');
            });

            // Función para ver documento en modal
            window.verDocumento = function(url, titulo, tipo, extension) {
                // Función auxiliar para escapar HTML
                function escapeHtml(text) {
                    if (!text) return '';
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }
                
                // Escapar variables para uso seguro en HTML
                const urlEscaped = escapeHtml(url);
                const tituloEscaped = escapeHtml(titulo);
                
                // Validar que haya URL
                if (!url || url.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo no disponible',
                        text: 'Este material no tiene un archivo asociado.'
                    });
                    return;
                }
                
                // Actualizar título del modal
                $('#documento-titulo').text(titulo);
                
                // Actualizar botones
                $('#btn-descargar-documento').attr('href', url);
                $('#btn-abrir-nueva-ventana').attr('href', url);
                
                // Mostrar modal inmediatamente
                $('#verDocumentoModal').modal('show');
                
                // Mostrar indicador de carga
                $('#documento-viewer').html(`
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                            <p class="mt-3 text-muted">Preparando visualización...</p>
                        </div>
                    </div>
                `);
                
                // Preparar contenido basándose en el tipo y extensión
                let contenido = '';
                const ext = extension.toLowerCase();
                
                // Lógica simplificada para determinar el tipo de visualización
                if (tipo === 'imagen' || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) {
                    // Mostrar imagen con controles mejorados
                    contenido = `
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-dark position-relative">
                            <div class="image-controls position-absolute" style="top: 10px; right: 10px; z-index: 1000;">
                                <button class="btn btn-sm btn-light" onclick="zoomImage('in')" title="Acercar">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-light ml-1" onclick="zoomImage('out')" title="Alejar">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button class="btn btn-sm btn-light ml-1" onclick="resetZoom()" title="Tamaño original">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                            </div>
                            <img id="document-image" src="${urlEscaped}" alt="${tituloEscaped}" class="img-fluid" 
                                 style="max-height: 90%; max-width: 90%; object-fit: contain; cursor: grab; transition: transform 0.2s;" 
                                 draggable="false">
                        </div>
                    `;
                } else if (extension === 'pdf' || url.toLowerCase().includes('.pdf')) {
                    // Mostrar PDF con visor embebido mejorado
                    contenido = `
                        <div class="pdf-viewer-container h-100 position-relative">
                            <div class="pdf-controls position-absolute" style="top: 10px; left: 10px; z-index: 1000;">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-primary" onclick="pdfAction('print')" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="pdfAction('fullscreen')" title="Pantalla completa">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                            <iframe id="pdf-viewer" src="${urlEscaped}#toolbar=1&navpanes=1&scrollbar=1" 
                                    width="100%" 
                                    height="100%" 
                                    frameborder="0"
                                    style="border: none;">
                                <p>Tu navegador no soporta la visualización de PDFs. 
                                   <a href="${urlEscaped}" target="_blank">Haz clic aquí para descargar el PDF</a>
                                </p>
                            </iframe>
                        </div>
                    `;
                } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension.toLowerCase())) {
                    // Para documentos de Office, usar múltiples opciones de visualización
                    let fullUrl = url;
                    if (!url.startsWith('http://') && !url.startsWith('https://')) {
                        fullUrl = window.location.origin + (url.startsWith('/') ? url : '/' + url);
                    }
                    const encodedFullUrl = encodeURIComponent(fullUrl);
                    
                    contenido = `
                        <div class="office-viewer-container h-100">
                            <div class="viewer-tabs mb-2">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#google-viewer" role="tab">Google Viewer</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#office-viewer" role="tab">Office Online</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content h-100">
                                <div class="tab-pane active h-100" id="google-viewer" role="tabpanel">
                                    <iframe src="https://docs.google.com/gview?url=${encodedFullUrl}&embedded=true" 
                                            width="100%" 
                                            height="90%" 
                                            frameborder="0">
                                        <p>No se puede mostrar el documento con Google Viewer.</p>
                                    </iframe>
                                </div>
                                <div class="tab-pane h-100" id="office-viewer" role="tabpanel">
                                    <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodedFullUrl}" 
                                            width="100%" 
                                            height="90%" 
                                            frameborder="0">
                                        <p>No se puede mostrar el documento con Office Online.</p>
                                    </iframe>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Si no se puede visualizar, 
                                    <a href="${urlEscaped}" target="_blank" class="text-primary">haz clic aquí para descargarlo</a>
                                </small>
                            </div>
                        </div>
                    `;
                } else if (tipo === 'video' || ['mp4', 'avi', 'mov', 'webm', 'ogg', 'mkv'].includes(ext)) {
                    // Mostrar video con controles nativos
                    contenido = `
                        <div class="d-flex justify-content-center align-items-center h-100 bg-dark">
                            <video controls class="w-100 h-100" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                <source src="${urlEscaped}" type="video/${escapeHtml(extension)}">
                                Tu navegador no soporta la reproducción de videos.
                            </video>
                        </div>
                    `;
                } else {
                    // Para otros tipos de archivo, mostrar opción de descarga
                    contenido = `
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <i class="fas fa-file fa-5x text-muted mb-4"></i>
                                <h4 class="text-muted">No se puede previsualizar este tipo de archivo</h4>
                                <p class="text-muted">Por favor, descarga el archivo para ver su contenido.</p>
                                <a href="${urlEscaped}" download class="btn btn-primary btn-lg">
                                    <i class="fas fa-download"></i> Descargar Archivo
                                </a>
                            </div>
                        </div>
                    `;
                }
                
                // Actualizar el contenido del modal con un pequeño delay para el efecto de carga
                setTimeout(() => {
                    $('#documento-viewer').html(contenido);
                    
                    // Inicializar funcionalidades especiales para imágenes
                    if (tipo === 'imagen' || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) {
                        initializeImageZoom();
                    }
                }, 500);
            };

            // Funciones auxiliares para el visor de imágenes
            window.zoomImage = function(action) {
                const img = document.getElementById('document-image');
                if (!img) return;
                
                const currentScale = img.dataset.scale || 1;
                let newScale = currentScale;
                
                if (action === 'in') {
                    newScale = Math.min(currentScale * 1.2, 5);
                } else if (action === 'out') {
                    newScale = Math.max(currentScale / 1.2, 0.1);
                }
                
                img.dataset.scale = newScale;
                img.style.transform = `scale(${newScale})`;
            };

            window.resetZoom = function() {
                const img = document.getElementById('document-image');
                if (!img) return;
                
                img.dataset.scale = 1;
                img.style.transform = 'scale(1)';
                resetImagePosition();
            };

            function initializeImageZoom() {
                const img = document.getElementById('document-image');
                if (!img) return;
                
                let isDragging = false;
                let startX, startY, scrollLeft, scrollTop;
                
                // Zoom con rueda del mouse
                img.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    const delta = e.deltaY > 0 ? 0.9 : 1.1;
                    const currentScale = img.dataset.scale || 1;
                    const newScale = Math.max(0.1, Math.min(currentScale * delta, 5));
                    
                    img.dataset.scale = newScale;
                    img.style.transform = `scale(${newScale})`;
                });
                
                // Funcionalidad de arrastre para imágenes zoomadas
                img.addEventListener('mousedown', function(e) {
                    const scale = img.dataset.scale || 1;
                    if (scale <= 1) return;
                    
                    isDragging = true;
                    img.style.cursor = 'grabbing';
                    startX = e.pageX - img.offsetLeft;
                    startY = e.pageY - img.offsetTop;
                });
                
                document.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - startX;
                    const y = e.pageY - startY;
                    img.style.transform = `scale(${img.dataset.scale}) translate(${x}px, ${y}px)`;
                });
                
                document.addEventListener('mouseup', function() {
                    if (isDragging) {
                        isDragging = false;
                        img.style.cursor = 'grab';
                    }
                });
            }

            function resetImagePosition() {
                const img = document.getElementById('document-image');
                if (!img) return;
                
                img.style.transform = 'scale(1)';
            }

            // Funciones para el visor de PDF
            window.pdfAction = function(action) {
                const pdfViewer = document.getElementById('pdf-viewer');
                if (!pdfViewer) return;
                
                if (action === 'print') {
                    pdfViewer.contentWindow.print();
                } else if (action === 'fullscreen') {
                    if (pdfViewer.requestFullscreen) {
                        pdfViewer.requestFullscreen();
                    } else if (pdfViewer.webkitRequestFullscreen) {
                        pdfViewer.webkitRequestFullscreen();
                    } else if (pdfViewer.msRequestFullscreen) {
                        pdfViewer.msRequestFullscreen();
                    }
                }
            };

            // =====================================================
            // FUNCIONES PARA GESTIÓN DE ACTIVIDADES
            // =====================================================
            
            // Variables globales para actividades
            window.actividadQuestions = [];
            window.actividadQuestionCounter = 0;
            window.actividadOptionCounters = {};
            window.optionLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

            // Mostrar modal para crear actividad
            window.mostrarModalActividad = function(tipo) {
                const typeLabels = {
                    tarea: 'Tarea',
                    quiz: 'Quiz',
                    evaluacion: 'Evaluación',
                    proyecto: 'Proyecto'
                };

                const requierePreguntas = tipo === 'quiz' || tipo === 'evaluacion';
                const tipoLabel = typeLabels[tipo];

                // Generar sección de prerrequisitos de actividades
                const actividadesExistentes = [];
                @foreach($curso->actividades as $act)
                    actividadesExistentes.push({
                        id: {{ $act->id }},
                        titulo: @json($act->titulo),
                        tipo: @json($act->tipo)
                    });
                @endforeach

                // Generar opciones de materiales para la sección de calificación
                let materialesOptionsHtml = '<option value="">-- Selecciona un material --</option>';
                @foreach($curso->materiales()->orderBy('orden')->get() as $mat)
                    @php
                        $porcentajeUsadoMat = $mat->actividades()->sum('porcentaje_curso');
                        $porcentajeDisponibleMat = max(0, $mat->porcentaje_curso - $porcentajeUsadoMat);
                        $estaLlenoMat = $porcentajeDisponibleMat <= 0;
                    @endphp
                    materialesOptionsHtml += `<option value="{{ $mat->id }}" data-porcentaje-disponible="{{ number_format($porcentajeDisponibleMat, 1) }}" data-porcentaje-material="{{ $mat->porcentaje_curso }}" {{ $estaLlenoMat ? 'style="color: #dc3545;"' : '' }}>{{ $mat->titulo }} ({{ $estaLlenoMat ? '✗ 100% asignado' : number_format($porcentajeDisponibleMat, 1) . '% disponible' }})</option>`;
                @endforeach

                // Sección de configuración de calificación
                const gradingSection = `
                    <hr class="my-3">
                    <div class="card bg-light mb-3">
                        <div class="card-header py-2">
                            <strong><i class="fas fa-star text-warning"></i> Configuración de Calificación</strong>
                        </div>
                        <div class="card-body py-2">
                            <div class="form-group mb-3">
                                <label for="actividad-material"><i class="fas fa-book text-info"></i> Material al que pertenece *</label>
                                <select class="form-control" id="actividad-material" onchange="updatePorcentajeDisponibleEdit()">
                                    ${materialesOptionsHtml}
                                </select>
                                <small class="form-text text-muted">Selecciona el material al que pertenece esta actividad</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="actividad-porcentaje">Porcentaje de la Actividad (%) *</label>
                                        <input type="number" class="form-control" id="actividad-porcentaje" 
                                               min="0.1" max="100" step="0.1" value="" placeholder="Ej: 25"
                                               oninput="validarPorcentajeActividadEdit()">
                                        <small class="form-text text-muted" id="edit-porcentaje-disponible-info">
                                            Selecciona un material para ver el porcentaje disponible
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="actividad-nota-minima">Nota Mínima Aprobación *</label>
                                        <input type="number" class="form-control" id="actividad-nota-minima" 
                                               min="0" max="5" step="0.1" value="3.0" placeholder="3.0">
                                        <small class="form-text text-muted">Escala: 0.0 - 5.0</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                const tipoIconsPrereq = {
                    tarea: '📝',
                    quiz: '❓',
                    evaluacion: '📋',
                    proyecto: '🔧'
                };

                let actividadesCheckboxes = '';
                if (actividadesExistentes.length > 0) {
                    actividadesExistentes.forEach(act => {
                        const tipoIcon = tipoIconsPrereq[act.tipo] || '📝';
                        actividadesCheckboxes += `
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input create-prereq-checkbox" id="create-prereq-${act.id}" value="${act.id}">
                                <label class="custom-control-label" for="create-prereq-${act.id}">
                                    ${tipoIcon} ${act.titulo}
                                </label>
                            </div>
                        `;
                    });
                }

                const prerequisitosSection = `
                    <hr class="my-3">
                    <div class="form-group">
                        <label><i class="fas fa-link text-info"></i> Prerrequisitos de Actividades</label>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="actividad-has-prereqs" onchange="toggleCreatePrereqsSelection()" ${actividadesExistentes.length === 0 ? 'disabled' : ''}>
                            <label class="custom-control-label" for="actividad-has-prereqs">
                                Requiere completar otras actividades antes
                            </label>
                        </div>
                        <div id="create-prereqs-selection-container" style="display: none;">
                            ${actividadesExistentes.length > 0 ? `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Selecciona las actividades que el estudiante debe completar:</small>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.querySelectorAll('.create-prereq-checkbox').forEach(cb => cb.checked = true)">
                                        <i class="fas fa-check-double"></i> Todas
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.querySelectorAll('.create-prereq-checkbox').forEach(cb => cb.checked = false)">
                                        <i class="fas fa-times"></i> Ninguna
                                    </button>
                                </div>
                            </div>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                ${actividadesCheckboxes}
                            </div>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" id="actividad-prereqs-obligatorio">
                                <label class="custom-control-label" for="actividad-prereqs-obligatorio">
                                    <i class="fas fa-exclamation-circle text-warning"></i> Prerrequisito obligatorio
                                    <small class="text-muted d-block">Si está activo, el estudiante no podrá acceder sin completar las actividades seleccionadas</small>
                                </label>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fas fa-info-circle"></i> El estudiante deberá completar las actividades seleccionadas antes de poder acceder a esta actividad.
                            </small>
                            ` : `
                            <div class="alert alert-light py-2 mb-0">
                                <i class="fas fa-info-circle text-muted"></i> <small class="text-muted">No hay otras actividades creadas aún. Crea primero otras actividades para poder establecer prerrequisitos.</small>
                            </div>
                            `}
                        </div>
                    </div>
                `;

                // Campos específicos para Quiz y Evaluación
                const quizFields = requierePreguntas ? `
                    <hr class="my-4">
                    <h5 class="text-primary"><i class="fas fa-list-ol"></i> Preguntas de la ${tipoLabel}</h5>
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle"></i> <strong>Nota máxima: 5.0</strong> — Cada pregunta tiene un <strong>porcentaje (%)</strong>. La suma de los porcentajes de todas las preguntas no puede exceder <strong>100%</strong>.<br>
                        <small>La nota se calcula así: si una respuesta es correcta, su porcentaje se multiplica por 5. Si es incorrecta, por 0. Si hay varias respuestas correctas, el porcentaje se distribuye entre ellas.</small>
                    </div>
                    <div class="form-group">
                        <label>Porcentaje total asignado:</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" id="edit-quiz-points-progress" style="width: 0%">0% / 100%</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="actividad-duration">Duración (minutos)</label>
                        <input type="number" class="form-control" id="actividad-duration" min="5" max="180" value="30">
                        <small class="form-text text-muted">Tiempo máximo para completar</small>
                    </div>
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <strong><i class="fas fa-shield-alt"></i> Configuración Anti-fraude (Banco de Preguntas)</strong>
                        </div>
                        <div class="card-body py-2">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="actividad-randomize-order">
                                <label class="custom-control-label" for="actividad-randomize-order">
                                    <i class="fas fa-random text-info"></i> Aleatorizar orden de preguntas para cada estudiante
                                </label>
                                <small class="form-text text-muted">Las preguntas se mostrarán en un orden diferente para cada estudiante.</small>
                            </div>
                            <hr class="my-2">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="actividad-enable-bank" onchange="toggleActBankConfig()">
                                <label class="custom-control-label" for="actividad-enable-bank">
                                    <i class="fas fa-database text-warning"></i> Habilitar Banco de Preguntas
                                </label>
                                <small class="form-text text-muted">Crea más preguntas de las necesarias. Cada estudiante recibirá solo un subconjunto aleatorio.</small>
                            </div>
                            <div id="actividad-bank-details" style="display: none;">
                                <div class="form-group mt-3">
                                    <label for="actividad-questions-per-attempt"><i class="fas fa-tasks"></i> Preguntas por intento *</label>
                                    <input type="number" class="form-control" id="actividad-questions-per-attempt" min="1" value="5" oninput="updateActBankInfo()">
                                    <small class="form-text text-muted" id="actividad-bank-info-text">
                                        Se seleccionarán aleatoriamente de las preguntas del banco para cada estudiante.
                                    </small>
                                </div>
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="fas fa-shield-alt"></i> <strong>Anti-fraude activo:</strong>
                                    <ul class="mb-0 mt-1 small">
                                        <li>Cada estudiante recibirá un conjunto diferente de preguntas.</li>
                                        <li>Las valoraciones se redistribuirán automáticamente de forma proporcional.</li>
                                        <li>Minimiza la copia entre estudiantes.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="actividad-questions-container">
                        <!-- Las preguntas se agregarán aquí -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="addActividadQuestion()">
                        <i class="fas fa-plus"></i> Agregar Pregunta
                    </button>
                    <small class="form-text text-muted text-center d-block mt-2">
                        <i class="fas fa-info-circle"></i> Cada pregunta puede tener de 2 a 10 opciones. Marca las respuestas correctas.
                    </small>
                ` : '';

                Swal.fire({
                    title: `Crear ${tipoLabel}`,
                    html: `
                        <div class="text-left" style="max-height: 600px; overflow-y: auto;">
                            <input type="hidden" id="actividad-tipo" value="${tipo}">
                            <div class="form-group">
                                <label for="actividad-titulo">Título *</label>
                                <input type="text" class="form-control" id="actividad-titulo" placeholder="Título de la actividad">
                            </div>
                            <div class="form-group">
                                <label for="actividad-descripcion">Descripción</label>
                                <textarea class="form-control" id="actividad-descripcion" rows="3" placeholder="Descripción de la actividad"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="actividad-instrucciones">Instrucciones</label>
                                <textarea class="form-control" id="actividad-instrucciones" rows="3" placeholder="Instrucciones para los estudiantes"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="actividad-fecha-apertura">Fecha de Apertura</label>
                                        <input type="datetime-local" class="form-control" id="actividad-fecha-apertura">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="actividad-fecha-cierre">Fecha de Cierre</label>
                                        <input type="datetime-local" class="form-control" id="actividad-fecha-cierre">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="actividad-puntos">Puntos Máximos</label>
                                        <input type="number" class="form-control" id="actividad-puntos" min="1" max="1000" value="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="actividad-intentos">Intentos Permitidos</label>
                                        <input type="number" class="form-control" id="actividad-intentos" min="1" max="10" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="actividad-obligatoria" checked>
                                    <label class="custom-control-label" for="actividad-obligatoria">Actividad obligatoria</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="actividad-entregas-tardias">
                                    <label class="custom-control-label" for="actividad-entregas-tardias">Permitir entregas tardías</label>
                                </div>
                            </div>
                            ${gradingSection}
                            ${prerequisitosSection}
                            ${quizFields}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: `Crear ${tipoLabel}`,
                    cancelButtonText: 'Cancelar',
                    width: '900px',
                    didOpen: () => {
                        // Inicializar variables para preguntas
                        window.actividadQuestions = [];
                        window.actividadQuestionCounter = 0;
                        window.actividadOptionCounters = {};

                        // Función para mostrar/ocultar selección de prerrequisitos
                        window.toggleCreatePrereqsSelection = function() {
                            const hasPrereqs = document.getElementById('actividad-has-prereqs')?.checked || false;
                            const container = document.getElementById('create-prereqs-selection-container');
                            if (container) {
                                container.style.display = hasPrereqs ? 'block' : 'none';
                            }
                        };
                        
                        // Funciones para configuración del banco de preguntas
                        window.toggleActBankConfig = function() {
                            const enabled = document.getElementById('actividad-enable-bank').checked;
                            document.getElementById('actividad-bank-details').style.display = enabled ? 'block' : 'none';
                            if (enabled) {
                                document.getElementById('actividad-randomize-order').checked = true;
                                updateActBankInfo();
                            }
                        };
                        
                        window.updateActBankInfo = function() {
                            const totalQuestions = window.actividadQuestions.length;
                            const perAttempt = parseInt(document.getElementById('actividad-questions-per-attempt').value) || 1;
                            const infoText = document.getElementById('actividad-bank-info-text');
                            if (infoText) {
                                if (totalQuestions > 0) {
                                    if (perAttempt >= totalQuestions) {
                                        infoText.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Debe ser menor que el total (' + totalQuestions + '). Agrega más preguntas.</span>';
                                    } else {
                                        infoText.innerHTML = 'Se seleccionarán <strong>' + perAttempt + '</strong> de <strong>' + totalQuestions + '</strong> preguntas para cada estudiante.';
                                    }
                                } else {
                                    infoText.innerHTML = 'Agrega preguntas al banco primero.';
                                }
                            }
                        };
                        
                        // Si es quiz o evaluación, agregar una pregunta por defecto
                        if (requierePreguntas) {
                            setTimeout(() => addActividadQuestion(), 100);
                        }
                    },
                    preConfirm: () => {
                        const titulo = document.getElementById('actividad-titulo').value;
                        const descripcion = document.getElementById('actividad-descripcion').value;
                        const instrucciones = document.getElementById('actividad-instrucciones').value;
                        const fechaApertura = document.getElementById('actividad-fecha-apertura').value;
                        const fechaCierre = document.getElementById('actividad-fecha-cierre').value;
                        const puntos = document.getElementById('actividad-puntos').value;
                        const intentos = document.getElementById('actividad-intentos').value;

                        // Datos de calificación
                        const materialId = document.getElementById('actividad-material')?.value || '';
                        const porcentajeCurso = parseFloat(document.getElementById('actividad-porcentaje')?.value) || 0;
                        const notaMinimaAprobacion = parseFloat(document.getElementById('actividad-nota-minima')?.value) || 3.0;

                        if (!titulo.trim()) {
                            Swal.showValidationMessage('El título es requerido');
                            return false;
                        }

                        // Validación para Quiz y Evaluación
                        let quizData = null;
                        if (requierePreguntas) {
                            const duration = document.getElementById('actividad-duration').value;
                            
                            if (window.actividadQuestions.length < 1) {
                                Swal.showValidationMessage('Debes crear al menos 1 pregunta');
                                return false;
                            }
                            
                            const questions = [];
                            let totalQuestionPoints = 0;
                            
                            for (const questionId of window.actividadQuestions) {
                                const questionText = document.getElementById(`act-question-text-${questionId}`).value;
                                const questionPoints = document.getElementById(`act-question-points-${questionId}`).value;
                                
                                if (!questionText.trim()) {
                                    Swal.showValidationMessage('Todas las preguntas deben tener texto');
                                    return false;
                                }
                                
                                const optionsContainer = document.getElementById(`act-options-container-${questionId}`);
                                const optionRows = optionsContainer.querySelectorAll('.option-row');
                                const options = {};
                                const correctAnswers = [];
                                
                                if (optionRows.length < 2) {
                                    Swal.showValidationMessage('Cada pregunta debe tener al menos 2 opciones');
                                    return false;
                                }
                                
                                let hasEmptyOption = false;
                                optionRows.forEach((row, index) => {
                                    const letter = window.optionLetters[index];
                                    const textInput = row.querySelector('input[type="text"]');
                                    const checkbox = row.querySelector('input[type="checkbox"]');
                                    
                                    if (!textInput.value.trim()) {
                                        hasEmptyOption = true;
                                    }
                                    
                                    options[letter] = textInput.value;
                                    
                                    if (checkbox && checkbox.checked) {
                                        correctAnswers.push(letter);
                                    }
                                });
                                
                                if (hasEmptyOption) {
                                    Swal.showValidationMessage('Todas las opciones deben tener texto');
                                    return false;
                                }
                                
                                if (correctAnswers.length === 0) {
                                    Swal.showValidationMessage('Cada pregunta debe tener al menos una respuesta correcta');
                                    return false;
                                }

                                const puntosPregunta = parseFloat(questionPoints) || 0;

                                if (puntosPregunta <= 0) {
                                    Swal.showValidationMessage('Cada pregunta debe tener un porcentaje mayor a 0%');
                                    return false;
                                }

                                if (puntosPregunta > 100) {
                                    Swal.showValidationMessage('El porcentaje de cada pregunta no puede exceder 100%');
                                    return false;
                                }
                                
                                totalQuestionPoints += puntosPregunta;
                                
                                questions.push({
                                    id: questionId,
                                    text: questionText,
                                    points: puntosPregunta,
                                    options: options,
                                    correctAnswers: correctAnswers,
                                    isMultipleChoice: correctAnswers.length > 1
                                });
                            }

                            // Validar que la suma total no exceda 100%
                            if (totalQuestionPoints > 100) {
                                Swal.showValidationMessage('La suma de porcentajes de todas las preguntas no puede exceder 100% (actual: ' + totalQuestionPoints.toFixed(1) + '%)');
                                return false;
                            }

                            // Validar banco de preguntas
                            const enableBank = document.getElementById('actividad-enable-bank')?.checked || false;
                            const randomizeOrder = document.getElementById('actividad-randomize-order')?.checked || false;
                            const questionsPerAttempt = parseInt(document.getElementById('actividad-questions-per-attempt')?.value) || questions.length;

                            if (enableBank && questionsPerAttempt >= questions.length) {
                                Swal.showValidationMessage('Las preguntas por intento (' + questionsPerAttempt + ') deben ser menos que el total del banco (' + questions.length + '). Agrega más preguntas o reduce el número por intento.');
                                return false;
                            }
                            
                            quizData = {
                                duration: parseInt(duration),
                                questions: questions,
                                totalPoints: totalQuestionPoints,
                                quizConfig: {
                                    enableQuestionBank: enableBank,
                                    questionsPerAttempt: enableBank ? questionsPerAttempt : questions.length,
                                    randomizeOrder: randomizeOrder
                                }
                            };
                        }

                        // Obtener actividades prerrequisito seleccionadas
                        const hasPrereqs = document.getElementById('actividad-has-prereqs')?.checked || false;
                        let prerequisiteActivityIds = [];
                        let prereqsObligatorio = false;
                        if (hasPrereqs) {
                            document.querySelectorAll('.create-prereq-checkbox:checked').forEach(cb => {
                                prerequisiteActivityIds.push(parseInt(cb.value));
                            });
                            prereqsObligatorio = document.getElementById('actividad-prereqs-obligatorio')?.checked || false;
                        }

                        return {
                            tipo: tipo,
                            titulo: titulo,
                            descripcion: descripcion,
                            instrucciones: instrucciones,
                            fecha_apertura: fechaApertura,
                            fecha_cierre: fechaCierre,
                            puntos_maximos: parseInt(puntos),
                            intentos_permitidos: parseInt(intentos),
                            contenido_json: quizData,
                            material_id: materialId ? parseInt(materialId) : null,
                            porcentaje_curso: porcentajeCurso,
                            nota_minima_aprobacion: notaMinimaAprobacion,
                            prerequisite_activity_ids: prerequisiteActivityIds,
                            prereqs_obligatorio: prereqsObligatorio
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        guardarActividad(result.value);
                    }
                });
            };

            // Agregar pregunta a la actividad
            window.addActividadQuestion = function() {
                const questionId = ++window.actividadQuestionCounter;
                const container = document.getElementById('actividad-questions-container');
                window.actividadOptionCounters[questionId] = 0;

                // Calcular porcentaje disponible
                const porcentajeUsado = calcularPorcentajeTotalQuizEdit();
                const porcentajeDisponible = Math.max(0, 100 - porcentajeUsado).toFixed(1);
                
                const questionHtml = `
                    <div class="card mb-3 quiz-question-card" id="act-question-${questionId}" style="border-left: 3px solid #007bff;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-question-circle text-primary"></i> Pregunta ${window.actividadQuestions.length + 1}</h6>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeActividadQuestion(${questionId})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="form-group">
                                <label>Texto de la Pregunta *</label>
                                <input type="text" class="form-control" id="act-question-text-${questionId}" placeholder="Escribe la pregunta aquí">
                            </div>
                            <div class="form-group">
                                <label>Porcentaje de la Pregunta (%) <small class="text-muted">Suma máx: 100%</small></label>
                                <input type="number" class="form-control act-question-points-input" id="act-question-points-${questionId}" 
                                       min="0.1" max="100" step="0.1" value="" placeholder="Ej: 20"
                                       oninput="actualizarPorcentajeDisponibleQuizEdit()">
                                <small class="form-text text-muted">Disponible: <span class="act-puntos-disponibles">${porcentajeDisponible}</span>% de 100%</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">Opciones de Respuesta * <small class="text-muted">(marca las correctas)</small></label>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="addActividadQuestionOption(${questionId})">
                                    <i class="fas fa-plus"></i> Agregar Opción
                                </button>
                            </div>
                            <div id="act-options-container-${questionId}">
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Marca con el checkbox las respuestas correctas. Si hay varias correctas, el porcentaje se distribuye entre ellas.</small>
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', questionHtml);
                window.actividadQuestions.push(questionId);
                
                // Agregar 2 opciones por defecto
                addActividadQuestionOption(questionId);
                addActividadQuestionOption(questionId);

                // Actualizar porcentaje disponible en todas las preguntas
                actualizarPorcentajeDisponibleQuizEdit();

                // Actualizar info del banco de preguntas
                if (typeof updateActBankInfo === 'function') updateActBankInfo();
            };

            // Eliminar pregunta
            window.removeActividadQuestion = function(questionId) {
                document.getElementById(`act-question-${questionId}`).remove();
                window.actividadQuestions = window.actividadQuestions.filter(id => id !== questionId);
                delete window.actividadOptionCounters[questionId];
                
                // Renumerar preguntas
                window.actividadQuestions.forEach((id, index) => {
                    const questionCard = document.getElementById(`act-question-${id}`);
                    if (questionCard) {
                        const header = questionCard.querySelector('h6');
                        if (header) {
                            header.innerHTML = `<i class="fas fa-question-circle text-primary"></i> Pregunta ${index + 1}`;
                        }
                    }
                });

                // Actualizar porcentajes disponibles
                actualizarPorcentajeDisponibleQuizEdit();
                if (typeof updateActBankInfo === 'function') updateActBankInfo();
            };

            // Agregar opción a una pregunta
            window.addActividadQuestionOption = function(questionId) {
                const optionsContainer = document.getElementById(`act-options-container-${questionId}`);
                const currentOptions = optionsContainer.querySelectorAll('.option-row').length;
                
                if (currentOptions >= 10) {
                    Swal.showValidationMessage('Máximo 10 opciones por pregunta');
                    return;
                }
                
                const optionLetter = window.optionLetters[currentOptions];
                const optionHtml = `
                    <div class="input-group mb-2 option-row" id="act-option-${questionId}-${optionLetter}">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="checkbox" name="act-correct-answer-${questionId}" value="${optionLetter}" title="Marcar como correcta">
                            </div>
                            <span class="input-group-text"><strong>${optionLetter}</strong></span>
                        </div>
                        <input type="text" class="form-control" id="act-question-option-${optionLetter.toLowerCase()}-${questionId}" placeholder="Opción ${optionLetter}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeActividadQuestionOption(${questionId}, '${optionLetter}')" title="Eliminar" style="display: ${currentOptions >= 2 ? 'block' : 'none'}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
                window.actividadOptionCounters[questionId] = currentOptions + 1;
                updateActividadOptionRemoveButtons(questionId);
            };

            // Eliminar opción
            window.removeActividadQuestionOption = function(questionId, optionLetter) {
                const optionsContainer = document.getElementById(`act-options-container-${questionId}`);
                const currentOptions = optionsContainer.querySelectorAll('.option-row').length;
                
                if (currentOptions <= 2) {
                    return;
                }
                
                const optionElement = document.getElementById(`act-option-${questionId}-${optionLetter}`);
                if (optionElement) {
                    optionElement.remove();
                    window.actividadOptionCounters[questionId]--;
                    renumberActividadOptions(questionId);
                }
            };

            // Renumerar opciones
            window.renumberActividadOptions = function(questionId) {
                const optionsContainer = document.getElementById(`act-options-container-${questionId}`);
                const optionRows = optionsContainer.querySelectorAll('.option-row');
                
                optionRows.forEach((row, index) => {
                    const newLetter = window.optionLetters[index];
                    row.id = `act-option-${questionId}-${newLetter}`;
                    
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.value = newLetter;
                    
                    const letterSpan = row.querySelector('.input-group-text strong');
                    if (letterSpan) letterSpan.textContent = newLetter;
                    
                    const textInput = row.querySelector('input[type="text"]');
                    if (textInput) {
                        textInput.id = `act-question-option-${newLetter.toLowerCase()}-${questionId}`;
                        textInput.placeholder = `Opción ${newLetter}`;
                    }
                    
                    const removeBtn = row.querySelector('button');
                    if (removeBtn) {
                        removeBtn.setAttribute('onclick', `removeActividadQuestionOption(${questionId}, '${newLetter}')`);
                    }
                });
                
                updateActividadOptionRemoveButtons(questionId);
            };

            // Actualizar visibilidad de botones de eliminar
            window.updateActividadOptionRemoveButtons = function(questionId) {
                const optionsContainer = document.getElementById(`act-options-container-${questionId}`);
                const optionRows = optionsContainer.querySelectorAll('.option-row');
                const removeButtons = optionsContainer.querySelectorAll('.btn-outline-danger');
                
                removeButtons.forEach(btn => {
                    btn.style.display = optionRows.length > 2 ? 'block' : 'none';
                });
            };

            // Calcular porcentaje total asignado del quiz (edit)
            window.calcularPorcentajeTotalQuizEdit = function() {
                let total = 0;
                document.querySelectorAll('.act-question-points-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                return total;
            };

            // Actualizar porcentaje disponible en todas las preguntas (edit)
            window.actualizarPorcentajeDisponibleQuizEdit = function() {
                const porcentajeUsado = calcularPorcentajeTotalQuizEdit();
                const porcentajeDisponible = Math.max(0, 100 - porcentajeUsado).toFixed(1);
                document.querySelectorAll('.act-puntos-disponibles').forEach(span => {
                    span.textContent = porcentajeDisponible;
                    span.style.color = porcentajeUsado > 100 ? 'red' : 'inherit';
                });

                // Actualizar barra de progreso si existe
                const progressBar = document.getElementById('edit-quiz-points-progress');
                if (progressBar) {
                    const barWidth = Math.min(100, porcentajeUsado);
                    progressBar.style.width = barWidth + '%';
                    progressBar.textContent = porcentajeUsado.toFixed(1) + '% / 100%';
                    progressBar.className = 'progress-bar ' + (porcentajeUsado > 100 ? 'bg-danger' : porcentajeUsado === 100 ? 'bg-success' : 'bg-info');
                }
            };

            // Actualizar porcentaje disponible al seleccionar material
            window.updatePorcentajeDisponibleEdit = function() {
                const select = document.getElementById('actividad-material');
                const infoEl = document.getElementById('edit-porcentaje-disponible-info');
                if (!select || !infoEl) return;

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const disponible = selectedOption.getAttribute('data-porcentaje-disponible');
                    infoEl.innerHTML = `Disponible en este material: <strong>${disponible}%</strong>`;
                } else {
                    infoEl.textContent = 'Selecciona un material para ver el porcentaje disponible';
                }
            };

            // Validar porcentaje de actividad en tiempo real
            window.validarPorcentajeActividadEdit = function() {
                const select = document.getElementById('actividad-material');
                const porcentajeInput = document.getElementById('actividad-porcentaje');
                if (!select || !porcentajeInput) return;

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const disponible = parseFloat(selectedOption.getAttribute('data-porcentaje-disponible')) || 0;
                    const valor = parseFloat(porcentajeInput.value) || 0;
                    if (valor > disponible) {
                        porcentajeInput.style.borderColor = '#dc3545';
                    } else {
                        porcentajeInput.style.borderColor = '';
                    }
                }
            };

            // Guardar actividad en el servidor
            window.guardarActividad = function(data) {
                Swal.fire({
                    title: 'Guardando...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                $.ajax({
                    url: '/capacitaciones/cursos/{{ $curso->id }}/classroom/actividades',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ...data
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actividad creada!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Error al crear la actividad';
                        Swal.fire('Error', message, 'error');
                    }
                });
            };

            // Editar actividad existente
            window.editarActividad = function(actividadId) {
                // Redirigir al classroom para editar
                window.location.href = `/capacitaciones/cursos/{{ $curso->id }}/classroom#actividades`;
            };

            // Eliminar actividad
            window.eliminarActividad = function(actividadId) {
                Swal.fire({
                    title: '¿Eliminar actividad?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/capacitaciones/cursos/{{ $curso->id }}/classroom/actividades/${actividadId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('¡Eliminada!', response.message, 'success');
                                    $(`[data-actividad-id="${actividadId}"]`).fadeOut(300, function() {
                                        $(this).remove();
                                        // Mostrar mensaje si no hay actividades
                                        if ($('#actividades-container .actividad-item').length === 0) {
                                            $('#actividades-container').html(`
                                                <div class="text-center py-5" id="no-actividades">
                                                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No hay actividades disponibles</h5>
                                                    <p class="text-muted">Agrega tareas, quizzes o evaluaciones para tus estudiantes.</p>
                                                </div>
                                            `);
                                        }
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar la actividad', 'error');
                            }
                        });
                    }
                });
            };
        });
    </script>
@stop

