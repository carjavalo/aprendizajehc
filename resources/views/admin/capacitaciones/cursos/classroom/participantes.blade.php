<!-- Vista de Participantes del Curso -->
<div class="row">
    <div class="col-md-8">
        <!-- Lista de Estudiantes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Estudiantes Inscritos</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $estudiantes->count() }} estudiantes</span>
                </div>
            </div>
            <div class="card-body">
                @forelse($estudiantes as $estudiante)
                    <div class="participante-item mb-3 p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <i class="fas fa-user-circle fa-3x text-secondary"></i>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1">{{ $estudiante->full_name }}</h6>
                                <p class="text-muted mb-0">{{ $estudiante->email }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Inscrito: {{ $estudiante->pivot->fecha_inscripcion ? \Carbon\Carbon::parse($estudiante->pivot->fecha_inscripcion)->format('d/m/Y') : 'N/A' }}
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge badge-{{ $estudiante->pivot->estado === 'activo' ? 'success' : ($estudiante->pivot->estado === 'completado' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($estudiante->pivot->estado) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <div class="progress mb-1">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $estudiante->pivot->progreso }}%" 
                                         aria-valuenow="{{ $estudiante->pivot->progreso }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $estudiante->pivot->progreso }}% completado</small>
                            </div>
                            <div class="col-md-2 text-right">
                                @if($esInstructor)
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" onclick="verDetalleEstudiante({{ $estudiante->id }})">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="enviarMensaje({{ $estudiante->id }})">
                                                <i class="fas fa-envelope"></i> Enviar Mensaje
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" onclick="removerEstudiante({{ $estudiante->id }})">
                                                <i class="fas fa-user-times"></i> Remover del Curso
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    Última actividad: {{ $estudiante->pivot->ultima_actividad ? \Carbon\Carbon::parse($estudiante->pivot->ultima_actividad)->diffForHumans() : 'Nunca' }}
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                @if($estudiante->pivot->progreso == 100)
                                    <span class="badge badge-success">
                                        <i class="fas fa-trophy"></i> Curso Completado
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay estudiantes inscritos</h5>
                        <p class="text-muted">Los estudiantes aparecerán aquí cuando se inscriban al curso.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Estadísticas de Participación -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Estadísticas de Participación</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Estudiantes</span>
                        <span class="info-box-number">{{ $estudiantes->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Activos</span>
                        <span class="info-box-number">{{ $estudiantes->where('pivot.estado', 'activo')->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Completados</span>
                        <span class="info-box-number">{{ $estudiantes->where('pivot.estado', 'completado')->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Inactivos</span>
                        <span class="info-box-number">{{ $estudiantes->where('pivot.estado', 'inactivo')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progreso Promedio -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Progreso Promedio</h3>
            </div>
            <div class="card-body">
                @php
                    $progresoPromedio = $estudiantes->avg('pivot.progreso') ?? 0;
                @endphp
                
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-gradient-success progress-bar-striped" 
                         role="progressbar" 
                         style="width: {{ $progresoPromedio }}%" 
                         aria-valuenow="{{ $progresoPromedio }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ round($progresoPromedio, 1) }}%
                    </div>
                </div>
                
                <p class="text-muted text-center">
                    El progreso promedio de todos los estudiantes es {{ round($progresoPromedio, 1) }}%
                </p>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Actividad Reciente</h3>
            </div>
            <div class="card-body">
                @php
                    $estudiantesRecientes = $estudiantes->sortByDesc('pivot.ultima_actividad')->take(5);
                @endphp
                
                @forelse($estudiantesRecientes as $estudiante)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-2x text-secondary mr-2"></i>
                            <div>
                                <strong>{{ Str::limit($estudiante->name, 15) }}</strong><br>
                                <small class="text-muted">
                                    {{ $estudiante->pivot->ultima_actividad ? \Carbon\Carbon::parse($estudiante->pivot->ultima_actividad)->diffForHumans() : 'Sin actividad' }}
                                </small>
                            </div>
                        </div>
                        <span class="badge badge-info">
                            {{ $estudiante->pivot->progreso }}%
                        </span>
                    </div>
                    @if(!$loop->last)<hr>@endif
                @empty
                    <p class="text-muted text-center">
                        <i class="fas fa-clock"></i><br>
                        No hay actividad reciente
                    </p>
                @endforelse
            </div>
        </div>

        <!-- Instructor -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Instructor</h3>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-4x text-primary mb-2"></i>
                <h6>{{ $curso->instructor->full_name }}</h6>
                <p class="text-muted">{{ $curso->instructor->email }}</p>
                
                @if(!$esInstructor)
                    <button class="btn btn-primary btn-sm" onclick="contactarInstructor()">
                        <i class="fas fa-envelope"></i> Contactar
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Funciones para instructores
    window.verDetalleEstudiante = function(estudianteId) {
        // Implementar vista detallada del estudiante
        Swal.fire('Información', 'Funcionalidad en desarrollo', 'info');
    };
    
    window.enviarMensaje = function(estudianteId) {
        // Implementar envío de mensajes
        Swal.fire('Mensaje', 'Funcionalidad en desarrollo', 'info');
    };
    
    window.removerEstudiante = function(estudianteId) {
        Swal.fire({
            title: '¿Remover estudiante?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implementar remoción de estudiante
                Swal.fire('Información', 'Funcionalidad en desarrollo', 'info');
            }
        });
    };
    
    window.contactarInstructor = function() {
        // Implementar contacto con instructor
        Swal.fire('Contacto', 'Funcionalidad en desarrollo', 'info');
    };
});
</script>
