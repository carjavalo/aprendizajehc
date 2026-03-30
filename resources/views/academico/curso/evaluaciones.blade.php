<div class="row">
    @if($evaluaciones->count() > 0)
        @foreach($evaluaciones as $evaluacion)
            @php
                $esCompletada = in_array($evaluacion->id, $evaluacionesCompletadas);
                $estaAbierta = !$evaluacion->fecha_cierre || now() <= $evaluacion->fecha_cierre;
                $puedeRealizar = $estaAbierta && !$esCompletada;
                $yaInicio = !$evaluacion->fecha_apertura || now() >= $evaluacion->fecha_apertura;
            @endphp
            
            <div class="col-md-12 mb-3">
                <div class="card {{ $esCompletada ? 'border-success' : ($puedeRealizar && $yaInicio ? 'border-danger' : 'border-warning') }}">
                    <div class="card-header {{ $esCompletada ? 'bg-success' : ($puedeRealizar && $yaInicio ? 'bg-danger' : 'bg-warning') }}">
                        <h5 class="card-title text-white mb-0">
                            <i class="fas fa-clipboard-check"></i>
                            {{ $evaluacion->titulo }}
                            
                            <span class="float-right">
                                @if($esCompletada)
                                    <span class="badge badge-light">
                                        <i class="fas fa-check-circle"></i> Completada
                                    </span>
                                @elseif($puedeRealizar && $yaInicio)
                                    <span class="badge badge-light">
                                        <i class="fas fa-exclamation-triangle"></i> Disponible
                                    </span>
                                @elseif(!$yaInicio)
                                    <span class="badge badge-light">
                                        <i class="fas fa-hourglass-start"></i> Próximamente
                                    </span>
                                @else
                                    <span class="badge badge-light">
                                        <i class="fas fa-lock"></i> Cerrada
                                    </span>
                                @endif
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                @if($evaluacion->descripcion)
                                    <p><strong>Descripción:</strong></p>
                                    <p>{{ $evaluacion->descripcion }}</p>
                                @endif
                                
                                @if($evaluacion->instrucciones)
                                    <p><strong>Instrucciones importantes:</strong></p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $evaluacion->instrucciones }}
                                    </div>
                                @endif
                                
                                @if($puedeRealizar && $yaInicio)
                                    <div class="alert alert-danger">
                                        <i class="fas fa-clock"></i>
                                        <strong>¡Atención!</strong> Esta evaluación está disponible para realizar. 
                                        @if($evaluacion->fecha_cierre)
                                            Tienes hasta el {{ $evaluacion->fecha_cierre->format('d/m/Y H:i') }} para completarla.
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tipo</span>
                                        <span class="info-box-number">Evaluación</span>
                                    </div>
                                </div>
                                
                                @if($evaluacion->fecha_apertura)
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Disponible desde</span>
                                            <span class="info-box-number">{{ $evaluacion->fecha_apertura->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($evaluacion->fecha_cierre)
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fecha límite</span>
                                            <span class="info-box-number">{{ $evaluacion->fecha_cierre->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($evaluacion->puntos_maximos)
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Puntos Máximos</span>
                                            <span class="info-box-number">{{ $evaluacion->puntos_maximos }}</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($evaluacion->duracion_minutos)
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Duración</span>
                                            <span class="info-box-number">{{ $evaluacion->duracion_minutos }} min</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($puedeRealizar && $yaInicio)
                            <hr>
                            <div class="evaluacion-section">
                                <h6><i class="fas fa-play-circle"></i> Realizar Evaluación</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Importante:</strong> Una vez que inicies la evaluación, deberás completarla en una sola sesión.
                                    @if($evaluacion->duracion_minutos)
                                        Tendrás {{ $evaluacion->duracion_minutos }} minutos para completarla.
                                    @endif
                                </div>
                                
                                <form id="evaluacionForm{{ $evaluacion->id }}" onsubmit="realizarEvaluacion(event, {{ $evaluacion->id }})">
                                    @csrf
                                    <div class="form-group">
                                        <label for="contenido{{ $evaluacion->id }}">Respuestas:</label>
                                        <textarea class="form-control" id="contenido{{ $evaluacion->id }}" name="contenido" rows="6" placeholder="Desarrolla tus respuestas aquí..." required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="archivo{{ $evaluacion->id }}">Archivo adjunto (si es requerido):</label>
                                        <input type="file" class="form-control-file" id="archivo{{ $evaluacion->id }}" name="archivo">
                                        <small class="form-text text-muted">Formatos permitidos: PDF, DOC, DOCX. Tamaño máximo: 10MB</small>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirmar{{ $evaluacion->id }}" required>
                                        <label class="form-check-label" for="confirmar{{ $evaluacion->id }}">
                                            Confirmo que he leído las instrucciones y estoy listo para enviar mi evaluación
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-paper-plane"></i> Enviar Evaluación
                                    </button>
                                </form>
                            </div>
                        @elseif($esCompletada)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Evaluación completada</strong> - Ya has enviado esta evaluación. Los resultados serán publicados por el instructor.
                            </div>
                        @elseif(!$yaInicio)
                            <div class="alert alert-info">
                                <i class="fas fa-hourglass-start"></i> 
                                <strong>Evaluación próximamente</strong> - Esta evaluación estará disponible a partir del {{ $evaluacion->fecha_apertura->format('d/m/Y H:i') }}.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-clock"></i> 
                                <strong>Evaluación cerrada</strong> - El plazo para realizar esta evaluación ha vencido.
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer text-muted">
                        <small>
                            <i class="fas fa-calendar"></i> 
                            Creada: {{ $evaluacion->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                <h5>No hay evaluaciones disponibles</h5>
                <p class="mb-0">El instructor aún no ha creado evaluaciones para este curso.</p>
            </div>
        </div>
    @endif
</div>

<script>
    function realizarEvaluacion(event, evaluacionId) {
        event.preventDefault();
        
        // Confirmación adicional para evaluaciones
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Una vez enviada la evaluación no podrás modificar tus respuestas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, enviar evaluación',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarEvaluacion(evaluacionId);
            }
        });
    }
    
    function enviarEvaluacion(evaluacionId) {
        const form = document.getElementById('evaluacionForm' + evaluacionId);
        const formData = new FormData(form);
        
        // Mostrar loading
        Swal.fire({
            title: 'Enviando evaluación...',
            text: 'Por favor espera mientras procesamos tu evaluación.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("academico.curso.actividad.entregar", [$curso->id, ":evaluacionId"]) }}'.replace(':evaluacionId', evaluacionId),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Evaluación enviada!',
                        text: 'Tu evaluación ha sido enviada correctamente. Los resultados serán publicados por el instructor.',
                        timer: 5000,
                        showConfirmButton: true
                    });
                    
                    // Recargar la pestaña de evaluaciones
                    setTimeout(function() {
                        loadTabContent('evaluaciones', '#evaluaciones-content');
                        // También actualizar progreso
                        location.reload();
                    }, 5000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo enviar la evaluación'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Ocurrió un error al procesar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    }
</script>

<style>
    .info-box {
        display: block;
        min-height: 60px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 2px;
        margin-bottom: 10px;
        padding: 10px;
    }
    .info-box-content {
        padding: 5px 10px;
        margin-left: 0;
    }
    .info-box-text {
        display: block;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #666;
    }
    .info-box-number {
        display: block;
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }
    .evaluacion-section {
        background-color: #fff5f5;
        padding: 20px;
        border-radius: 5px;
        border: 2px solid #fed7d7;
    }
    .btn-danger.btn-lg {
        font-size: 1.1em;
        padding: 12px 30px;
    }
</style>
