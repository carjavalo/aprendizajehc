@extends('admin.layouts.master')

@section('title', 'Bandeja de Mensajes')

@section('content_header')
    <h1><i class="fas fa-inbox"></i> Bandeja de Mensajes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Estadísticas -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $mensajesRecibidos->total() }}</h3>
                        <p>Mensajes Recibidos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $mensajesEnviados->total() }}</h3>
                        <p>Mensajes Enviados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $noLeidos }}</h3>
                        <p>Mensajes No Leídos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#recibidos" data-toggle="tab">
                            <i class="fas fa-inbox"></i> Recibidos
                            @if($noLeidos > 0)
                                <span class="badge badge-danger">{{ $noLeidos }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#enviados" data-toggle="tab">
                            <i class="fas fa-paper-plane"></i> Enviados
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Mensajes Recibidos -->
                    <div class="active tab-pane" id="recibidos">
                        @forelse($mensajesRecibidos as $mensaje)
                            <div class="card mb-2 mensaje-card {{ !$mensaje->leido ? 'mensaje-no-leido' : '' }}" data-mensaje-id="{{ $mensaje->id }}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">
                                                @if(!$mensaje->leido)
                                                    <span class="badge badge-primary">Nuevo</span>
                                                @endif
                                                <strong>De:</strong> {{ $mensaje->remitente->full_name }}
                                                <small class="text-muted">({{ $mensaje->remitente->role }})</small>
                                            </h5>
                                            <p class="mb-1">{{ $mensaje->mensaje }}</p>
                                            @if($mensaje->tipo === 'grupal')
                                                <small class="text-muted">
                                                    <i class="fas fa-users"></i> Mensaje grupal: {{ $mensaje->grupo_destinatario }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> {{ $mensaje->created_at->diffForHumans() }}
                                            </small>
                                            <br>
                                            <small class="text-muted">{{ $mensaje->created_at->format('d/m/Y H:i') }}</small>
                                            @if(!$mensaje->leido)
                                                <br>
                                                <button class="btn btn-sm btn-success mt-2 marcar-leido" data-id="{{ $mensaje->id }}">
                                                    <i class="fas fa-check"></i> Marcar como leído
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No tienes mensajes recibidos</h4>
                            </div>
                        @endforelse

                        <!-- Paginación -->
                        @if($mensajesRecibidos->hasPages())
                            <div class="mt-3">
                                {{ $mensajesRecibidos->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Mensajes Enviados -->
                    <div class="tab-pane" id="enviados">
                        @forelse($mensajesEnviados as $mensaje)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">
                                                <strong>Para:</strong> 
                                                @if($mensaje->destinatario)
                                                    {{ $mensaje->destinatario->full_name }}
                                                    <small class="text-muted">({{ $mensaje->destinatario->role }})</small>
                                                @else
                                                    <span class="text-muted">Grupo: {{ $mensaje->grupo_destinatario }}</span>
                                                @endif
                                            </h5>
                                            <p class="mb-1">{{ $mensaje->mensaje }}</p>
                                            @if($mensaje->tipo === 'grupal')
                                                <small class="text-muted">
                                                    <i class="fas fa-users"></i> Mensaje grupal: {{ $mensaje->grupo_destinatario }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> {{ $mensaje->created_at->diffForHumans() }}
                                            </small>
                                            <br>
                                            <small class="text-muted">{{ $mensaje->created_at->format('d/m/Y H:i') }}</small>
                                            @if($mensaje->destinatario && $mensaje->leido)
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-check-double"></i> Leído
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-paper-plane fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No has enviado mensajes</h4>
                            </div>
                        @endforelse

                        <!-- Paginación -->
                        @if($mensajesEnviados->hasPages())
                            <div class="mt-3">
                                {{ $mensajesEnviados->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para volver al dashboard -->
        <div class="row mt-3">
            <div class="col-12">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .mensaje-no-leido {
            background-color: #f0f8ff;
            border-left: 4px solid #007bff;
        }
        
        .mensaje-card {
            transition: all 0.3s ease;
        }
        
        .mensaje-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .small-box {
            border-radius: 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Marcar mensaje como leído
            $('.marcar-leido').on('click', function() {
                const mensajeId = $(this).data('id');
                const btn = $(this);
                const card = $(`.mensaje-card[data-mensaje-id="${mensajeId}"]`);
                
                $.ajax({
                    url: `/chat/marcar-leido/${mensajeId}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remover badge "Nuevo"
                            card.find('.badge-primary').remove();
                            // Remover botón
                            btn.remove();
                            // Remover clase de no leído
                            card.removeClass('mensaje-no-leido');
                            
                            // Actualizar contador
                            const badgeNoLeidos = $('.nav-link .badge-danger');
                            if (badgeNoLeidos.length) {
                                let count = parseInt(badgeNoLeidos.text()) - 1;
                                if (count > 0) {
                                    badgeNoLeidos.text(count);
                                } else {
                                    badgeNoLeidos.remove();
                                }
                            }
                            
                            // Actualizar contador en small-box
                            const smallBoxNoLeidos = $('.bg-warning .inner h3');
                            if (smallBoxNoLeidos.length) {
                                let count = parseInt(smallBoxNoLeidos.text()) - 1;
                                smallBoxNoLeidos.text(count);
                            }
                            
                            // Mostrar notificación
                            Swal.fire({
                                icon: 'success',
                                title: 'Mensaje marcado como leído',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo marcar el mensaje como leído'
                        });
                    }
                });
            });
        });
    </script>
@stop
