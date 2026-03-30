{{-- Vista parcial del detalle de una actividad individual --}}
<div class="card actividad-detalle">
    <div class="card-header {{ $esCompletada ? 'bg-success' : ($estaAbierta ? '

bg-primary' : 'bg-warning') }}">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-white">
                <i class="{{ $actividad->tipo_icon }}"></i>
                {{ $actividad->titulo }}
            </h4>
            <span class="badge badge-light">
                @if($esCompletada)
                    <i class="fas fa-check-circle text-success"></i> Completada
                @elseif($estaAbierta)
                    <i class="fas fa-clock text-warning"></i> Abierta
                @else
                    <i class="fas fa-lock text-muted"></i> Cerrada
                @endif
            </span>
        </div>
    </div>
    
    <div class="card-body">
        <div class="row">
            {{-- Contenido Principal --}}
            <div class="col-md-8">
                @if($actividad->descripcion)
                    <div class="mb-3">
                        <h6><i class="fas fa-info-circle"></i> Descripción:</h6>
                        <p>{{ $actividad->descripcion }}</p>
                    </div>
                @endif
                
                @if($actividad->instrucciones)
                    <div class="alert alert-info mb-3">
                        <h6><i class="fas fa-list-ol"></i> Instrucciones:</h6>
                        {{ $actividad->instrucciones }}
                    </div>
                @endif
                
                {{-- Sección de Entrega --}}
                @if($puedeEntregar)
                    <hr>
                    @if($actividad->tipo === 'quiz')
                        {{-- Vista de Quiz Interactivo --}}
                        @if($actividad->habilitado)
                            <div class="entrega-section bg-gradient-quiz p-4 rounded">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-question-circle"></i> Quiz Interactivo
                                    </h5>
                                    @if($actividad->contenido_json && isset($actividad->contenido_json['duration']))
                                        <span class="badge badge-light">
                                            <i class="fas fa-clock"></i> {{ $actividad->contenido_json['duration'] }} minutos
                                        </span>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-warning btn-lg btn-block" 
                                        onclick="iniciarQuiz({{ $actividad->id }})">
                                    <i class="fas fa-play-circle"></i> Iniciar Quiz
                                </button>
                                <textarea id="quiz-data-{{ $actividad->id }}" class="d-none">{!! json_encode($actividad, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</textarea>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-lock"></i> Este quiz no está habilitado por el instructor
                            </div>
                        @endif
                    @else
                        {{-- Formulario estándar para otras actividades --}}
                        <div class="entrega-section p-4 rounded">
                            <h6 class="mb-3"><i class="fas fa-upload"></i> Entregar Actividad</h6>
                            <form id="entregaForm{{ $actividad->id }}" onsubmit="entregarActividad(event, {{ $actividad->id }})">
                                @csrf
                                <div class="form-group">
                                    <label for="contenido{{ $actividad->id }}">
                                        <i class="fas fa-align-left"></i> Respuesta/Contenido:
                                    </label>
                                    <textarea class="form-control" id="contenido{{ $actividad->id }}" 
                                              name="contenido" rows="5" 
                                              placeholder="Escribe tu respuesta aquí..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="observaciones{{ $actividad->id }}">
                                        <i class="fas fa-sticky-note"></i> Observaciones (Opcional):
                                    </label>
                                    <textarea class="form-control" id="observaciones{{ $actividad->id }}" 
                                              name="observaciones" rows="3" 
                                              placeholder="Agrega cualquier comentario, aclaración o nota que quieras compartir con tu docente sobre esta entrega..."></textarea>
                                    <small class="form-text text-muted">
                                        Este espacio te permite explicar algo sobre tu entrega, como dificultades encontradas, 
                                        consultas o aclaraciones que el docente deba saber.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="archivo{{ $actividad->id }}">
                                        <i class="fas fa-paperclip"></i> Archivo adjunto (opcional):
                                    </label>
                                    <input type="file" class="form-control-file" 
                                           id="archivo{{ $actividad->id }}" name="archivo">
                                    <small class="form-text text-muted">
                                        Formatos permitidos: PDF, DOC, DOCX, JPG, PNG. Tamaño máximo: 10MB
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> Entregar Actividad
                                </button>
                            </form>
                        </div>
                    @endif
                @elseif($esCompletada)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Actividad completada</strong> - Ya has entregado esta actividad exitosamente.
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-lock"></i> 
                        <strong>Actividad cerrada</strong> - El plazo de entrega ha vencido.
                    </div>
                @endif
            </div>
            
            {{-- Panel Lateral de Información --}}
            <div class="col-md-4">
                <div class="info-card">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                    
                    <div class="info-item">
                        <small class="text-muted">Tipo</small>
                        <p class="mb-2"><strong>{{ ucfirst($actividad->tipo) }}</strong></p>
                    </div>
                    
                    @if($actividad->fecha_apertura)
                        <div class="info-item">
                            <small class="text-muted">Apertura</small>
                            <p class="mb-2">
                                <i class="fas fa-calendar-alt"></i> 
                                {{ $actividad->fecha_apertura->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif
                    
                    @if($actividad->fecha_cierre)
                        <div class="info-item">
                            <small class="text-muted">Cierre</small>
                            <p class="mb-2">
                                <i class="fas fa-calendar-times"></i> 
                                {{ $actividad->fecha_cierre->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif
                    
                    @if($actividad->puntos_maximos)
                        <div class="info-item">
                            <small class="text-muted">Puntos Máximos</small>
                            <p class="mb-2">
                                <i class="fas fa-star"></i> 
                                <strong class="text-primary">{{ $actividad->puntos_maximos }} puntos</strong>
                            </p>
                        </div>
                    @endif
                    
                    <div class="info-item">
                        <small class="text-muted">Creada</small>
                        <p class="mb-0">
                            <i class="fas fa-clock"></i> 
                            {{ $actividad->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-footer">
        <div class="row">
            <div class="col-6">
                @if($indiceActual > 0)
                    <button class="btn btn-outline-primary btn-actividad-anterior">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                @endif
            </div>
            <div class="col-6 text-right">
                @if($indiceActual < $totalActividades - 1)
                    <button class="btn btn-outline-primary btn-actividad-siguiente">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .actividad-detalle {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .entrega-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    
    .bg-gradient-quiz {
        background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
        color: #fff;
    }
    
    .info-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .info-item {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
</style>


