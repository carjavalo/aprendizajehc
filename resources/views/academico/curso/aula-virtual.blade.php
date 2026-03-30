@extends('adminlte::page')

@section('title', $curso->titulo . ' - Aula Virtual')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1><i class="fas fa-chalkboard-teacher text-primary"></i> {{ $curso->titulo }}</h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-layer-group"></i> {{ $curso->area->descripcion ?? 'Sin área' }} • 
                    <i class="fas fa-user-tie"></i> {{ $curso->instructor->full_name ?? 'Sin instructor' }} • 
                    <i class="fas fa-clock"></i> {{ $curso->duracion_horas ?? 'N/A' }} horas
                </p>
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('academico.cursos.disponibles') }}">Cursos</a></li>
                    <li class="breadcrumb-item active">Aula Virtual</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Panel Lateral - Lista de Materiales -->
        <div class="col-lg-3">
            <div class="card card-outline card-primary sticky-top" style="top: 70px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Contenido del Curso
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 75vh; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @forelse($materiales as $index => $material)
                            @php
                                // Verificar si el material tiene prerrequisito y si está completado
                                $tienePrerequisito = !empty($material->prerequisite_id);
                                $prerequisitoCompletado = true;
                                $prerequisitoNombre = '';
                                
                                if ($tienePrerequisito) {
                                    $prerequisitoCompletado = in_array($material->prerequisite_id, $materialesVistos);
                                    $prerequisito = $materiales->firstWhere('id', $material->prerequisite_id);
                                    $prerequisitoNombre = $prerequisito ? $prerequisito->titulo : 'Material previo';
                                }
                                
                                $materialBloqueado = $tienePrerequisito && !$prerequisitoCompletado;
                                $materialCompletado = in_array($material->id, $materialesVistos);
                            @endphp
                            <a href="#" 
                               class="list-group-item list-group-item-action material-item @if($index === 0 && !$materialBloqueado) active @endif {{ $materialBloqueado ? 'material-bloqueado' : '' }}" 
                               data-material-id="{{ $material->id }}"
                               data-material-tipo="{{ $material->tipo }}"
                               data-material-url="{{ $material->archivo_url }}"
                               data-material-bloqueado="{{ $materialBloqueado ? 'true' : 'false' }}"
                               data-prerequisito-nombre="{{ $prerequisitoNombre }}"
                               data-material-titulo="{{ $material->titulo }}"
                               data-material-descripcion="{{ $material->descripcion ?? '' }}">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        @if($materialBloqueado)
                                            <i class="fas fa-lock text-warning mr-2"></i>
                                        @else
                                            <i class="{{ $material->tipo_icon }} mr-2"></i>
                                        @endif
                                        <span class="material-titulo">{{ $material->titulo }}</span>
                                    </div>
                                    @if($materialCompletado)
                                        <i class="fas fa-check-circle text-success" title="Completado"></i>
                                    @elseif($materialBloqueado)
                                        <i class="fas fa-lock text-warning" title="Bloqueado - Completa: {{ $prerequisitoNombre }}"></i>
                                    @else
                                        <i class="far fa-circle text-muted" title="Pendiente"></i>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {!! $material->tipo_badge !!}
                                    @if($material->archivo_size)
                                        • {{ $material->archivo_size_formatted }}
                                    @endif
                                    @if($materialBloqueado)
                                        <br><span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Prerrequisito: {{ $prerequisitoNombre }}</span>
                                    @endif
                                </small>
                            </a>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="fas fa-folder-open fa-3x mb-2"></i>
                                <p>No hay materiales disponibles</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <div class="progress">
                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" 
                             style="width: {{ $progreso }}%" aria-valuenow="{{ $progreso }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ $progreso }}%
                        </div>
                    </div>
                    <small class="text-muted">Tu progreso en el curso</small>
                    @if($resumen['aprobado'])
                    <div class="mt-3 text-center">
                        <a href="{{ route('academico.curso.certificado', $curso->id) }}" target="_blank" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-certificate"></i> Descargar Certificado
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información del Curso -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información
                    </h3>
                </div>
                <div class="card-body">
                    @if($curso->fecha_inicio || $curso->fecha_fin)
                        <strong><i class="fas fa-calendar mr-1"></i> Duración</strong>
                        <p class="text-muted">
                            <small>
                                {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'N/A' }} - 
                                {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A' }}
                            </small>
                        </p>
                    @endif
                    
                    <strong><i class="fas fa-book mr-1"></i> Materiales</strong>
                    <p class="text-muted"><small>{{ $materiales->count() }} recursos disponibles</small></p>
                    
                    <strong><i class="fas fa-tasks mr-1"></i> Actividades</strong>
                    <p class="text-muted"><small>{{ $curso->actividades->count() }} tareas asignadas</small></p>
                </div>
            </div>
        </div>

        <!-- Área de Visualización Principal -->
        <div class="col-lg-9">
            <!-- Header del Material Actual -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title" id="current-material-title">
                        <i class="fas fa-play-circle"></i> 
                        @if($materiales->isNotEmpty())
                            {{ $materiales->first()->titulo }}
                        @else
                            Bienvenido al Aula Virtual
                        @endif
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="btn-marcar-completo">
                            <i class="fas fa-check"></i> Marcar como completado
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Área de Contenido Multimedia -->
                    <div id="material-viewer" class="bg-dark" style="min-height: 500px; position: relative;">
                        @if($materiales->isNotEmpty())
                            @php $firstMaterial = $materiales->first(); @endphp
                            
                            @if($firstMaterial->tipo === 'video')
                                <!-- Reproductor de Video -->
                                <video id="video-player" class="w-100" controls style="max-height: 600px;">
                                    <source src="{{ $firstMaterial->archivo_url }}" type="video/mp4">
                                    Tu navegador no soporta la reproducción de videos.
                                </video>
                            @elseif($firstMaterial->tipo === 'imagen')
                                <!-- Visor de Imágenes -->
                                <div class="text-center p-4">
                                    <img id="image-viewer" src="{{ $firstMaterial->archivo_url }}" 
                                         alt="{{ $firstMaterial->titulo }}" 
                                         class="img-fluid" 
                                         style="max-height: 600px; object-fit: contain;">
                                </div>
                            @elseif($firstMaterial->tipo === 'documento')
                                <!-- Visor de Documentos PDF -->
                                <iframe id="document-viewer" 
                                        src="{{ $firstMaterial->archivo_url }}" 
                                        class="w-100" 
                                        style="height: 600px; border: none;">
                                </iframe>
                            @else
                                <!-- Enlace de Descarga -->
                                <div class="text-center text-white p-5">
                                    <i class="fas fa-download fa-5x mb-4"></i>
                                    <h4>Material Descargable</h4>
                                    <a href="{{ $firstMaterial->archivo_url }}" 
                                       class="btn btn-light btn-lg mt-3" 
                                       download>
                                        <i class="fas fa-download"></i> Descargar Archivo
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-white p-5">
                                <i class="fas fa-graduation-cap fa-5x mb-4"></i>
                                <h3>¡Bienvenido al Aula Virtual!</h3>
                                <p class="lead">Aún no hay materiales disponibles en este curso</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Descripción del Material -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-align-left"></i> Descripción
                    </h3>
                </div>
                <div class="card-body">
                    <p id="material-description">
                        @if($materiales->isNotEmpty() && $materiales->first()->descripcion)
                            {{ $materiales->first()->descripcion }}
                        @else
                            <span class="text-muted">Sin descripción disponible</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Navegación entre Materiales -->
            @if($materiales->count() > 1)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-primary" id="btn-anterior" disabled>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <span class="text-muted">
                            Material <span id="current-index">1</span> de {{ $materiales->count() }}
                        </span>
                        <button class="btn btn-outline-primary" id="btn-siguiente">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sección de Actividades -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i> Actividades del Curso
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($curso->actividades as $actividad)
                        @php
                            $esQuizOEvaluacion = in_array($actividad->tipo, ['quiz', 'evaluacion']);
                            $tienePreguntas = $actividad->contenido_json && isset($actividad->contenido_json['questions']) && count($actividad->contenido_json['questions']) > 0;
                            $completada = in_array($actividad->id, $actividadesCompletadas);
                            
                            // Determinar icono y color según tipo
                            $iconoTipo = match($actividad->tipo) {
                                'quiz' => 'fa-question-circle',
                                'evaluacion' => 'fa-clipboard-check',
                                'tarea' => 'fa-file-alt',
                                'proyecto' => 'fa-project-diagram',
                                default => 'fa-tasks'
                            };
                            $colorTipo = match($actividad->tipo) {
                                'quiz' => 'bg-info',
                                'evaluacion' => 'bg-danger',
                                'tarea' => 'bg-primary',
                                'proyecto' => 'bg-success',
                                default => 'bg-warning'
                            };
                        @endphp
                        
                        <div class="info-box @if($completada) bg-success @endif">
                            <span class="info-box-icon {{ $colorTipo }}">
                                <i class="fas {{ $iconoTipo }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">
                                    {{ $actividad->titulo }}
                                    <span class="badge badge-secondary ml-2">{{ ucfirst($actividad->tipo) }}</span>
                                </span>
                                <span class="info-box-number">
                                    @if($actividad->fecha_cierre)
                                        Fecha límite: {{ $actividad->fecha_cierre->format('d/m/Y H:i') }}
                                    @else
                                        Sin fecha límite
                                    @endif
                                    @if($esQuizOEvaluacion && $tienePreguntas)
                                        • {{ count($actividad->contenido_json['questions']) }} preguntas
                                        • {{ $actividad->contenido_json['duration'] ?? 30 }} min
                                    @endif
                                </span>
                                
                                @if($completada)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Completada
                                    </span>
                                @else
                                    @if($esQuizOEvaluacion && $tienePreguntas)
                                        {{-- Botón para iniciar Quiz/Evaluación desde modal --}}
                                        <button type="button" class="btn btn-sm btn-{{ $actividad->tipo == 'quiz' ? 'info' : 'danger' }}" 
                                                onclick="iniciarQuiz({{ $actividad->id }})">
                                            <i class="fas fa-play-circle"></i> Iniciar {{ ucfirst($actividad->tipo) }}
                                        </button>
                                        {{-- Datos del quiz en textarea oculto --}}
                                        <textarea id="quiz-data-{{ $actividad->id }}" style="display: none;">{!! json_encode($actividad, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</textarea>
                                    @else
                                        <a href="{{ route('academico.curso.actividades', $curso->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-paper-plane"></i> Ver y Entregar
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> No hay actividades asignadas
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .material-item {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .material-item:hover {
        background-color: #f8f9fa;
    }
    
    .material-item.active {
        background-color: #007bff;
        color: white;
    }
    
    .material-item.active .material-titulo,
    .material-item.active small {
        color: white !important;
    }
    
    .material-item.active .badge {
        background-color: white !important;
        color: #007bff !important;
    }
    
    /* Estilos para materiales bloqueados */
    .material-item.material-bloqueado {
        background-color: #fff3cd;
        cursor: not-allowed;
        opacity: 0.8;
    }
    
    .material-item.material-bloqueado:hover {
        background-color: #ffeeba;
    }
    
    .material-item.material-bloqueado .material-titulo {
        color: #856404;
    }
    
    .sticky-top {
        position: sticky;
        will-change: transform;
    }
    
    #material-viewer {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress {
        height: 25px;
    }
    
    .progress-bar {
        line-height: 25px;
        font-weight: bold;
    }
    
    /* Estilos para el reproductor de video */
    video {
        background-color: #000;
    }
    
    /* Animaciones suaves */
    .fade-in {
        animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let currentMaterialId = @if($materiales->isNotEmpty()) {{ $materiales->first()->id }} @else null @endif;
    let materiales = {!! json_encode($materiales, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    let currentIndex = 0;
    let materialesVistos = {!! json_encode($materialesVistos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

    // Función para cargar un material
    function loadMaterial(material, index) {
        currentMaterialId = material.id;
        currentIndex = index;
        
        // Actualizar título
        $('#current-material-title').html('<i class="fas fa-play-circle"></i> ' + material.titulo);
        $('#current-index').text(index + 1);
        
        // Actualizar descripción
        $('#material-description').html(material.descripcion || '<span class="text-muted">Sin descripción disponible</span>');
        
        // Limpiar el visor
        let viewer = $('#material-viewer');
        viewer.html('');
        viewer.addClass('fade-in');
        
        // Cargar contenido según el tipo
        switch(material.tipo) {
            case 'video':
                viewer.html(`
                    <video id="video-player" class="w-100" controls style="max-height: 600px;">
                        <source src="${material.archivo_url}" type="video/mp4">
                        Tu navegador no soporta la reproducción de videos.
                    </video>
                `);
                break;
                
            case 'imagen':
                viewer.html(`
                    <div class="text-center p-4 w-100">
                        <img src="${material.archivo_url}" 
                             alt="${material.titulo}" 
                             class="img-fluid" 
                             style="max-height: 600px; object-fit: contain;">
                    </div>
                `);
                break;
                
            case 'documento':
                viewer.html(`
                    <iframe src="${material.archivo_url}" 
                            class="w-100" 
                            style="height: 600px; border: none;">
                    </iframe>
                `);
                break;
                
            default:
                viewer.html(`
                    <div class="text-center text-white p-5">
                        <i class="fas fa-download fa-5x mb-4"></i>
                        <h4>Material Descargable</h4>
                        <a href="${material.archivo_url}" 
                           class="btn btn-light btn-lg mt-3" 
                           download>
                            <i class="fas fa-download"></i> Descargar Archivo
                        </a>
                    </div>
                `);
        }
        
        // Actualizar botones de navegación
        $('#btn-anterior').prop('disabled', index === 0);
        $('#btn-siguiente').prop('disabled', index === materiales.length - 1);
        
        // Actualizar elemento activo en la lista
        $('.material-item').removeClass('active');
        $(`.material-item[data-material-id="${material.id}"]`).addClass('active');
        
        // Actualizar estado del botón de completado
        updateCompleteButton();
    }
    
    // Click en un material de la lista
    $('.material-item').click(function(e) {
        e.preventDefault();
        
        // Verificar si el material está bloqueado
        let bloqueado = $(this).data('material-bloqueado') === 'true' || $(this).data('material-bloqueado') === true;
        let prerequisitoNombre = $(this).data('prerequisito-nombre');
        
        if (bloqueado) {
            Swal.fire({
                icon: 'warning',
                title: 'Material Bloqueado',
                html: `<p>Este material tiene un prerrequisito que debes completar primero.</p>
                       <p><strong>Prerrequisito:</strong> ${prerequisitoNombre}</p>`,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        let materialId = $(this).data('material-id');
        let material = materiales.find(m => m.id == materialId);
        let index = materiales.findIndex(m => m.id == materialId);
        
        if (material) {
            loadMaterial(material, index);
        }
    });
    
    // Navegación con botones
    $('#btn-anterior').click(function() {
        if (currentIndex > 0) {
            // Verificar si el material anterior está bloqueado
            let prevMaterial = materiales[currentIndex - 1];
            let prevItem = $(`.material-item[data-material-id="${prevMaterial.id}"]`);
            let bloqueado = prevItem.data('material-bloqueado') === 'true' || prevItem.data('material-bloqueado') === true;
            
            if (bloqueado) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Material Bloqueado',
                    text: 'El material anterior tiene un prerrequisito pendiente.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            
            loadMaterial(prevMaterial, currentIndex - 1);
        }
    });
    
    $('#btn-siguiente').click(function() {
        if (currentIndex < materiales.length - 1) {
            // Verificar si el siguiente material está bloqueado
            let nextMaterial = materiales[currentIndex + 1];
            let nextItem = $(`.material-item[data-material-id="${nextMaterial.id}"]`);
            let bloqueado = nextItem.data('material-bloqueado') === 'true' || nextItem.data('material-bloqueado') === true;
            
            if (bloqueado) {
                let prerequisitoNombre = nextItem.data('prerequisito-nombre');
                Swal.fire({
                    icon: 'warning',
                    title: 'Material Bloqueado',
                    html: `<p>El siguiente material tiene un prerrequisito que debes completar primero.</p>
                           <p><strong>Prerrequisito:</strong> ${prerequisitoNombre}</p>`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            
            loadMaterial(nextMaterial, currentIndex + 1);
        }
    });
    
    // Marcar material como completado
    $('#btn-marcar-completo').click(function() {
        if (!currentMaterialId) {
            Swal.fire('Error', 'No hay material seleccionado', 'error');
            return;
        }
        
        $.ajax({
            url: '{{ route("academico.curso.material.marcar", ["curso" => $curso->id, "material" => ":material"]) }}'.replace(':material', currentMaterialId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Completado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Marcar como visto en la lista
                    materialesVistos.push(currentMaterialId);
                    updateCompleteButton();
                    
                    // Actualizar icono en la lista
                    let item = $(`.material-item[data-material-id="${currentMaterialId}"]`);
                    item.find('.fa-circle').removeClass('far text-muted').addClass('fas fa-check-circle text-success');
                    
                    // Actualizar progreso sin reload completo
                    setTimeout(() => {
                        if (typeof updateProgress === 'function') {
                            updateProgress();
                        }
                    }, 2000);
                }
            },
            error: function(xhr) {
                let errorMsg = 'No se pudo marcar el material como completado';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });
    
    // Actualizar estado del botón de completado
    function updateCompleteButton() {
        if (materialesVistos.includes(currentMaterialId)) {
            $('#btn-marcar-completo')
                .removeClass('btn-success')
                .addClass('btn-secondary')
                .html('<i class="fas fa-check"></i> Completado')
                .prop('disabled', true);
        } else {
            $('#btn-marcar-completo')
                .removeClass('btn-secondary')
                .addClass('btn-success')
                .html('<i class="fas fa-check"></i> Marcar como completado')
                .prop('disabled', false);
        }
    }
    
    // Inicializar estado del botón
    updateCompleteButton();
});

// ============================================
// FUNCIONES PARA QUIZ Y EVALUACIONES
// ============================================

var quizTimer = null;
var tiempoInicio = null;

// Función para iniciar el quiz/evaluación
function iniciarQuiz(actividadId) {
    const quizDataElement = document.getElementById('quiz-data-' + actividadId);
    
    if (!quizDataElement) {
        Swal.fire('Error', 'No se encontraron los datos del quiz', 'error');
        return;
    }
    
    try {
        const actividad = JSON.parse(quizDataElement.value);
        
        // Fix for double-encoded JSON
        if (typeof actividad.contenido_json === 'string') {
            try {
                actividad.contenido_json = JSON.parse(actividad.contenido_json);
            } catch (e) {
                console.error('Error parsing nested JSON:', e);
            }
        }
        
        if (!actividad || !actividad.contenido_json) {
            Swal.fire('Error', 'No se encontraron las preguntas', 'error');
            return;
        }
        
        mostrarModalQuiz(actividadId, actividad);
    } catch (e) {
        console.error('Error parsing quiz data:', e);
        Swal.fire('Error', 'Error al procesar los datos', 'error');
    }
}

// Mostrar modal del quiz/evaluación
function mostrarModalQuiz(actividadId, actividad) {
    const quizData = actividad.contenido_json;
    const preguntas = quizData.questions || [];
    const duracion = quizData.duration || 30;
    const tipoActividad = actividad.tipo === 'evaluacion' ? 'Evaluación' : 'Quiz';
    
    if (preguntas.length === 0) {
        Swal.fire('Error', 'No hay preguntas configuradas', 'error');
        return;
    }
    
    let preguntasHTML = '';
    preguntas.forEach((pregunta, index) => {
        const esMultiple = (pregunta.isMultipleChoice === true) || 
                           (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
        const inputType = esMultiple ? 'checkbox' : 'radio';
        const indicadorTipo = esMultiple ? '<span class="badge badge-info ml-2"><i class="fas fa-check-double"></i> Múltiple respuesta</span>' : '';
        
        preguntasHTML += `
            <div class="quiz-question" data-multiple="${esMultiple}">
                <h5><span class="badge badge-primary">${index + 1}</span> ${pregunta.text} ${indicadorTipo}</h5>
                <small class="text-muted"><i class="fas fa-percentage"></i> ${pregunta.points}% del quiz</small>
                ${esMultiple ? '<br><small class="text-info"><i class="fas fa-info-circle"></i> Selecciona todas las respuestas correctas</small>' : ''}
                <div class="mt-3">
        `;
        
        // Manejar diferentes formatos de opciones
        if (pregunta.options && Array.isArray(pregunta.options)) {
            pregunta.options.forEach((opcion, opIndex) => {
                const letra = String.fromCharCode(65 + opIndex); // A, B, C, D...
                const textoOpcion = typeof opcion === 'object' ? opcion.text : opcion;
                preguntasHTML += `
                    <label class="quiz-option">
                        <input type="${inputType}" name="pregunta_${pregunta.id}" value="${letra}"
                               ${esMultiple ? `data-pregunta-id="${pregunta.id}"` : ''}>
                        <strong>${letra})</strong> ${textoOpcion}
                    </label>
                `;
            });
        } else if (pregunta.options && typeof pregunta.options === 'object') {
            Object.keys(pregunta.options).forEach(opcion => {
                preguntasHTML += `
                    <label class="quiz-option">
                        <input type="${inputType}" name="pregunta_${pregunta.id}" value="${opcion}"
                               ${esMultiple ? `data-pregunta-id="${pregunta.id}"` : ''}>
                        <strong>${opcion})</strong> ${pregunta.options[opcion]}
                    </label>
                `;
            });
        }
        
        preguntasHTML += `
                </div>
            </div>
        `;
    });
    
    Swal.fire({
        title: `<i class="fas fa-${actividad.tipo === 'evaluacion' ? 'clipboard-check' : 'question-circle'}"></i> ${actividad.titulo}`,
        html: `
            <div class="text-center mb-3">
                <div class="quiz-timer" id="quiz-timer">
                    <i class="fas fa-clock"></i> <span id="tiempo-restante">${duracion}:00</span>
                </div>
                <small class="text-muted">Total de puntos: ${quizData.totalPoints || actividad.puntos_maximos}</small>
            </div>
            <div id="quiz-preguntas" style="max-height: 400px; overflow-y: auto; text-align: left;">
                ${preguntasHTML}
            </div>
        `,
        width: '800px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check-circle"></i> Enviar Respuestas',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            iniciarTemporizador(duracion, actividadId, actividad);
            
            // Manejar selección de opciones - radio buttons
            $(document).off('click.quizRadio').on('click.quizRadio', '.quiz-option input[type="radio"]', function() {
                const name = $(this).attr('name');
                $(`input[name="${name}"]`).closest('.quiz-option').removeClass('selected');
                $(this).closest('.quiz-option').addClass('selected');
            });
            
            // Manejar selección de opciones - checkboxes
            $(document).off('click.quizCheckbox').on('click.quizCheckbox', '.quiz-option input[type="checkbox"]', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.quiz-option').addClass('selected');
                } else {
                    $(this).closest('.quiz-option').removeClass('selected');
                }
            });
            
            // Click en el label activa selección visual
            $(document).off('click.quizOption').on('click.quizOption', '.quiz-option', function(e) {
                if ($(e.target).is('input')) return;
                const input = $(this).find('input');
                if (input.attr('type') === 'radio') {
                    input.prop('checked', true).trigger('click');
                } else {
                    input.prop('checked', !input.prop('checked')).trigger('click');
                }
            });
        },
        willClose: () => {
            if (quizTimer) {
                clearInterval(quizTimer);
            }
            $(document).off('click.quizRadio click.quizCheckbox click.quizOption');
        },
        preConfirm: () => {
            const respuestas = {};
            let todasRespondidas = true;
            
            preguntas.forEach(pregunta => {
                const esMultiple = (pregunta.isMultipleChoice === true) || 
                                   (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
                
                if (esMultiple) {
                    // Para preguntas de múltiple respuesta, obtener todos los checkboxes marcados
                    const respuestasSeleccionadas = [];
                    $(`input[name="pregunta_${pregunta.id}"]:checked`).each(function() {
                        respuestasSeleccionadas.push($(this).val());
                    });
                    
                    if (respuestasSeleccionadas.length > 0) {
                        respuestas[pregunta.id] = respuestasSeleccionadas;
                    } else {
                        todasRespondidas = false;
                    }
                } else {
                    // Para preguntas de respuesta única
                    const respuesta = $(`input[name="pregunta_${pregunta.id}"]:checked`).val();
                    if (respuesta) {
                        respuestas[pregunta.id] = respuesta;
                    } else {
                        todasRespondidas = false;
                    }
                }
            });
            
            if (!todasRespondidas) {
                Swal.showValidationMessage('Por favor responde todas las preguntas');
                return false;
            }
            
            return respuestas;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            enviarRespuestasQuiz(actividadId, result.value);
        }
    });
}

// Iniciar temporizador
function iniciarTemporizador(duracionMinutos, actividadId, actividad) {
    tiempoInicio = Date.now();
    let tiempoRestante = duracionMinutos * 60;
    
    quizTimer = setInterval(() => {
        tiempoRestante--;
        
        const minutos = Math.floor(tiempoRestante / 60);
        const segundos = tiempoRestante % 60;
        
        $('#tiempo-restante').text(`${minutos}:${segundos.toString().padStart(2, '0')}`);
        
        if (tiempoRestante <= 60) {
            $('#tiempo-restante').parent().css('color', '#dc3545');
        }
        
        if (tiempoRestante <= 0) {
            clearInterval(quizTimer);
            Swal.close();
            Swal.fire({
                icon: 'warning',
                title: 'Tiempo Agotado',
                text: 'El tiempo ha terminado. Se enviarán las respuestas marcadas.',
                confirmButtonText: 'Entendido'
            }).then(() => {
                const respuestas = {};
                const preguntas = actividad.contenido_json.questions || [];
                preguntas.forEach(pregunta => {
                    const esMultiple = (pregunta.isMultipleChoice === true) || 
                                       (pregunta.correctAnswers && pregunta.correctAnswers.length > 1);
                    if (esMultiple) {
                        const checked = [];
                        $(`input[name="pregunta_${pregunta.id}"]:checked`).each(function() {
                            checked.push($(this).val());
                        });
                        if (checked.length > 0) respuestas[pregunta.id] = checked;
                    } else {
                        const respuesta = $(`input[name="pregunta_${pregunta.id}"]:checked`).val();
                        if (respuesta) respuestas[pregunta.id] = respuesta;
                    }
                });
                enviarRespuestasQuiz(actividadId, respuestas);
            });
        }
    }, 1000);
}

// Enviar respuestas
function enviarRespuestasQuiz(actividadId, respuestas) {
    const tiempoTranscurrido = Math.floor((Date.now() - tiempoInicio) / 1000);
    
    Swal.fire({
        title: 'Enviando respuestas...',
        html: '<i class="fas fa-spinner fa-spin fa-3x"></i>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    $.ajax({
        url: '{{ route("academico.curso.quiz.resolver", [$curso->id, ":actividadId"]) }}'.replace(':actividadId', actividadId),
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            respuestas: respuestas,
            tiempo_transcurrido: tiempoTranscurrido
        },
        success: function(response) {
            if (response.success) {
                mostrarResultados(response);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Error al enviar las respuestas';
            Swal.fire('Error', message, 'error');
        }
    });
}

// Mostrar resultados
function mostrarResultados(response) {
    const resultados = response.resultados || [];
    const porcentaje = response.porcentaje || 0;
    const aprobado = response.aprobado || false;
    
    let resultadosHTML = '';
    resultados.forEach((resultado, index) => {
        const claseResultado = resultado.es_correcta ? 'quiz-result-correct' : 'quiz-result-incorrect';
        const iconoResultado = resultado.es_correcta ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
        
        resultadosHTML += `
            <div class="quiz-question ${claseResultado}">
                <div class="d-flex justify-content-between align-items-start">
                    <h6><span class="badge badge-secondary">${index + 1}</span> ${resultado.pregunta}</h6>
                    ${iconoResultado}
                </div>
                <p class="mb-1"><strong>Tu respuesta:</strong> ${resultado.respuesta_estudiante || 'Sin respuesta'}</p>
                ${!resultado.es_correcta ? `<p class="mb-1"><strong>Respuesta correcta:</strong> ${resultado.respuesta_correcta}</p>` : ''}
                <p class="mb-0"><strong>Puntos:</strong> ${resultado.puntos}</p>
            </div>
        `;
    });
    
    Swal.fire({
        icon: aprobado ? 'success' : 'warning',
        title: aprobado ? '¡Felicitaciones!' : 'Completado',
        html: `
            <div class="text-center mb-4">
                <h2 class="display-4">${porcentaje}%</h2>
                <p class="lead">${response.puntos_obtenidos} de ${response.puntos_maximos} puntos</p>
                <span class="badge badge-${aprobado ? 'success' : 'warning'} badge-pill px-3 py-2">
                    ${aprobado ? 'APROBADO' : 'NO APROBADO'}
                </span>
            </div>
            <div style="max-height: 400px; overflow-y: auto; text-align: left;">
                <h5 class="mb-3">Revisión de Respuestas:</h5>
                ${resultadosHTML}
            </div>
        `,
        width: '800px',
        confirmButtonText: '<i class="fas fa-check"></i> Entendido',
        confirmButtonColor: '#007bff'
    }).then(() => {
        // Actualizar sin reload completo
        if (typeof loadTabContent === 'function') {
            loadTabContent('actividades', '#actividades-content');
        }
    });
}
</script>

<style>
    /* Quiz Styles */
    .quiz-question {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .quiz-option {
        padding: 12px;
        margin: 8px 0;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
    }
    
    .quiz-option:hover {
        background: #e9ecef;
        border-color: #007bff;
    }
    
    .quiz-option input[type="radio"] {
        margin-right: 10px;
    }
    
    .quiz-option.selected {
        background: #cfe2ff;
        border-color: #007bff;
    }
    
    .quiz-timer {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
    }
    
    .quiz-result-correct {
        background: #d4edda;
        border-left-color: #28a745;
    }
    
    .quiz-result-incorrect {
        background: #f8d7da;
        border-left-color: #dc3545;
    }
</style>
@stop

