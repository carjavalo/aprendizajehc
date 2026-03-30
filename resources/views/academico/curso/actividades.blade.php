<div class="row">
    @if($actividades->count() > 0)
        {{-- Panel Izquierdo: Lista de Actividades --}}
        <div class="col-md-4">
            <div class="card sticky-actividades">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-1">
                        <i class="fas fa-tasks"></i> Actividades del Curso
                    </h5>
                    <div class="progress-summary">
                        <small>
                            <i class="fas fa-check-circle"></i> 
                            {{ count($actividadesCompletadas) }} de {{ $actividades->count() }} completadas
                        </small>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $actividades->count() > 0 ? (count($actividadesCompletadas) / $actividades->count() * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Filtros --}}
                <div class="card-body p-2">
                    <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="todas">
                            <i class="fas fa-list"></i> Todas
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-filter="pendientes">
                            <i class="fas fa-clock"></i> Pendientes
                        </button>
                        <button type="button" class="btn btn-outline-success" data-filter="completadas">
                            <i class="fas fa-check"></i> Completadas
                        </button>
                    </div>
                </div>
                
                {{-- Lista de Actividades --}}
                <div class="list-group list-group-flush actividades-lista">
                    @foreach($actividades as $index => $actividad)
                        @php
                            $esCompletada = in_array($actividad->id, $actividadesCompletadas);
                            $estaAbierta = !$actividad->fecha_cierre || now() <= $actividad->fecha_cierre;
                            $estado = $esCompletada ? 'completada' : ($estaAbierta ? 'pendiente' : 'cerrada');
                        @endphp
                        
                        <a href="#" 
                           class="list-group-item list-group-item-action actividad-item {{ $index === 0 ? 'active' : '' }}" 
                           data-actividad-id="{{ $actividad->id }}"
                           data-actividad-index="{{ $index }}"
                           data-estado="{{ $estado }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        @if($esCompletada)
                                            <i class="fas fa-check-circle text-success"></i>
                                        @elseif($estaAbierta)
                                            <i class="fas fa-circle text-warning"></i>
                                        @else
                                            <i class="fas fa-lock text-muted"></i>
                                        @endif
                                        {{ Str::limit($actividad->titulo, 35) }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="{{ $actividad->tipo_icon }}"></i> {{ ucfirst($actividad->tipo) }}
                                        @if($actividad->fecha_cierre)
                                            • <i class="fas fa-calendar"></i> {{ $actividad->fecha_cierre->format('d/m/Y') }}
                                        @endif
                                    </small>
                                </div>
                                @if($actividad->puntos_maximos)
                                    <span class="badge badge-info">{{ $actividad->puntos_maximos }}pts</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Panel Derecho: Área de Trabajo --}}
        <div class="col-md-8">
            <div id="actividad-workspace">
                @php
                    $primeraActividad = $actividades->first();
                    $esCompletada = in_array($primeraActividad->id, $actividadesCompletadas);
                    $estaAbierta = !$primeraActividad->fecha_cierre || now() <= $primeraActividad->fecha_cierre;
                    $puedeEntregar = $estaAbierta && !$esCompletada;
                @endphp
                
                @include('academico.curso.partials.actividad-detalle', [
                    'actividad' => $primeraActividad,
                    'esCompletada' => $esCompletada,
                    'estaAbierta' => $estaAbierta,
                    'puedeEntregar' => $puedeEntregar,
                    'curso' => $curso,
                    'totalActividades' => $actividades->count(),
                    'indiceActual' => 0
                ])
            </div>
        </div>
    @else
        {{-- Mensaje cuando no hay actividades --}}
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-tasks fa-3x mb-3"></i>
                <h5>No hay actividades disponibles</h5>
                <p class="mb-0">El instructor aún no ha creado actividades para este curso.</p>
            </div>
        </div>
    @endif
</div>

{{-- Detalles de actividades ocultos (para carga dinámica) --}}
<div id="actividades-data" style="display: none;">
    @foreach($actividades as $index => $actividad)
        @php
            $esCompletada = in_array($actividad->id, $actividadesCompletadas);
            $estaAbierta = !$actividad->fecha_cierre || now() <= $actividad->fecha_cierre;
            $puedeEntregar = $estaAbierta && !$esCompletada;
        @endphp
        
        <div class="actividad-data" data-actividad-id="{{ $actividad->id }}">
            @include('academico.curso.partials.actividad-detalle', [
                'actividad' => $actividad,
                'esCompletada' => $esCompletada,
                'estaAbierta' => $estaAbierta,
                'puedeEntregar' => $puedeEntregar,
                'curso' => $curso,
                'totalActividades' => $actividades->count(),
                'indiceActual' => $index
            ])
        </div>
    @endforeach
</div>

<script>
    $(document).ready(function() {
        // Manejar clic en actividad de la lista
        $('.actividad-item').on('click', function(e) {
            e.preventDefault();
            
            const actividadId = $(this).data('actividad-id');
            
            // Marcar como activa visualmente
            $('.actividad-item').removeClass('active');
            $(this).addClass('active');
            
            // Cargar contenido de la actividad
            cargarActividadLocal(actividadId);
            
            // Scroll suave nativo (mejor rendimiento)
            const workspace = document.getElementById('actividad-workspace');
            if (workspace) {
                workspace.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
            }
        });
        
        // Función para cargar actividad desde datos locales
        function cargarActividadLocal(actividadId) {
            const actividadData = $(`.actividad-data[data-actividad-id="${actividadId}"]`).html();
            
            if (actividadData) {
                $('#actividad-workspace').html(actividadData);
            }
        }
        
        // Filtros de actividades
        $('[data-filter]').on('click', function() {
            const filtro = $(this).data('filter');
            
            // Actualizar botón activo
            $('[data-filter]').removeClass('active');
            $(this).addClass('active');
            
            // Filtrar actividades
            if (filtro === 'todas') {
                $('.actividad-item').show();
            } else {
                $('.actividad-item').hide();
                $(`.actividad-item[data-estado="${filtro === 'pendientes' ? 'pendiente' : 'completada'}"]`).show();
            }
            
            // Si no hay actividades visibles, mostrar mensaje
            if ($('.actividad-item:visible').length === 0) {
                // Opcional: mostrar mensaje de "no hay actividades"
            } else {
                // Seleccionar la primera visible
                $('.actividad-item:visible').first().click();
            }
        });
        
        // Navegación con botones anterior/siguiente
        $(document).on('click', '.btn-actividad-anterior', function() {
            const actividadActual = $('.actividad-item.active');
            const actividadAnterior = actividadActual.prevAll('.actividad-item:visible').first();
            
            if (actividadAnterior.length > 0) {
                actividadAnterior.click();
            }
        });
        
        $(document).on('click', '.btn-actividad-siguiente', function() {
            const actividadActual = $('.actividad-item.active');
            const actividadSiguiente = actividadActual.nextAll('.actividad-item:visible').first();
            
            if (actividadSiguiente.length > 0) {
                actividadSiguiente.click();
            }
        });
    });
    
    // Mantener funciones existentes de entrega y quiz
    function entregarActividad(event, actividadId) {
        event.preventDefault();
        
        const form = document.getElementById('entregaForm' + actividadId);
        const formData = new FormData(form);
        
        Swal.fire({
            title: 'Entregando actividad...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("academico.curso.actividad.entregar", [$curso->id, ":actividadId"]) }}'.replace(':actividadId', actividadId),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actividad entregada!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo entregar la actividad'
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
    /* Estilos para el panel de actividades */
    .sticky-actividades {
        position: sticky;
        top: 70px;
        max-height: calc(100vh - 90px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        will-change: transform;
    }
    
    .actividades-lista {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
        flex: 1;
    }
    
    .actividad-item {
        cursor: pointer;
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    .actividad-item.active {
        background-color: #e7f3ff;
        border-left-color: #007bff;
        font-weight: 500;
    }
    
    .actividad-item:hover {
        background-color: #f8f9fa;
    }
    
    .progress-summary small {
        font-size: 0.85rem;
    }
    
    /* Área de trabajo */
    #actividad-workspace {
        min-height: 400px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .sticky-actividades {
            position: relative;
            top: 0;
            max-height: none;
            margin-bottom: 20px;
        }
        
        .actividades-lista {
            max-height: 300px;
        }
    }
</style>

{{-- Scripts para Quiz --}}
@include('academico.curso.partials.quiz-scripts')
