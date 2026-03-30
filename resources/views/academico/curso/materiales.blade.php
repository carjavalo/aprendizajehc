<div class="row">
    @if($materiales->count() > 0)
        @foreach($materiales as $material)
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
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card {{ $materialCompletado ? 'border-success' : ($materialBloqueado ? 'border-warning' : 'border-secondary') }}">
                    <div class="card-header {{ $materialCompletado ? 'bg-success' : ($materialBloqueado ? 'bg-warning' : 'bg-secondary') }}">
                        <h5 class="card-title text-white mb-0">
                            @if($materialBloqueado)
                                <i class="fas fa-lock"></i>
                            @else
                                <i class="fas {{ $material->tipo_icon }}"></i>
                            @endif
                            {{ $material->titulo }}
                            @if($materialCompletado)
                                <span class="float-right">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            @elseif($materialBloqueado)
                                <span class="float-right">
                                    <i class="fas fa-lock"></i>
                                </span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($materialBloqueado)
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Material bloqueado</strong><br>
                                <small>Debes completar primero: <strong>{{ $prerequisitoNombre }}</strong></small>
                            </div>
                        @endif
                        
                        @if($material->descripcion)
                            <p class="card-text">{{ Str::limit($material->descripcion, 100) }}</p>
                        @endif
                        
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-tag"></i> {{ ucfirst($material->tipo) }}
                                @if($material->archivo_size)
                                    • <i class="fas fa-file"></i> {{ $material->archivo_size_formatted }}
                                @endif
                            </small>
                        </div>
                        
                        @if($materialBloqueado)
                            {{-- Botones deshabilitados para material bloqueado --}}
                            <div class="btn-group btn-block" role="group">
                                <button type="button" class="btn btn-secondary btn-sm" disabled>
                                    <i class="fas fa-lock"></i> Bloqueado
                                </button>
                            </div>
                        @else
                            {{-- Botones normales para material desbloqueado --}}
                            <div class="btn-group btn-block" role="group">
                                @if($material->archivo_path)
                                    <a href="{{ $material->archivo_url }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                @endif
                                
                                @if($material->url_externa)
                                    <a href="{{ $material->url_externa }}" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Ver Online
                                    </a>
                                @endif
                                
                                @if(!$materialCompletado)
                                    <button type="button" class="btn btn-success btn-sm" onclick="marcarVisto({{ $material->id }})">
                                        <i class="fas fa-check"></i> Marcar Visto
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer text-muted">
                        <small>
                            @if($tienePrerequisito)
                                <i class="fas fa-link text-info"></i> 
                                Prerrequisito: {{ $prerequisitoNombre }}
                                @if($prerequisitoCompletado)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                                <br>
                            @endif
                            @if($material->created_at)
                                <i class="fas fa-calendar"></i> 
                                Publicado: {{ $material->created_at->format('d/m/Y H:i') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <h5>No hay materiales disponibles</h5>
                <p class="mb-0">El instructor aún no ha publicado materiales para este curso.</p>
            </div>
        </div>
    @endif
</div>

<script>
    function marcarVisto(materialId) {
        $.ajax({
            url: '{{ route("academico.curso.material.marcar", [$curso->id, ":materialId"]) }}'.replace(':materialId', materialId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Excelente!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Actualizar la UI sin recargar la página
                    setTimeout(function() {
                        // Recargar solo la pestaña de materiales
                        if (typeof loadTabContent === 'function') {
                            loadTabContent('materiales', '#materiales-content');
                        } else {
                            // Fallback: recargar página solo si es necesario
                            window.location.href = window.location.href;
                        }
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo marcar el material como visto'
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Ocurrió un error al procesar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    }
</script>

<style>
    .card {
        transition: transform 0.2s ease-out;
        will-change: transform;
    }
    .card:hover {
        transform: translate3d(0, -2px, 0);
    }
    .border-success {
        border-color: #28a745 !important;
    }
    .border-warning {
        border-color: #ffc107 !important;
    }
    .bg-success {
        background-color: #28a745 !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
    }
    .btn-group.btn-block {
        display: flex;
        width: 100%;
    }
    .btn-group.btn-block .btn {
        flex: 1;
    }
</style>
