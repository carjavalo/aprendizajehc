@extends('adminlte::page')

@section('title', 'Entregas - ' . $actividad->titulo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check"></i> Entregas de Actividad</h1>
        <a href="{{ route('capacitaciones.cursos.classroom', $curso->id) }}#actividades" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Curso
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Información de la Actividad -->
    <div class="card mb-4" style="border-left: 4px solid #2e3a75;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="mb-2">{{ $actividad->titulo }}</h3>
                    <p class="text-muted mb-3">{{ $curso->nombre }} • {{ $curso->area->categoria->nombre ?? 'Sin categoría' }}</p>
                    <p class="mb-0">{{ $actividad->descripcion }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="mb-2">
                        <small class="text-muted">Creado el:</small><br>
                        <strong>{{ $actividad->created_at->format('d/m/Y') }}</strong>
                    </div>
                    {!! $actividad->tipo_badge !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalEstudiantes }}</h3>
                    <p>Total Estudiantes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $entregasRealizadas }}</h3>
                    <p>Entregas Realizadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $entregasPendientes }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box" style="background-color: #2e3a75; color: white;">
                <div class="inner">
                    <h3>{{ number_format($promedioCalificacion, 1) }}%</h3>
                    <p>Promedio de Calificación</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Distribución de Calificaciones -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: #2e3a75; color: white;">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Distribución de Calificaciones</h3>
                </div>
                <div class="card-body">
                    <canvas id="gradeDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado de Entregas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: #2e3a75; color: white;">
                    <h3 class="card-title"><i class="fas fa-pie-chart"></i> Estado de Entregas</h3>
                </div>
                <div class="card-body">
                    <canvas id="submissionStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Entregas -->
    <div class="card">
        <div class="card-header" style="background-color: #2e3a75; color: white;">
            <h3 class="card-title"><i class="fas fa-list"></i> Entregas de Estudiantes</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchStudent" class="form-control" placeholder="Buscar estudiante...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="entregasTable">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th>Estudiante</th>
                            <th>Fecha de Entrega</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entregas as $entrega)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar mr-2">
                                            @if(isset($entrega->estudiante->profile_photo_path) && $entrega->estudiante->profile_photo_path)
                                                <img src="{{ url('media/' . $entrega->estudiante->profile_photo_path) }}" 
                                                     alt="{{ $entrega->estudiante->name }}" 
                                                     class="img-circle elevation-2" 
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="img-circle elevation-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px; background-color: #2e3a75; color: white; font-weight: bold; font-size: 14px;">
                                                    {{ strtoupper(substr($entrega->estudiante->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-weight-bold">{{ $entrega->estudiante->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($entrega->fecha_entrega)
                                        <small class="text-muted">
                                            {{ $entrega->fecha_entrega->format('d/m/Y, h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entrega->estado === 'entregado')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Entregado
                                        </span>
                                    @elseif($entrega->estado === 'tarde')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Tarde
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($entrega->calificacion !== null)
                                        <span class="font-weight-bold" style="color: #2e3a75;">
                                            {{ $entrega->calificacion }}/{{ $actividad->puntos_maximos ?? 100 }}
                                        </span>
                                    @else
                                        <span class="text-muted font-weight-bold">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($entrega->estado !== 'pendiente')
                                        <button class="btn btn-sm" style="background-color: #2e3a75; color: white;" 
                                                onclick="verEntrega({{ $entrega->id }})">
                                            <i class="fas fa-eye"></i> Ver Trabajo
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-hourglass-half"></i> Sin Entregar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay entregas registradas aún</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entregas->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando {{ $entregas->firstItem() }} a {{ $entregas->lastItem() }} de {{ $entregas->total() }} estudiantes
                    </small>
                    {{ $entregas->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .small-box .icon {
        font-size: 70px;
    }
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table thead th {
        border-bottom: 2px solid #2e3a75;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .table tbody tr:hover {
        background-color: #f8fafc;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Distribución de Calificaciones
    const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
    const gradeChart = new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: ['0-60', '61-70', '71-80', '81-90', '91-100'],
            datasets: [{
                label: 'Número de Estudiantes',
                data: {!! json_encode($distribucionCalificaciones, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(46, 58, 117, 0.7)',
                    'rgba(16, 185, 129, 0.7)'
                ],
                borderColor: [
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(46, 58, 117)',
                    'rgb(16, 185, 129)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Estado de Entregas
    const statusCtx = document.getElementById('submissionStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['A Tiempo', 'Tarde', 'Pendiente'],
            datasets: [{
                data: [
                    {{ $entregasATiempo }},
                    {{ $entregasTarde }},
                    {{ $entregasPendientes }}
                ],
                backgroundColor: [
                    'rgba(46, 58, 117, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(148, 163, 184, 0.8)'
                ],
                borderColor: [
                    'rgb(46, 58, 117)',
                    'rgb(245, 158, 11)',
                    'rgb(148, 163, 184)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Búsqueda de estudiantes
    document.getElementById('searchStudent').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#entregasTable tbody tr');
        
        rows.forEach(row => {
            const studentName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            if (studentName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Ver entrega individual
    function verEntrega(entregaId) {
        // Aquí puedes implementar un modal o redirección para ver el detalle de la entrega
        Swal.fire({
            title: 'Ver Entrega',
            text: 'Funcionalidad de visualización de entrega individual próximamente',
            icon: 'info'
        });
    }
</script>
@stop
