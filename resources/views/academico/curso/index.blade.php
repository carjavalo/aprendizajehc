@extends('admin.layouts.master')

@section('title', $curso->titulo)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1><i class="fas fa-book-open"></i> {{ $curso->titulo }}</h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-layer-group"></i> {{ $curso->area->descripcion }} • 
                    <i class="fas fa-user-tie"></i> {{ $curso->instructor->full_name }}
                </p>
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('academico.cursos.disponibles') }}">Cursos Disponibles</a></li>
                    <li class="breadcrumb-item active">{{ $curso->titulo }}</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Información del curso y progreso -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Información del Curso
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($curso->imagen_portada)
                            <div class="text-center mb-3">
                                <img src="{{ $curso->imagen_portada_url }}" alt="Portada del curso" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        @endif
                        
                        <p><strong>Descripción:</strong></p>
                        <p>{{ $curso->descripcion ?? 'Sin descripción disponible' }}</p>
                        
                        @if($curso->objetivos)
                            <p><strong>Objetivos:</strong></p>
                            <p>{{ $curso->objetivos }}</p>
                        @endif
                        
                        @if($curso->requisitos)
                            <p><strong>Requisitos:</strong></p>
                            <p>{{ $curso->requisitos }}</p>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha de inicio:</strong> {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : 'No definida' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha de fin:</strong> {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'No definida' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Duración:</strong> {{ $curso->duracion_horas ? $curso->duracion_horas . ' horas' : 'No definida' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Código de acceso:</strong> <code>{{ $curso->codigo_acceso }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Progreso del estudiante -->
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title text-white">
                            <i class="fas fa-chart-pie"></i> Tu Progreso
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progreso }}%">
                                {{ $progreso }}%
                            </div>
                        </div>
                        <p class="text-muted">Has completado {{ $progreso }}% del curso</p>
                          @if(isset($resumen) && $resumen['aprobado'])
                          <div class="mt-3 text-center">
                              <a href="{{ route('academico.curso.certificado', $curso->id) }}" target="_blank" class="btn btn-success btn-block shadow-sm">
                                  <i class="fas fa-certificate"></i> Descargar Certificado
                              </a>
                          </div>
                          @endif
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="info-box-content">
                                    <span class="info-box-text">Materiales</span>
                                    <span class="info-box-number">{{ count($materialesVistos) }}/{{ $curso->materiales->count() }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-content">
                                    <span class="info-box-text">Actividades</span>
                                    <span class="info-box-number">{{ count($actividadesCompletadas) }}/{{ $curso->actividades->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i> Acciones Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('academico.curso.materiales', $curso->id) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-file-alt"></i> Ver Materiales
                            </a>
                            <a href="{{ route('academico.curso.actividades', $curso->id) }}" class="btn btn-info btn-block">
                                <i class="fas fa-tasks"></i> Ver Actividades
                            </a>
                            <a href="{{ route('academico.curso.evaluaciones', $curso->id) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-clipboard-check"></i> Ver Evaluaciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido del curso en pestañas -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="materiales-tab" data-toggle="pill" href="#materiales" role="tab" aria-controls="materiales" aria-selected="true">
                                    <i class="fas fa-file-alt"></i> Materiales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="actividades-tab" data-toggle="pill" href="#actividades" role="tab" aria-controls="actividades" aria-selected="false">
                                    <i class="fas fa-tasks"></i> Actividades
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="evaluaciones-tab" data-toggle="pill" href="#evaluaciones" role="tab" aria-controls="evaluaciones" aria-selected="false">
                                    <i class="fas fa-clipboard-check"></i> Evaluaciones
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="materiales" role="tabpanel" aria-labelledby="materiales-tab">
                                <div id="materiales-content">
                                    <div class="text-center">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando materiales...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="actividades" role="tabpanel" aria-labelledby="actividades-tab">
                                <div id="actividades-content">
                                    <div class="text-center">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando actividades...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="evaluaciones" role="tabpanel" aria-labelledby="evaluaciones-tab">
                                <div id="evaluaciones-content">
                                    <div class="text-center">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p>Cargando evaluaciones...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra_css')
    <style>
        .progress {
            height: 30px;
        }
        .progress-bar {
            line-height: 30px;
            font-weight: bold;
        }
        .info-box-content {
            padding: 5px 10px;
        }
        .info-box-text {
            display: block;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 18px;
        }
        .btn-block {
            margin-bottom: 10px;
        }
    </style>
@stop

@section('extra_js')
    <script>
        $(document).ready(function() {
            // Obtener parámetro tab de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            // Si hay parámetro tab, activar esa pestaña
            if (tabParam && ['materiales', 'actividades', 'evaluaciones'].includes(tabParam)) {
                $(`#${tabParam}-tab`).tab('show');
                loadTabContent(tabParam, `#${tabParam}-content`);
            } else {
                // Cargar contenido inicial de materiales por defecto
                loadTabContent('materiales', '#materiales-content');
            }
            
            // Manejar cambio de pestañas
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr("href");
                const tabName = target.replace('#', '');
                const contentDiv = target + '-content';
                
                if ($(contentDiv).find('.fa-spinner').length > 0) {
                    loadTabContent(tabName, contentDiv);
                }
            });
        });

        // Función para cargar contenido de pestañas
        function loadTabContent(tabName, target) {
            const urls = {
                'materiales': '{{ route("academico.curso.materiales", $curso->id) }}',
                'actividades': '{{ route("academico.curso.actividades", $curso->id) }}',
                'evaluaciones': '{{ route("academico.curso.evaluaciones", $curso->id) }}'
            };

            if (urls[tabName]) {
                $.get(urls[tabName])
                    .done(function(data) {
                        $(target).html(data);
                    })
                    .fail(function() {
                        $(target).html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar el contenido</div>');
                    });
            }
        }
    </script>
@stop

