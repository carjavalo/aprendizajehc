@extends('admin.layouts.master')

@section('title', 'Control Pedagógico')

@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Control Pedagógico</h1>
@stop

@section('content')
<div class="gradebook-container">
    @if(isset($sinCursos) && $sinCursos)
        {{-- Estado vacío: no hay cursos disponibles --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                <h4>No hay cursos disponibles</h4>
                <p class="text-muted">
                    @if(auth()->user()->role === 'Docente')
                        No tiene cursos asignados actualmente. Contacte al administrador para que le asigne cursos desde 
                        <strong>Configuración → Asignación de Cursos</strong>.
                    @else
                        No se encontraron cursos activos en el sistema. Verifique que existan cursos con estado <strong>activo</strong> 
                        en <strong>Configuración → Cursos</strong>.
                    @endif
                </p>
            </div>
        </div>
    @else
    <!-- Selector de Curso -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="font-weight-bold mb-2">
                        <i class="fas fa-book-open text-primary"></i> Seleccionar Curso
                    </label>
                    <select id="cursoSelector" class="form-control form-control-lg">
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ $curso->id == $cursoActual->id ? 'selected' : '' }}>
                                {{ $curso->titulo }} - {{ $curso->codigo_acceso }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Estudiantes</span>
                                <span class="stat-value">{{ count($estudiantes) }}</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Aprobados</span>
                                <span class="stat-value">{{ collect($estudiantes)->where('estado', 'passed')->count() }}</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">En Riesgo</span>
                                <span class="stat-value">{{ collect($estudiantes)->where('estado', 'at_risk')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estructura de Evaluación -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Estructura de Evaluación</h5>
        </div>
        <div class="card-body">
            <div class="evaluation-structure">
                @foreach($estructuraEvaluacion as $item)
                    <div class="eval-item">
                        <div class="eval-header">
                            <span class="eval-name">
                                <i class="fas fa-{{ $item['tipo'] == 'material' ? 'file-alt' : 'tasks' }}"></i>
                                {{ $item['nombre'] }}
                            </span>
                            <span class="eval-weight badge badge-primary">{{ number_format($item['peso'], 2) }}%</span>
                        </div>
                        @if(!empty($item['componentes']))
                            <div class="eval-components">
                                @foreach($item['componentes'] as $componente)
                                    <span class="component-badge">
                                        <i class="fas fa-{{ $componente['tipo'] == 'tarea' ? 'clipboard-check' : ($componente['tipo'] == 'quiz' ? 'question-circle' : 'file-alt') }}"></i>
                                        {{ Str::limit($componente['nombre'], 20) }} 
                                        <strong class="text-primary">({{ number_format($componente['peso'], 2) }}%)</strong>
                                        @if(in_array($componente['tipo'], ['quiz', 'evaluacion']))
                                            @if($componente['habilitado'])
                                                <span class="badge badge-success badge-sm ml-1" title="Habilitado"><i class="fas fa-check-circle"></i></span>
                                            @else
                                                <span class="badge badge-secondary badge-sm ml-1" title="Deshabilitado"><i class="fas fa-ban"></i></span>
                                            @endif
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Panel de Activación de Quiz/Evaluaciones (solo para roles autorizados) --}}
    @if(in_array(auth()->user()->role, ['Super Admin', 'Administrador', 'Operador', 'Docente']))
        @php
            $quizActividades = [];
            foreach ($estructuraEvaluacion as $item) {
                if (!empty($item['componentes'])) {
                    foreach ($item['componentes'] as $comp) {
                        if (in_array($comp['tipo'], ['quiz', 'evaluacion'])) {
                            $quizActividades[] = array_merge($comp, ['material' => $item['nombre']]);
                        }
                    }
                }
            }
        @endphp
        @if(count($quizActividades) > 0)
            <div class="card shadow-sm mb-4 toggle-panel-card">
                <div class="card-header toggle-panel-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-toggle-on"></i> Activar / Desactivar Quiz y Evaluaciones
                        </h5>
                        <span class="badge badge-light toggle-counter" id="toggleCounter">
                            <span id="activasCount">{{ collect($quizActividades)->where('habilitado', true)->count() }}</span> 
                            / {{ count($quizActividades) }} activas
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle"></i> 
                        Activa o desactiva los Quiz y Evaluaciones para que los estudiantes puedan realizarlos.
                        Los cambios se aplican inmediatamente.
                    </p>
                    <div class="toggle-activities-grid">
                        @foreach($quizActividades as $qa)
                            <div class="toggle-activity-card {{ $qa['habilitado'] ? 'toggle-active' : 'toggle-inactive' }}" 
                                 id="toggle-card-{{ $qa['id'] }}">
                                <div class="toggle-activity-top">
                                    <div class="toggle-activity-icon {{ $qa['tipo'] === 'quiz' ? 'icon-quiz' : 'icon-eval' }}">
                                        <i class="fas fa-{{ $qa['tipo'] === 'quiz' ? 'question-circle' : 'file-alt' }}"></i>
                                    </div>
                                    <div class="toggle-activity-info">
                                        <span class="toggle-activity-type badge {{ $qa['tipo'] === 'quiz' ? 'badge-info' : 'badge-warning' }}">
                                            {{ $qa['tipo'] === 'quiz' ? 'Quiz' : 'Evaluación' }}
                                        </span>
                                        <h6 class="toggle-activity-name mb-0">{{ $qa['nombre'] }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-folder-open"></i> {{ $qa['material'] }}
                                            &bull; <strong>{{ number_format($qa['peso'], 1) }}%</strong>
                                        </small>
                                    </div>
                                </div>
                                <div class="toggle-activity-bottom">
                                    <span class="toggle-status-label" id="toggle-label-{{ $qa['id'] }}">
                                        @if($qa['habilitado'])
                                            <i class="fas fa-check-circle text-success"></i> Habilitado
                                        @else
                                            <i class="fas fa-ban text-secondary"></i> Deshabilitado
                                        @endif
                                    </span>
                                    <label class="toggle-switch" title="{{ $qa['habilitado'] ? 'Desactivar' : 'Activar' }}">
                                        <input type="checkbox" 
                                               class="toggle-actividad-check" 
                                               data-actividad-id="{{ $qa['id'] }}" 
                                               data-nombre="{{ $qa['nombre'] }}"
                                               data-tipo="{{ $qa['tipo'] }}"
                                               {{ $qa['habilitado'] ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Panel de Reintentos para Evaluaciones Reprobadas (solo roles autorizados) --}}
    @if(in_array(auth()->user()->role, ['Super Admin', 'Administrador', 'Operador', 'Docente']))
        @php
            $reprobadosQuiz = [];
            foreach ($estructuraEvaluacion as $item) {
                if (!empty($item['componentes'])) {
                    foreach ($item['componentes'] as $comp) {
                        if (in_array($comp['tipo'], ['quiz', 'evaluacion'])) {
                            $estudiantesReprobados = [];
                            foreach ($estudiantes as $est) {
                                $califs = $est['calificaciones']['material_' . $item['id']] ?? [];
                                $key = $comp['tipo'] . '_' . $comp['id'];
                                $nota = $califs[$key] ?? 0;
                                $porcentajeNota = ($nota / 5.0) * 100;
                                if ($nota > 0 && $porcentajeNota < 60) {
                                    $estudiantesReprobados[] = [
                                        'id' => $est['id'],
                                        'nombre' => $est['nombre'],
                                        'email' => $est['email'],
                                        'nota' => $nota,
                                        'avatar' => $est['avatar'] ?? null,
                                    ];
                                }
                            }
                            if (count($estudiantesReprobados) > 0) {
                                $reprobadosQuiz[] = [
                                    'actividad_id' => $comp['id'],
                                    'nombre' => $comp['nombre'],
                                    'tipo' => $comp['tipo'],
                                    'material' => $item['nombre'],
                                    'estudiantes' => $estudiantesReprobados,
                                ];
                            }
                        }
                    }
                }
            }
            $totalReprobados = collect($reprobadosQuiz)->sum(function($rq) { return count($rq['estudiantes']); });
        @endphp

        @if($totalReprobados > 0)
            <div class="card shadow-sm mb-4 retry-panel-card">
                <div class="card-header retry-panel-header" 
                     data-toggle="collapse" data-target="#retryPanelBody" 
                     aria-expanded="false" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-redo-alt"></i> Reintentos — Evaluaciones Reprobadas
                            <i class="fas fa-chevron-down ml-2 retry-collapse-icon" style="font-size: 0.8rem; transition: transform 0.3s;"></i>
                        </h5>
                        <span class="badge badge-light retry-total-badge">
                            <i class="fas fa-user-times"></i> {{ $totalReprobados }} reprobado{{ $totalReprobados > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <div id="retryPanelBody" class="collapse">
                    <div class="card-body py-3">
                        <p class="text-muted mb-3" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle"></i>
                            Habilita reintentos individuales o grupales para estudiantes que reprobaron Quiz o Evaluaciones.
                            Al habilitar, se elimina su entrega actual y podrán realizarla de nuevo.
                        </p>
                        @foreach($reprobadosQuiz as $rqIndex => $rq)
                            <div class="retry-activity-group {{ $rqIndex < count($reprobadosQuiz) - 1 ? 'mb-3' : '' }}">
                                <div class="retry-activity-header">
                                    <div class="retry-activity-title">
                                        <span class="retry-type-badge {{ $rq['tipo'] === 'quiz' ? 'badge-info' : 'badge-warning' }}">
                                            <i class="fas fa-{{ $rq['tipo'] === 'quiz' ? 'question-circle' : 'file-alt' }}"></i>
                                            {{ $rq['tipo'] === 'quiz' ? 'Quiz' : 'Evaluación' }}
                                        </span>
                                        <strong>{{ $rq['nombre'] }}</strong>
                                        <small class="text-muted">({{ $rq['material'] }})</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-warning retry-all-btn" 
                                            onclick="habilitarReintentosGrupo({{ $cursoActual->id }}, {{ $rq['actividad_id'] }}, this)"
                                            title="Habilitar reintento para todos los reprobados">
                                        <i class="fas fa-redo"></i> Todos
                                    </button>
                                </div>
                                <div class="retry-students-chips">
                                    @foreach($rq['estudiantes'] as $est)
                                        <div class="retry-chip" id="retry-chip-{{ $rq['actividad_id'] }}-{{ $est['id'] }}">
                                            <div class="retry-chip-avatar">
                                                @if($est['avatar'])
                                                    <img src="{{ url('media/' . $est['avatar']) }}" alt="">
                                                @else
                                                    {{ strtoupper(substr($est['nombre'], 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="retry-chip-info">
                                                <span class="retry-chip-name">{{ Str::limit($est['nombre'], 22) }}</span>
                                                <span class="retry-chip-grade">{{ number_format($est['nota'], 1) }}/5.0</span>
                                            </div>
                                            <button class="retry-chip-btn" 
                                                    onclick="event.stopPropagation(); habilitarReintentoQuiz({{ $cursoActual->id }}, {{ $est['id'] }}, {{ $rq['actividad_id'] }}, this)"
                                                    title="Habilitar reintento para {{ $est['nombre'] }}">
                                                <i class="fas fa-redo-alt"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Gradebook Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table"></i> Libro de Calificaciones</h5>
            <div class="header-actions">
                <button class="btn btn-sm btn-light" onclick="exportarGradebook()">
                    <i class="fas fa-download"></i> Exportar
                </button>
                <button class="btn btn-sm btn-light" onclick="imprimirGradebook()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive gradebook-scroll">
                <table class="table table-hover gradebook-table mb-0">
                    <thead class="thead-light sticky-header">
                        <!-- Fila 1: Nombres de Materiales (agrupados) -->
                        <tr class="material-header-row">
                            <th class="student-col sticky-col" rowspan="2">
                                <i class="fas fa-user"></i> Estudiante
                            </th>
                            @foreach($estructuraEvaluacion as $item)
                                @if($item['tipo'] == 'material')
                                    @if(!empty($item['componentes']))
                                        <th class="material-group-header text-center" colspan="{{ count($item['componentes']) }}">
                                            <div class="material-group-title">
                                                <i class="fas fa-folder-open"></i> {{ $item['nombre'] }}
                                                <span class="badge badge-primary ml-2">{{ number_format($item['peso'], 1) }}%</span>
                                            </div>
                                        </th>
                                    @else
                                        {{-- Material sin actividades --}}
                                        <th class="material-group-header text-center" rowspan="2">
                                            <div class="material-group-title">
                                                <i class="fas fa-folder"></i> {{ $item['nombre'] }}
                                                <span class="badge badge-secondary ml-2">{{ number_format($item['peso'], 1) }}%</span>
                                                <br><small class="text-white-50">Sin actividades</small>
                                            </div>
                                        </th>
                                    @endif
                                @else
                                    <th class="activity-group-header text-center" rowspan="2">
                                        <div class="col-header">
                                            <i class="fas fa-tasks"></i>
                                            <span class="col-title">{{ Str::limit($item['nombre'], 15) }}</span>
                                            <small class="col-subtitle">{{ $item['peso'] }}%</small>
                                        </div>
                                    </th>
                                @endif
                            @endforeach
                            <th class="final-col text-center sticky-right" rowspan="2">
                                <div class="col-header">
                                    <span class="col-title">Promedio</span>
                                    <small class="col-subtitle">Final</small>
                                </div>
                            </th>
                            <th class="status-col text-center sticky-right-2" rowspan="2">
                                <i class="fas fa-flag"></i> Estado
                            </th>
                        </tr>
                        <!-- Fila 2: Nombres de Actividades -->
                        <tr class="activity-header-row">
                            @foreach($estructuraEvaluacion as $item)
                                @if($item['tipo'] == 'material' && !empty($item['componentes']))
                                    @foreach($item['componentes'] as $componente)
                                        <th class="grade-col text-center" title="{{ $item['nombre'] }} - {{ $componente['nombre'] }}">
                                            <div class="col-header">
                                                <i class="fas fa-{{ $componente['tipo'] == 'tarea' ? 'clipboard-check' : ($componente['tipo'] == 'quiz' ? 'question-circle' : 'file-alt') }}"></i>
                                                <span class="col-title">{{ Str::limit($componente['nombre'], 12) }}</span>
                                                <small class="col-subtitle">{{ $componente['peso'] }}%</small>
                                            </div>
                                        </th>
                                    @endforeach
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estudiantes as $estudiante)
                            <tr class="student-row">
                                <td class="student-col sticky-col">
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            @if($estudiante['avatar'])
                                                <img src="{{ url('media/' . $estudiante['avatar']) }}" alt="{{ $estudiante['nombre'] }}">
                                            @else
                                                <div class="avatar-placeholder">
                                                    {{ substr($estudiante['nombre'], 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="student-details">
                                            <span class="student-name">{{ $estudiante['nombre'] }}</span>
                                            <small class="student-email">{{ $estudiante['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                @foreach($estructuraEvaluacion as $item)
                                    @if($item['tipo'] == 'material')
                                        @if(!empty($item['componentes']))
                                            {{-- Material con actividades --}}
                                            @php
                                                $califs = $estudiante['calificaciones']['material_' . $item['id']] ?? [];
                                            @endphp
                                            @foreach($item['componentes'] as $componente)
                                                @php
                                                    $key = $componente['tipo'] . '_' . $componente['id'];
                                                    $nota = $califs[$key] ?? 0;
                                                    // Convertir a porcentaje para determinar color
                                                    $porcentaje = ($nota / 5.0) * 100;
                                                    $esQuizReprobado = in_array($componente['tipo'], ['quiz', 'evaluacion']) && $nota > 0 && $porcentaje < 60;
                                                @endphp
                                                <td class="grade-col text-center grade-cell-interactive {{ $esQuizReprobado ? 'grade-cell-retry' : '' }}" 
                                                    data-estudiante-id="{{ $estudiante['id'] }}"
                                                    data-actividad-id="{{ $componente['id'] }}"
                                                    data-actividad-tipo="{{ $componente['tipo'] }}"
                                                    data-actividad-nombre="{{ $componente['nombre'] }}"
                                                    data-estudiante-nombre="{{ $estudiante['nombre'] }}"
                                                    onclick="abrirDetalleCalificacion(this)"
                                                    style="cursor: pointer;"
                                                    title="Click para ver detalles y calificar">
                                                    @if($nota > 0)
                                                        <span class="grade-badge grade-{{ $porcentaje >= 60 ? 'good' : ($porcentaje >= 50 ? 'warning' : 'poor') }}">
                                                            {{ number_format($nota, 1) }}/5.0
                                                        </span>
                                                        @if($esQuizReprobado && in_array(auth()->user()->role, ['Super Admin', 'Administrador', 'Operador', 'Docente']))
                                                            <i class="fas fa-redo-alt retry-inline-icon" title="Reprobado — Puede solicitar reintento"></i>
                                                        @endif
                                                    @else
                                                        <span class="text-muted"><i class="fas fa-edit"></i> -</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @else
                                            {{-- Material sin actividades --}}
                                            <td class="grade-col text-center">
                                                <span class="text-muted"><i class="fas fa-minus"></i></span>
                                            </td>
                                        @endif
                                    @else
                                        {{-- Actividad independiente --}}
                                        @php
                                            $nota = $estudiante['calificaciones']['actividad_' . $item['id']] ?? 0;
                                            // Convertir a porcentaje para determinar color
                                            $porcentaje = ($nota / 5.0) * 100;
                                        @endphp
                                        <td class="grade-col text-center grade-cell-interactive"
                                            data-estudiante-id="{{ $estudiante['id'] }}"
                                            data-actividad-id="{{ $item['id'] }}"
                                            data-actividad-tipo="{{ $item['tipo_actividad'] }}"
                                            data-actividad-nombre="{{ $item['nombre'] }}"
                                            data-estudiante-nombre="{{ $estudiante['nombre'] }}"
                                            onclick="abrirDetalleCalificacion(this)"
                                            style="cursor: pointer;"
                                            title="Click para ver detalles y calificar">
                                            @if($nota > 0)
                                                <span class="grade-badge grade-{{ $porcentaje >= 60 ? 'good' : ($porcentaje >= 50 ? 'warning' : 'poor') }}">
                                                    {{ number_format($nota, 1) }}/5.0
                                                </span>
                                            @else
                                                <span class="text-muted"><i class="fas fa-edit"></i> -</span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                                <td class="final-col text-center sticky-right">
                                    @php
                                        $porcentajeProgreso = ($estudiante['progreso'] / 5.0) * 100;
                                    @endphp
                                    <span class="final-grade grade-{{ $porcentajeProgreso >= 60 ? 'good' : ($porcentajeProgreso >= 50 ? 'warning' : 'poor') }}">
                                        {{ number_format($estudiante['progreso'], 2) }}/5.0
                                    </span>
                                </td>
                                <td class="status-col text-center sticky-right-2">
                                    <span class="status-badge status-{{ $estudiante['estado'] }}">
                                        @if($estudiante['estado'] == 'passed')
                                            @if($cursoActual->plantillaCertificado)
                                                <a href="javascript:void(0)" 
                                                   class="btn-certificado-preview"
                                                   data-estudiante-id="{{ $estudiante['id'] }}"
                                                   data-estudiante-nombre="{{ $estudiante['nombre'] }} {{ $estudiante['apellido1'] }} {{ $estudiante['apellido2'] }}"
                                                   data-estudiante-documento="{{ $estudiante['tipo_documento'] ? $estudiante['tipo_documento'] . ': ' : '' }}{{ $estudiante['numero_documento'] }}"
                                                   data-curso-id="{{ $cursoActual->id }}"
                                                   data-curso-nombre="{{ $cursoActual->titulo }}"
                                                   data-fecha-inicio="{{ $cursoActual->fecha_inicio ? \Carbon\Carbon::parse($cursoActual->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') : 'N/A' }}"
                                                   data-fecha-fin="{{ $cursoActual->fecha_fin ? \Carbon\Carbon::parse($cursoActual->fecha_fin)->translatedFormat('d \d\e F \d\e Y') : 'N/A' }}"
                                                   title="Click para ver certificado"
                                                   style="text-decoration: none; color: inherit; cursor: pointer;">
                                                    <i class="fas fa-check-circle"></i> Aprobado
                                                    <i class="fas fa-certificate ml-1" style="font-size: 0.7rem; color: #ffc107;"></i>
                                                </a>
                                            @else
                                                <i class="fas fa-check-circle"></i> Aprobado
                                            @endif
                                        @elseif($estudiante['estado'] == 'at_risk')
                                            <i class="fas fa-exclamation-triangle"></i> En Riesgo
                                        @else
                                            <i class="fas fa-times-circle"></i> Reprobado
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay estudiantes inscritos en este curso</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            <div class="modal-body p-0" style="background: #525659;">
                <!-- Info del estudiante -->
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
                <!-- Iframe con el certificado (escalado para caber en el modal) -->
                <div id="certIframeWrapper" style="display: flex; justify-content: center; align-items: flex-start; padding: 15px; overflow: hidden; position: relative;">
                    <!-- Loading indicator -->
                    <div id="certLoadingIndicator" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 10;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #fff;"></i>
                        <p style="color: #ccc; margin-top: 10px; font-family: 'Inter',sans-serif;">Cargando certificado...</p>
                    </div>
                    <div id="certIframeScaler" style="width: 960px; height: 680px; transform-origin: top center; flex-shrink: 0;">
                        <iframe id="certificadoIframe" onload="document.getElementById('certLoadingIndicator').style.display='none'; scaleCertificateIframe();" style="width: 960px; height: 680px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: white; display: block;" allowfullscreen></iframe>
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
    @endif
@stop

@section('css')
<style>
    /* Variables de colores corporativos */
    :root {
        --corp-primary: #2c4370;
        --corp-primary-dark: #1e2f4d;
        --corp-primary-light: #3d5a8a;
        --corp-success: #27ae60;
        --corp-warning: #f39c12;
        --corp-danger: #e74c3c;
    }

    .gradebook-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark)) !important;
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .card-header h5 {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .header-actions .btn {
        margin-left: 0.5rem;
        border-radius: 6px;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-light));
    }

    .stat-icon.bg-success {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, #f39c12, #f1c40f);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }

    /* Evaluation Structure */
    .evaluation-structure {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .eval-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .eval-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .eval-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .eval-name i {
        color: var(--corp-primary);
        margin-right: 0.5rem;
    }

    .eval-weight {
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
        background: var(--corp-primary) !important;
        font-weight: 700;
    }

    .eval-components {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .component-badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #495057;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .component-badge i {
        font-size: 0.875rem;
    }

    .component-badge strong {
        font-weight: 700;
        margin-left: 0.25rem;
    }

    /* Gradebook Table */
    .gradebook-scroll {
        overflow-x: auto;
        position: relative;
    }

    .gradebook-table {
        min-width: 100%;
        width: max-content;
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    .gradebook-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid var(--corp-primary);
        font-weight: 600;
        color: #2c3e50;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    /* Fila de materiales (agrupación) */
    .material-header-row th {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark));
        color: white;
        font-weight: 700;
        border-bottom: 2px solid var(--corp-primary-dark);
    }

    .material-group-header {
        padding: 0.75rem 0.5rem;
    }

    .material-group-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .material-group-title .badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    /* Fila de actividades */
    .activity-header-row th {
        background: #e9ecef;
        border-bottom: 1px solid #dee2e6;
    }

    .activity-group-header {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }

    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa;
    }

    .material-header-row {
        position: sticky;
        top: 0;
        z-index: 11;
    }

    .activity-header-row {
        position: sticky;
        top: 60px; /* Altura aproximada de la primera fila */
        z-index: 10;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background: white;
        z-index: 5;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    /* Sticky col en headers debe tener mayor z-index */
    thead .sticky-col {
        z-index: 12;
        background: #f8f9fa;
    }

    .material-header-row .sticky-col {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark));
        z-index: 13;
    }

    .sticky-right {
        position: sticky;
        right: 120px;
        background: white;
        z-index: 5;
        box-shadow: -2px 0 5px rgba(0,0,0,0.05);
    }

    /* Sticky right en headers */
    thead .sticky-right {
        z-index: 12;
        background: #f8f9fa;
    }

    .material-header-row .sticky-right {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark));
        z-index: 13;
    }

    .sticky-right-2 {
        position: sticky;
        right: 0;
        background: white;
        z-index: 5;
        box-shadow: -2px 0 5px rgba(0,0,0,0.05);
    }

    /* Sticky right-2 en headers */
    thead .sticky-right-2 {
        z-index: 12;
        background: #f8f9fa;
    }

    .material-header-row .sticky-right-2 {
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-dark));
        z-index: 13;
    }

    .student-col {
        min-width: 250px;
        max-width: 250px;
    }

    .grade-col {
        min-width: 100px;
        max-width: 100px;
    }

    .final-col {
        min-width: 120px;
        max-width: 120px;
    }

    .status-col {
        min-width: 120px;
        max-width: 120px;
    }

    .col-header {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .col-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .col-subtitle {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 400;
    }

    /* Student Info */
    .student-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }

    .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--corp-primary), var(--corp-primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
    }

    .student-details {
        display: flex;
        flex-direction: column;
    }

    .student-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
    }

    .student-email {
        color: #6c757d;
        font-size: 0.75rem;
    }

    /* Grade Badges */
    .grade-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .grade-good {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .grade-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .grade-poor {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .final-grade {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1.125rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8125rem;
    }

    .status-passed {
        background: #d4edda;
        color: #155724;
    }

    .status-at_risk {
        background: #fff3cd;
        color: #856404;
    }

    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }

    /* Row Hover */
    .student-row:hover {
        background: #f8f9fa;
    }

    /* Celdas interactivas */
    .grade-cell-interactive:hover {
        background: #e3f2fd !important;
        transform: scale(1.05);
        transition: all 0.2s ease;
    }

    .grade-cell-interactive {
        position: relative;
    }

    .grade-cell-interactive:hover::after {
        content: '\f044'; /* fa-edit */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 0.7rem;
        color: var(--corp-primary);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .evaluation-structure {
            grid-template-columns: 1fr;
        }

        .toggle-activities-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ==========================================
       Toggle Panel - Activar/Desactivar Quiz
       ========================================== */
    .toggle-panel-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .toggle-panel-header {
        background: linear-gradient(135deg, #2c4370, #1a5276) !important;
        color: white;
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .toggle-panel-header h5 {
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
    }

    .toggle-panel-header .toggle-counter {
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 20px;
    }

    .toggle-activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    .toggle-activity-card {
        border-radius: 12px;
        padding: 1.15rem;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .toggle-activity-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: all 0.35s ease;
    }

    .toggle-activity-card.toggle-active {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-color: #86efac;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.12);
    }

    .toggle-activity-card.toggle-active::before {
        background: linear-gradient(180deg, #22c55e, #16a34a);
    }

    .toggle-activity-card.toggle-inactive {
        background: linear-gradient(135deg, #f8f9fa, #f1f3f5);
        border-color: #dee2e6;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .toggle-activity-card.toggle-inactive::before {
        background: #adb5bd;
    }

    .toggle-activity-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .toggle-activity-top {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    .toggle-activity-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .toggle-activity-icon.icon-quiz {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .toggle-activity-icon.icon-eval {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .toggle-activity-info {
        flex: 1;
        min-width: 0;
    }

    .toggle-activity-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1e293b;
        margin-top: 0.25rem;
        word-break: break-word;
    }

    .toggle-activity-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        padding-top: 0.75rem;
        margin-left: 4px;
    }

    .toggle-status-label {
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #cbd5e1;
        border-radius: 28px;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.3);
    }

    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(24px);
    }

    .toggle-switch input:focus + .toggle-slider {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    }

    /* Animación de procesando */
    .toggle-activity-card.toggle-processing {
        opacity: 0.7;
        pointer-events: none;
    }

    .toggle-activity-card.toggle-processing::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes togglePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .toggle-activity-card.toggle-just-changed {
        animation: togglePulse 0.4s ease;
    }

    /* ==========================================
       Retry Panel - Reintentos Reprobados
       ========================================== */
    .retry-panel-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .retry-panel-header {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        color: white;
        padding: 0.9rem 1.25rem;
        border: none;
        transition: background 0.3s ease;
    }

    .retry-panel-header:hover {
        background: linear-gradient(135deg, #c82333, #b21f2d) !important;
    }

    .retry-panel-header h5 {
        font-weight: 600;
        font-size: 1rem;
        color: white;
    }

    .retry-total-badge {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: rgba(255,255,255,0.95) !important;
        color: #dc3545 !important;
    }

    .retry-collapse-icon {
        transition: transform 0.3s ease;
    }

    [aria-expanded="true"] .retry-collapse-icon {
        transform: rotate(180deg);
    }

    .retry-activity-group {
        background: #fafbfc;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 0.85rem;
    }

    .retry-activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.65rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .retry-activity-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        font-size: 0.9rem;
    }

    .retry-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }

    .retry-type-badge.badge-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .retry-type-badge.badge-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .retry-all-btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-weight: 600;
        border-color: #f39c12;
        color: #f39c12;
        white-space: nowrap;
    }

    .retry-all-btn:hover {
        background: #f39c12;
        color: white;
    }

    .retry-students-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .retry-chip {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: white;
        border: 1px solid #f5c6cb;
        border-radius: 24px;
        padding: 0.3rem 0.5rem 0.3rem 0.3rem;
        transition: all 0.25s ease;
        max-width: 260px;
    }

    .retry-chip:hover {
        border-color: #dc3545;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
        transform: translateY(-1px);
    }

    .retry-chip-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.65rem;
    }

    .retry-chip-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .retry-chip-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        line-height: 1.15;
    }

    .retry-chip-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .retry-chip-grade {
        font-size: 0.65rem;
        font-weight: 700;
        color: #dc3545;
    }

    .retry-chip-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
        font-size: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.25s ease;
        padding: 0;
    }

    .retry-chip-btn:hover {
        background: linear-gradient(135deg, #e67e22, #d35400);
        transform: scale(1.15);
        box-shadow: 0 2px 6px rgba(243, 156, 18, 0.4);
    }

    .retry-chip.retry-done {
        opacity: 0.4;
        pointer-events: none;
        background: #f0f0f0;
        border-color: #ddd;
    }

    .retry-chip.retry-done .retry-chip-btn {
        background: #adb5bd;
    }

    .retry-chip.retry-processing {
        opacity: 0.5;
        pointer-events: none;
    }

    @keyframes retryPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(0.95); opacity: 0.6; }
        100% { transform: scale(1); opacity: 1; }
    }

    .retry-chip.retry-processing {
        animation: retryPulse 0.8s ease infinite;
    }

    /* Inline retry icon on grade cells */
    .grade-cell-retry {
        position: relative;
    }

    .retry-inline-icon {
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 0.55rem;
        color: #f39c12;
        opacity: 0.7;
        transition: all 0.2s ease;
    }

    .grade-cell-retry:hover .retry-inline-icon {
        opacity: 1;
        color: #e67e22;
        transform: rotate(-45deg);
    }

    @media (max-width: 992px) {
        .retry-students-chips {
            gap: 0.35rem;
        }
        .retry-chip {
            max-width: 100%;
        }
        .retry-activity-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* Estilos para botón de certificado en badge Aprobado */
    .btn-certificado-preview {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        transition: all 0.2s ease;
    }
    .btn-certificado-preview:hover {
        filter: brightness(0.9);
        transform: scale(1.05);
    }
    .status-passed .btn-certificado-preview {
        color: #155724;
    }
    .status-passed:has(.btn-certificado-preview) {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .status-passed:has(.btn-certificado-preview):hover {
        background: #b8e6c8;
        box-shadow: 0 2px 8px rgba(39,174,96,0.3);
    }

    /* Modal certificado responsive iframe - escalado proporcional */
    #certificadoPreviewModal .modal-body {
        overflow: hidden;
    }
    #certIframeWrapper {
        transition: height 0.2s ease;
    }
</style>
@stop

@section('js')
<script>
    const userRole = {!! json_encode(auth()->user()->role) !!};
    const canResetActivity = ['Super Admin', 'Administrador', 'Operador', 'Docente'].includes(userRole);
    const canToggleActivity = ['Super Admin', 'Administrador', 'Operador', 'Docente'].includes(userRole);

    $(document).ready(function() {
        // Cambiar curso
        $('#cursoSelector').on('change', function() {
            const cursoId = $(this).val();
            window.location.href = '{{ route("academico.control-pedagogico.index") }}?curso_id=' + cursoId;
        });

        // Toggle de activar/desactivar quiz/evaluaciones
        if (canToggleActivity) {
            $(document).on('change', '.toggle-actividad-check', function() {
                const checkbox = $(this);
                const actividadId = checkbox.data('actividad-id');
                const nombre = checkbox.data('nombre');
                const tipo = checkbox.data('tipo');
                const habilitado = checkbox.is(':checked');
                const card = $(`#toggle-card-${actividadId}`);
                const tipoLabel = tipo === 'quiz' ? 'Quiz' : 'Evaluación';
                const accion = habilitado ? 'habilitar' : 'deshabilitar';

                // Confirmar
                Swal.fire({
                    title: `¿${habilitado ? 'Habilitar' : 'Deshabilitar'} ${tipoLabel}?`,
                    html: `
                        <div class="text-left">
                            <p><strong>${nombre}</strong></p>
                            <p class="text-muted">
                                ${habilitado 
                                    ? '<i class="fas fa-check-circle text-success"></i> Los estudiantes <strong>podrán</strong> realizar esta actividad.' 
                                    : '<i class="fas fa-ban text-danger"></i> Los estudiantes <strong>no podrán</strong> realizar esta actividad hasta que se habilite.'}
                            </p>
                        </div>
                    `,
                    icon: habilitado ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: `<i class="fas fa-${habilitado ? 'check' : 'ban'}"></i> Sí, ${accion}`,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: habilitado ? '#22c55e' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Marcar procesando
                        card.addClass('toggle-processing');

                        $.ajax({
                            url: '{{ route("academico.control-pedagogico.toggle-actividad") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                actividad_id: actividadId,
                                habilitado: habilitado ? 1 : 0
                            },
                            success: function(response) {
                                card.removeClass('toggle-processing');

                                if (response.success) {
                                    // Actualizar UI
                                    const label = $(`#toggle-label-${actividadId}`);
                                    if (habilitado) {
                                        card.removeClass('toggle-inactive').addClass('toggle-active');
                                        label.html('<i class="fas fa-check-circle text-success"></i> Habilitado');
                                    } else {
                                        card.removeClass('toggle-active').addClass('toggle-inactive');
                                        label.html('<i class="fas fa-ban text-secondary"></i> Deshabilitado');
                                    }

                                    // Animación
                                    card.addClass('toggle-just-changed');
                                    setTimeout(() => card.removeClass('toggle-just-changed'), 500);

                                    // Actualizar contador
                                    const activasCount = $('.toggle-actividad-check:checked').length;
                                    $('#activasCount').text(activasCount);

                                    // Toast
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    });
                                    Toast.fire({
                                        icon: 'success',
                                        title: response.mensaje
                                    });
                                } else {
                                    // Revertir checkbox
                                    checkbox.prop('checked', !habilitado);
                                    Swal.fire('Error', response.error || 'No se pudo cambiar el estado', 'error');
                                }
                            },
                            error: function(xhr) {
                                card.removeClass('toggle-processing');
                                // Revertir checkbox
                                checkbox.prop('checked', !habilitado);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.error || 'No se pudo cambiar el estado de la actividad',
                                    confirmButtonColor: '#2c4370'
                                });
                            }
                        });
                    } else {
                        // Revertir checkbox si cancela
                        checkbox.prop('checked', !habilitado);
                    }
                });
            });
        }

        console.log('Control Pedagógico cargado correctamente');
    });

    function exportarGradebook() {
        Swal.fire({
            icon: 'info',
            title: 'Exportar Gradebook',
            text: 'Funcionalidad en desarrollo',
            confirmButtonColor: '#2c4370'
        });
    }

    function imprimirGradebook() {
        window.print();
    }

    // Función para abrir modal de detalle de calificación
    function abrirDetalleCalificacion(cell) {
        const estudianteId = $(cell).data('estudiante-id');
        const actividadId = $(cell).data('actividad-id');
        const actividadTipo = $(cell).data('actividad-tipo');
        const actividadNombre = $(cell).data('actividad-nombre');
        const estudianteNombre = $(cell).data('estudiante-nombre');
        const cursoId = $('#cursoSelector').val();

        // Mostrar loading
        Swal.fire({
            title: 'Cargando...',
            text: 'Obteniendo información de la entrega',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Obtener detalles de la entrega
        $.ajax({
            url: '{{ route("academico.control-pedagogico.index") }}',
            method: 'GET',
            data: {
                action: 'get_entrega',
                curso_id: cursoId,
                estudiante_id: estudianteId,
                actividad_id: actividadId
            },
            success: function(response) {
                mostrarModalCalificacion(response, {
                    estudianteId,
                    actividadId,
                    actividadTipo,
                    actividadNombre,
                    estudianteNombre,
                    cursoId
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la información de la entrega',
                    confirmButtonColor: '#2c4370'
                });
            }
        });
    }

    // Función para mostrar modal de calificación
    function mostrarModalCalificacion(entrega, datos) {
        let contenidoHTML = '';
        
        // Información del estudiante y actividad
        contenidoHTML += `
            <div class="mb-3 text-left">
                <h5><i class="fas fa-user"></i> ${datos.estudianteNombre}</h5>
                <p class="mb-1"><strong>Actividad:</strong> ${datos.actividadNombre}</p>
                <p class="mb-1"><strong>Tipo:</strong> <span class="badge badge-info">${datos.actividadTipo}</span></p>
            </div>
            <hr>
        `;

        // Según el tipo de actividad
        if (datos.actividadTipo === 'quiz' || datos.actividadTipo === 'evaluacion') {
            // Quiz o Evaluación - Mostrar nota automática
            if (entrega && entrega.calificacion) {
                contenidoHTML += `
                    <div class="alert alert-success">
                        <h4><i class="fas fa-check-circle"></i> Calificación Automática</h4>
                        <h2 class="mb-0">${entrega.calificacion} / 5.0</h2>
                        <small>Equivalente a: ${(entrega.calificacion * 20).toFixed(1)}%</small>
                    </div>
                `;
                
                if (entrega.respuestas) {
                    contenidoHTML += `
                        <div class="mt-3">
                            <h6>Respuestas del estudiante:</h6>
                            <div style="max-height: 300px; overflow-y: auto; text-align: left;">
                                ${formatearRespuestas(entrega.respuestas)}
                            </div>
                        </div>
                    `;
                }
            } else {
                contenidoHTML += `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        El estudiante aún no ha completado esta evaluación
                    </div>
                `;
            }
        } else {
            // Tarea - Permitir calificar manualmente
            if (entrega && entrega.archivo_path) {
                contenidoHTML += `
                    <div class="mb-3">
                        <h6><i class="fas fa-file"></i> Archivo Entregado:</h6>
                        <a href="/storage/${entrega.archivo_path}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-download"></i> Descargar Archivo
                        </a>
                    </div>
                `;
            }
            
            if (entrega && entrega.comentario) {
                contenidoHTML += `
                    <div class="mb-3">
                        <h6><i class="fas fa-comment"></i> Comentario del estudiante:</h6>
                        <div class="alert alert-light">${entrega.comentario}</div>
                    </div>
                `;
            }
            
            if (entrega && entrega.fecha_entrega) {
                contenidoHTML += `
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Entregado: ${entrega.fecha_entrega}
                        </small>
                    </div>
                `;
            }
            
            // Formulario de calificación
            const notaActual = entrega && entrega.calificacion ? entrega.calificacion : '';
            contenidoHTML += `
                <hr>
                <div class="form-group text-left">
                    <label for="calificacion"><strong>Calificación (0.0 - 5.0):</strong></label>
                    <input type="number" class="form-control" id="calificacion" 
                           min="0" max="5" step="0.1" value="${notaActual}"
                           placeholder="Ingrese la calificación">
                </div>
                <div class="form-group text-left">
                    <label for="retroalimentacion"><strong>Retroalimentación:</strong></label>
                    <textarea class="form-control" id="retroalimentacion" rows="3"
                              placeholder="Comentarios para el estudiante">${entrega && entrega.retroalimentacion ? entrega.retroalimentacion : ''}</textarea>
                </div>
            `;
            
            if (!entrega || !entrega.archivo_path) {
                contenidoHTML += `
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        El estudiante aún no ha entregado esta actividad
                    </div>
                `;
            }
        }

        // Agregar botón de reset para roles permitidos si hay una entrega
        if (typeof canResetActivity !== 'undefined' && canResetActivity && entrega && !('entrega' in entrega && entrega.entrega === null)) {
            contenidoHTML += `
                <div class="mt-4 pt-3 border-top text-right">
                    <button type="button" class="btn btn-warning btn-sm" onclick="habilitarReintento(${datos.cursoId}, ${datos.estudianteId}, ${datos.actividadId})">
                        <i class="fas fa-redo"></i> Permitir Reintento (Limpiar Entrega)
                    </button>
                    <small class="d-block text-muted mt-1 text-right">Esto eliminará la calificación actual y permitirá al estudiante intentar de nuevo.</small>
                </div>
            `;
        }

        // Mostrar modal
        Swal.fire({
            title: 'Detalle de Calificación',
            html: contenidoHTML,
            width: '700px',
            showCancelButton: datos.actividadTipo !== 'quiz' && datos.actividadTipo !== 'evaluacion',
            confirmButtonText: datos.actividadTipo === 'quiz' || datos.actividadTipo === 'evaluacion' ? 'Cerrar' : '<i class="fas fa-save"></i> Guardar Calificación',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2c4370',
            preConfirm: () => {
                if (datos.actividadTipo === 'quiz' || datos.actividadTipo === 'evaluacion') {
                    return true;
                }
                
                const calificacion = document.getElementById('calificacion').value;
                const retroalimentacion = document.getElementById('retroalimentacion').value;
                
                if (!calificacion) {
                    Swal.showValidationMessage('Por favor ingrese una calificación');
                    return false;
                }
                
                if (parseFloat(calificacion) < 0 || parseFloat(calificacion) > 5) {
                    Swal.showValidationMessage('La calificación debe estar entre 0.0 y 5.0');
                    return false;
                }
                
                return { calificacion, retroalimentacion };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value !== true) {
                guardarCalificacion(datos, result.value);
            }
        });
    }

    // Función para formatear respuestas de quiz
    function formatearRespuestas(respuestas) {
        let html = '<div class="list-group">';
        
        if (typeof respuestas === 'string') {
            try {
                respuestas = JSON.parse(respuestas);
            } catch (e) {
                return '<p>No se pudieron cargar las respuestas</p>';
            }
        }
        
        if (Array.isArray(respuestas)) {
            respuestas.forEach((resp, index) => {
                const esCorrecta = resp.correcta || resp.is_correct || resp.es_correcta;
                const clase = esCorrecta ? 'list-group-item-success' : 'list-group-item-danger';
                const icono = esCorrecta ? 'fa-check-circle' : 'fa-times-circle';
                const pregunta = resp.pregunta || resp.question || 'N/A';
                const respuesta = resp.respuesta || resp.answer || 'N/A';
                const puntos = resp.puntos || resp.points || 0;
                
                html += `
                    <div class="list-group-item ${clase}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Pregunta ${index + 1}:</strong> ${pregunta}<br>
                                <strong>Respuesta:</strong> ${respuesta}
                            </div>
                            <div class="text-right">
                                <i class="fas ${icono} fa-2x"></i><br>
                                <small>${puntos} pts</small>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted">No hay respuestas disponibles</p>';
        }
        
        html += '</div>';
        return html;
    }

    // Función para guardar calificación
    function guardarCalificacion(datos, valores) {
        Swal.fire({
            title: 'Guardando...',
            text: 'Guardando calificación',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("academico.control-pedagogico.guardar-calificacion") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                curso_id: datos.cursoId,
                estudiante_id: datos.estudianteId,
                actividad_id: datos.actividadId,
                calificacion: valores.calificacion,
                retroalimentacion: valores.retroalimentacion
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'La calificación ha sido guardada correctamente',
                    confirmButtonColor: '#2c4370'
                }).then(() => {
                    // Recargar la página para actualizar las calificaciones
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar la calificación',
                    confirmButtonColor: '#2c4370'
                });
            }
        });
    }

    // Función para habilitar reintento (eliminar entrega)
    function habilitarReintento(cursoId, estudianteId, actividadId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto eliminará la entrega actual. El estudiante podrá realizar la actividad (Quiz, Evaluación o Tarea) de nuevo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, habilitar reintento',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("academico.control-pedagogico.reset-actividad") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        curso_id: cursoId,
                        estudiante_id: estudianteId,
                        actividad_id: actividadId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Listo!',
                                text: response.mensaje,
                                confirmButtonColor: '#2c4370'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.mensaje || 'Error desconocido', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'No se pudo habilitar el reintento',
                            confirmButtonColor: '#2c4370'
                        });
                    }
                });
            }
        });
    }

    // Función para habilitar reintento individual desde el panel de reintentos (chip)
    function habilitarReintentoQuiz(cursoId, estudianteId, actividadId, btn) {
        const chip = $(btn).closest('.retry-chip');
        const estudianteNombre = chip.find('.retry-chip-name').text().trim();

        Swal.fire({
            title: '¿Habilitar reintento?',
            html: `<p>Se eliminará la entrega actual de <strong>${estudianteNombre}</strong> y podrá realizar esta evaluación de nuevo.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-redo-alt"></i> Sí, habilitar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                chip.addClass('retry-processing');

                $.ajax({
                    url: '{{ route("academico.control-pedagogico.reset-actividad") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        curso_id: cursoId,
                        estudiante_id: estudianteId,
                        actividad_id: actividadId
                    },
                    success: function(response) {
                        chip.removeClass('retry-processing');
                        if (response.success) {
                            chip.addClass('retry-done');
                            chip.find('.retry-chip-btn').html('<i class="fas fa-check"></i>');
                            chip.find('.retry-chip-grade').text('Reintento habilitado').css('color', '#27ae60');

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            Toast.fire({
                                icon: 'success',
                                title: `Reintento habilitado para ${estudianteNombre}`
                            });

                            // Actualizar contador de reprobados
                            actualizarContadorReprobados();
                        } else {
                            Swal.fire('Error', response.error || 'No se pudo habilitar el reintento', 'error');
                        }
                    },
                    error: function(xhr) {
                        chip.removeClass('retry-processing');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'No se pudo habilitar el reintento',
                            confirmButtonColor: '#2c4370'
                        });
                    }
                });
            }
        });
    }

    // Función para habilitar reintento grupal (todos los reprobados de un quiz)
    function habilitarReintentosGrupo(cursoId, actividadId, btn) {
        const group = $(btn).closest('.retry-activity-group');
        const chips = group.find('.retry-chip:not(.retry-done)');
        const count = chips.length;

        if (count === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sin pendientes',
                text: 'Ya se habilitaron todos los reintentos para esta evaluación.',
                confirmButtonColor: '#2c4370'
            });
            return;
        }

        const estudianteIds = [];
        chips.each(function() {
            const id = $(this).attr('id').split('-').pop();
            estudianteIds.push(parseInt(id));
        });

        Swal.fire({
            title: '¿Habilitar reintento grupal?',
            html: `<p>Se habilitará el reintento para <strong>${count} estudiante(s)</strong> que reprobaron esta evaluación.</p>
                   <p class="text-muted"><small>Se eliminarán sus entregas actuales y podrán realizarla de nuevo.</small></p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `<i class="fas fa-redo"></i> Sí, habilitar ${count} reintento(s)`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
        }).then((result) => {
            if (result.isConfirmed) {
                chips.addClass('retry-processing');
                $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

                $.ajax({
                    url: '{{ route("academico.control-pedagogico.reset-actividad-grupo") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        curso_id: cursoId,
                        actividad_id: actividadId,
                        estudiante_ids: estudianteIds
                    },
                    success: function(response) {
                        chips.removeClass('retry-processing');
                        if (response.success) {
                            chips.addClass('retry-done');
                            chips.find('.retry-chip-btn').html('<i class="fas fa-check"></i>');
                            chips.find('.retry-chip-grade').text('Reintento habilitado').css('color', '#27ae60');
                            $(btn).html('<i class="fas fa-check-circle"></i> Completado').removeClass('btn-outline-warning').addClass('btn-success').prop('disabled', true);

                            Swal.fire({
                                icon: 'success',
                                title: '¡Reintentos habilitados!',
                                text: response.mensaje,
                                confirmButtonColor: '#2c4370'
                            });

                            actualizarContadorReprobados();
                        } else {
                            $(btn).prop('disabled', false).html('<i class="fas fa-redo"></i> Todos');
                            Swal.fire('Error', response.error || 'Error desconocido', 'error');
                        }
                    },
                    error: function(xhr) {
                        chips.removeClass('retry-processing');
                        $(btn).prop('disabled', false).html('<i class="fas fa-redo"></i> Todos');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'No se pudieron habilitar los reintentos',
                            confirmButtonColor: '#2c4370'
                        });
                    }
                });
            }
        });
    }

    // Actualizar el contador de reprobados en el badge del panel
    function actualizarContadorReprobados() {
        const pendientes = $('.retry-chip:not(.retry-done)').length;
        const badge = $('.retry-total-badge');
        if (pendientes === 0) {
            badge.html('<i class="fas fa-check-circle"></i> Todos habilitados');
            badge.removeClass('text-danger').css('color', '#27ae60');
        } else {
            badge.html(`<i class="fas fa-user-times"></i> ${pendientes} reprobado${pendientes > 1 ? 's' : ''}`);
        }
    }

    // Toggle collapse icon rotation
    $(document).ready(function() {
        $('#retryPanelBody').on('show.bs.collapse', function() {
            $(this).closest('.retry-panel-card').find('.retry-collapse-icon').css('transform', 'rotate(180deg)');
        }).on('hide.bs.collapse', function() {
            $(this).closest('.retry-panel-card').find('.retry-collapse-icon').css('transform', 'rotate(0deg)');
        });

        // Click en botón de certificado (Aprobado)
        $(document).on('click', '.btn-certificado-preview', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const estudianteId = $(this).data('estudiante-id');
            const estudianteNombre = $(this).data('estudiante-nombre');
            const estudianteDocumento = $(this).data('estudiante-documento');
            const cursoId = $(this).data('curso-id');
            const cursoNombre = $(this).data('curso-nombre');
            const fechaInicio = $(this).data('fecha-inicio');
            const fechaFin = $(this).data('fecha-fin');

            // Rellenar info del estudiante en el modal
            $('#certInfoNombre').text(estudianteNombre);
            $('#certInfoDocumento').text(estudianteDocumento || 'No registrado');
            $('#certInfoFechaInicio').text(fechaInicio);
            $('#certInfoFechaFin').text(fechaFin);

            // Construir URL del certificado (con parámetro iframe=1 para ocultar toolbar)
            const certUrl = `{{ url('academico/control-pedagogico/preview-certificado') }}/${cursoId}/${estudianteId}?iframe=1`;
            const certUrlNewTab = `{{ url('academico/control-pedagogico/preview-certificado') }}/${cursoId}/${estudianteId}`;
            
            // Mostrar loading
            document.getElementById('certLoadingIndicator').style.display = 'block';
            
            // Cargar en iframe directamente (X-Frame-Options: SAMEORIGIN permite esto)
            $('#certificadoIframe').attr('src', certUrl);
            $('#certOpenNewTab').attr('href', certUrlNewTab);

            // Botón imprimir
            $('#certPrintBtn').off('click').on('click', function() {
                const iframeEl = document.getElementById('certificadoIframe');
                if (iframeEl && iframeEl.contentWindow) {
                    iframeEl.contentWindow.print();
                }
            });

            // Mostrar modal
            $('#certificadoPreviewModal').modal('show');
        });

        // Escalar iframe para que quepa en el modal manteniendo proporciones
        function scaleCertificateIframe() {
            const wrapper = document.getElementById('certIframeWrapper');
            const scaler = document.getElementById('certIframeScaler');
            if (!wrapper || !scaler) return;
            const availableWidth = wrapper.clientWidth - 30; // 15px padding a cada lado
            const nativeW = 960;
            const nativeH = 680;
            const scale = Math.min(1, availableWidth / nativeW);
            scaler.style.transform = 'scale(' + scale + ')';
            wrapper.style.height = (nativeH * scale + 30) + 'px';
        }

        $('#certificadoPreviewModal').on('shown.bs.modal', function() {
            scaleCertificateIframe();
        });
        $(window).on('resize', function() {
            if ($('#certificadoPreviewModal').is(':visible')) {
                scaleCertificateIframe();
            }
        });

        // Limpiar iframe al cerrar modal
        $('#certificadoPreviewModal').on('hidden.bs.modal', function() {
            $('#certificadoIframe').attr('src', '');
            document.getElementById('certLoadingIndicator').style.display = 'block';
        });
    });
</script>
@stop
